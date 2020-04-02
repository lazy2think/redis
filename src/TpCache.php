<?php
/*
 * @Author: zane
 * @Date: 2020-03-30 16:33:31
 * @LastEditTime: 2020-03-30 17:09:43
 * @Description: 
 */


namespace cache;

use think\Config;
use think\cache\Driver;
use redis\RedisManager;

class TpCache
{
    /**
     * @var array 缓存的实例
     */
    public static $instance = [];

    /**
     * @var int 缓存读取次数
     */
    public static $readTimes = 0;

    /**
     * @var int 缓存写入次数
     */
    public static $writeTimes = 0;

    /**
     * @var object 操作句柄
     */
    public static $handler;

    /**
     * 自动初始化缓存
     * @access public
     * @param  array $options 配置数组
     * @return Driver
     */
    public static function init(array $options = [])
    {
        if (is_null(self::$handler)) {
            if (empty($options) && 'complex' == Config::get('cache.type')) {
                $default = Config::get('cache.default');
                // 获取默认缓存配置，并连接
                $options = Config::get('cache.' . $default['type']) ?: $default;
            } elseif (empty($options)) {
                $options = Config::get('cache');
            }

            self::$handler = self::connect($options);
        }

        return self::$handler;
    }

    /**
     * 切换缓存类型 需要配置 cache.type 为 complex
     * @access public
     * @param  string $name 缓存标识
     * @return Driver
     */
    public static function store($name = '')
    {
        if ('' !== $name && 'complex' == Config::get('cache.type')) {
            return self::connect(Config::get('cache.' . $name), strtolower($name));
        }

        return self::init();
    }

    /**
     * 判断缓存是否存在
     * @access public
     * @param  string $name 缓存变量名
     * @return bool
     */
    public static function has($name)
    {
        self::$readTimes++;

        return self::init()->has($name);
    }

    /**
     * 读取缓存
     * @access public
     * @param  string $name    缓存标识
     * @param  mixed  $default 默认值
     * @return mixed
     */
    public static function get($name, $default = false)
    {
        self::$readTimes++;

        return self::init()->get($name, $default);
    }

    /**
     * 写入缓存
     * @access public
     * @param  string   $name   缓存标识
     * @param  mixed    $value  存储数据
     * @param  int|null $expire 有效时间 0为永久
     * @return boolean
     */
    public static function set($name, $value, $expire = null)
    {
        self::$writeTimes++;

        return self::init()->set($name, $value, $expire);
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param  string $name 缓存变量名
     * @param  int    $step 步长
     * @return false|int
     */
    public static function inc($name, $step = 1)
    {
        self::$writeTimes++;

        return self::init()->inc($name, $step);
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param  string $name 缓存变量名
     * @param  int    $step 步长
     * @return false|int
     */
    public static function dec($name, $step = 1)
    {
        self::$writeTimes++;

        return self::init()->dec($name, $step);
    }

    /**
     * 删除缓存
     * @access public
     * @param  string $name 缓存标识
     * @return boolean
     */
    public static function rm($name)
    {
        self::$writeTimes++;

        return self::init()->rm($name);
    }

    /**
     * 清除缓存
     * @access public
     * @param  string $tag 标签名
     * @return boolean
     */
    public static function clear($tag = null)
    {
        self::$writeTimes++;

        return self::init()->clear($tag);
    }

    /**
     * 读取缓存并删除
     * @access public
     * @param  string $name 缓存变量名
     * @return mixed
     */
    public static function pull($name)
    {
        self::$readTimes++;
        self::$writeTimes++;

        return self::init()->pull($name);
    }

    /**
     * 如果不存在则写入缓存
     * @access public
     * @param  string $name   缓存变量名
     * @param  mixed  $value  存储数据
     * @param  int    $expire 有效时间 0为永久
     * @return mixed
     */
    public static function remember($name, $value, $expire = null)
    {
        self::$readTimes++;

        return self::init()->remember($name, $value, $expire);
    }

    /**
     * 缓存标签
     * @access public
     * @param  string       $name    标签名
     * @param  string|array $keys    缓存标识
     * @param  bool         $overlay 是否覆盖
     * @return Driver
     */
    public static function tag($name, $keys = null, $overlay = false)
    {
        return self::init()->tag($name, $keys, $overlay);
    }

}
