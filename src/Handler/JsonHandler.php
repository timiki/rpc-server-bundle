<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Handler;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Timiki\Bundle\RpcServerBundle\Event;
use Timiki\Bundle\RpcServerBundle\Exceptions;
use Timiki\Bundle\RpcServerBundle\Mapper\Mapper;
use Timiki\Bundle\RpcServerBundle\Mapper\MetaData;
use Timiki\Bundle\RpcServerBundle\Method\Context;
use Timiki\Bundle\RpcServerBundle\Traits\EventDispatcherTrait;
use Timiki\Bundle\RpcServerBundle\Traits\StopwatchTrait;
use Timiki\RpcCommon\JsonRequest;
use Timiki\RpcCommon\JsonResponse;

class JsonHandler implements JsonHandlerInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;
    use StopwatchTrait;
    use EventDispatcherTrait;

    public function __construct(private readonly Mapper $mapper)
    {
    }

    public function handleJsonRequest(JsonRequest $jsonRequest): JsonResponse
    {
        $this->stopwatch?->start('rpc.execute');

        try {
            /** @var Event\JsonRequestEvent $event */
            $event = $this->dispatch(new Event\JsonRequestEvent($jsonRequest, $this->mapper));

            $jsonResponse = $event->getJsonResponse();

            if (null === $jsonResponse) {
                $metadata = $this->mapper->getMetaData($jsonRequest->getMethod());
                $jsonResponse = new JsonResponse($jsonRequest);

                $result = $this->executeJsonRequest($metadata, $jsonRequest);

                if ($result instanceof JsonResponse) {
                    // Proxy response error
                    if ($result->isError()) {
                        $jsonResponse->setErrorCode($result->getErrorCode());
                        $jsonResponse->setErrorData($result->getErrorData());
                        $jsonResponse->setErrorMessage($result->getErrorMessage());
                    } else {
                        $jsonResponse->setResult($result->getResult());
                    }
                } else {
                    $jsonResponse->setResult($result);
                }
            }
        } catch (\Throwable $exception) {
            $jsonResponse = $this->createJsonResponseFromException($exception, $jsonRequest);
        }

        $this->dispatch(new Event\JsonResponseEvent($jsonResponse, $this->mapper));

        $this->stopwatch?->stop('rpc.execute');

        return $jsonResponse;
    }

    public function createJsonResponseFromException(\Throwable $exception, JsonRequest $jsonRequest = null): JsonResponse
    {
        $jsonResponse = new JsonResponse();
        $jsonResponse->setRequest($jsonRequest);

        if ($exception instanceof Exceptions\ErrorDataExceptionInterface) {
            $jsonResponse->setErrorCode(0 !== $exception->getCode() ? $exception->getCode() : -32603);
            $jsonResponse->setErrorMessage(!empty($exception->getMessage()) ? $exception->getMessage() : 'Internal error');
            $jsonResponse->setErrorData($exception->getData());
        } else {
            $jsonResponse->setErrorCode(0 !== $exception->getCode() ? $exception->getCode() : -32603);
            $jsonResponse->setErrorMessage(!empty($exception->getMessage()) ? $exception->getMessage() : 'Internal error');
        }

        return $jsonResponse;
    }

    private function executeJsonRequest(MetaData $metaData, JsonRequest $jsonRequest): mixed
    {
        $object = clone $this->container->get($metaData->get('class'));

        // Inject container
        if ($object instanceof ContainerAwareInterface && null !== $this->container) {
            $object->setContainer($this->container);
        }

        /**
         * @var Event\JsonPreExecuteEvent $event
         */
        $event = $this->dispatch(new Event\JsonPreExecuteEvent($object, $metaData, $this->mapper, $jsonRequest));

        if ($event->isPropagationStopped()) {
            return $event->getResult();
        }

        $method = $metaData->get('execute');
        $reflectionObject = new \ReflectionObject($object);

        if (!$reflectionObject->hasMethod($method)) {
            throw new Exceptions\ErrorException('Method not have execute');
        }

        $reflectionMethod = $reflectionObject->getMethod($method);
        $params = [];

        foreach ($reflectionMethod->getParameters() as $key => $reflectionParameter) {
            $id = (string) $reflectionParameter->getType();

            switch ($id) {
                case Context::class:
                    $params[$key] = new Context($metaData, $jsonRequest);
                    break;
                default:
                    if ($this->container->has($id)) {
                        $params[$key] = $this->container->get($id);
                    } else {
                        throw new Exceptions\ErrorException("Failed inject parameter {$key}: {$id}");
                    }
            }
        }

        $result = $object->{$metaData->get('execute')}(...$params);

        /**
         * @var Event\JsonExecuteEvent $event
         */
        $event = $this->dispatch(new Event\JsonExecuteEvent($object, $metaData, $this->mapper, $jsonRequest, $result));

        return $event->getResult();
    }
}
