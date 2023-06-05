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
namespace Core\Dependencies;

use Core\Decorator\ResponseDecorator;
use Core\Helper\ApplicationHelper;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Response as HttpServerResponse;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use function Hyperf\Support\make;

class Response extends HttpServerResponse
{
    /**
     * @param null|array|Arrayable|Jsonable|string $data
     * @return PsrResponseInterface
     */
    public function json($data): PsrResponseInterface
    {
        $decorator = make(ResponseDecorator::class);
        $decorator->withData($data);
        $data = $this->toJson($decorator->toArray());
        return $this->getResponse()
            ->withHeader('Server', ApplicationHelper::getConfig()->get('http.server'))
            ->withAddedHeader('Content-Type', ApplicationHelper::getConfig()->get('http.content-type'))
            ->withBody(new SwooleStream($data));
    }
}
