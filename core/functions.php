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
use Core\Helper\ApplicationHelper;
use Core\Exception\BusinessException;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\HttpServer\Contract\RequestInterface;

if (! function_exists('t')) {
    /**
     * 多语言函数.
     */
    function t(string $key, array $replace = []): string
    {
        // 调用方式例如: t('validation.accepted')
        $acceptLanguage = ApplicationHelper::getContainer()->get(RequestInterface::class)->getHeaderLine('Accept-Language');
        $language = ! empty($acceptLanguage) ? explode(',', $acceptLanguage)[0] : 'zh_CN';
        return ApplicationHelper::getContainer()->get(TranslatorInterface::class)->trans($key, $replace, $language);
    }
}

if (! function_exists('error')) {
    /**
     * 抛出业务异常函数.
     */
    function error(int $code, ?string $message = null)
    {
        throw new BusinessException($code, $message);
    }
}
