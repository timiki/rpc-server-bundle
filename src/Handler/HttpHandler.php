<?php

namespace Timiki\Bundle\RpcServerBundle\Handler;

use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\DataCollector\ExceptionDataCollector;
use Timiki\Bundle\RpcServerBundle\Event;
use Timiki\Bundle\RpcServerBundle\Exceptions;
use Timiki\Bundle\RpcServerBundle\Traits\EventDispatcherTrait;
use Timiki\Bundle\RpcServerBundle\Traits\ProfilerTrait;
use Timiki\RpcCommon\JsonRequest;
use Timiki\RpcCommon\JsonResponse;

class HttpHandler
{
    use EventDispatcherTrait;
    use ProfilerTrait;

    /**
     * Json handler.
     *
     * @var JsonHandler|null
     */
    private $jsonHandler;

    /**
     * Response error code.
     *
     * @var int
     */
    private $errorCode;

    /**
     * HttpHandler constructor.
     *
     * @param int $errorCode
     */
    public function __construct(JsonHandler $jsonHandler, $errorCode = 200)
    {
        $this->jsonHandler = $jsonHandler;
        $this->errorCode = $errorCode;
    }

    /**
     * Parser HttpRequest to JsonRequest.
     *
     * @throws Exceptions\ParseException
     *
     * @return JsonRequest|JsonRequest[]
     */
    public function parserHttpRequest(HttpRequest $httpRequest)
    {
        $json = \json_decode($httpRequest->getContent(), true);

        if (JSON_ERROR_NONE !== \json_last_error()) {
            throw new Exceptions\ParseException();
        }

        /**
         * Create new JsonRequest.
         *
         * @param array $json
         *
         * @return JsonRequest
         */
        $createJsonRequest = function ($json) use ($httpRequest) {
            $id = null;
            $method = null;
            $params = [];

            if (\is_array($json)) {
                $id = \array_key_exists('id', $json) ? $json['id'] : null;
                $method = \array_key_exists('method', $json) ? $json['method'] : null;
                $params = \array_key_exists('params', $json) ? $json['params'] : [];
            }

            $request = new JsonRequest($method, $params, $id);
            $request->headers()->add($httpRequest->headers->all());

            return $request;
        };

        // If batch request
        if (\array_keys($json) === \range(0, \count($json) - 1)) {
            $requests = [];
            foreach ($json as $part) {
                $requests[] = $createJsonRequest($part);
            }
        } else {
            $requests = $createJsonRequest($json);
        }

        return $requests;
    }

    /**
     * Handle http request.
     *
     * @return HttpResponse
     */
    public function handleHttpRequest(HttpRequest $httpRequest)
    {
        /* @var  Event\HttpRequestEvent $event */
        $event = $this->dispatch(new Event\HttpRequestEvent($httpRequest));
        $httpRequest = $event->getHttpRequest();

        try {
            $jsonRequests = $this->parserHttpRequest($httpRequest);
        } catch (Exceptions\ParseException  $e) {
            return $this->createHttpResponseFromException($e);
        }

        $jsonResponses = $this->jsonHandler->handleJsonRequest($jsonRequests);
        $httpResponse = new HttpResponse();

        if ($this->profiler) {
            /**
             * @param JsonResponse|JsonResponse[] $jsonResponse
             */
            $collect = function ($jsonResponse) use (&$collect, $httpRequest, $httpResponse) {
                if (\is_array($jsonResponse)) {
                    foreach ($jsonResponse as $value) {
                        $collect($value);
                    }
                } else {
                    if ($jsonResponse->isError()) {
                        $this->collectException(
                            $httpRequest,
                            $httpResponse,
                            new Exceptions\ErrorException($jsonResponse->getErrorMessage(), $jsonResponse->getErrorCode(), $jsonResponse->getErrorData(), $jsonResponse->getId())
                        );
                    }
                }
            };

            $collect($jsonResponses);
        }

        // Set httpResponse content.

        if (\is_array($jsonResponses)) {
            $results = [];

            foreach ($jsonResponses as $jsonResponse) {
                if ($jsonResponse->isError() || null !== $jsonResponse->getId()) {
                    $results[] = $jsonResponse;
                }

                if ($jsonResponse->isError()) {
                    $httpResponse->setStatusCode($this->errorCode);
                }
            }

            $httpResponse->setContent(\json_encode($results));
        } else {
            if ($jsonResponses->isError() || null !== $jsonResponses->getId()) {
                $httpResponse->setContent(\json_encode($jsonResponses));
            }

            if ($jsonResponses->isError()) {
                $httpResponse->setStatusCode($this->errorCode);
            }
        }

        // Set httpResponse headers
        if (\is_array($jsonResponses)) {
            foreach ($jsonResponses as $jsonResponse) {
                if ($jsonResponse->isError() || null !== $jsonResponse->getId()) {
                    $httpResponse->headers->add($jsonResponse->headers()->all());
                }
            }
        } else {
            $httpResponse->headers->add($jsonResponses->headers()->all());
        }

        $httpResponse->headers->set('Content-Type', 'application/json');

        $this->dispatch(new Event\HttpResponseEvent($httpResponse, $jsonResponses));

        return $httpResponse;
    }

    /**
     * Create new HttpResponse from exception.
     *
     * @return HttpResponse
     */
    public function createHttpResponseFromException(\Exception $exception)
    {
        $httpResponse = new HttpResponse();
        $json = [];
        $json['jsonrpc'] = '2.0';
        $json['error'] = [];

        if ($exception instanceof Exceptions\ErrorException) {
            $json['error']['code'] = $exception->getCode();
            $json['error']['message'] = $exception->getMessage();

            if ($exception->getData()) {
                $json['error']['data'] = $exception->getData();
            }

            $json['id'] = $exception->getId();
        } else {
            $json['error']['code'] = -32603;
            $json['error']['message'] = 'Internal error';
            $json['id'] = null;
        }

        $httpResponse->headers->set('Content-Type', 'application/json');
        $httpResponse->setContent(\json_encode($json));
        $httpResponse->setStatusCode($this->errorCode);

        $this->dispatch(new Event\HttpResponseEvent($httpResponse));

        return $httpResponse;
    }

    /**
     * Collect exception.
     *
     * @param HttpRequest  $httpRequest
     * @param HttpResponse $httpResponse
     * @param \Exception   $exception
     */
    private function collectException($httpRequest, $httpResponse, $exception)
    {
        if ($this->profiler) {
            $collector = new ExceptionDataCollector();
            $collector->collect($httpRequest, $httpResponse, $exception);
            $this->profiler->add($collector);
        }
    }

    /**
     * Get Json handler.
     *
     * @return \Timiki\Bundle\RpcServerBundle\Handler\JsonHandler|null
     */
    public function getJsonHandler()
    {
        return $this->jsonHandler;
    }
}
