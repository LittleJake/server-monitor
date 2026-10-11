<?php

use think\facade\Route;

Route::get('disk/:uuid', 'Disk/get');
Route::get('cpu/:uuid', 'Cpu/get');
Route::get('memory/:uuid', 'Memory/get');
Route::get('network/:uuid', 'Network/get');
Route::get('thermal/:uuid', 'Thermal/get');
Route::get('battery/:uuid', 'Battery/get');
Route::get('ping/:uuid', 'Ping/get');
Route::get('io/:uuid', 'IO/get');

Route::post('report/collection/:uuid', 'Report/collection');
Route::post('report/info/:uuid', 'Report/info');
Route::post('report/hash/:uuid', 'Report/hash');
Route::get('report/command/:uuid', 'Report/command');