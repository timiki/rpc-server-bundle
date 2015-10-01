<?php

namespace Timiki\Bundle\RpcServerBundle;

use Timiki\RpcClientCommon\Client;

/**
 * RPC Proxy instance
 */
class RpcProxy
{
    /**
     * Proxy locale (default en)
     *
     * @var array
     */
    protected $locale = 'en';

    /**
     * RPC client
     *
     * @var Client
     */
    protected $client;

    /**
     * Container
     *
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * Create new proxy
     *
     * @param array                                            $client
     * @param string                                           $locale
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function __construct(array $client = [], $locale = 'en', \Symfony\Component\DependencyInjection\Container $container = null)
    {
        $this->setLocale($locale);
        $this->setClient(new Client($client['address'], $client['options'], $client['type'], $this->getLocale()));
    }

    /**
     * Set proxy client
     *
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get proxy client
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set server locale
     *
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get server locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Call method
     *
     * @param string $method
     * @param array  $params
     * @param array  $extra
     * @return mixed
     */
    public function callMethod($method, array $params = [], array $extra = [])
    {
        return $this->client->call($method, $params, $extra);
    }
}
