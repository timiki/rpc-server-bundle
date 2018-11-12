<?php

namespace Tests\Timiki\Bundle\RpcServerBundle\Method\SubMethod;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;
use Timiki\Bundle\RpcServerBundle\Mapper\MethodInterface;
use Timiki\Bundle\RpcServerBundle\Mapping as Rpc;

/**
 * @Rpc\Method("get_data_sub")
 */
class GetDataSub implements MethodInterface
{
    /**
     * @Rpc\Param
     * @Assert\NotBlank
     * @Assert\Type(type="integer")
     */
    protected $a;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @Rpc\Execute
     */
    public function execute()
    {
        return ['hello', 5];
    }
}
