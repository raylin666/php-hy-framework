<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Core\Helper;

class RequestHelper
{
    /**
     * 获取客户端IP地址.
     */
    public static function getClientIP()
    {
        $request = ApplicationHelper::getServerRequest();
        $ip = $request->getServerParams()['remote_addr'] ?? '0.0.0.0';
        $headers = $request->getHeaders();

        if (isset($headers['x-real-ip'])) {
            $ip = $headers['x-real-ip'][0];
        } elseif (isset($headers['x-forwarded-for'])) {
            $ip = $headers['x-forwarded-for'][0];
        } elseif (isset($headers['http_x_forwarded_for'])) {
            $ip = $headers['http_x_forwarded_for'][0];
        }

        return $ip;
    }
}
