<?php

namespace Timiki\Bundle\RpcServerBundle\Handler;

use Timiki\Bundle\RpcServerBundle\Mapper\Mapper;
use Timiki\Bundle\RpcServerBundle\Serializer\SerializerInterface;
use Timiki\Bundle\RpcServerBundle\Exceptions;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Timiki\Bundle\RpcServerBundle\Traits\CacheTrait;
use Timiki\Bundle\RpcServerBundle\Traits\EventDispatcherTrait;
use Timiki\Bundle\RpcServerBundle\Traits\StopwatchTrait;
use Timiki\Bundle\RpcServerBundle\Event;
use Timiki\RpcCommon\JsonResponse;
use Timiki\RpcCommon\JsonRequest;

class JsonHandler implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use CacheTrait;
    use StopwatchTrait;
    use EventDispatcherTrait;

    /**
     * Rpc mapper.
     *
     * @var Mapper|null
     */
    protected $mapper;

    /**
     * Serializer.
     *
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * JsonHandler constructor.
     *
     * @param Mapper $mapper
     * @param SerializerInterface|null $serializer
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
        if ($this->container && $this->container->has('kernel')) {
            return $this->container->get('kernel')->isDebug();
        }

        return true;
    }

    /**
     * Get serializer.
     *
     * @return SerializerInterface|null
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Serialize data.
     *
     * @param $data
     * @return array|string|integer|null|\JsonSerializable
     */
    public function serialize($data)
    {
        if (!$this->serializer || is_numeric($data) || is_string($data) || empty($data) || $data instanceof \JsonSerializable) {
            return $data;
        }

        return $this->getSerializer()->serialize($data);
    }

    /**
     * Get mapper.
     *
     * @return Mapper|null
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * Create new JsonResponse from exception.
     *
     * @param \Exception $exception
     * @param JsonRequest|null $jsonRequest
     *
     * @return JsonResponse
     */
    public function createJsonResponseFromException(\Exception $exception, JsonRequest $jsonRequest = null)
    {
        $jsonResponse = new JsonResponse();

        if ($exception instanceof Exceptions\ErrorException) {
            $jsonResponse->setErrorCode($exception->getCode());
            $jsonResponse->setErrorMessage($exception->getMessage());
            $jsonResponse->setErrorData($exception->getData());
        } else {
            $jsonResponse->setErrorCode(-32603);
            $jsonResponse->setErrorMessage($this->isDebug() ? $exception->getMessage() : 'Internal error');
        }

        if ($jsonRequest) {
            $jsonResponse->setId($jsonRequest->getId());
        }

        return $jsonResponse;
    }

    /**
     * Load methods metadata.
     *
     * @return array
     * @throws \Timiki\Bundle\RpcServerBundle\Exceptions\InvalidMappingException
     */
    protected function loadMetadata()
    {
        return $this->mapper === null ? [] : $this->mapper->loadMetadata();
    }

    /**
     * Handle json request.
     *
     * @param JsonRequest|JsonRequest[] $jsonRequest
     * @return JsonResponse|JsonResponse[]
     */
    public function handleJsonRequest($jsonRequest)
    {
        // Batch requests
        if (is_array($jsonRequest)) {

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

            $this->dispatch(Event\JsonRequestEvent::EVENT, new Event\JsonRequestEvent($jsonRequest));

            $jsonResponse = new JsonResponse($jsonRequest);
            $object = $this->getMethod($jsonRequest);
            $metadata = $this->mapper->loadObjectMetadata($object);

            // Cache
            if ($this->isCacheSupport($jsonRequest)) {
                $jsonResponse->setResult($this->getCache()->fetch($jsonRequest->getHash()));
            } else {

                $result = $this->executeJsonRequest($object, $jsonRequest);

                if ($result instanceof JsonResponse) {
                    $jsonResponse = $result;
                } else {
                    $jsonResponse->setResult($this->serialize($result));
                }

            }

            // Save cache
            if ($this->isCacheSupport($jsonRequest)) {
                $this->cache->save($jsonRequest->getHash(), $jsonResponse->getResult(), $metadata['cache']->lifetime);
            }

            $this->dispatch(Event\JsonResponseEvent::EVENT, new Event\JsonResponseEvent($jsonResponse));

        } catch (\Exception $exception) {
            $jsonResponse = $this->createJsonResponseFromException($exception, $jsonRequest);
        }

        if ($this->stopwatch) {
            $this->stopwatch->stop('rpc.execute');
        }

        return $jsonResponse;
    }

    /**
     * Check is cache support for JsonRequest.
     *
     * @param JsonRequest $jsonRequest
     * @return bool
     */
    private function isCacheSupport(JsonRequest $jsonRequest)
    {
        try {

            $object = $this->getMethod($jsonRequest);
            $metadata = $this->mapper->loadObjectMetadata($object);

            return $jsonRequest->getId()
                && $metadata['cache']
                && !$this->isDebug()
                && $this->getCache();

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get RPC method from request.
     *
     * @param JsonRequest $jsonRequest
     *
     * @return object
     * @throws \Timiki\Bundle\RpcServerBundle\Exceptions\InvalidMappingException
     */
    protected function getMethod($jsonRequest)
    {
        $method = $jsonRequest->getMethod();
        $metadata = $this->loadMetadata();

        if (isset($metadata['methods'][$method])) {
            return new $metadata['methods'][$method]['class'];
        }

        throw new Exceptions\MethodNotFoundException($jsonRequest->getMethod());
    }

    /**
     * Execute json request.
     *
     * @param             $object
     * @param JsonRequest $jsonRequest
     *
     * @return mixed
     * @throws \Timiki\Bundle\RpcServerBundle\Exceptions\InvalidMappingException
     */
    protected function executeJsonRequest($object, JsonRequest $jsonRequest)
    {
        $metadata = $this->mapper->loadObjectMetadata($object);

        // Inject container

        if ($object instanceof ContainerAwareInterface && $this->container) {
            $object->setContainer($this->container);
        }

        // Get params

        if (array_keys($jsonRequest->getParams()) === range(0, count($jsonRequest->getParams()) - 1)) {

            // Given only values

            $values = $jsonRequest->getParams();
            $params = [];

            foreach (array_keys($metadata['params']) as $id => $key) {
                if (isset($values[$id])) {
                    $params[$key] = $values[$id];
                }
            }

        } else {
            // Given name => value
            $params = $jsonRequest->getParams();
        }

        // Inject params
        $reflection = new \ReflectionObject($object);

        foreach ($params as $name => $value) {

            if (!$reflection->hasProperty($name)) {
                throw new Exceptions\InvalidParamsException(null, $jsonRequest->getId());
            }

            $reflectionProperty = $reflection->getProperty($name);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($object, $value);
        }

        // Dispatch execute json

        $this->dispatch(
            Event\JsonExecuteEvent::EVENT,
            new Event\JsonExecuteEvent($object, $metadata)
        );

        return $object->{$metadata['executeMethod']}();
    }
}