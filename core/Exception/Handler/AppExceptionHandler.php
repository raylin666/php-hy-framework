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
namespace Core\Exception\Handler;

use Core\Constants\HttpErrorCode;
use Core\Constants\Log;
use Core\Decorator\ResponseDecorator;
use Core\Exception\BusinessException;
use Core\Exception\ErrorException;
use Core\Helper\ApplicationHelper;
use Hyperf\Codec\Json;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

use function Hyperf\Support\make;

class AppExceptionHandler extends ExceptionHandler
{
    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = ApplicationHelper::getLogger()->get(Log::APP_EXCEPTION);
    }

    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $errLog = [
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'info' => $throwable->getTraceAsString(),
        ];

        $decorator = make(ResponseDecorator::class);
        $code = HttpErrorCode::HTTP_INTERNAL_SERVER_ERROR;
        $message = $throwable->getMessage();
        switch (get_class($throwable)) {
            case BusinessException::class:
                $this->logger->warning($message, $errLog);
                $code = HttpErrorCode::HTTP_BAD_REQUEST;
                $decorator->withCode($throwable->getCode());
                $message && $decorator->withMessage($message);
                break;
            case ErrorException::class:
                $this->logger->error($message, $errLog);
                $throwable->getCode() ? $decorator->withCode($throwable->getCode()) : $decorator->withCode($code);
                $message && $decorator->withMessage($message);
                break;
            default:
                $this->logger->error($message, $errLog);
                $decorator->withCode($code);
        }

        return $response
            ->withHeader('Server', ApplicationHelper::getConfig()->get('http.server'))
            ->withAddedHeader('Content-Type', ApplicationHelper::getConfig()->get('http.content-type'))
            ->withStatus($code)
            ->withBody(new SwooleStream(Json::encode($decorator->toArray())));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
