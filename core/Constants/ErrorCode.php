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

/**
 * 业务状态码
 *
 * #### 错误码为 6 位数
 *
 * | 1 | 00 | 001 |
 * | :------ | :------ | :------ |
 * | 服务级错误码 | 模块级错误码 | 具体错误码 |
 *
 * - 服务级错误码：1 位数进行表示，比如 1 为系统级错误；2 为普通错误，通常是由用户非法操作引起。
 * - 模块级错误码：2 位数进行表示，比如 00 为系统相关; 01 为字典模块；02 为用户模块 ...。
 * - 具体的错误码：3 位数进行表示，比如 001 为手机号不合法；002 为验证码输入错误。
 *
 * @method static getMessage($code)
 * @Constants
 */
#[Constants]
class ErrorCode extends AbstractConstants
{

}
