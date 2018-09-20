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

use JimChen\EasySms\Exceptions\GatewayErrorException;
use JimChen\EasySms\Gateways\QcloudGateway;
use JimChen\EasySms\Message;
use JimChen\EasySms\PhoneNumber;
use JimChen\EasySms\Support\Config;
use JimChen\EasySms\Tests\TestCase;

class QcloudGatewayTest extends TestCase
{
    public function testSend()
    {
        $config = [
            'sdk_app_id' => 'mock-sdk-app-id',
            'app_key' => 'mock-api-key',
        ];
        $gateway = \Mockery::mock(QcloudGateway::class.'[request]', [$config])->shouldAllowMockingProtectedMethods();

        $expected = [
            'tel' => [
                'nationcode' => '86',
                'mobile' => strval(new PhoneNumber(18888888888)),
            ],
            'type' => 0,
            'msg' => 'This is a test message.',
            'timestamp' => time(),
            'extend' => '',
            'ext' => '',
        ];

        $gateway->shouldReceive('request')
                ->andReturn([
                    'result' => 0,
                    'errmsg' => 'OK',
                    'ext' => '',
                    'sid' => 3310228982,
                    'fee' => 1,
                ], [
                    'result' => 1001,
                    'errmsg' => 'sig校验失败',
                ])->twice();

        $message = new Message(['data' => ['type' => 0], 'content' => 'This is a test message.']);

        $config = new Config($config);

        $this->assertSame([
            'result' => 0,
            'errmsg' => 'OK',
            'ext' => '',
            'sid' => 3310228982,
            'fee' => 1,
        ], $gateway->send(new PhoneNumber(18888888888), $message, $config));

//        $this->expectException(GatewayErrorException::class);
//        $this->expectExceptionCode(1001);
//        $this->expectExceptionMessage('sig校验失败');
        $this->setExpectedException(GatewayErrorException::class, 'sig校验失败', 1001);

        $gateway->send(new PhoneNumber(18888888888), $message, $config);
    }

    public function testSendUsingNationCode()
    {
        $config = [
            'sdk_app_id' => 'mock-sdk-app-id',
            'app_key' => 'mock-api-key',
        ];
        $gateway = \Mockery::mock(QcloudGateway::class.'[request]', [$config])->shouldAllowMockingProtectedMethods();

        $expected = [
            'tel' => [
                'nationcode' => 251,
                'mobile' => 18888888888,
            ],
            'type' => 0,
            'msg' => 'This is a test message.',
            'timestamp' => time(),
            'extend' => '',
            'ext' => '',
        ];

        $gateway->shouldReceive('request')
            ->andReturn([
                'result' => 0,
                'errmsg' => 'OK',
                'ext' => '',
                'sid' => 3310228982,
                'fee' => 1,
            ], [
                'result' => 1001,
                'errmsg' => 'sig校验失败',
            ])->twice();

        $message = new Message(['data' => ['type' => 0], 'content' => 'This is a test message.']);

        $config = new Config($config);

        $this->assertSame([
            'result' => 0,
            'errmsg' => 'OK',
            'ext' => '',
            'sid' => 3310228982,
            'fee' => 1,
        ], $gateway->send(new PhoneNumber(18888888888, 251), $message, $config));

//        $this->expectException(GatewayErrorException::class);
//        $this->expectExceptionCode(1001);
//        $this->expectExceptionMessage('sig校验失败');
        $this->setExpectedException(GatewayErrorException::class, 'sig校验失败', 1001);

        $gateway->send(new PhoneNumber(18888888888, 251), $message, $config);
    }
}
