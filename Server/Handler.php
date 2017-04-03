<?php

namespace Timiki\Bundle\RpcServerBundle\Server;

use Symfony\Component\HttpKernel\DataCollector\ExceptionDataCollector;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\Validator\ConstraintViolation;
use Timiki\Bundle\RpcServerBundle\Server\Exceptions;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * RPC handler
 */
class Handler implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use Traits\CacheTrait;

    /**
     * Rpc mapper.
     *
     * @var Mapper|null
     */
    protected $mapper;

    /**
     * Rpc proxy.
     *
     * @var Proxy|null
     */
    protected $proxy;

    /**
     * Set the mapper.
     *
     * @param Mapper $mapper
     */
    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
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
     * Set the Proxy.
     *
     * @param Proxy $proxy
     */
    public function setProxy(Proxy $proxy)
    {
        $this->proxy = $proxy;
    }

    /**
     * Get Proxy.
     *
     * @return Proxy|null
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * Parser HttpRequestRequest to JsonRequest.
     *
     * @param HttpRequest $httpRequest
     *
     * @return JsonRequest|JsonRequest[]
     * @throws Exceptions\ParseException
     */
    protected function parserHttpRequest(HttpRequest $httpRequest)
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

        // Try parse HttpRequest to JsonRequest|JsonRequest[]

        try {

            $jsonRequests = $this->parserHttpRequest($httpRequest);

        } catch (Exceptions\ParseException  $e) {

            return $this->createHttpResponseFromException($e);
        }

        $jsonResponses = $this->handleJsonRequest($jsonRequests);

        // Response

        $httpResponse = HttpResponse::create();

        // Is has exception?

        if ($this->container && $this->container->has('profiler')) {

            /* @var Profile $profiler */
            $profiler = $this->container->get('profiler');

            if (is_array($jsonResponses)) {

                foreach ($jsonResponses as $jsonResponse) {

                    if ($exception = $jsonResponse->getException()) {
                        $collector = new ExceptionDataCollector();
                        $collector->collect($httpRequest, $httpResponse, $exception);
                        $profiler->add($collector);
                    }

                }

            } else {

                if ($exception = $jsonResponses->getException()) {
                    $collector = new ExceptionDataCollector();
                    $collector->collect($httpRequest, $httpResponse, $exception);
                    $profiler->add($collector);
                }

            }

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

        // Set httpResponse headers.

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

        return $httpResponse;
    }

    /**
     * Create new jsonResponse from exception.
     *
     * @param \Exception       $exception
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
            $jsonResponse->setErrorMessage('Internal error');

        }

        $jsonResponse->setException($exception);

        if ($jsonRequest) {
            $jsonResponse->setId($jsonRequest->getId());
        }

        return $jsonResponse;
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

        return $httpResponse;
    }

    /**
     * Load methods metadata.
     *
     * @return array
     * @throws \Timiki\Bundle\RpcServerBundle\Server\Exceptions\InvalidMappingException
     */
    protected function loadMetadata()
    {
        return $this->mapper === null ? [] : $this->mapper->loadMetadata();
    }

    /**
     * Handle json request.
     *
     * @param JsonRequest|JsonRequest[] $jsonRequest
     *
     * @throws Exceptions\InvalidRequestException
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

        // Single request

        // Is cache?

        $cache = $this->getCache();

        if ($cache && $jsonRequest->getId() && !$this->container->getParameter('kernel.debug')) {
            if ($result = $cache->fetch($jsonRequest->getHash())) {

                $jsonResponse = new JsonResponse($jsonRequest);
                $jsonResponse->setResult($result);

                return $jsonResponse;

            }
        }

        try {

            // Validate request

            if (
                empty($jsonRequest->getMethod())
                || (
                    !empty($jsonRequest->getParams())
                    && !is_array($jsonRequest->getParams())
                )
            ) {

                throw new Exceptions\InvalidRequestException();

            }

            // Get method from metadata or proxy it

            $object = null;

            try {

                $object = $this->getMethod($jsonRequest);

            } catch (Exceptions\MethodNotFoundException $e) {

                if ($this->proxy) {

                    return $this->proxy->handleJsonRequest($jsonRequest);

                }

                throw $e;

            }

            $metadata = $this->mapper->loadObjectMetadata($object);

            if ($this->container && $this->container->has('debug.stopwatch')) {
                $stopwatch = $this->container->get('debug.stopwatch');
                $stopwatch->start('rpc.execute');
            }

            // Execute object

            $result = $this->executeJsonRequest($object, $jsonRequest);

            $jsonResponse = new JsonResponse($jsonRequest);
            $jsonResponse->setResult($result);

            // Save cache?

            if ($cache && $jsonRequest->getId() && $metadata['cache']) {
                $cache->save($jsonRequest->getHash(), $result, $metadata['cache']->lifetime);
            }

            return $jsonResponse;

        } catch (\Exception $exception) {

            return $this->createJsonResponseFromException($exception, $jsonRequest);

        } finally {

            if (isset($stopwatch)) {
                $stopwatch->stop('rpc.execute');
            }

        }

    }

    /**
     * Get RPC method from request.
     *
     * @param JsonRequest $jsonRequest
     *
     * @return object
     * @throws \Timiki\Bundle\RpcServerBundle\Server\Exceptions\InvalidMappingException
     */
    protected function getMethod($jsonRequest)
    {
        $method   = $jsonRequest->getMethod();
        $metadata = $this->loadMetadata();

        foreach ($metadata as $class => $meta) {

            if ($meta['method']->value == $method) {
                return new $class;
            }

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
     * @throws \Timiki\Bundle\RpcServerBundle\Server\Exceptions\InvalidMappingException
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

        // Validate

        if ($this->container && $this->container->has('validator')) {

            $validator = $this->container->get('validator');
            $result    = $validator->validate($object);

            if ($result->count() > 0) {

                $data = [];

                /* @var ConstraintViolation $constraintViolation */
                foreach ($result as $constraintViolation) {

                    $name = $constraintViolation->getPropertyPath() ? $constraintViolation->getPropertyPath() : 'violations';

                    if (!isset($data[$name])) {
                        $data[$name] = [];
                    }

                    $data[$name][] = $constraintViolation->getMessage();
                }

                throw new Exceptions\InvalidParamsException($data);
            }

        }

        // Roles grant

        if ($this->container && $this->container->has('security.authorization_checker') && !empty($metadata['roles'])) {

            if (!$this->container->get('security.authorization_checker')->isGranted((array)$metadata['roles']->value)) {
                throw new Exceptions\MethodNotGrantedException();
            }

        }

        // Execute

        return $object->{$metadata['executeMethod']}();
    }
}