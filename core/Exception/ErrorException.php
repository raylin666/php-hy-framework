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
namespace Core\Exception;

use Core\Constants\ErrorCode;
use Throwable;

class ErrorException extends RuntimeException
{
    public function __construct(int $code = 0, ?string $message = null, ?Throwable $previous = null)
    {
        if (is_null($message)) {
            $message = ErrorCode::getMessage($code);
        }

        parent::__construct($code, $message, $previous);
    }
}
