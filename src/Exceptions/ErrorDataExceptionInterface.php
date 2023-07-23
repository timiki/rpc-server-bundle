<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

interface ErrorDataExceptionInterface
{
    public function getData(): mixed;
}
