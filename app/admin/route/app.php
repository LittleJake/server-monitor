<?php

use think\facade\Route;

Route::get('login', 'Index/login');
Route::post('login', 'Index/login');
Route::get('logout', 'Index/logout');
Route::get('info/clear', 'Info/clear');
Route::get('info/purge', 'Info/purge');
Route::any('info/:uuid', 'Info/index');
Route::any('index/:type', 'Index/index');
Route::any('/', 'Index/index');