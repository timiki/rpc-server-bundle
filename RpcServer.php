<?php

namespace Timiki\Bundle\RpcServerBundle;

use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Timiki\Bundle\RpcServerBundle\Server\Method;
use Timiki\Bundle\RpcServerBundle\Server\Handler as HandlerInterface;
use Timiki\Bundle\RpcServerBundle\Method\Result;
use Timiki\Bundle\RpcServerBundle\Method\Validator;

/**
 * RPC Server instance
 */
class RpcServer
{
    /**
     * Server methods array
     *
     * @var array
     */
    protected $methods = [];

    /**
     * Server methods path
     *
     * @var array
     */
    protected $methodsPath = [];

    /**
     * Server default handler
     *
     * @var array
     */
    protected $defaultHandlers = 'json';

    /**
     * Server locale (default en)
     *
     * @var array
     */
    protected $locale = 'en';

    /**
     * Container
     *
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * Create new server
     *
     * @param array $paths
     * @param string $handler
     * @param string $locale
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function __construct(array $paths = [], $handler = 'json', $locale = 'en', \Symfony\Component\DependencyInjection\Container $container = null)
    {
        // Add foundation methods
        $this->addMethodsDirectory(__DIR__ . '/Rpc', '\\Timiki\\Bundle\\RpcServerBundle\\Rpc');

        $this->locale    = $locale;
        $this->container = $container;

        foreach ($paths as $namespace => $path) {
            $this->addMethodsDirectory($path, $namespace);
        }
    }

    /**
     * Add directory with methods class
     *
     * @param string $path
     * @param string $namespace
     * @return $this
     */
    public function addMethodsDirectory($path, $namespace = '\\')
    {
        $namespace = rtrim($namespace, '\\');

        if (!array_key_exists($path, $this->methodsPath)) {
            $this->methodsPath[$path] = $namespace;
        }

        return $this;
    }

