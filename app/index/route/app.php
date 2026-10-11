<?php

use think\facade\Route;

Route::get('/', 'Index/index');
Route::get('info/:uuid', 'Index/info');
Route::get('/manifest.json', 'Index/manifest');