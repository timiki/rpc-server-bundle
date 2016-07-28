<?php

namespace Timiki\Bundle\RpcServerBundle\Tests;

use PHPUnit_Framework_TestCase;

class MappingTest extends PHPUnit_Framework_TestCase
{
    public function testMapping()
    {
        try {

            $mapper = Utils::getMapper(__DIR__.DIRECTORY_SEPARATOR.'Method');

            self::assertCount(6, $mapper->loadMetadata());

        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }
    }

    public function testNotSetExecuteMethod()
    {
        $mapper = Utils::getMapper();

        try {

            $mapper->loadFileMetadata(__DIR__.DIRECTORY_SEPARATOR.'InvalidMethod'.DIRECTORY_SEPARATOR.'NoExecuteMethod.php');

            self::fail('Need exception');

        } catch (\Exception $e) {
            self::assertTrue(true);
        }
    }

    public function testNoRpcClass()
    {
        $mapper = Utils::getMapper();
        $result = $mapper->loadFileMetadata(__DIR__.DIRECTORY_SEPARATOR.'InvalidMethod'.DIRECTORY_SEPARATOR.'NoRpcClass.php');

        self::assertEmpty($result);
    }

}
