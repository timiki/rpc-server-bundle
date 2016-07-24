<?php

namespace Timiki\Bundle\RpcServerBundle\Tests;

use Timiki\Bundle\RpcServerBundle\Server\Handler;
use Timiki\Bundle\RpcServerBundle\Server\Mapper;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\HttpFoundation\Request;

class Utils
{
    /**
     * @param $path
     * @return \Timiki\Bundle\RpcServerBundle\Server\Mapper
     * @throws \Timiki\Bundle\RpcServerBundle\Server\Exceptions\InvalidMappingException
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
     * @return \Timiki\Bundle\RpcServerBundle\Server\Handler
     * @throws \Timiki\Bundle\RpcServerBundle\Server\Exceptions\InvalidMappingException
     */
    public static function getHandler($path = null)
    {
        $mapper  = self::getMapper($path);
        $handler = new Handler();

        $handler->setMapper($mapper);

        return $handler;
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
