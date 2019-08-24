<?php

namespace Timiki\Bundle\RpcServerBundle\Handler;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Timiki\Bundle\RpcServerBundle\Event;
use Timiki\Bundle\RpcServerBundle\Exceptions;
use Timiki\Bundle\RpcServerBundle\Mapper\Mapper;
use Timiki\Bundle\RpcServerBundle\Mapper\MethodMetaData;
use Timiki\Bundle\RpcServerBundle\Serializer\SerializerInterface;
use Timiki\Bundle\RpcServerBundle\Traits\CacheTrait;
use Timiki\Bundle\RpcServerBundle\Traits\EventDispatcherTrait;
use Timiki\Bundle\RpcServerBundle\Traits\StopwatchTrait;
use Timiki\RpcCommon\JsonRequest;
use Timiki\RpcCommon\JsonResponse;

class JsonHandler implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use CacheTrait;
    use StopwatchTrait;
    use EventDispatcherTrait;

    /**
     * Rpc mapper.
     *
     * @var null|Mapper
     */
    private $mapper;

    /**
     * Serializer.
     *
     * @var null|SerializerInterface
     */
    private $serializer;

    /**
     * JsonHandler constructor.
     *
     * @param Mapper                   $mapper
     * @param null|SerializerInterface $serializer
     */
    public function __construct(Mapper $mapper, SerializerInterface $serializer = null)
    {
        $this->mapper = $mapper;
        $this->serializer = $serializer;
    }

    /**
     * Is debug.
     *
     * @return bool
     */
    public function isDebug()
    {
        if (null !== $this->container && $this->container->has('kernel')) {
            return $this->container->get('kernel')->isDebug();
        }

        return true;
    }

    /**
     * Get serializer.
     *
     * @return null|SerializerInterface
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Serialize data.
     *
     * @param mixed $data
     *
     * @return null|array|int|\JsonSerializable|string
     */
    public function serialize($data)
    {
        if (!$this->serializer || \is_numeric($data) || \is_string($data) || empty($data) || $data instanceof \JsonSerializable) {
            return $data;
        }

        return $this->getSerializer()->serialize($data);
    }

    /**
     * Get mapper.
     *
     * @return null|Mapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * Create new JsonResponse from exception.
     *
     * @param \Exception       $exception
     * @param null|JsonRequest $jsonRequest
     *
     * @return JsonResponse
     */
    public function createJsonResponseFromException(\Exception $exception, JsonRequest $jsonRequest = null)
    {
        $jsonResponse = new JsonResponse();
        $jsonResponse->setRequest($jsonRequest);

        if ($exception instanceof Exceptions\ErrorException) {
            $jsonResponse->setErrorCode(0 !== $exception->getCode() ? $exception->getCode() : -32603);
            $jsonResponse->setErrorMessage(!empty($exception->getMessage()) ? $exception->getMessage() : 'Internal error');
            $jsonResponse->setErrorData($exception->getData());
        } else {
            $jsonResponse->setErrorCode(0 !== $exception->getCode() ? $exception->getCode() : -32603);
            $jsonResponse->setErrorMessage(!empty($exception->getMessage()) ? $exception->getMessage() : 'Internal error');
        }

        return $jsonResponse;
    }

    /**
     * Handle json request.
     *
     * @param JsonRequest|JsonRequest[] $jsonRequest
     *
     * @return JsonResponse|JsonResponse[]
     */
    public function handleJsonRequest($jsonRequest)
    {
        // Batch requests
        if (\is_array($jsonRequest)) {
            $jsonResponse = [];
            foreach ($jsonRequest as $request) {
                $jsonResponse[] = $this->handleJsonRequest($request);
            }

            return $jsonResponse;
        }

        if ($this->stopwatch) {
            $this->stopwatch->start('rpc.execute');
        }

        try {
            $this->dispatch(new Event\JsonRequestEvent($jsonRequest));

            $metadata = $this->getMethod($jsonRequest);
            $isCache = $this->isCacheSupport($jsonRequest);
            $cacheId = $jsonRequest->getHash();

            $jsonResponse = new JsonResponse($jsonRequest);
            // Cache
            if (true === $isCache && true === $this->getCache()->contains($cacheId)) {
                $jsonResponse->setResult($this->getCache()->fetch($cacheId));
                $isCache = false; // we don't want warm check without left ttl
            }

            $result = $jsonResponse->getResult();
            if (null === $result) { // if not cache
                $result = $this->executeJsonRequest($metadata, $jsonRequest);
            }

            if ($result instanceof JsonResponse) {
                $jsonResponse = $result;
                $jsonResponse->setRequest($jsonRequest);
            } else {
                $jsonResponse->setResult($this->serialize($result));
            }

            // Save cache
            $isCache && $this->cache->save($cacheId, $jsonResponse->getResult(), $metadata->getCache());
        } catch (\Exception $exception) {
            $jsonResponse = $this->createJsonResponseFromException($exception, $jsonRequest);
        }

        $this->dispatch(new Event\JsonResponseEvent($jsonResponse));

        if ($this->stopwatch) {
            $this->stopwatch->stop('rpc.execute');
        }

        return $jsonResponse;
    }

    /**
     * Check is cache support for JsonRequest.
     *
     * @param JsonRequest $jsonRequest
     *
     * @return bool
     */
    private function isCacheSupport(JsonRequest $jsonRequest)
    {
        try {
            return $jsonRequest->getId()
                && null !== $this->getMethod($jsonRequest)->getCache()
                && !$this->isDebug()
                && $this->getCache();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param JsonRequest $jsonRequest
     *
     * @return MethodMetaData
     */
    private function getMethod($jsonRequest): MethodMetaData
    {
        $method = $jsonRequest->getMethod();

        if (false === $this->mapper->hasMethod($method)) {
            throw new Exceptions\MethodNotFoundException($jsonRequest->getMethod());
        }

        return $this->mapper->getMethod($method);
    }

    /**
     * @param MethodMetaData $methodMetaData
     * @param JsonRequest    $jsonRequest
     *
     * @return mixed
     */
    private function executeJsonRequest(MethodMetaData $methodMetaData, JsonRequest $jsonRequest)
    {
        $method = clone $this->container->get($methodMetaData->getMethod());

        // Inject container
        if ($method instanceof ContainerAwareInterface && null !== $this->container) {
            $method->setContainer($this->container);
        }

        // Dispatch execute json
        $this->dispatch(new Event\JsonPreExecuteEvent($method, $methodMetaData, $jsonRequest));

        return $method->{$methodMetaData->getExecute()}();
    }
}
