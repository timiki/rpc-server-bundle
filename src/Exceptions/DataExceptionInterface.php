<?php

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

use Throwable;

interface DataExceptionInterface extends Throwable
{
    /**
     * Get data.
     *
     * @return mixed
     */
    public function getData();
}
