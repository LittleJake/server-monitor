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


Route::get('info/:token','index/index/info');
Route::get('api/disk/:token','api/disk/get');
Route::get('api/cpu/:token','api/cpu/get');
Route::get('api/swap/:token','api/swap/get');
Route::get('api/memory/:token','api/memory/get');
Route::get('api/network/:token','api/network/get');

return [];
