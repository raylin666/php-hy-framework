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

return [
    Core\Contract\ModuleInfoInterface::class => Core\Decorator\ModuleInfoDecorator::class,
    Hyperf\HttpServer\Contract\ResponseInterface::class => Core\Dependencies\Response::class,
    Hyperf\HttpServer\CoreMiddleware::class => Core\Middleware\HttpCoreMiddleware::class,
];
