<?php
// 全局中间件定义文件
$namespaceSeparator = chr(92);

return [
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    // 多语言加载
    // \think\middleware\LoadLangPack::class,
    // Session初始化
    'think' . $namespaceSeparator . 'middleware' . $namespaceSeparator . 'SessionInit',
];
