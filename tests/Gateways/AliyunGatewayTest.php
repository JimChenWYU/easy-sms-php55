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
use JimChen\EasySms\Gateways\AliyunGateway;
use JimChen\EasySms\Message;
use JimChen\EasySms\PhoneNumber;
use JimChen\EasySms\Support\Config;
use JimChen\EasySms\Tests\TestCase;

class AliyunGatewayTest extends TestCase
{
    public function testSend()
    {
        $config = [
            'access_key_id' => 'mock-api-key',
            'access_key_secret' => 'mock-api-secret',
            'sign_name' => 'mock-api-sign-name',
            'template_code' => 'mock-template-code',
        ];
        $gateway = \Mockery::mock(AliyunGateway::class.'[get]', [$config])->shouldAllowMockingProtectedMethods();

        $expected = [
            'RegionId' => 'cn-hangzhou',
            'AccessKeyId' => 'mock-api-key',
            'Format' => 'JSON',
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureVersion' => '1.0',
            // 'SignatureNonce' => uniqid(),
            // 'Timestamp' => date('Y-m-d\TH:i:s\Z'),
            'Action' => 'SendSms',
            'Version' => '2017-05-25',
            'PhoneNumbers' => strval(new PhoneNumber(18888888888)),
            'SignName' => 'mock-api-sign-name',
            'TemplateCode' => 'mock-template-code',
            'TemplateParam' => json_encode(['code' => '123456']),
        ];
        $gateway->shouldReceive('get')
            ->with(AliyunGateway::ENDPOINT_URL, \Mockery::on(function ($params) use ($expected) {
                if (empty($params['Signature'])) {
                    return false;
                }

                unset($params['SignatureNonce'], $params['Timestamp'], $params['Signature']);

                ksort($params);
                ksort($expected);

                return $params == $expected;
            }))
            ->andReturn([
                'Code' => 'OK',
                'Message' => 'mock-result',
            ], [
                'Code' => 1234,
                'Message' => 'mock-err-msg',
            ])
            ->twice();

        $message = new Message([
            'template' => 'mock-template-code',
            'data' => ['code' => '123456'],
        ]);

        $config = new Config($config);

        $this->assertSame([
            'Code' => 'OK',
            'Message' => 'mock-result',
        ], $gateway->send(new PhoneNumber(18888888888), $message, $config));

//        $this->expectException(GatewayErrorException::class);
//        $this->expectExceptionCode(1234);
//        $this->expectExceptionMessage('mock-err-msg');

        $this->setExpectedException(GatewayErrorException::class, 'mock-err-msg', 1234);

        $gateway->send(new PhoneNumber(18888888888), $message, $config);
    }
}
