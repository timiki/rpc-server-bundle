<?php

namespace Timiki\Bundle\RpcServerBundle\Tests;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Timiki\Bundle\RpcServerBundle\EventSubscriber;
use Timiki\Bundle\RpcServerBundle\Handler\HttpHandler;
use Timiki\Bundle\RpcServerBundle\Handler\JsonHandler;
use Timiki\Bundle\RpcServerBundle\Mapper\Mapper;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\HttpFoundation\Request;

class Utils
{
    /**
     * @param $path
     * @return \Timiki\Bundle\RpcServerBundle\Mapper\Mapper
     * @throws \Timiki\Bundle\RpcServerBundle\Exceptions\InvalidMappingException
     */
    public static function getMapper($path = null)
    {
        AnnotationRegistry::registerFile(dirname(__DIR__).DIRECTORY_SEPARATOR.'Mapping'.DIRECTORY_SEPARATOR.'Cache.php');
        AnnotationRegistry::registerFile(dirname(__DIR__).DIRECTORY_SEPARATOR.'Mapping'.DIRECTORY_SEPARATOR.'Execute.php');
        AnnotationRegistry::registerFile(dirname(__DIR__).DIRECTORY_SEPARATOR.'Mapping'.DIRECTORY_SEPARATOR.'Method.php');
        AnnotationRegistry::registerFile(dirname(__DIR__).DIRECTORY_SEPARATOR.'Mapping'.DIRECTORY_SEPARATOR.'Param.php');
        AnnotationRegistry::registerFile(dirname(__DIR__).DIRECTORY_SEPARATOR.'Mapping'.DIRECTORY_SEPARATOR.'Roles.php');

        $mapper = new Mapper();

        if ($path) {
            $mapper->addPath($path);
        }

        return $mapper;
    }

    /**
     * @param $path
     * @return HttpHandler
     */
    public static function getHandler($path = null)
    {
        $mapper      = self::getMapper($path);
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new EventSubscriber\ValidateRequestSubscriber());
        $eventDispatcher->addSubscriber(new EventSubscriber\ValidatorSubscriber());

        $jsonHandler = new JsonHandler($mapper);
        $httpHandler = new HttpHandler($jsonHandler);

        $jsonHandler->setEventDispatcher($eventDispatcher);
        $httpHandler->setEventDispatcher($eventDispatcher);

        return $httpHandler;
    }

    /**
     * @param $json
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public static function getHttpRequest($json)
    {
        $query      = [];
        $request    = [];
        $attributes = [];
        $cookies    = [];
        $files      = [];
        $server     = [];
        $content    = $json;

        return new Request($query, $request, $attributes, $cookies, $files, $server, $content);
    }

}
