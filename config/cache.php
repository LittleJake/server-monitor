<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

return [
    // 驱动方式
    'type' => 'complex',
    'default' => [
        'type' => 'file',
        // 全局缓存有效期（0为永久有效）
        'expire'=> 150,
        // 缓存前缀
        'prefix'=> 'monitor',
        // 缓存目录
        'path' => '../runtime/cache/',
    ],
    'redis' => [
        'type'       => 'Redis',
        'host'       => Env::get('redis.host'),
        'port'       => Env::get('redis.port'),
        'password'   => Env::get('redis.password'),
        'select'     => 0,
        'timeout'    => 0,
        'expire'     => 0,
        'persistent' => false,
        'prefix'     => '',
        'serialize'  => true,
    ],
    'flag' => [
        'type' => 'file',
        // 全局缓存有效期（0为永久有效）
        'expire'=> 0,
        // 缓存前缀
        'prefix'=> 'flag',
        // 缓存目录
        'path' => '../runtime/cache/',
    ],

];
