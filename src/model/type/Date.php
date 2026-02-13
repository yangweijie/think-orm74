<?php

declare (strict_types = 1);

namespace think\model\type;

use think\model\contract\Modelable;

class Date extends DateTime
{
    protected $data;

    /**
     * @param mixed $value
     */
    public static function from($value, Modelable $model)
    {
        $static = new static();
        $static->data($value, 'Y-m-d');
        return $static;
    }
}
