<?php

namespace app\common\lib;

use think\Exception;
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
        if(is_array($ip)){
            foreach ($ip as $v){
                if(Cache::has("system_monitor:stat:$v")
                    && !empty(Cache::get("system_monitor:stat:$v"))){
                    $stat[$v] = Cache::get("system_monitor:stat:$v");
                } else{
                    $tmp = json_decode(Cache::store('redis')->handler()
                        ->get("system_monitor:stat:$v"),true);
                    $stat[$v] = $tmp;
                    Cache::set("system_monitor:stat:$v", $tmp);
                }
            }
        }else{
            if(Cache::has("system_monitor:stat:$ip")
                && !empty(Cache::get("system_monitor:stat:$ip"))){
                $stat = Cache::get("system_monitor:stat:$ip");
            } else{
                $stat = json_decode(Cache::store('redis')->handler()
                    ->get("system_monitor:stat:$ip"),true);
                Cache::set("system_monitor:stat:$ip", $stat);
            }
        }

        return $stat;
    }

    static public function getInfo($ip) {
        if(is_array($ip)){
            foreach ($ip as $v){
                if(Cache::has("system_monitor:info:$v")
                    && !empty(Cache::get("system_monitor:info:$v"))){
                    $info[$v] = Cache::get("system_monitor:info:$v");
                }else{
                    $tmp = Cache::store('redis')->handler()
                    ->hGetAll("system_monitor:info:$v");
                    $info[$v] = $tmp;
                    Cache::set("system_monitor:info:$v", $tmp);
                }
            }
        }else{
            if(Cache::has("system_monitor:info:$ip")
                && !empty(Cache::get("system_monitor:info:$ip"))){
                $info = Cache::get("system_monitor:info:$ip");
            } else{
                $info = Cache::store('redis')->handler()
                    ->hGetAll("system_monitor:info:$ip");
                Cache::set("system_monitor:info:$ip", $info);
            }
        }

        return $info;
    }

    static public function timeFormat($time){
        return lang("Uptime Format", [floor($time / (24*60*60)), floor(($time / (60*60)) % 24)
            , floor(($time / (60)) % 60), floor($time % 60)]);
    }

    static private function curl_get($url){
        $header = ['Accept: application/json'];
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

    static public function getCpuCollection($ip){
        $time =  time();
        if(!Cache::has("system_monitor:collection:cpu:$ip")){
            $data = Cache::store('redis')->handler()
                ->zRangeByScore("system_monitor:collection:cpu:$ip", 0, $time
                    , ['withscores' => TRUE]);
            Cache::set("system_monitor:collection:cpu:$ip",$data,150);
        } else {
            $data = Cache::get("system_monitor:collection:cpu:$ip");
        }
        return $data;
    }

    static public function getSwapCollection($ip){
        $time =  time();
        if(!Cache::has("system_monitor:collection:swap:$ip")){
            $data = Cache::store('redis')->handler()
                ->zRangeByScore("system_monitor:collection:swap:$ip", 0, $time
                    , ['withscores' => TRUE]);
            Cache::set("system_monitor:collection:swap:$ip",$data,150);
        } else {
            $data = Cache::get("system_monitor:collection:swap:$ip");
        }
        return $data;
    }

    static public function getMemoryCollection($ip){
        $time =  time();

        if(!Cache::has("system_monitor:collection:memory:$ip")){
            $data = Cache::store('redis')->handler()
                ->zRangeByScore("system_monitor:collection:memory:$ip", 0, $time
                    , ['withscores' => TRUE]);
            Cache::set("system_monitor:collection:memory:$ip",$data,150);
        } else {
            $data = Cache::get("system_monitor:collection:memory:$ip");
        }
        return $data;
    }

    static public function getDiskCollection($ip){
        $time =  time();

        if(!Cache::has("system_monitor:collection:disk:$ip")){
            $data = Cache::store('redis')->handler()
                ->zRangeByScore("system_monitor:collection:disk:$ip", 0, $time
                    , ['withscores' => TRUE]);
            Cache::set("system_monitor:collection:disk:$ip",$data,150);
        } else {
            $data = Cache::get("system_monitor:collection:disk:$ip");
        }

        return $data;
    }

    static public function getNetworkRXCollection($ip){
        $time =  time();

        if(!Cache::has("system_monitor:collection:network:RX:$ip")){
            $data = Cache::store('redis')->handler()
                ->zRangeByScore("system_monitor:collection:network:RX:$ip", 0, $time
                    , ['withscores' => TRUE]);
            Cache::set("system_monitor:collection:network:RX:$ip",$data,150);
        } else {
            $data = Cache::get("system_monitor:collection:network:RX:$ip");
        }

        return $data;
    }

    static public function getNetworkTXCollection($ip){
        $time =  time();

        if(!Cache::has("system_monitor:collection:network:TX:$ip")){
            $data = Cache::store('redis')->handler()
                ->zRangeByScore("system_monitor:collection:network:TX:$ip", 0, $time
                    , ['withscores' => TRUE]);
            Cache::set("system_monitor:collection:network:TX:$ip",$data,150);
        } else {
            $data = Cache::get("system_monitor:collection:network:TX:$ip");
        }

        return $data;
    }


    static public function collectionFormat($data){
        $k = [];
        $v = [];

        foreach ($data as $kk => $vv){
            $k[] = date('m-d H:i',$vv);
            $v[] = floatval(json_decode($kk, true)['value']);
        }

        return [
            'time' => $k,
            'load' => $v
        ];
    }



    static public function networkFormat($data){
        $k = [];
        $packets = [];
        $bytes = [];

        foreach ($data as $kk => $vv){
            $k[] = date('m-d H:i',$vv);
            $arr = explode(',',json_decode($kk, true)['value']);
            $packets[] = intval($arr[0]);
            $bytes[] = intval($arr[1]);
        }

        return [
            'time' => $k,
            'packets' => $packets,
            'bytes' => $bytes,

        ];
    }

    static public function getIPByHash($token){
        $hash = SystemMonitor::getHashes();
        $ip = $hash[$token];

        if(empty($ip))
            throw new Exception("Wrong Token", 403);

        return $ip;
    }
}
