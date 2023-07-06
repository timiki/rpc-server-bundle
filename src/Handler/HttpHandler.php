<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Handler;

use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\DataCollector\ExceptionDataCollector;
use Timiki\Bundle\RpcServerBundle\Event;
use Timiki\Bundle\RpcServerBundle\Exceptions;
use Timiki\Bundle\RpcServerBundle\Serializer\SerializerInterface;
use Timiki\Bundle\RpcServerBundle\Traits\EventDispatcherTrait;
use Timiki\Bundle\RpcServerBundle\Traits\ProfilerTrait;
use Timiki\RpcCommon\JsonRequest;
use Timiki\RpcCommon\JsonResponse;

class HttpHandler implements HttpHandlerInterface
{
    use EventDispatcherTrait;
    use ProfilerTrait;

    public function __construct(
        private readonly JsonHandlerInterface $jsonHandler,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function handleHttpRequest(HttpRequest $httpRequest): HttpResponse
    {
        /* @var  Event\HttpRequestEvent $event */
        $event = $this->dispatch(new Event\HttpRequestEvent($httpRequest));
        $httpRequest = $event->getHttpRequest();

        try {
            $json = $this->parserHttpRequest($httpRequest);
        } catch (\Throwable $e) {
            return $this->createHttpResponseFromJsonResponse(
                $this->createJsonResponseFromException($e),
                $httpRequest
            );
        }

        $isBatch = \array_keys($json) === \range(0, \count($json) - 1);

        if ($isBatch) {
            $result = [];

            foreach ($json as $part) {
                try {
                    $result[] = $this->executeJson($part);
                } catch (\Throwable $e) {
                    $result[] = $this->createJsonResponseFromException($e);
                }
            }
        } else {
            try {
                $result = $this->executeJson($json);
            } catch (\Throwable $e) {
                $result = $this->createJsonResponseFromException($e);
            }
        }

        return $this->createHttpResponseFromJsonResponse($result);
    }

    private function parserHttpRequest(HttpRequest $httpRequest): array
    {
        $json = \json_decode($httpRequest->getContent(), true);

        if (JSON_ERROR_NONE !== \json_last_error()) {
            throw new Exceptions\ParseException();
        }

        $createJsonRequest = static function (array $json) use ($httpRequest): array {
            return [
                'id' => $json['id'] ?? null,
                'method' => $json['method'] ?? null,
                'params' => $json['params'] ?? null,
                'headers' => $httpRequest->headers->all(),
            ];
        };

        // If batch request
        if (\array_keys($json) === \range(0, \count($json) - 1)) {
            $request = [];
            foreach ($json as $part) {
                $request[] = $createJsonRequest((array) $part);
            }
        } else {
            $request = $createJsonRequest((array) $json);
        }

        return $request;
    }

    public function executeJson(array|null $json): JsonResponse
    {
        if (empty($json)) {
            throw new Exceptions\InvalidRequestException();
        }

        $id = $json['id'] ?? null;
        $method = $json['method'] ?? null;
        $params = $json['params'] ?? [];
        $headers = $json['headers'] ?? [];

        if (!is_array($params) || !is_string($method)) {
            throw new Exceptions\InvalidRequestException(null, $id);
        }

        $jsonRequest = new JsonRequest($method, $params, $id);
        $jsonRequest->headers()->add((array) $headers);

        return $this->jsonHandler->handleJsonRequest($jsonRequest);
    }

    private function createJsonResponseFromException(\Throwable $exception): JsonResponse
    {
        $jsonResponse = new JsonResponse();

        if ($exception instanceof Exceptions\ErrorExceptionInterface) {
            $jsonResponse->setErrorCode($exception->getCode());
            $jsonResponse->setErrorMessage($exception->getMessage());
            $jsonResponse->setErrorData($exception->getData());
            $jsonResponse->setId($exception->getId());
        } elseif ($exception instanceof Exceptions\ErrorDataExceptionInterface) {
            $jsonResponse->setErrorCode($exception->getCode());
            $jsonResponse->setErrorMessage($exception->getMessage());
            $jsonResponse->setErrorData($exception->getData());
        } else {
            $jsonResponse->setErrorCode(-32000);
            $jsonResponse->setErrorMessage($exception->getMessage());
        }

        return $jsonResponse;
    }

    private function createHttpResponseFromJsonResponse(JsonResponse|array $jsonResponse, HttpRequest $httpRequest = null): HttpResponse
    {
        $httpResponse = new HttpResponse();
        $httpResponse->headers->set('Content-Type', 'application/json');
        $result = null;

        if (\is_array($jsonResponse)) {
            $result = [];

            foreach ($jsonResponse as $item) {
                if ($item->isError() || null !== $item->getId()) {
                    $result[] = $item;
                    $httpResponse->headers->add($item->headers()->all());
                }
            }
        } else {
            if ($jsonResponse->isError() || null !== $jsonResponse->getId()) {
                $result = $jsonResponse;
                $httpResponse->headers->add($jsonResponse->headers()->all());
            }
        }

        if ($httpRequest && $this->profiler) {
            $profiler = $this->profiler;
            $collect = static function (array|JsonResponse $jsonResponse) use (&$collect, $httpRequest, $httpResponse, $profiler) {
                if (\is_array($jsonResponse)) {
                    foreach ($jsonResponse as $value) {
                        $collect($value);
                    }
                } else {
                    if ($jsonResponse->isError()) {
                        $collector = new ExceptionDataCollector();
                        $collector->collect(
                            $httpRequest,
                            $httpResponse,
                            new Exceptions\ErrorException(
                                $jsonResponse->getErrorMessage(),
                                $jsonResponse->getErrorCode(),
                                $jsonResponse->getErrorData(),
                                $jsonResponse->getId()
                            )
                        );
                        $profiler->add($collector);
                    }
                }
            };

            $collect($result);
        }

        $httpResponse->setContent($this->serializer->serialize($result));
        $this->dispatch(new Event\HttpResponseEvent($httpResponse, $jsonResponse));

        return $httpResponse;
    }
}
