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
Route::get('api/storage/:token','api/storage/get');
Route::get('api/cpu/:token','api/cpu/get');
Route::get('api/swap/:token','api/swap/get');
Route::get('api/memory/:token','api/memory/get');
Route::get('api/network/:token','api/network/get');
Route::any('admin/info/:token','admin/info/index');
Route::any('admin/','admin/index/index');
Route::rule('admin/login','admin/index/login', 'GET|POST');
Route::rule('admin/logout','admin/index/logout', 'GET');

return [];
