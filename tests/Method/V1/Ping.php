<?php

namespace Tests\Timiki\Bundle\RpcServerBundle\Method\V1;

use Symfony\Component\Validator\Constraints as Assert;
use Tests\Timiki\Bundle\RpcServerBundle\Method\AbstractMethod;
use Timiki\Bundle\RpcServerBundle\Mapping as Rpc;

/**
 * @Rpc\Method("ping")
 */
class Ping extends AbstractMethod
{
    /**
     * @Rpc\Param
     * @Assert\NotBlank
     * @Assert\Type(type="string")
     */
    protected $param;

    /**
     * @Rpc\Execute
     */
    public function execute()
    {
        return ['pong', $this->param];
    }
}
