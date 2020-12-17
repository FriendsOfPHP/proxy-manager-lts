<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Factory\RemoteObject\Adapter;

use Laminas\Server\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Factory\RemoteObject\Adapter\JsonRpc;

/**
 * Tests for {@see \ProxyManagerLts\Factory\RemoteObject\Adapter\JsonRpc}
 *
 * @group Coverage
 */
final class JsonRpcTest extends TestCase
{
    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManagerLts\Factory\RemoteObject\Adapter\JsonRpc::__construct
     * @covers \ProxyManagerLts\Factory\RemoteObject\Adapter\JsonRpc::getServiceName
     */
    public function testCanBuildAdapterWithJsonRpcClient(): void
    {
        $client = $this->getMockBuilder(Client::class)->setMethods(['call'])->getMock();

        $adapter = new JsonRpc($client);

        $client
            ->expects(self::once())
            ->method('call')
            ->with('foo.bar', ['tab' => 'taz'])
            ->willReturn('baz');

        self::assertSame('baz', $adapter->call('foo', 'bar', ['tab' => 'taz']));
    }
}