    /**
     * Get server method
     *
     * @param $method
     * @return null|Method
     */
    public function getMethod($method)
    {
        if (!array_key_exists($method, $this->methods)) {
            foreach ($this->methodsPath as $path => $namespace) {
                if (file_exists($path)) {
                    $directory = new \DirectoryIterator($path);
                    foreach ($directory as $file) {
                        /* @var \DirectoryIterator $file */
                        if (!$file->isDot() and !$file->isDir()) {
                            $filename = explode('.', $file->getBasename());
                            if ($filename[count($filename) - 1] === 'php') {
                                if ($file->getBasename('.php') === $method) {
                                    $className = $namespace . '\\' . $file->getBasename('.php');
                                    if (class_exists($className)) {
                                        /* @var Method $methodObject */
                                        $methodObject = new $className();
                                        $methodObject->setServer($this);
                                        $methodObject->setContainer($this->container);
                                        $this->methods[$method] = $methodObject;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if (!array_key_exists($method, $this->methods)) {
                $this->methods[$method] = null;
            }
        }

        return $this->methods[$method];
    }

    /**
     * Set server locale
     *
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get server locale
     *
     * @return String
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Get server method list
     *
     * @return array
     */
    public function getMethods()
    {
        // Load all methods in paths
        foreach ($this->methodsPath as $path => $namespace) {
            $directory = new \DirectoryIterator($path);
            foreach ($directory as $file) {
                /* @var \DirectoryIterator $file */
                if (!$file->isDot() and !$file->isDir()) {
                    if (!array_key_exists($file->getBasename('.php'), $this->methods)) {
                        $className = $namespace . '\\' . $file->getBasename('.php');
                        if (class_exists($className)) {
                            /* @var Method $methodObject */
                            $methodObject = new $className();
                            $methodObject->setServer($this);
                            $this->methods[$file->getBasename('.php')] = $methodObject;
                        }
                    }
                }
            }
        }

        $methods = [];
        foreach ($this->methods as $method) {
            /* @var Method $method */
            $methods[$method->getName()] = $method->getDescription();
        }
        asort($methods);

        return $methods;
    }

    /**
     * Check if method exists
     *
     * @param $method
     * @return boolean
     */
    public function isMethodExists($method)
    {
        return array_key_exists($method, $this->getMethods()) ? true : false;
    }

    /**
     * Call method
     *
     * @param string $method
     * @param array $params
     * @param array $extra
     * @return Result
     */
    public function callMethod($method, array $params = [], array $extra = [])
    {
        $method = $this->getMethod($method);
        $result = new Result();
        if ($method !== null) {

            // Prepare methods params value
            $methodParams = [];

            foreach ($method->getParams() as $value) {
                if (array_key_exists($value[0], $params)) {
                    $methodParams[$value[0]] = $params[$value[0]];
                } else {
                    // Set default or null
                    if (array_key_exists(2, $value)) {
                        $methodParams[$value[0]] = $value[2];
                    }
                    // else {
                    // $methodParams[$value[0]] = null;
                    // }
                }
            }

            // Validate methods params
            $validator      = new Validator();
            $validateResult = $validator->validate($method, $methodParams);

            if (count($validateResult) > 0) {
                // have some errors
                $result->setError($validateResult);
            } else {

                $reflection = new  \ReflectionObject($method);

                /*
                | Reflection method beforeExecute function
                */
                if ($reflection->hasMethod('beforeExecute')) {
                    $methodBeforeExecuteParams = $reflection->getMethod('beforeExecute')->getParameters();
                    $args                      = [];

                    foreach ($methodBeforeExecuteParams as $param) {
                        if ($param->getName() == 'result') {
                            $args[] = &$result;
                        } elseif ($param->getName() == 'extra') {
                            $args[] = $extra;
                        } else {
                            if (array_key_exists($param->getName(), $params)) {
                                $args[] = $params[$param->getName()];
                            } else {
                                $args[] = null;
                            }
                        }
                    }

                    $reflection->getMethod('beforeExecute')->invokeArgs($method, $args);
                }

                if (!$result->isError()) {
                    /*
                    | Reflection method execute function
                    */
                    if ($reflection->hasMethod('execute')) {
                        $methodExecuteParams = $reflection->getMethod('execute')->getParameters();
                        $args                = [];
                        foreach ($methodExecuteParams as $param) {
                            if ($param->getName() == 'result') {
                                $args[] = &$result;
                            } elseif ($param->getName() == 'extra') {
                                $args[] = $extra;
                            } else {
                                if (array_key_exists($param->getName(), $params)) {
                                    $args[] = $params[$param->getName()];
                                } else {
                                    $args[] = null;
                                }
                            }
                        }
                        $reflection->getMethod('execute')->invokeArgs($method, $args);
                    }
                }
            }
        } else {
            $result->setError(['error' => 'methodNotFound', 'message' => 'Method not found']);
        }

        return $result;
    }

    /**
     * Handle http request
     *
     * @param HttpRequest $httpRequest
     * @param string $type
     * @param HttpResponse $httpResponse
     * @return HttpResponse
     */
    public function handleHttpRequest(HttpRequest $httpRequest, $type = 'json', HttpResponse $httpResponse = null)
    {
        /*
        | Create HttpResponse
        */

        if ($httpResponse === null) {
            $httpResponse = HttpResponse::create();
        }

        /*
        | Get HttpRequest handler
        */

        if (class_exists('\\Timiki\\Bundle\\RpcServerBundle\\Server\\Handlers\\' . ucfirst(strtolower($type)))) {
            $handlerClass = '\\Timiki\\Bundle\\RpcServerBundle\\Server\\Handlers\\' . ucfirst(strtolower($type));
            $handler      = new $handlerClass();
        } else {
            $handlerClass = '\\Timiki\\Bundle\\RpcServerBundle\\Server\\Handlers\\' . ucfirst(strtolower($this->defaultHandlers));
            $handler      = new $handlerClass();
        }

        /* @var HandlerInterface $handler */
        $handler->setServer($this);

        /*
        | Process HttpRequest
        */

        $methodName   = $handler->getHttpRequestMethod($httpRequest);
        $methodParams = $handler->getHttpRequestParams($httpRequest);
        $methodExtra  = $handler->getHttpRequestExtra($httpRequest);

        /*
        | Execute method
        */

        $result = $this->callMethod($methodName, $methodParams, $methodExtra);

        /*
        | Process HttpResponse
        */

        $handler->processResult($httpRequest, $httpResponse, $result);

        return $httpResponse;
    }
}
