<?php

declare (strict_types = 1);

namespace think\model\contract;

use think\model\contract\Modelable as Model;

interface Typeable
{
    /**
     * @param mixed $value
     */
    public static function from($value, Model $model);

    /**
     * @return mixed
     */
    public function value();
}
