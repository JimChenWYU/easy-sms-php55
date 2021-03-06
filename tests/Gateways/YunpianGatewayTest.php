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
use JimChen\EasySms\Gateways\YunpianGateway;
use JimChen\EasySms\Message;
use JimChen\EasySms\PhoneNumber;
use JimChen\EasySms\Support\Config;
use JimChen\EasySms\Tests\TestCase;

class YunpianGatewayTest extends TestCase
{
    public function testSend()
    {
        $config = [
            'api_key' => 'mock-api-key',
        ];
        $gateway = \Mockery::mock(YunpianGateway::class.'[request]', [$config])->shouldAllowMockingProtectedMethods();

        $gateway->shouldReceive('request')->with('post', 'https://sms.yunpian.com/v2/sms/single_send.json', [
            'form_params' => [
                'apikey' => 'mock-api-key',
                'mobile' => '18188888888',
                'text' => '【overtrue】This is a test message.',
            ],
            'exceptions' => false,
        ])->andReturn([
            'code' => 0,
            'msg' => '发送成功',
            'count' => 1, //成功发送的短信计费条数
            'fee' => 0.05,    //扣费条数，70个字一条，超出70个字时按每67字一条计
            'unit' => 'RMB',  // 计费单位
            'mobile' => '18188888888', // 发送手机号
            'sid' => 3310228982,   // 短信ID
        ], [
            'code' => 100,
            'msg' => '发送失败',
        ])->times(2);

        $message = new Message(['content' => '【overtrue】This is a test message.']);
        $config = new Config($config);
        $this->assertSame([
            'code' => 0,
            'msg' => '发送成功',
            'count' => 1, //成功发送的短信计费条数
            'fee' => 0.05,    //扣费条数，70个字一条，超出70个字时按每67字一条计
            'unit' => 'RMB',  // 计费单位
            'mobile' => '18188888888', // 发送手机号
            'sid' => 3310228982,   // 短信ID
        ], $gateway->send(new PhoneNumber(18188888888), $message, $config));

//        $this->expectException(GatewayErrorException::class);
//        $this->expectExceptionCode(100);
//        $this->expectExceptionMessage('发送失败');
        $this->setExpectedException(GatewayErrorException::class, '发送失败', 100);

        $gateway->send(new PhoneNumber(18188888888), $message, $config);
    }
}
