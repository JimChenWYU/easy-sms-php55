<?php

/*
 * This file is part of the overtrue/easy-sms.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace JimChen\EasySms\Tests\Gateways;

use JimChen\EasySms\Contracts\MessageInterface;
use JimChen\EasySms\Contracts\PhoneNumberInterface;
use JimChen\EasySms\Gateways\Gateway;
use JimChen\EasySms\Support\Config;
use JimChen\EasySms\Tests\TestCase;

class GatewayTest extends TestCase
{
    public function testTimeout()
    {
        $gateway = new DummyGatewayForGatewayTest(['foo' => 'bar']);

        $this->assertInstanceOf(Config::class, $gateway->getConfig());
        $this->assertSame(5.0, $gateway->getTimeout());
        $gateway->setTimeout(4.0);
        $this->assertSame(4.0, $gateway->getTimeout());

        $gateway = new DummyGatewayForGatewayTest(['foo' => 'bar', 'timeout' => 12.0]);
        $this->assertSame(12.0, $gateway->getTimeout());
    }

    public function testConfigSetterAndGetter()
    {
        $gateway = new DummyGatewayForGatewayTest(['foo' => 'bar']);

        $this->assertInstanceOf(Config::class, $gateway->getConfig());

        $config = new Config(['name' => 'overtrue']);
        $this->assertSame($config, $gateway->setConfig($config)->getConfig());
    }
}

class DummyGatewayForGatewayTest extends Gateway
{
    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        return 'mock-result';
    }
}
