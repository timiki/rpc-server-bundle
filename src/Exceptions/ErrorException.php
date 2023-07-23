<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

class ErrorException extends \RuntimeException implements ErrorExceptionInterface
{
    private mixed $data;
    private string|int|float|null $id;

    public function __construct(string $message = '', int $code = -32603, mixed $data = null, string|int|float $id = null)
    {
        $this->data = $data;
        $this->id = $id;

        parent::__construct($message, $code);
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getId(): string|int|float|null
    {
        return $this->id;
    }
}
