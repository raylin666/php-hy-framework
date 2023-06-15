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
namespace Core\Middleware;

use Core\Constants\HttpErrorCode;
use Core\Decorator\ResponseDecorator;
use Core\Helper\ApplicationHelper;
use Hyperf\Codec\Json;
use Hyperf\Context\Context;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\CoreMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Hyperf\Support\make;

class HttpCoreMiddleware extends CoreMiddleware
{
    /**
     * 跨域
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $this->coreResponseHeaders();

        if ($request->getMethod() == 'OPTIONS') {
            return $response;
        }

        return parent::process($request, $handler);
    }

    /**
     * Handle the response when cannot found any routes.
     */
    protected function handleNotFound(ServerRequestInterface $request): ResponseInterface
    {
        $code = HttpErrorCode::HTTP_NOT_FOUND;
        $decorator = make(ResponseDecorator::class);
        $decorator->withCode($code);
        return $this->response()
            ->withHeader('Server', ApplicationHelper::getConfig()->get('http.server'))
            ->withAddedHeader('Content-Type', ApplicationHelper::getConfig()->get('http.content-type'))
            ->withStatus($code)
            ->withBody(new SwooleStream(Json::encode($decorator->toArray())));
    }

    /**
     * Handle the response when the routes found but doesn't match any available methods.
     */
    protected function handleMethodNotAllowed(array $methods, ServerRequestInterface $request): ResponseInterface
    {
        $code = HttpErrorCode::HTTP_METHOD_NOT_ALLOWED;
        $decorator = make(ResponseDecorator::class);
        $decorator->withCode($code);
        $decorator->withMessage(HttpErrorCode::getMessage($code) . ', Allow: ' . implode(', ', $methods));
        return $this->response()
            ->withHeader('Server', ApplicationHelper::getConfig()->get('http.server'))
            ->withAddedHeader('Content-Type', ApplicationHelper::getConfig()->get('http.content-type'))
            ->withStatus($code)
            ->withBody(new SwooleStream(Json::encode($decorator->toArray())));
    }

    /**
     * 设置跨域响应头参数.
     * @return ResponseInterface
     */
    protected function coreResponseHeaders(): ResponseInterface
    {
        $response = Context::get(ResponseInterface::class);
        $response = $response
            ->withHeader('Server', ApplicationHelper::getConfig()->get('http.server'))
            ->withHeader('Access-Control-Allow-Origin', ApplicationHelper::getConfig()->get('http.allow-origin'))
            ->withHeader('Access-Control-Allow-Methods', ApplicationHelper::getConfig()->get('http.allow-method'))
            ->withHeader('Access-Control-Allow-Credentials', ApplicationHelper::getConfig()->get('http.allow-credentials'))
            ->withHeader('Access-Control-Allow-Headers', ApplicationHelper::getConfig()->get('http.allow-headers'));

        Context::set(ResponseInterface::class, $response);
        return $response;
    }
}
