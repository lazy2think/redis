<?php
/*
 * @Author: zane
 * @Date: 2020-03-25 11:02:02
 * @LastEditTime: 2020-03-30 14:31:01
 * @Description: 
 */

/**
 * 助手函数
 */
abstract class PhpHelper
{
    public static function tap($value, $callback)
    {
        $callback($value);

        return $value;
    }
}