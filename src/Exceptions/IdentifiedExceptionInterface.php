<?php

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

use Throwable;

interface IdentifiedExceptionInterface extends Throwable
{
    /**
     * Get id.
     *
     * @return mixed
     */
    public function getId();
}
