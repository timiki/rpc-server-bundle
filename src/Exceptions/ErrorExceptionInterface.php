<?php

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

use Throwable;

interface ErrorExceptionInterface extends Throwable
{
    /**
     * Get id.
     *
     * @return mixed
     */
    public function getId();
    /**
     * Get data.
     *
     * @return mixed
     */
    public function getData();
}
