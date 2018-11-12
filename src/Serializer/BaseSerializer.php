<?php

namespace Timiki\Bundle\RpcServerBundle\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class BaseSerializer implements SerializerInterface
{
    /**
     * @var null|Serializer
     */
    protected $serializer;

    /**
     * BaseSerializer constructor.
     *
     * @param null|Serializer $serializer
     */
    public function __construct(Serializer $serializer = null)
    {
        $this->serializer = $serializer;

        if (null === $this->serializer) {
            $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncode()]);
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
        if ($this->serializer) {
            return \json_decode($this->serializer->serialize($data, 'json'), true);
        }

        return $data;
    }
}
