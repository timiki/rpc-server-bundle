<?php

namespace Timiki\Bundle\RpcServerBundle\Server;

use Timiki\RpcClient\Client;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Proxy RPC
 */
class Proxy implements ContainerAwareInterface
{

    use ContainerAwareTrait;

    /**
     * RPC client
     *
     * @var Client
     */
    protected $client;

    /**
     * RPC handler
     *
     * @var Handler
     */
    protected $handler;

    /**
     * @param string|array                                  $address
     * @param \Timiki\Bundle\RpcServerBundle\Server\Handler $handler
     */
    public function __construct($address, Handler $handler)
    {
        $this->client  = new Client($address);
        $this->handler = $handler;
    }

    /**
     * Handle json request.
     *
     * @param JsonRequest|JsonRequest[] $jsonRequest
     * @return JsonResponse|JsonResponse[]|null
     */
    public function handleJsonRequest(JsonRequest $jsonRequest)
    {
        // Fix session block
        if ($this->container) {
            $this->container->get('session')->save();
        }

        try {
            $jsonResponse = $this->client->execute($jsonRequest);
        } catch (\Exception $e) {

            if (is_array($jsonRequest)) {

                $jsonResponse = [];

                foreach ($jsonRequest as $id => $request) {
                    $jsonResponse[$id] = $this->handler->createJsonResponseFromException(
                        new Exceptions\ProxyException($e->getMessage(), $request->getId()),
                        $request
                    );
                }

            } else {

                $jsonResponse = $this->handler->createJsonResponseFromException(
                    new Exceptions\ProxyException($e->getMessage(), $jsonRequest->getId()),
                    $jsonRequest
                );

            }

        }

        // Restore session
        if ($this->container) {
            $this->container->get('session')->save();
        }

        return $jsonResponse;
    }
}
