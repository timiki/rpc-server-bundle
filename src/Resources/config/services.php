<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Timiki\Bundle\RpcServerBundle\Controller\RpcController;
use Timiki\Bundle\RpcServerBundle\EventSubscriber\AuthorizationCheckerSubscriber;
use Timiki\Bundle\RpcServerBundle\EventSubscriber\ParamConverterSubscriber;
use Timiki\Bundle\RpcServerBundle\EventSubscriber\ValidatorSubscriber;
use Timiki\Bundle\RpcServerBundle\Make\MakeMethod;
use Timiki\Bundle\RpcServerBundle\Registry\HttpHandlerRegistry;
use Timiki\Bundle\RpcServerBundle\Registry\HttpHandlerRegistryInterface;
use Timiki\Bundle\RpcServerBundle\Serializer\BaseSerializer;
use Timiki\Bundle\RpcServerBundle\Serializer\RoleSerializer;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set(HttpHandlerRegistryInterface::class, HttpHandlerRegistry::class)
        ->public()
        ->alias('rpc.server.http_handler_registry', HttpHandlerRegistryInterface::class)
        ->public();

    $services->set(RpcController::class)
        ->args([
            service(HttpHandlerRegistryInterface::class),
        ])
        ->tag('controller.service_arguments');

    $services->set(MakeMethod::class)
        ->tag('maker.command');

    $services->set(BaseSerializer::class)
        ->public()
        ->args([
            service(SerializerInterface::class),
        ])
        ->alias('rpc.server.serializer.base', BaseSerializer::class)
        ->public();

    $services->set(RoleSerializer::class)
        ->public()
        ->args([
            service(SerializerInterface::class),
            service('Symfony\Bundle\SecurityBundle\Security')->nullOnInvalid(),
        ])
        ->alias('rpc.server.serializer.role', RoleSerializer::class)
        ->public();

    $services->set(ParamConverterSubscriber::class)
        ->args([
            service(ParameterBagInterface::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(AuthorizationCheckerSubscriber::class)
        ->args([
            service('security.authorization_checker')->nullOnInvalid(),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(ValidatorSubscriber::class)
        ->args([
            service('Symfony\Component\Validator\Validator\ValidatorInterface')->nullOnInvalid(),
        ])
        ->tag('kernel.event_subscriber');
};
