<?php

namespace Timiki\Bundle\RpcServerBundle\Server;

use Timiki\Bundle\RpcServerBundle\RpcServer;

/**
 * Abstract Method
 *
 *     Add next method to your code:
 *
 *     public function beforeExecute()
 *     {
 *         // Your method code
 *     }
 *
 *     public function execute()
 *     {
 *         // Your method code
 *     }
 *
 *
 */
abstract class Method
{
    /**
     * The server instance
     *
     * @var string
     */
    private $server;

    /**
     * The container instance
     *
     * @var \Symfony\Component\DependencyInjection\Container
     */
    private $container;

//    /**
//     * Event before execute method
//     */
//    public function beforeExecute()
//    {
//        // Your method code
//    }
//
//    /**
//     * Execute the server method
//     */
//    public function execute()
//    {
//        // Your method code
//    }

    /**
     * Get the method params
     *
     * @return array
     */
    public function getParams()
    {
        //
        // Example code:
        //  return [
        //      ['param name', 'param validate', 'param default']
        //      ......
        //  ];
        //

        return [];
    }

    /**
     * Get the method description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * Get current locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->getServer()->getLocale();
    }

    /**
     * Translates the given message
     *
     * @param       $id
     * @param array $parameters
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        return $this->getContainer()->get('translator')->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Translates the given choice message by choosing a translation according to a number
     *
     * @param       $id
     * @param       $number
     * @param array $parameters
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        return $this->getContainer()->get('translator')->transChoice($id, $number, $parameters, $domain, $locale);
    }

    /**
     * Get the method name
     *
     * @return string|null
     */
    public function getName()
    {
        $className = get_class($this);
        $className = explode('\\', $className);

        return $className[count($className) - 1];
    }

    /**
     * Call method
     *
     * @param       $method
     * @param array $params
     * @param array $extra
     * @return mixed
     */
    public function call($method, $params = [], $extra = [])
    {
        return $this->getServer()->callMethod($method, $params, $extra);
    }

    /**
     * Set container instance
     *
     * @param $container
     * @return $this
     */
    public function setContainer(&$container)
    {
        if ($container instanceof \Symfony\Component\DependencyInjection\Container) {
            $this->container = $container;
        }

        return $this;
    }

    /**
     * Get container instance
     *
     * @return \Symfony\Component\DependencyInjection\Container
     */
    public function &getContainer()
    {
        return $this->container;
    }

    /**
     * Set server instance
     *
     * @param $server
     * @return Method
     */
    public function setServer(&$server)
    {
        if ($server instanceof RpcServer) {
            $this->server = $server;
        }

        return $this;
    }

    /**
     * Get server instance
     *
     * @return null|RpcServer
     */
    public function &getServer()
    {
        return $this->server;
    }
}
