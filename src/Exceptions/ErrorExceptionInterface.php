<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

interface ErrorExceptionInterface extends ErrorDataExceptionInterface
{
    public function getId(): string|int|float|null;
}
