<?php
// 中间件配置
return [
    // 别名或分组
    'alias'    => [
        'FlowControl' => app\http\middleware\FlowControl::class,
        'CheckToken'  => app\http\middleware\CheckToken::class,
        'CheckUUID'   => app\http\middleware\CheckUUID::class,
        'CheckJson'   => app\http\middleware\CheckJson::class,
    ],
    // 优先级设置，此数组中的中间件会按照数组中的顺序优先执行
    'priority' => [],
];
