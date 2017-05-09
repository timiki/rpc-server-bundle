JSON-RPC server bundle for symfony
==================================

[![Build Status](https://travis-ci.org/timiki/rpc-server-bundle.svg?branch=master)](https://travis-ci.org/timiki/rpc-server-bundle)


JSON-RPC is a remote procedure call protocol encoded in JSON. It is a very simple protocol (and very similar to XML-RPC), defining only a handful of data types and commands. 
JSON-RPC allows for notifications (data sent to the server that does not require a response) and for multiple calls to be sent to the server which may be answered out of order.

[Wikipedia][1] | [Specification][2]

Install
-------

Add to composer from command line

    composer require timiki/rpc-client

or add in composer.json

    "require"     : {
        "timiki/rpc-server-bundle" : "^2.0"
    }

Configs
-------
    
    # RPC server
    rpc_server:
        mapping: ~
        cache: ~
        serializer: ~
        
Main configs:

**mapping** Path to JSON-RPC methods. Default null (Search dir Method in all bundles).

    // Methods in bundle
     
    mapping: 
        - "@AppBundle/Method"
        - "@MyBundle/Method"
     
    
    mapping: "@AppBundle/Method"
     
    // Or methods by path
     
    mapping: 
        - "path/to/method"
        - "path/to/other/method"
        
    mapping: "path/to/method"
    
**cache** Cache service id. Service must be instance of Doctrine\Common\Cache\CacheProvider.

    cache: service.cache.id

**serializer** Serializer service id. Service must be instance of Timiki\Bundle\RpcServerBundle\Serializer\SerializerInterface.

    cache: service.cache.id

Controller
----------

Default:

You can use you own controller for JSON-RPC request. For example:

```php
    <?php
    
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Request;
    
    class RpcController extends Controller
    {
        public function indexAction(Request $request)
        {
            return $this->get('rpc.server.http_handler')->handleHttpRequest($request);
        }
    }
```

or add default JSON-RPC route (default POST to /rpc) to you routing.yml

    rpc:
        resource: "@RpcServerBundle/Controller/"
        type:     annotation


If web site and JSON-RPC server located on a different domain remember about [CORS][3]. Use custom Controller for solve it.




Method
------

```php

    <?php
    
    use Timiki\Bundle\RpcServerBundle\Mapping as Rpc;
    use Symfony\Component\Validator\Constraints as Assert;

    /**
     * @Rpc\Method("name")
     * @Rpc\Roles({
     *   "ROLE_NAME"
     * })
     * @Rpc\Cache(lifetime=3600)
     * @Rpc\Version(1)
     */
    class Method
    {
        /**
         * @Rpc\Param()
         * @Assert\NotBlank()
         */
        protected $param;
    
        /**
         * @Rpc\Execute()
         */
        public function execute()
        {
            $param = $this->param;
            
            ...
            
            return $result;
        }
    }
    
```

Annotation
----------

**@Method**

Define class as JSON-RPC method. 

```php
    @Method("method name")
```

**@Roles**

Set roles for access to method. If user not granted for access server return error with message "Method not granted" and code "-32001".

```php
    @Roles({
      "ROLE_NAME",
      "ROLE_OTHER",
    })
```

**@Cache**

If define cache in configs it set response lifetime.

```php
    @Cache(lifetime=3600)
```

**@Param**

Define JSON-RPC params. Use Symfony\Component\Validator\Constraints for validate it.

```php
    /**
     * @Param()
     */
    protected $param;
    
    /**
     * @Param()
     */
    protected $param = null'; // Default value for param
```

**@Execute**

Define execute function in class.

```php
    /**
     * @Rpc\Execute()
     */
    public function someMethod()
    {
        // Code
    }
```

[1]: https://wikipedia.org/wiki/JSON-RPC
[2]: http://www.jsonrpc.org/specification
[3]: https://wikipedia.org/wiki/Cross-origin_resource_sharing