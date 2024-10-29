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
    $url = "https://cdnjs.cloudflare.com/ajax/libs/simple-icons/6.22.0/";
    $icon = [
        'redhat' => "#EE0000",
        'centos' => "#262577",
        'ubuntu' => "#E95420",
        'debian' => "#A81D33",
        'windows' => "#0078D6",
        'intel' => "#0071C5",
        'amd' => "#ED1C24",
        'android' => "#3DDC84",
        'qualcomm' => "#3253DC",
        'mediatek' => "#EC9430",
        'alpine linux' => "#0D597F",
        
        'arm' => "#0091BD",
        'qemu' => "#FF6600",
        'linux' => "#FCC624",
    ];

    foreach ($icon as $k => $v)
        if(stristr($desp, $k))
            return [$k, $url.iconStandadize($k).".svg", $v];

    return ['linux', $url."linux.svg", $icon['linux']];
}

function iconStandadize($name) {
    return str_replace(".", "dot", str_replace(" ", "", $name));
}
