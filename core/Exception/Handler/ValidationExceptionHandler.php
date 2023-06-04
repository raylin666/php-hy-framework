<?php
/**
 * MineAdmin is committed to providing solutions for quickly building web applications
 * Please view the LICENSE file that was distributed with this source code,
 * For the full copyright and license information.
 * Thank you very much for using MineAdmin.
 *
 * @Author X.Mo<root@imoi.cn>
 * @Link   https://gitee.com/xmo/MineAdmin
 */

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Core\Exception\Handler;

use Core\Constants\ErrorCode;
use Core\Decorator\ResponseDecorator;
use Core\Helper\ApplicationHelper;
use Hyperf\Codec\Json;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

use function Hyperf\Support\make;

class ValidationExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $this->stopPropagation();
        $code = ErrorCode::UNPROCESSABLE_ENTITY_ERROR;
        /** @var ValidationException $throwable */
        $message = $throwable->validator->errors()->first();
        $decorator = make(ResponseDecorator::class);
        $decorator->withCode($code);
        $decorator->withMessage($message);
        return $response
            ->withHeader('Server', ApplicationHelper::getConfig()->get('http.server'))
            ->withAddedHeader('Content-Type', ApplicationHelper::getConfig()->get('http.content-type'))
            ->withStatus($code)
            ->withBody(new SwooleStream(Json::encode($decorator->toArray())));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ValidationException;
    }
}
