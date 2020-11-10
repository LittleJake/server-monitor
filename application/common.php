<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function getIcon($desp = ''){
    $url = "https://cdn.jsdelivr.net/npm/simple-icons@v3/icons/";
    $icon = ['redhat', 'centos', 'ubuntu', 'debian', 'windows', 'intel', 'amd', 'qemu', 'linux'];

    foreach ($icon as $v)
        if(stristr($desp, $v)) {
            $url.="$v.svg";
            return $url;
        }


    return $url."linux.svg";
}