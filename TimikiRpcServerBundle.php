<?php

namespace Timiki\Bundle\RpcServerBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class TimikiRpcServerBundle extends Bundle
{
    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(
            new RegisterListenersPass(
                'rpc.server.event_dispatcher',
                'rpc.server.event_listener',
                'rpc.server.event_subscriber'
            )
        );
    }
}
