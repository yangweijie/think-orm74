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

namespace think\db;

/**
 * SQL Express.
 */
class Express
{
    /**
     * @var string
     */
    protected string $type;
    protected float $step = 1;
    /**
     * @var int
     */
    protected int $lazyTime = 0;
    /**
     * 创建一个SQL运算表达式.
     *
     * @param string $type
     * @param float $value
     * @param int   $lazyTime
     *
     * @return void
     */
    public function __construct(string $type, float $step = 1, int $lazyTime = 0)
    {
        $this->type = $type;
        $this->step = $step;
        $this->lazyTime = $lazyTime;
    }

    public function getStep()
    {
        return $this->step;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getLazyTime()
    {
        return $this->lazyTime;
    }

    /**
     * 获取表达式.
     *
     * @return string
     */
    public function getValue(): string
    {
        switch ($this->type) {
            case '+':
                return ' + ' . $this->step;
            case '-':
                return ' - ' . $this->step;
            case '*':
                return ' * ' . $this->step;
            case '/':
                return ' / ' . $this->step;
            default:
                return ' + 0';
        }
    }
}
