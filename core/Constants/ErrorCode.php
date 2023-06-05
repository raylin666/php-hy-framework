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
namespace Core\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

#[Constants]
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("Internal Server Error")
     */
    public const SERVER_ERROR = 500;

    /**
     * @Message("Bad Request")
     */
    public const BAD_REQUEST_ERROR = 400;

    /**
     * @Message("Not Found")
     */
    public const NOT_FOUND_ERROR = 404;

    /**
     * @Message("Method Not Allowed")
     */
    public const METHOD_NOT_ALLOWED_ERROR = 405;

    /**
     * @Message("Unprocessable Entity")
     */
    public const UNPROCESSABLE_ENTITY_ERROR = 422;
}
