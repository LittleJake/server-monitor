<?php

// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

return [
    // 默认缓存驱动
    'default' => 'file',

    // 缓存连接方式配置
    'stores'  => [
        'file' => [
            // 驱动方式
            'type'       => 'File',
            // 缓存保存目录
            'path'       => runtime_path() . 'cache' . DIRECTORY_SEPARATOR,
            // 缓存前缀
            'prefix'     => 'monitor_',
            // 缓存有效期 0表示永久缓存
            'expire'     => 240,
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'  => [],
        ],
        'redis' => [
            'type'       => 'redis',
            'host'       => env('redis.host', '127.0.0.1'),
            'port'       => env('redis.port', 6379),
            'password'   => env('redis.password', ''),
            'select'     => 0,
            'timeout'    => 0,
            'expire'     => 0,
            'persistent' => false,
            'prefix'     => '',
            'serialize'  => true,
        ],
        'flag' => [
            'type'       => 'File',
            'path'       => runtime_path() . 'cache' . DIRECTORY_SEPARATOR,
            'prefix'     => 'flag_',
            'expire'     => 86400,
            'tag_prefix' => 'flag_tag:',
            'serialize'  => [],
        ],
        'token' => [
            'type'       => 'File',
            'path'       => runtime_path() . 'cache' . DIRECTORY_SEPARATOR,
            'prefix'     => 'token_',
            'expire'     => 0,
            'tag_prefix' => 'token_tag:',
            'serialize'  => [],
        ],
    ],
];
