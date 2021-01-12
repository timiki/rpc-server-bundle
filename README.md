JSON-RPC server bundle for symfony
==================================

[![Build Status](https://travis-ci.com/timiki/rpc-server-bundle.svg?branch=master)](https://travis-ci.com/timiki/rpc-server-bundle)


JSON-RPC is a remote procedure call protocol encoded in JSON. It is a very simple protocol (and very similar to XML-RPC), defining only a handful of data types and commands. 
JSON-RPC allows for notifications (data sent to the server that does not require a response) and for multiple calls to be sent to the server which may be answered out of order.

[Wikipedia][1] | [Specification][2]

Install
-------

Symfony >= 4.3 use version ^4.1

```bash
composer require timiki/rpc-server-bundle "^4.1"
```

Symfony < 4.3 use version ^4.0

```bash
composer require timiki/rpc-server-bundle "^4.0"
```

Configs
-------

```yaml

rpc_server:
    
    # mapping configs
    # 
    # Default mapping
    #   mapping: '%kernel.root_dir%/Method' 
    #
    # Multi dir mapping
    #   mapping:
    #   - '%kernel.root_dir%/Method1'
    #   - '%kernel.root_dir%/Method2'  
    # 
    # Multi handler|dir mapping
    #   mapping:
    #    v1:
    #       - '%kernel.root_dir%/Method/V1'
    #    v2:
    #       - '%kernel.root_dir%/Method/V2'
    #   

    mapping: '%kernel.root_dir%/Method'

        v1:
        - '%kernel.root_dir%/Method/V1'
    
    # id cache service, must be instance of Doctrine\Common\Cache\CacheProvider

    cache: ~

    # id serializer service, must be instance of Timiki\Bundle\RpcServerBundle\Serializer\SerializerInterface
    # by default use 'rpc.server.serializer.base' service

    serializer: ~
    
``` 

Add methods dir to exclude from autowire

```yaml
    App\:
        resource: '../src/*'
        exclude: '../src/{Method}'
```

Controller
----------

Default:

You can use you own controller for JSON-RPC request. For example:

```php
<?php

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class RpcController extends AbstractController
{
    public function indexAction(Request $request)
    {
        return $this->get('rpc.server.http_handler.default')->handleHttpRequest($request);
    }
}
```

or add default JSON-RPC route (default POST to /rpc) to you routing.yml

```yaml    
rpc:
    path:     /rpc
    defaults: { _controller: RpcServerBundle:Rpc:handler }
    methods:  [POST]
```

If web site and JSON-RPC server located on a different domain remember about [CORS][3].


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

Serialize
----------

For convert output result from method to array in bundle used serializer

Config for serializer:

```yaml    
rpc:
    ...
    serializer: 'rpc.server.serializer.base' # serialize service id
```

In bundle include next serializers:

rpc.server.serializer.base - (default) simple convert output result to array
rpc.server.serializer.role - use user roles as @Group (@see https://symfony.com/doc/current/components/serializer.html) for control access to output array


Create custom serializer

Here is an example of a simple class for serialization. All serializer must return array which will be convert to result json.

```php

<?php

namespace App\Serializer;

use Timiki\Bundle\RpcServerBundle\Serializer\SerializerInterface;

class MySerializer implements SerializerInterface
{
    /**
     * Serialize data.
     *
     * @param mixed $data
     *
     * @return array
     */
    public function serialize($data)
    {
        return (array) $data;
    }
}

```

And then add custom serializer service id to config

```yaml    
rpc:
    ...
    serializer: 'MySerializer' # serialize service id
```


[1]: https://wikipedia.org/wiki/JSON-RPC
[2]: http://www.jsonrpc.org/specification
[3]: https://wikipedia.org/wiki/Cross-origin_resource_sharing