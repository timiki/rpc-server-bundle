<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Serializer;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;

class RoleSerializer implements SerializerInterface
{
    public function __construct(
        private readonly SymfonySerializerInterface $serializer,
        private readonly null|Security $security
    ) {
    }

    public function serialize(mixed $data): string
    {
        $user = $this->getUser();

        if (null === $user) {
            return $this->serializer->serialize($data, 'json');
        }

        return $this->serializer->serialize(
            $data,
            'json',
            [
                'groups' => $user->getRoles(),
            ]
        );
    }

    private function getUser(): null|UserInterface
    {
        return $this->security?->getUser();
    }
}
