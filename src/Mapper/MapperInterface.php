<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Mapper;

interface MapperInterface
{
    public function addMethods(array $methods): void;

    public function hasMethod(string $name): bool;

    public function getMetaData(string $name): MetaData;

    public function getHash(): string;
}
