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
        'expire'=> 240,
        'prefix'=> 'monitor',
        'path' => __DIR__.'/../runtime/cache/',
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
        'expire'=> 24*60*60,
        'prefix'=> 'flag',
        'path' => __DIR__.'/../runtime/cache/',
    ],
    'token' => [
        'type' => 'file',
        'expire' => 0,
        'prefix' => 'token',
        'path' => __DIR__.'/../runtime/cache/',
    ]
];
