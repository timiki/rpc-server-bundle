<?php

namespace Timiki\Bundle\RpcServerBundle;

use Timiki\RpcClientCommon\Client;
use Timiki\RpcClientCommon\Client\Response;
use Symfony\Component\DependencyInjection\Container;

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
     * @var Container
     */
    protected $container;

    /**
     * Create new proxy
     *
     * @param array     $client
     * @param string    $locale
     * @param Container $container
     */
    public function __construct(array $client = [], $locale = 'en', $container = null)
    {
        $this->setLocale($locale);
        $this->setContainer($container);
        $this->setClient(new Client($client['address'], $client['options'], $client['type'], $this->getLocale()));
    }

    /**
     * Set container
     *
     * @param Container|null $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        if ($container instanceof Container) {
            $this->container = $container;
        }

        return $this;
    }

    /**
     * Get container
     *
     * @return Container|null
     */
    public function getContainer()
    {
        return $this->container;
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
     * @return Response
     */
    public function callMethod($method, array $params = [], array $extra = [])
    {
        // Before run call need stop session
        if ($this->getContainer() !== null) {
            $this->getContainer()->get('session')->save();
        }

        // Call method
        $response = $this->client->call($method, $params, $extra);

        // After run call need restart session
        if ($this->getContainer() !== null) {
            $this->getContainer()->get('session')->migrate();
        }

        return $response;
    }
}
