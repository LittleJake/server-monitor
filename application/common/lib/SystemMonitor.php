<?php

namespace app\common\lib;

use think\Exception;
use think\facade\Cache;
use think\facade\Log;

class SystemMonitor
{
    static private $url = "http://ip-api.com/json/";
    static public function fetchIPInfo($ip){
        $info = [];
        if(is_array($ip)){
            foreach ($ip as $v){
                $tmp = !empty(strstr($v, '/', true))?strstr($v, '/', true):$v;

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
            $tmp = !empty(strstr($ip, '/', true))?strstr($ip, '/', true):$ip;

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
            Cache::set('system_monitor:hashes', $hash, 150);
        }

        return $hash;
    }

    static public function getStat($hash) {
        $stat = [];
        if(is_array($hash)){
            foreach ($hash as $v){
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
            if(Cache::has("system_monitor:stat:$hash")
                && !empty(Cache::get("system_monitor:stat:$hash"))){
                $stat = Cache::get("system_monitor:stat:$hash");
            } else{
                $stat = json_decode(Cache::store('redis')->handler()
                    ->get("system_monitor:stat:$hash"),true);
                Cache::set("system_monitor:stat:$hash", $stat);
            }
        }

        return $stat;
    }

    static public function getInfo($hash) {
        $info = [];
        if(is_array($hash)){
            foreach ($hash as $v){
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
            if(Cache::has("system_monitor:info:$hash")
                && !empty(Cache::get("system_monitor:info:$hash"))){
                $info = Cache::get("system_monitor:info:$hash");
            } else{
                $info = Cache::store('redis')->handler()
                    ->hGetAll("system_monitor:info:$hash");
                Cache::set("system_monitor:info:$hash", $info);
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

    static public function getCpuCollection($hash){
        $time =  time();
        if(!Cache::has("system_monitor:collection:cpu:$hash")){
            $data = Cache::store('redis')->handler()
                ->zRangeByScore("system_monitor:collection:cpu:$hash", 0, $time
                    , ['withscores' => TRUE]);
            Cache::set("system_monitor:collection:cpu:$hash",$data,150);
        } else {
            $data = Cache::get("system_monitor:collection:cpu:$hash");
        }
        return $data;
    }

    static public function getSwapCollection($hash){
        $time =  time();
        if(!Cache::has("system_monitor:collection:swap:$hash")){
            $data = Cache::store('redis')->handler()
                ->zRangeByScore("system_monitor:collection:swap:$hash", 0, $time
                    , ['withscores' => TRUE]);
            Cache::set("system_monitor:collection:swap:$hash",$data,150);
        } else {
            $data = Cache::get("system_monitor:collection:swap:$hash");
        }
        return $data;
    }
    static public function getThermalCollection($hash){
        $time =  time();

        if(!Cache::has("system_monitor:collection:thermal:$hash")){
            $data = Cache::store('redis')->handler()
                ->zRangeByScore("system_monitor:collection:thermal:$hash", 0, $time
                    , ['withscores' => TRUE]);
            Cache::set("system_monitor:collection:thermal:$hash",$data,150);
        } else {
            $data = Cache::get("system_monitor:collection:thermal:$hash");
        }
        return $data;
    }
    static public function getMemoryCollection($hash){
        $time =  time();

        if(!Cache::has("system_monitor:collection:memory:$hash")){
            $data = Cache::store('redis')->handler()
                ->zRangeByScore("system_monitor:collection:memory:$hash", 0, $time
                    , ['withscores' => TRUE]);
            Cache::set("system_monitor:collection:memory:$hash",$data,150);
        } else {
            $data = Cache::get("system_monitor:collection:memory:$hash");
        }
        return $data;
    }

    static public function getDiskCollection($hash){
        $time =  time();

        if(!Cache::has("system_monitor:collection:disk:$hash")){
            $data = Cache::store('redis')->handler()
                ->zRangeByScore("system_monitor:collection:disk:$hash", 0, $time
                    , ['withscores' => TRUE]);
            Cache::set("system_monitor:collection:disk:$hash",$data,150);
        } else {
            $data = Cache::get("system_monitor:collection:disk:$hash");
        }

        return $data;
    }

    static public function getNetworkRXCollection($hash){
        $time =  time();

        if(!Cache::has("system_monitor:collection:network:RX:$hash")){
            $data = Cache::store('redis')->handler()
                ->zRangeByScore("system_monitor:collection:network:RX:$hash", 0, $time
                    , ['withscores' => TRUE]);
            Cache::set("system_monitor:collection:network:RX:$hash",$data,150);
        } else {
            $data = Cache::get("system_monitor:collection:network:RX:$hash");
        }

        return $data;
    }

    static public function getNetworkTXCollection($hash){
        $time =  time();

        if(!Cache::has("system_monitor:collection:network:TX:$hash")){
            $data = Cache::store('redis')->handler()
                ->zRangeByScore("system_monitor:collection:network:TX:$hash", 0, $time
                    , ['withscores' => TRUE]);
            Cache::set("system_monitor:collection:network:TX:$hash",$data,150);
        } else {
            $data = Cache::get("system_monitor:collection:network:TX:$hash");
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

    static public function diskFormat($data){
        $k = [];
        $v = [];

        foreach ($data as $kk => $vv){
            $k[] = date('m-d H:i',$vv);
            foreach (json_decode($kk, true)['value'] as $kkk => $vvv)
                $v[$kkk][] = $vvv['used'];
        }

        return [
            'time' => $k,
            'load' => $v
        ];
    }

    static public function networkFormat($data){
        $k = [];
        $packets = [];
        $megabytes = [];

        foreach ($data as $kk => $vv){
            $k[] = date('m-d H:i',$vv);
            $arr = explode(',',json_decode($kk, true)['value']);
            $packets[] = (intval($arr[0])*1.0)/1000;
            $megabytes[] = intval(intval(intval($arr[1])*100.00)/1048576)*1.0/100;
        }

        return [
            'time' => $k,
            'packets' => $packets,
            'megabytes' => $megabytes,
        ];
    }

    static public function getIPByHash($token){
        $hash = SystemMonitor::getHashes();

        if(empty($hash[$token]))
            throw new Exception("Wrong Token", 403);

        return $hash[$token];
    }

    static public function deleteInfo($hash){
        Cache::rm("system_monitor:collection:cpu:$hash");
        Cache::rm("system_monitor:collection:disk:$hash");
        Cache::rm("system_monitor:collection:memory:$hash");
        Cache::rm("system_monitor:collection:swap:$hash");
        Cache::rm("system_monitor:collection:network:RX:$hash");
        Cache::rm("system_monitor:collection:network:TX:$hash");
        Cache::rm("system_monitor:collection:network:tmp:$hash");
        Cache::rm("system_monitor:collection:info:$hash");
        Cache::rm("system_monitor:stat:$hash");
        Cache::rm("system_monitor:hashes");
        Cache::store('redis')->rm("system_monitor:collection:cpu:$hash");
        Cache::store('redis')->rm("system_monitor:collection:disk:$hash");
        Cache::store('redis')->rm("system_monitor:collection:memory:$hash");
        Cache::store('redis')->rm("system_monitor:collection:swap:$hash");
        Cache::store('redis')->rm("system_monitor:collection:network:RX:$hash");
        Cache::store('redis')->rm("system_monitor:collection:network:TX:$hash");
        Cache::store('redis')->rm("system_monitor:collection:network:tmp:$hash");
        Cache::store('redis')->rm("system_monitor:collection:info:$hash");
        Cache::store('redis')->rm("system_monitor:stat:$hash");
        Cache::store('redis')->handler()->sRem("system_monitor:nodes", $ip);
        Cache::store('redis')->handler()->hDel("system_monitor:hashes", $hash);
    }

    public static function setDisplay($hash, $display){
        if (!empty($display))
            Cache::store('redis')->handler()
                ->sRem("system_monitor:hide", $hash);
        else
            Cache::store('redis')->handler()
                ->sAdd("system_monitor:hide", $hash);

        Cache::rm("system_monitor:hide");
    }

    public static function hashIsDisplay($hash){
        if(!Cache::has("system_monitor:hide")){
            $data = Cache::store('redis')->handler()
                ->sMembers("system_monitor:hide");
            Cache::set("system_monitor:hide",$data,150);
        } else {
            $data = Cache::get("system_monitor:hide");
        }
        $data =  array_flip($data);
        return !isset($data[$hash]);
    }

    public static function getHide(){
        if(!Cache::has("system_monitor:hide")){
            $data = Cache::store('redis')->handler()
                ->sMembers("system_monitor:hide");
            Cache::set("system_monitor:hide",$data,150);
        } else {
            $data = Cache::get("system_monitor:hide");
        }
        return $data;
    }
}
