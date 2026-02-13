<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2025 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think;

use ArrayAccess;
use InvalidArgumentException;
use JsonSerializable;
use ReflectionClass;
use think\contract\Arrayable;
use think\contract\Jsonable;
use think\model\contract\Modelable;

/**
 * Class Entity.
 * @mixin Model
 */
abstract class Entity implements JsonSerializable, ArrayAccess, Arrayable, Jsonable, Modelable
{
    /** @var mixed|null WeakMap for PHP 8.0+ or null for PHP 7.4 */
    private static $weakMap = null;

    /** @var array Object store for PHP 7.4 compatibility */
    private static $objectStore = [];

    /**
     * 架构函数.
     *
     * @param Model $model 模型连接对象
     */
    public function __construct(?Model $model = null)
    {
        $this->initWeakMap();

        // 获取实体模型参数
        $baseOptions = $this->getBaseOptions();
        $options     = $this->getOptions();
        
        foreach (['viewMapping', 'autoMapping'] as $item) {
            $options[$item] = array_merge($baseOptions[$item] ?? [], $options[$item] ?? []);
        }

        $options = array_merge($baseOptions, $options);

        if (is_null($model)) {
            $class = !empty($options['modelClass']) ? $options['modelClass'] : str_replace('\\entity\\', '\\model\\', static::class);
            $model = new $class();
        }

        $model->entity($this);
        self::setObjectData($this, ['model' => $model]);

        // 初始化模型
        $this->setOptions($options);
        $this->init($options);
    }

    /**
     * 初始化 WeakMap 或 objectStore（PHP 7.4 兼容）
     */
    protected function initWeakMap(): void
    {
        if (PHP_VERSION_ID >= 80000) {
            if (!self::$weakMap) {
                self::$weakMap = new \WeakMap();
            }
        }
    }

    /**
     * 获取对象存储数据（PHP 7.4 兼容）
     *
     * @param object $object
     * @return array|null
     */
    private static function getObjectData(object $object): ?array
    {
        if (PHP_VERSION_ID >= 80000) {
            return self::$weakMap[$object] ?? null;
        } else {
            $hash = spl_object_hash($object);
            return self::$objectStore[$hash] ?? null;
        }
    }

    /**
     * 设置对象存储数据（PHP 7.4 兼容）
     *
     * @param object $object
     * @param array $data
     */
    private static function setObjectData(object $object, array $data): void
    {
        if (PHP_VERSION_ID >= 80000) {
            self::$weakMap[$object] = $data;
        } else {
            $hash = spl_object_hash($object);
            self::$objectStore[$hash] = $data;
        }
    }

    /**
     * 定义实体模型的基础配置参数.
     *
     * @return array
     */
    protected function getBaseOptions(): array
    {
        return [];
    }

    /**
     * 定义实体模型相关配置参数.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [];
    }

    /**
     * 批量设置模型参数
     * @param array  $options  值
     * @return void
     */
    public function setOptions(array $options): void
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    /**
     * 设置模型参数
     *
     * @param string $name  参数名
     * @param mixed  $value  值
     *
     * @return $this
     */
    public function setOption(string $name, $value)
    {
        $data = self::getObjectData($this);
        if ($data !== null) {
            $data[$name] = $value;
            self::setObjectData($this, $data);
        }
        return $this;
    }

    /**
     * 获取模型参数
     *
     * @param string $name  参数名
     * @param mixed  $default  默认值
     *
     * @return mixed
     */
    public function getOption(string $name, $default = null)
    {
        $data = self::getObjectData($this);
        return $data[$name] ?? $default;
    }

    /**
     * 创建新的实例.
     *
     * @param Model $model 模型连接对象
     * @param array $options 查询参数
     */
    public function newInstance(?Model $model, array $options = [])
    {
        $entity = new static();
        return $entity->setModel($model, $options);
    }

    /**
     *  初始化模型.
     *
     * @param array $options 模型参数
     * @return void
     */
    protected function init(array $options = []): void {}

    /**
     * 获取模型对象实例.
     * @return Model
     */
    public function model()
    {
        $data = self::getObjectData($this);
        return $data['model'] ?? null;
    }

    /**
     *  设置模型.
     *
     * @param Model $model 模型对象
     * @return $this
     */
    public function setModel(Model $model)
    {
        $data = self::getObjectData($this);
        if ($data !== null) {
            $data['model'] = $model;
            self::setObjectData($this, $data);
        }
        return $this;
    }

    /**
     * 获取克隆的模型实例.
     *
     * @return static
     */
    public function clone()
    {
        $model = new static();
        $data = self::getObjectData($this);
        if ($data !== null) {
            self::setObjectData($model, $data);
        }
        return $model;
    }

    /**
     * 克隆模型实例
     * 
     * @return void
     */
    public function __clone()
    {
        throw new InvalidArgumentException('use $modelObj->clone() replace clone $modelObj');
    }

    /**
     * 序列化模型对象
     * 
     * @return array
     */
    public function __serialize(): array
    {
        $data = self::getObjectData($this);
        return array_diff_key($data ?? []);
    }

    /**
     * 反序列化模型对象
     * 
     * @param array $data 
     * @return void
     */
    public function __unserialize(array $data)
    {
        $this->initWeakMap();
        self::setObjectData($this, $data);
    }

    /**
     * 获取属性 支持获取器
     *
     * @param string $name 名称
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->model()->get($name);
    }

    /**
     * 设置数据 支持类型自动转换
     *
     * @param string $name  名称
     * @param mixed  $value 值
     *
     * @return void
     */
    public function __set(string $name, $value): void
    {
        $this->model()->set($name, $value);
    }

    /**
     * 检测数据对象的值
     *
     * @param string $name 名称
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->model()->__isset($name);
    }

    /**
     * 销毁数据对象的值
     *
     * @param string $name 名称
     *
     * @return void
     */
    public function __unset(string $name): void
    {
        $this->model()->__unset($name);
    }

    public function __toString()
    {
        return $this->model()->toJson();
    }

    public function __debugInfo()
    {
        return $this->model()->getData();
    }

    // JsonSerializable
    public function jsonSerialize(): array
    {
        return $this->model()->toArray();
    }

    /**
     * 模型数据转数组.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->model()->toArray();
    }   
     
    /**
     * 模型数据转Json.
     *
     * @param int $options json参数
     * @return string
     */
    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        return $this->model()->toJson($options);
    }

    // ArrayAccess
    /**
     * @param mixed $name
     * @param mixed $value
     */
    public function offsetSet($name, $value): void
    {
        $this->__set($name, $value);
    }

    /**
     * @param mixed $name
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($name)
    {
        return $this->__get($name);
    }

    /**
     * @param mixed $name
     */
    public function offsetExists($name): bool
    {
        return $this->__isset($name);
    }

    /**
     * @param mixed $name
     */
    public function offsetUnset($name): void
    {
        $this->__unset($name);
    }

    public static function __callStatic($method, $args)
    {
        $entity = new static();
        if (in_array($method, ['destroy', 'create', 'update', 'saveAll'])) {
            // 调用model的静态方法
            $db = $entity->model();
        } else {
            // 调用Query类查询方法
            $db = $entity->model()->db();
        }

        return call_user_func_array([$db, $method], $args);
    }

    public function __call($method, $args)
    {
        // 调用Model类方法
        return call_user_func_array([$this->model(), $method], $args);
    }
}
