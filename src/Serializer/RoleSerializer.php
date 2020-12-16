<?php

namespace Timiki\Bundle\RpcServerBundle\Serializer;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Serializer;

class RoleSerializer extends BaseSerializer
{
    /**
     * @var null|UserInterface
     */
    protected $user;

    /**
     * @param \Symfony\Component\Serializer\Serializer       $serializer
     * @param null|\Symfony\Component\Security\Core\Security $security
     */
    public function __construct(Serializer $serializer, Security $security = null)
    {
        parent::__construct($serializer);

        if ($security) {
            $this->user = $security->getUser();
        }
    }

    /**
     * Serialize data.
     *
     * @param mixed $data
     *
     * @return array
     */
    public function serialize($data)
    {
        if (!$this->user) {
            return parent::serialize($data);
        }

        return \json_decode(
            $this->serializer->serialize(
                $data,
                'json',
                ['groups' => $this->user->getRoles()]
            ),
            true
        );
    }
}
