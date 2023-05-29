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
namespace Core\Generator;

use Core\Contract\ModuleInfoInterface;
use Core\Decorator\ModelDataDecorator;
use Core\Decorator\ModelOptionDecorator;
use Core\Helper\ModelUpdateVisitorHelper;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Commands\Ast\GenerateModelIDEVisitor;
use Hyperf\Database\Commands\Ast\ModelRewriteConnectionVisitor;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Schema\Builder;
use Hyperf\Stringable\Str;
use PhpParser\Lexer;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use RuntimeException;

use function Hyperf\Support\make;

class ModelGenerator extends Generator
{
    public const STUB_REPLACE_TABLE = '%TABLE%';

    public const STUB_REPLACE_CONNECTION = '%CONNECTION%';

    protected ?ModuleInfoInterface $moduleInfo = null;

    protected ConnectionResolverInterface $resolver;

    protected ConfigInterface $config;

    protected Lexer $lexer;

    protected Parser $astParser;

    protected PrettyPrinterAbstract $printer;

    public function __construct()
    {
        parent::__construct();

        $container = container();
        $this->resolver = $container->get(ConnectionResolverInterface::class);
        $this->config = $container->get(ConfigInterface::class);
        $this->lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);

        $this->astParser = make(ParserFactory::class)->create(ParserFactory::ONLY_PHP7, $this->lexer);
        $this->printer = make(Standard::class);
    }

    public function getModuleInfo(): ?ModuleInfoInterface
    {
        return $this->moduleInfo;
    }

    public function withModuleInfo(ModuleInfoInterface $moduleInfo): static
    {
        $this->moduleInfo = $moduleInfo;
        return $this;
    }

    /**
     * 创建业务模块数据表父级模型.
     * @return array
     */
    public function createModuleModel(): array
    {
        // 未设置 Module 暂不支持创建
        if (empty($this->getModuleInfo())) {
            throw new RuntimeException('请调用 withModuleInfo 函数设置业务模块信息');
        }

        if (empty($this->getModuleInfo()->getName())) {
            throw new RuntimeException('业务模块名称为空');
        }

        $class = 'Model';
        $this->withStub('init/model.stub');
        $this->withNamespace('App\\' . $this->getModuleInfo()->getName() . '\\Model');
        $path = $this->convertPathByNamespace($this->getNamespace(), true) . '/' . $class . '.php';
        if (is_file($path)) {
            throw new RuntimeException(sprintf('%s 类已存在', $this->getNamespace()));
        }

        $this->makeDirectory($path);

        $content = $this->buildClass($class);

        file_put_contents($path, $content);

        return [
            'path' => $path,
            'namespace' => $this->getNamespace(),
            'class' => $class,
            'content' => $content,
        ];
    }

    /**
     * 创建单个数据表模型.
     */
    public function createModel(string $table, ModelOptionDecorator $modelOptionDecorator): array
    {
        $result = ['module' => '', 'content' => '', 'message' => ''];

        $this->withStub('model.stub');
        $builder = $this->getModelSchemaBuilder($modelOptionDecorator->getPool());
        $table = Str::replaceFirst($modelOptionDecorator->getPrefix(), '', $table);
        $columns = $this->modelColumnsTransformByDbColumns($builder->getColumnTypeListing($table));

        $class = $modelOptionDecorator->getTableMapping()[$table] ?? Str::studly(Str::singular($table));
        if ($this->getModuleInfo() instanceof ModuleInfoInterface) {
            $result['module'] = $this->getModuleInfo()->getName();
            $this->withNamespace('App\\' . $this->getModuleInfo()->getName() . '\\Model');
        } else {
            $this->withNamespace('Core\\Model');
        }

        if (empty($modelOptionDecorator->getPath())) {
            $namespace = $this->getNamespace() . '\\' . $class;
        } else {
            $modelOptionDecorator->setUses($this->getNamespace() . '\\Model');
            $namespace = $this->convertNamespaceByPath($modelOptionDecorator->getPath(), $class);
            $this->withNamespace($this->convertNamespaceByPath($modelOptionDecorator->getPath()));
        }

        $path = $this->convertPathByNamespace($namespace);

        $result['path'] = $path;
        $result['table'] = $table;
        $result['namespace'] = $namespace;
        $result['class'] = $class;

        if (file_exists($path)) {
            $result['message'] = sprintf('数据表模型 %s 已存在', $namespace);
            return $result;
        }

        $this->makeDirectory($path);

        $content = $this->buildClass($class);
        $content = $this->stubModelReplaceInheritance($modelOptionDecorator->getInheritance(), $content);
        $content = $this->stubModelReplaceConnection($modelOptionDecorator->getPool(), $content);
        $content = $this->stubModelReplaceUses($modelOptionDecorator->getUses(), $content);
        $content = $this->stubModelReplaceTable($table, $content);
        file_put_contents($path, $content);

        $columns = $this->getModelColumns($namespace, $columns, $modelOptionDecorator->isForceCasts());

        $traverser = new NodeTraverser();
        $traverser->addVisitor(make(ModelUpdateVisitorHelper::class, [
            'class' => $namespace,
            'columns' => $columns,
            'option' => $modelOptionDecorator,
        ]));
        $traverser->addVisitor(make(ModelRewriteConnectionVisitor::class, [$namespace, $modelOptionDecorator->getPool()]));
        $modelData = make(ModelDataDecorator::class, ['class' => $namespace, 'columns' => $columns]);
        foreach ($modelOptionDecorator->getVisitors() as $visitorClass) {
            $traverser->addVisitor(make($visitorClass, [$modelOptionDecorator, $modelData]));
        }

        $traverser->addVisitor(new CloningVisitor());

        $originStmts = $this->astParser->parse(file_get_contents($path));
        $originTokens = $this->lexer->getTokens();
        $newStmts = $traverser->traverse($originStmts);

        $code = $this->printer->printFormatPreserving($newStmts, $originStmts, $originTokens);
        $result['content'] = $code;
        file_put_contents($path, $code);

        if ($modelOptionDecorator->isWithIde()) {
            $this->modelGenerateIDE($code, $modelOptionDecorator, $modelData);
        }

        return $result;
    }

    /**
     * 创建模块下所有数据表模型.
     */
    public function createModels(ModelOptionDecorator $modelOptionDecorator): array
    {
        $result = $tables = [];
        $builder = $this->getModelSchemaBuilder($modelOptionDecorator->getPool());

        foreach ($builder->getAllTables() as $row) {
            $row = (array) $row;
            $table = reset($row);
            if ($this->modelIsIgnoreTable($table, $modelOptionDecorator)) {
                continue;
            }

            $tables[] = $table;
        }

        foreach ($tables as $table) {
            $result[] = $this->createModel($table, $modelOptionDecorator);
        }

        return $result;
    }

    /**
     * 获取模型构建模式.
     * @param string $poolName DB 连接名称
     */
    protected function getModelSchemaBuilder(string $poolName): Builder
    {
        $connection = $this->resolver->connection($poolName);
        return $connection->getSchemaBuilder();
    }

    protected function getModelColumns($namespace, $columns, $forceCasts): array
    {
        /** @var Model $model */
        $model = new $namespace();
        $dates = $model->getDates();
        $casts = [];
        if (! $forceCasts) {
            $casts = $model->getCasts();
        }

        foreach ($dates as $date) {
            if (! isset($casts[$date])) {
                $casts[$date] = 'datetime';
            }
        }

        foreach ($columns as $key => $value) {
            $columns[$key]['cast'] = $casts[$value['column_name']] ?? null;
        }

        return $columns;
    }

    /**
     * 将数据表列的键转换为小写/大写.
     * @param array $columns 数据表字段
     * @param int $case 大小写
     */
    protected function modelColumnsTransformByDbColumns(array $columns, int $case = CASE_LOWER): array
    {
        return array_map(function ($item) use ($case) {
            return array_change_key_case($item, $case);
        }, $columns);
    }

    /**
     * 忽略用于创建模型的表.
     * @param string $table 表名称
     */
    protected function modelIsIgnoreTable(string $table, ModelOptionDecorator $modelOptionDecorator): bool
    {
        if (in_array($table, $modelOptionDecorator->getIgnoreTables())) {
            return true;
        }

        return $table === $this->config->get('databases.migrations', 'migrations');
    }

    /**
     * 替换给定存根的继承/基层类名.
     * @param string $inheritance 继承/基层类名
     * @param string $content 文件内容
     */
    protected function stubModelReplaceInheritance(string $inheritance, string $content): string
    {
        return str_replace(static::STUB_REPLACE_INHERITANCE, $inheritance, $content);
    }

    /**
     * 替换给定存根的数据库池连接名称.
     * @param string $connection 数据库池连接名称
     * @param string $content 文件名称
     */
    protected function stubModelReplaceConnection(string $connection, string $content): string
    {
        return str_replace(static::STUB_REPLACE_CONNECTION, $connection, $content);
    }

    /**
     * 替换给定存根的引入类命名空间名称.
     * @param string $uses 引入类命名空间名称
     * @param string $content 文件内容
     */
    protected function stubModelReplaceUses(string $uses, string $content): string
    {
        $uses = $uses ? "use {$uses};" : '';
        return str_replace(static::STUB_REPLACE_USES, $uses, $content);
    }

    /**
     * 替换给定存根的表名.
     * @param string $table 数据表名称
     * @param string $content 文件内容
     */
    protected function stubModelReplaceTable(string $table, string $content): string
    {
        return str_replace(static::STUB_REPLACE_TABLE, $table, $content);
    }

    /**
     * @param string $code 代码内容
     * @param ModelOptionDecorator $modelOptionDecorator 模型配置项
     * @param ModelDataDecorator $data 模型数据
     */
    protected function modelGenerateIDE(string $code, ModelOptionDecorator $modelOptionDecorator, ModelDataDecorator $data): void
    {
        $stmts = $this->astParser->parse($code);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(make(GenerateModelIDEVisitor::class, [$modelOptionDecorator, $data]));
        $stmts = $traverser->traverse($stmts);
        $code = $this->printer->prettyPrintFile($stmts);
        $class = str_replace('\\', '_', $data->getClass());
        $path = BASE_PATH . '/runtime/ide/' . $class . '.php';
        $this->makeDirectory($path);
        file_put_contents($path, $code);
    }
}
