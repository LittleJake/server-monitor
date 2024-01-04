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


Route::get('info/:uuid','index/index/info');
Route::get('api/disk/:uuid','api/disk/get');
Route::get('api/cpu/:uuid','api/cpu/get');
Route::get('api/swap/:uuid','api/swap/get');
Route::get('api/memory/:uuid','api/memory/get');
Route::get('api/network/:uuid','api/network/get');
Route::get('api/thermal/:uuid','api/thermal/get');
Route::post('api/report/collection/:uuid','api/collection/info');
Route::post('api/report/info/:uuid','api/report/info');
Route::post('api/report/hash/:uuid','api/report/hash');
Route::any('admin/info/:uuid','admin/info/index');
Route::any('admin/','admin/index/index');
Route::rule('admin/login','admin/index/login', 'GET|POST');
Route::rule('admin/logout','admin/index/logout', 'GET');


Route::rule('/manifest.json','index/index/manifest', 'GET');

return [];
