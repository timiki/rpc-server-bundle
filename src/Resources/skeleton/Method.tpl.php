<?php echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use Timiki\Bundle\RpcServerBundle\Mapping as RPC;

/**
* @RPC\Method("<?php echo $method_name; ?>")
*/
class <?php echo $class_name; ?>
{
    /**
    * @RPC\Param()
    */
    protected $param;

    /**
    * @RPC\Execute()
    */
    public function execute()
    {
        // Method code...
    }
}