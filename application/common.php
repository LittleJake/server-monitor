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
    $icon = [
        'redhat' => "#EE0000",
        'centos' => "#262577",
        'ubuntu' => "#E95420",
        'debian' => "#A81D33",
        'windows' => "#0078D6",
        'intel' => "#0071C5",
        'amd' => "#ED1C24",
        'qemu' => "#FF6600",
        'linux' => "#FCC624",
        'android' => "#3DDC84",
        'qualcomm' => "#3253DC",
        'mediatek' => "#EC9430"
    ];

    foreach ($icon as $k => $v)
        if(stristr($desp, $k))
            return [$k, $url."$k.svg", $v];

    return ['linux', $url."linux.svg", $icon['linux']];
}