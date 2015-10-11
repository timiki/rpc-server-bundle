<?php

namespace Timiki\Bundle\RpcServerBundle\Server;

use Timiki\Bundle\RpcServerBundle\Method\Result;

abstract class MethodGranted extends Method
{
    /**
     * Granted roles
     *
     * @var array
     */
    protected $granted = [];

    public function beforeExecute(Result $result)
    {
        $isGranted = [];
        foreach ($this->granted as $role) {
            $isGranted[] = $this->getContainer()->get('security.authorization_checker')->isGranted($role);
        }
        if (in_array(false, $isGranted, true)) {
            $result->setError('not_granted');
        }
    }
}
