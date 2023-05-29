# HY Web API 框架 (基于 Hyperf)

### 获取项目

```shell
git clone git@github.com:raylin666/php-hy-framework.git
```

### 查看命令行列表

```shell
php bin/server.php
```

| 命令                    | 描述            |
|-----------------------|---------------|
| core:command          | 创建命令文件        |
| core:controller       | 生成控制器命令       |
| core:jwt              | 生成 JWT 系统密钥   |
| core:logic            | 生成业务逻辑层命令     |
| core:migrate-gen      | 生成数据表迁移文件     |
| core:migrate-rollback | 运行模块的迁移回滚类    |
| core:migrate-run      | 运行数据表迁移       |
| core:model            | 生成数据表模型       |
| core:module           | 生成业务模块/模块配置更新 |
| core:seeder-gen       | 生成数据表迁移种子数据文件 |
| core:seeder-run       | 运行数据表迁移种子数据   |

##### 业务模块目录

| 目录         | 描述        |
|------------|-----------|
| Controller | 控制器目录     |
| Event      | 事件管理目录    |
| Listener   | 监听事件目录    |
| Logic      | 业务逻辑目录    |
| Middleware | 中间件处理目录   |
| Model      | 模型目录      |
| Request    | 请求验证目录    |
| Service    | 业务模块逻辑层目录 |

本架构未设置 `Mapper` 数据库映射访问层(`Dao`), 所以数据访问层由模型层 `Model` 充当。

> 控制器层 `Controller` 只负责处理请求数据、数据校验和响应数据, 对接服务层
> 服务层 `Service` 负责数据转换、数据组装和逻辑调用, 将数据回包控制器层
> 业务逻辑层 `Logic` 负责数据处理和业务处理, 将数据回包服务层
> 模型层 `Model` 负责数据库关系及数据获取, 交互于业务逻辑层

业务调用流程为:

> `Controller` => `Service` => `Logic` => `Model`

1. 控制器(`Controller`) 调用 服务层(`Service`)
2. 服务层(`Service`) 调用 业务逻辑层(`Logic`)
3. 业务逻辑层(`Logic`) 调用 模型层(`Model`)

##### 路由使用

通用路由入口为 `config/routes.php` 文件, 子模块路由放在 `config/routes` 目录下, 在路由入口内添加路由组 `Router::addGroup` 后 `include` 引入子模块路由文件即可。

##### 事件机制

事件模式是一种经过了充分测试的可靠机制，是一种非常适用于解耦的机制，分别存在以下 3 种角色：

> 1. 事件(`Event`) 是传递于应用代码与 监听器(`Listener`) 之间的通讯对象
> 2. 监听器(`Listener`) 是用于监听 事件(`Event`) 的发生的监听对象
> 3. 事件调度器(`EventDispatcher`) 是用于触发 事件(`Event`) 和管理 监听器(`Listener`) 与 事件(`Event`) 之间的关系的管理者对象

用通俗易懂的例子来说明就是，假设我们存在一个 UserService::register() 方法用于注册一个账号，在账号注册成功后我们可以通过事件调度器触发 UserRegistered 事件，由监听器监听该事件的发生，在触发时进行某些操作，比如发送用户注册成功短信，在业务发展的同时我们可能会希望在用户注册成功之后做更多的事情，比如发送用户注册成功的邮件等待，此时我们就可以通过再增加一个监听器监听 UserRegistered 事件即可，无需在 UserService::register() 方法内部增加与之无关的代码。

本架构中, 事件机制可以存在于核心模块的 `core` 中, 也可以存在于业务模块中, 创建 `Event` 和 `Listener` 目录。

> 首先, 定义一个事件 (一个事件其实就是一个用于管理状态数据的普通类，触发时将应用数据传递到事件里，然后监听器对事件类进行操作，一个事件可被多个监听器监听):

```php
<?php
namespace Core\Event;

class UserRegistered
{
    // 建议这里定义成 public 属性，以便监听器对该属性的直接使用，或者你提供该属性的 Getter
    public $user;
    
    public function __construct($user)
    {
        $this->user = $user;    
    }
}
```

> 然后, 定义一个通过注解注册的监听器 (监听器都需要实现一下 `Hyperf\Event\Contract\ListenerInterface` 接口的约束方法):

```php
<?php
namespace App\Api\Listener;

use Core\Event\UserRegistered;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class UserRegisteredListener implements ListenerInterface
{
    public function listen(): array
    {
        // 返回一个该监听器要监听的事件数组，可以同时监听多个事件
        return [
            UserRegistered::class,
        ];
    }

    /**
     * @param UserRegistered $event
     */
    public function process(object $event): void
    {
        // 事件触发后该监听器要执行的代码写在这里，比如该示例下的发送用户注册成功短信等
        // 直接访问 $event 的 user 属性获得事件触发时传递的参数值
        // $event->user;
    }
}
```

注意: 在通过注解注册监听器时，我们可以通过设置 `priority` 属性定义当前监听器的顺序，如 #[Listener(priority=1)] ，底层使用 `SplPriorityQueue` 结构储存，`priority` 数字越大优先级越高。

> 触发事件 (事件需要通过 事件调度器(`EventDispatcher`) 调度才能让 监听器(`Listener`) 监听到):

```php
<?php
namespace App\Api\Service;

use Hyperf\Di\Annotation\Inject;
use Psr\EventDispatcher\EventDispatcherInterface;
use Core\Event\UserRegistered; 

class UserService
{
    /**
     * @var EventDispatcherInterface
     */
    #[Inject]
    private $eventDispatcher;
    
    public function register()
    {
        // 我们假设存在 User 这个实体
        $user = new User();
        $result = $user->save();
        // 完成账号注册的逻辑
        // 这里 dispatch(object $event) 会逐个运行监听该事件的监听器
        $this->eventDispatcher->dispatch(new UserRegistered($user));
        return $result;
    }
}
```

### 启动服务

```shell
php bin/server.php start
```