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
 * Class HuyiGateway.
 *
 * @see http://www.ihuyi.com/api/sms.html
 */
class HuyiGateway extends Gateway
{
    use HasHttpRequest;

    const ENDPOINT_URL = 'http://106.ihuyi.com/webservice/sms.php?method=Submit';

    const ENDPOINT_FORMAT = 'json';

    const SUCCESS_CODE = 2;

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
        $params = [
            'account' => $config->get('api_id'),
            'mobile' => $to->getIDDCode() ? \sprintf('%s %s', $to->getIDDCode(), $to->getNumber()) : $to->getNumber(),
            'content' => $message->getContent($this),
            'time' => time(),
            'format' => self::ENDPOINT_FORMAT,
        ];

        $params['password'] = $this->generateSign($params);

        $result = $this->post(self::ENDPOINT_URL, $params);

        if (self::SUCCESS_CODE != $result['code']) {
            throw new GatewayErrorException($result['msg'], $result['code'], $result);
        }

        return $result;
    }

    /**
     * Generate Sign.
     *
     * @param array $params
     *
     * @return string
     */
    protected function generateSign($params)
    {
        return md5($params['account'].$this->config->get('api_key').$params['mobile'].$params['content'].$params['time']);
    }
}
