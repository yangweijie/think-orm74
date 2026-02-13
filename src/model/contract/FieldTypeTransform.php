<?php

declare (strict_types = 1);

namespace think\model\contract;

use think\model\contract\Modelable as Model;

interface FieldTypeTransform
{
    /**
     * @param mixed $value
     */
    public static function get($value, Model $model): ?self;

    /**
     * @return static|mixed
     */
    public static function set($value, Model $model);
}
