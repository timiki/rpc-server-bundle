<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Traits;

use Symfony\Component\DependencyInjection\ContainerInterface;

trait ContainerTrait
{
    protected ?ContainerInterface $container = null;

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    public function setContainer(?ContainerInterface $container = null): void
    {
        $this->container = $container;
    }
}
