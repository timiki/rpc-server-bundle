<?php

declare(strict_types=1);

namespace Tests\Timiki\Bundle\RpcServerBundle\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MethodTest extends WebTestCase
{
    private KernelBrowser|null $client = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
    }

    /**
     * @group functional
     *
     * @dataProvider dataSets
     */
    public function test($data, $expected)
    {
        $response = $this->doRequest($data);
        $content = \json_decode($response->getContent(), true);

        $this->assertEquals($expected, $content);
    }

    public function dataSets()
    {
        yield 'get_context' => $this->getContext();
        yield 'get_data' => $this->getData();
        yield 'getError' => $this->getError();
        yield 'multiRequests' => $this->multiRequests();
        yield 'subtractSuccess1' => $this->subtractSuccess1();
        yield 'subtractSuccess2' => $this->subtractSuccess2();
        yield 'subtractSuccess3' => $this->subtractSuccess3();
        yield 'update' => $this->update();
        yield 'foobarMethodNotFound' => $this->foobarMethodNotFound();
        yield 'foobarParseError' => $this->foobarParseError();
        yield 'sumParseError' => $this->sumParseError();
        yield 'invalidRequest' => $this->invalidRequest();
        yield 'invalidRequest2' => $this->invalidRequest2();
        yield 'invalidRequest3' => $this->invalidRequest3();
        yield 'invalidRequest4' => $this->invalidRequest4();
    }

    private function getContext()
    {
        $data = [
            [
                'jsonrpc' => '2.0',
                'method' => 'get_context',
                'params' => [
                    'b' => 2,
                ],
                'id' => 9,
            ],
            [
                'jsonrpc' => '2.0',
                'result' => [
                    'a' => 1,
                    'b' => 2,
                ],
                'id' => 9,
            ],
        ];

        return $data;
    }

    private function getData()
    {
        $data = [
            [
                'jsonrpc' => '2.0',
                'method' => 'get_data',
                'params' => [
                    'a' => 1111,
                ],
                'id' => 9,
            ],
            [
                'jsonrpc' => '2.0',
                'result' => [
                    'hello',
                    5,
                ],
                'id' => 9,
            ],
        ];

        return $data;
    }

    private function multiRequests()
    {
        $data = [
            [
                'jsonrpc' => '2.0',
                'method' => 'sum',
                'params' => [1, 2, 4],
                'id' => 1,
            ],
            [
                'jsonrpc' => '2.0',
                'method' => 'notify_hello',
                'params' => [7],
            ],
            [
                'jsonrpc' => '2.0',
                'method' => 'subtract',
                'params' => [42, 23],
                'id' => 2,
            ],
            [
                'foo' => 'boo',
            ],
            [
                'jsonrpc' => '2.0',
                'method' => 'foo.get',
                'params' => [
                    'name' => 'myself',
                ],
                'id' => 5,
            ],
            [
                'jsonrpc' => '2.0',
                'method' => 'get_data',
                'id' => 9,
            ],
        ];

        $expected = [
            [
                'jsonrpc' => '2.0',
                'result' => 7,
                'id' => 1,
            ],
            [
                'jsonrpc' => '2.0',
                'result' => 19,
                'id' => 2,
            ],
            [
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32600,
                    'message' => 'Invalid Request',
                ],
                'id' => null,
            ],
            [
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32601,
                    'message' => 'Method not found',
                    'data' => 'foo.get',
                ],
                'id' => 5,
            ],
            [
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32602,
                    'message' => 'Invalid params',
                    'data' => [
                        'a' => [
                            'This value should not be blank.',
                        ],
                    ],
                ],
                'id' => 9,
            ],
        ];

        return [$data, $expected];
    }

    private function getError()
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'get_error',
            'id' => 1,
        ];

        $expected = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => -32002,
                'message' => 'Exception',
                'data' => 'Data',
            ],
            'id' => 1,
        ];

        return [$data, $expected];
    }

    private function subtractSuccess1()
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'subtract',
            'params' => [42, 23],
            'id' => 1,
        ];

        $expected = [
            'jsonrpc' => '2.0',
            'result' => 19,
            'id' => 1,
        ];

        return [$data, $expected];
    }

    private function subtractSuccess2()
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'subtract',
            'params' => [23, 42],
            'id' => 1,
        ];

        $expected = [
            'jsonrpc' => '2.0',
            'result' => -19,
            'id' => 1,
        ];

        return [$data, $expected];
    }

    private function subtractSuccess3()
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'subtract',
            'params' => ['subtrahend' => 23, 'minuend' => 42],
            'id' => 1,
        ];

        $expected = [
            'jsonrpc' => '2.0',
            'result' => -19,
            'id' => 1,
        ];

        return [$data, $expected];
    }

    private function update()
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'update',
            'params' => [1, 2, 3, 4, 5],
        ];

        $expected = '';

        return [$data, $expected];
    }

    private function foobarMethodNotFound()
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'foobar',
            'id' => 1,
        ];

        $expected = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => -32601,
                'message' => 'Method not found',
                'data' => 'foobar',
            ],
            'id' => 1,
        ];

        return [$data, $expected];
    }

    private function foobarParseError()
    {
        $data = '{"jsonrpc": "2.0", "method": "foobar, "params": "bar", "baz]'; // invalid json

        $expected = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => -32700,
                'message' => 'Parse error',
            ],
            'id' => null,
        ];

        return [$data, $expected];
    }

    private function sumParseError()
    {
        $data = '[{"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"}, {"jsonrpc": "2.0", "method"]'; // invalid json

        $expected = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => -32700,
                'message' => 'Parse error',
            ],
            'id' => null,
        ];

        return [$data, $expected];
    }

    private function invalidRequest()
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 1,
            'params' => 'bar',
        ];

        $expected = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => -32600,
                'message' => 'Invalid Request',
            ],
            'id' => null,
        ];

        return [$data, $expected];
    }

    private function invalidRequest2()
    {
        $data = [];

        $expected = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => -32600,
                'message' => 'Invalid Request',
            ],
            'id' => null,
        ];

        return [$data, $expected];
    }

    private function invalidRequest3()
    {
        $data = [1];

        $expected = [
            [
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32600,
                    'message' => 'Invalid Request',
                ],
                'id' => null,
            ],
        ];

        return [$data, $expected];
    }

    private function invalidRequest4()
    {
        $data = [1, 2, 3];

        $expectedSnapshot = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => -32600,
                'message' => 'Invalid Request',
            ],
            'id' => null,
        ];

        $expected = [
            $expectedSnapshot,
            $expectedSnapshot,
            $expectedSnapshot,
        ];

        return [$data, $expected];
    }

    private function doRequest($data, $version = 'v1'): Response
    {
        if (\is_array($data)) {
            $data = \json_encode($data);
        }

        $this->client->request(Request::METHOD_POST, '/'.$version, [], [], [], $data);

        return $this->client->getResponse();
    }
}
