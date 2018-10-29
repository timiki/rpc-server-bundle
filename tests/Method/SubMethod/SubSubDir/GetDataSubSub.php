<?php

namespace Tests\Timiki\Bundle\RpcServerBundle\Method\SubMethod\SubSubDir;

use Symfony\Component\Validator\Constraints as Assert;
use Timiki\Bundle\RpcServerBundle\Mapper\MethodInterface;
use Timiki\Bundle\RpcServerBundle\Mapping as Rpc;

/**
 * @Rpc\Method("get_data_sub_sub")
 */
class GetDataSubSub implements MethodInterface
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
