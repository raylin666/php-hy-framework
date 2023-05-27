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
| core:migrate-gen      | 生成数据表迁移文件     |
| core:migrate-rollback | 运行模块的迁移回滚类    |
| core:migrate-run      | 运行数据表迁移       |
| core:model            | 生成数据表模型       |
| core:module           | 生成业务模块/模块配置更新 |
| core:seeder-gen       | 生成数据表迁移种子数据文件 |
| core:seeder-run       | 运行数据表迁移种子数据   |

##### 路由使用

通用路由入口为 `config/routes.php` 文件, 子模块路由放在 `config/routes` 目录下, 在路由入口内添加路由组 `Router::addGroup` 后 `include` 引入子模块路由文件即可。

### 启动服务

```shell
php bin/server.php start
```