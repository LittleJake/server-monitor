<?php

namespace app\common\lib;

use think\facade\Cache;
use think\facade\Log;

class SystemMonitor
{
    static private $url = "http://ip-api.com/json/";
    static public function fetchIPInfo($ip){
        if(is_array($ip)){
            foreach ($ip as $v){
                $tmp = strstr($v, '/', true);

                if(Cache::store('flag')->has($v)
                    && !empty(Cache::store('flag')->get($v)))
                    $info[$v] = Cache::store('flag')->get($v);
                else {
                    $i = json_decode(self::curl_get(self::$url.$tmp),true);
                    Cache::store('flag')->set($v, $i);
                    $info[$v] = $i;
                }
            }
        }else{
            $tmp = strstr($ip, '/', true);
            if(Cache::store('flag')->has($ip)
                && !empty(Cache::store('flag')->get($ip)))
                return Cache::store('flag')->get($ip);
            else {
                $info[$ip] = json_decode(self::curl_get(self::$url.$tmp),true);
                Cache::store('flag')->set($ip, $info);
            }
        }

        return $info;
    }

    static public function getHashes(){
        if(Cache::has('system_monitor:hashes')
            && !empty(Cache::get('system_monitor:hashes'))){
            $hash = Cache::get('system_monitor:hashes');
        } else {
            $hash = Cache::store('redis')->handler()
                ->hGetAll("system_monitor:hashes");
            Cache::set('system_monitor:hashes', $hash);
        }


        return $hash;
    }

    static public function getStat($ip) {
        if(Cache::has("system_monitor:stat:$ip")
            && !empty(Cache::get("system_monitor:stat:$ip"))){
            $stat = Cache::get("system_monitor:stat:$ip");
        } else{
            $stat = Cache::store('redis')->handler()
                ->get("system_monitor:stat:$ip");
            Cache::set("system_monitor:stat:$ip", $stat);
        }

        return $stat;
    }

    static public function getInfo($ip) {
        if(Cache::has("system_monitor:info:$ip")
            && !empty(Cache::get("system_monitor:info:$ip"))){
            $info = Cache::get("system_monitor:info:$ip");
        } else{
            $info = Cache::store('redis')->handler()
                ->hGetAll("system_monitor:info:$ip");
            Cache::set("system_monitor:info:$ip", $info);
        }

        return $info;
    }

    static public function timeFormat($time){
        return floor($time / (24*60*60)) ." Days ".
            floor(($time / (60*60)) % 24) ." Hours ".
            floor(($time / (60)) % 60) . " Minutes ".
            floor($time % 60) . " Seconds";
    }

    static private function curl_get($url){
        $header = array(
            'Accept: application/json',
        );
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // 超时设置,以秒为单位
        curl_setopt($curl, CURLOPT_TIMEOUT, 1);

        // 超时设置，以毫秒为单位
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, 1000);

        // 设置请求头
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        //执行命令
        $data = curl_exec($curl);

        // 显示错误信息
        if (curl_error($curl))
            Log::error(curl_error($curl));
        else
            curl_close($curl);

        return $data;
    }
}
