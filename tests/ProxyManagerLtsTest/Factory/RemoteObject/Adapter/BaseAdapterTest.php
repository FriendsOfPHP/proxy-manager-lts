<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Factory\RemoteObject\Adapter;

use Laminas\Server\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Factory\RemoteObject\Adapter\BaseAdapter;

/**
 * Tests for {@see \ProxyManagerLts\Factory\RemoteObject\Adapter\Soap}
 *
 * @group Coverage
 */
final class BaseAdapterTest extends TestCase
{
    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManagerLts\Factory\RemoteObject\Adapter\BaseAdapter::__construct
     * @covers \ProxyManagerLts\Factory\RemoteObject\Adapter\BaseAdapter::call
     * @covers \ProxyManagerLts\Factory\RemoteObject\Adapter\Soap::getServiceName
     */
    public function testBaseAdapter(): void
    {
        $client = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['call'])
            ->getMock();

        $adapter = $this->getMockForAbstractClass(
            BaseAdapter::class,
            [$client]
        );

        $client
            ->expects(self::once())
            ->method('call')
            ->with('foobarbaz', ['tab' => 'taz'])
            ->willReturn('baz');

        $adapter
            ->expects(self::once())
            ->method('getServiceName')
            ->with('foo', 'bar')
            ->willReturn('foobarbaz');

        self::assertSame('baz', $adapter->call('foo', 'bar', ['tab' => 'taz']));
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManagerLts\Factory\RemoteObject\Adapter\BaseAdapter::__construct
     * @covers \ProxyManagerLts\Factory\RemoteObject\Adapter\BaseAdapter::call
     * @covers \ProxyManagerLts\Factory\RemoteObject\Adapter\Soap::getServiceName
     */
    public function testBaseAdapterWithServiceMap(): void
    {
        $client = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['call'])
            ->getMock();

        $adapter = $this->getMockForAbstractClass(
            BaseAdapter::class,
            [$client, ['foobarbaz' => 'mapped']]
        );

        $client
            ->expects(self::once())
            ->method('call')
            ->with('mapped', ['tab' => 'taz'])
            ->willReturn('baz');

        $adapter
            ->expects(self::once())
            ->method('getServiceName')
            ->with('foo', 'bar')
            ->willReturn('foobarbaz');

        self::assertSame('baz', $adapter->call('foo', 'bar', ['tab' => 'taz']));
    }
}
