<?php

namespace Timiki\Bundle\RpcServerBundle\Handler;

use Symfony\Component\HttpKernel\DataCollector\ExceptionDataCollector;
use Timiki\Bundle\RpcServerBundle\Exceptions;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Timiki\Bundle\RpcServerBundle\JsonRequest;
use Timiki\Bundle\RpcServerBundle\Event;
use Timiki\Bundle\RpcServerBundle\JsonResponse;
use Timiki\Bundle\RpcServerBundle\Traits\EventDispatcherTrait;
use Timiki\Bundle\RpcServerBundle\Traits\ProfilerTrait;

/**
 * RPC http handler
 */
class HttpHandler
{
    use EventDispatcherTrait;
    use ProfilerTrait;

    /**
     * Json handler.
     *
     * @var JsonHandler|null
     */
    protected $jsonHandler;

    /**
     * HttpHandler constructor.
     *
     * @param JsonHandler $jsonHandler
     */
    public function __construct(JsonHandler $jsonHandler)
    {
        $this->jsonHandler = $jsonHandler;
    }

    /**
     * Parser HttpRequest to JsonRequest.
     *
     * @param HttpRequest $httpRequest
     *
     * @return JsonRequest|JsonRequest[]
     * @throws Exceptions\ParseException
     */
    public function parserHttpRequest(HttpRequest $httpRequest)
    {
        $json = json_decode($httpRequest->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exceptions\ParseException();
        }

        /**
         * Create new JsonRequest
         *
         * @param $json
         *
         * @return JsonRequest
         */
        $createJsonRequest = function ($json) use ($httpRequest) {

            $id     = null;
            $method = null;
            $params = [];

            if (is_array($json)) {

                $id     = array_key_exists('id', $json) ? $json['id'] : null;
                $method = array_key_exists('method', $json) ? $json['method'] : null;
                $params = array_key_exists('params', $json) ? $json['params'] : [];

            }

            $request = new JsonRequest($method, $params, $id);
            $request->headers()->add($httpRequest->headers->all());
            $request->setHttpRequest($httpRequest);

            return $request;
        };

        if (array_keys($json) === range(0, count($json) - 1)) {

            // Batch request

            $requests = [];

            foreach ($json as $part) {
                $requests[] = $createJsonRequest($part);
            }

        } else {

            // Single request

            $requests = $createJsonRequest($json);

        }

        return $requests;
    }

    /**
     * Handle http request.
     *
     * @param HttpRequest $httpRequest
     *
     * @return HttpResponse
     */
    public function handleHttpRequest(HttpRequest $httpRequest)
    {
        $event       = $this->dispatch(Event\HttpRequestEvent::EVENT, new Event\HttpRequestEvent($httpRequest));
        $httpRequest = $event->getHttpRequest();

        try {
            $jsonRequests = $this->parserHttpRequest($httpRequest);
        } catch (Exceptions\ParseException  $e) {
            return $this->createHttpResponseFromException($e);
        }

        $jsonResponses = $this->jsonHandler->handleJsonRequest($jsonRequests);
        $httpResponse  = HttpResponse::create();

        if ($this->profiler) {

            /**
             * @param JsonResponse|JsonResponse[] $jsonResponse
             */
            $collect = function ($jsonResponse) use (&$collect, $httpRequest, $httpResponse) {

                if (is_array($jsonResponse)) {
                    foreach ($jsonResponse as $value) {
                        $collect($value);
                    }
                } else {
                    if ($jsonResponse->getException()) {
                        $this->collectException($httpRequest, $httpResponse, $jsonResponse->getException());
                    }
                }

            };

            $collect($jsonResponses);
        }

        // Set httpResponse content.

        if (is_array($jsonResponses)) {

            $results = [];

            foreach ($jsonResponses as $jsonResponse) {

                if ($jsonResponse->isError() || $jsonResponse->getId()) {
                    $results[] = $jsonResponse;
                }

                if ($jsonResponse->isError()) {
                    $httpResponse->setStatusCode(500);
                }

            }

            $httpResponse->setContent(json_encode($results));

        } else {

            if ($jsonResponses->isError() || $jsonResponses->getId()) {
                $httpResponse->setContent(json_encode($jsonResponses));
            }

            if ($jsonResponses->isError()) {
                $httpResponse->setStatusCode(500);
            }

        }

        // Set httpResponse headers

        if (is_array($jsonResponses)) {

            foreach ($jsonResponses as $jsonResponse) {
                if ($jsonResponse->isError() || $jsonResponse->getId()) {
                    $httpResponse->headers->add($jsonResponse->headers()->all());
                }
            }

        } else {
            $httpResponse->headers->add($jsonResponses->headers()->all());
        }

        $httpResponse->headers->set('Content-Type', 'application/json');

        $this->dispatch(
            Event\HttpResponseEvent::EVENT,
            new Event\HttpResponseEvent($httpResponse)
        );

        return $httpResponse;
    }

    /**
     * Create new HttpResponse from exception.
     *
     * @param \Exception $exception
     *
     * @return HttpResponse
     */
    public function createHttpResponseFromException(\Exception $exception)
    {
        $httpResponse    = HttpResponse::create();
        $json            = [];
        $json['jsonrpc'] = '2.0';
        $json['error']   = [];

        if ($exception instanceof Exceptions\ErrorException) {

            $json['error']['code']    = $exception->getCode();
            $json['error']['message'] = $exception->getMessage();

            if ($exception->getData()) {
                $json['error']['data'] = $exception->getData();
            }

            $json['id'] = $exception->getId();

        } else {

            $json['error']['code']    = -32603;
            $json['error']['message'] = 'Internal error';
            $json['id']               = null;

        }

        $httpResponse->headers->set('Content-Type', 'application/json');
        $httpResponse->setContent(json_encode($json));
        $httpResponse->setStatusCode(500);

        $this->dispatch(
            Event\HttpResponseEvent::EVENT,
            new Event\HttpResponseEvent($httpResponse)
        );

        return $httpResponse;
    }

    /**
     * Collect exception.
     *
     * @param $httpRequest
     * @param $httpResponse
     * @param $exception
     */
    protected function collectException($httpRequest, $httpResponse, $exception)
    {
        if ($this->profiler) {
            $collector = new ExceptionDataCollector();
            $collector->collect($httpRequest, $httpResponse, $exception);
            $this->profiler->add($collector);
        }
    }
}