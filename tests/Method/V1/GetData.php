<?php

namespace Tests\Timiki\Bundle\RpcServerBundle\Method\V1;

use Symfony\Component\Validator\Constraints as Assert;
use Tests\Timiki\Bundle\RpcServerBundle\Method\AbstractMethod;
use Timiki\Bundle\RpcServerBundle\Mapping as Rpc;

/**
 * @Rpc\Method("get_data")
 * @Rpc\Roles({"Some_Role"})
 * @Rpc\Cache(10)
 */
class GetData extends AbstractMethod
{
    /**
     * @Rpc\Param
     * @Assert\NotBlank
     * @Assert\Type(type="integer")
     */
    protected $a;

    /**
     * @Rpc\Execute
     */
    public function execute()
    {
        return ['hello', 5];
    }
}
