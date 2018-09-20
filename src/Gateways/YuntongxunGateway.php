<?php

/*
 * This file is part of the overtrue/easy-sms.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace JimChen\EasySms\Gateways;

use JimChen\EasySms\Contracts\MessageInterface;
use JimChen\EasySms\Contracts\PhoneNumberInterface;
use JimChen\EasySms\Exceptions\GatewayErrorException;
use JimChen\EasySms\Support\Config;
use JimChen\EasySms\Traits\HasHttpRequest;

/**
 * Class YuntongxunGateway.
 *
 * @see http://www.yuntongxun.com/doc/rest/sms/3_2_2_2.html
 */
class YuntongxunGateway extends Gateway
{
    use HasHttpRequest;

    const ENDPOINT_TEMPLATE = 'https://%s:%s/%s/%s/%s/%s/%s?sig=%s';

    const SERVER_IP = 'app.cloopen.com';

    const DEBUG_SERVER_IP = 'sandboxapp.cloopen.com';

    const DEBUG_TEMPLATE_ID = 1;

    const SERVER_PORT = '8883';

    const SDK_VERSION = '2013-12-26';

    const SUCCESS_CODE = '000000';

    /**
     * @param \JimChen\EasySms\Contracts\PhoneNumberInterface $to
     * @param \JimChen\EasySms\Contracts\MessageInterface     $message
     * @param \JimChen\EasySms\Support\Config                 $config
     *
     * @return array
     *
     * @throws \JimChen\EasySms\Exceptions\GatewayErrorException ;
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $datetime = date('YmdHis');

        $endpoint = $this->buildEndpoint('SMS', 'TemplateSMS', $datetime, $config);

        $result = $this->request('post', $endpoint, [
            'json' => [
                'to' => $to,
                'templateId' => (int) ($this->config->get('debug') ? self::DEBUG_TEMPLATE_ID : $message->getTemplate($this)),
                'appId' => $config->get('app_id'),
                'datas' => $message->getData($this),
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json;charset=utf-8',
                'Authorization' => base64_encode($config->get('account_sid').':'.$datetime),
            ],
        ]);

        if (self::SUCCESS_CODE != $result['statusCode']) {
            throw new GatewayErrorException($result['statusCode'], $result['statusCode'], $result);
        }

        return $result;
    }

    /**
     * Build endpoint url.
     *
     * @param string                           $type
     * @param string                           $resource
     * @param string                           $datetime
     * @param \JimChen\EasySms\Support\Config $config
     *
     * @return string
     */
    protected function buildEndpoint($type, $resource, $datetime, Config $config)
    {
        $serverIp = $this->config->get('debug') ? self::DEBUG_SERVER_IP : self::SERVER_IP;

        $accountType = $this->config->get('is_sub_account') ? 'SubAccounts' : 'Accounts';

        $sig = strtoupper(md5($config->get('account_sid').$config->get('account_token').$datetime));

        return sprintf(self::ENDPOINT_TEMPLATE, $serverIp, self::SERVER_PORT, self::SDK_VERSION, $accountType, $config->get('account_sid'), $type, $resource, $sig);
    }
}
