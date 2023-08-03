<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Serializer;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;

class RoleSerializer extends BaseSerializer implements SerializerInterface
{
    public function __construct(
        private readonly SymfonySerializerInterface $serializer,
        private readonly null|Security $security
    ) {
        parent::__construct($serializer);
    }

    public function serialize(mixed $data): string
    {
        $user = $this->getUser();

        if (null === $user) {
            return parent::serialize($data);
        }

        return $this->serializer->serialize(
            $data,
            'json',
            [
                AbstractNormalizer::GROUPS => $user->getRoles(),
                AbstractNormalizer::IGNORED_ATTRIBUTES => [
                    '__initializer__',
                    '__cloner__',
                    '__isInitialized__',
                ],
            ]
        );
    }

    private function getUser(): null|UserInterface
    {
        return $this->security?->getUser();
    }
}
