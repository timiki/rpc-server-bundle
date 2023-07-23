<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Timiki\Bundle\RpcServerBundle\DependencyInjection\Compiler\RpcMethodPass;

class RpcServerBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // Compile rpc methods
        $container->addCompilerPass(new RpcMethodPass());
    }
}
