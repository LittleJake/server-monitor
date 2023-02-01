<?php

namespace app\common\lib;

use think\Exception;
use think\facade\Cache;
use think\facade\Log;

class SystemMonitor
{
    static private $url = "https://ipapi.co/";
    static private $cacheTime = 150;

    static public function fetchIPInfo($ip){
        $info = [];
        $ips = is_array($ip)?$ip:[$ip];

        foreach ($ips as $v){
            $ip_without_cidr = !empty(strstr($v, '/', true))?strstr($v, '/', true):$v;
            try {
                //For Multicast IP use TTL
                $info[$v] = Cache::store('flag')->remember($v, function () use ($ip_without_cidr){
                    $data = json_decode(self::curl_get(self::$url.$ip_without_cidr."/json/"),true);
                    if($data == null) throw new Exception("Failed to query country code");
                    return $data;
                }, 24*60*60);
            }
            catch (Exception $e){
                $info[$v] = [
                    'country_code' => '',
                    'country_name' => ''
                ];
            }

            if(!is_array($ip)) return $info[$v];
        }

        return $info;
    }

    static public function getHashes(){
        return Cache::remember('system_monitor:hashes', function (){
            return Cache::store('redis')->handler()
                ->hGetAll("system_monitor:hashes");
        }, self::$cacheTime);
    }

    static public function getLatest($hash) {
        $stat = [];
        $hashes = is_array($hash)?$hash:[$hash];

        foreach ($hashes as $h){
            $info = array_keys(self::getCollection($h));
            $stat[$h] = array_pop($info);

            if(!is_array($hash)) return $stat[$h];
        }

        return $stat;
    }

    static public function getInfo($hash) {
        $info = [];
        $hashes = is_array($hash)?$hash:[$hash];

        foreach ($hashes as $h){
            $info[$h] = Cache::remember("system_monitor:info:$h", function () use ($h){
                return Cache::store('redis')->handler()->hGetAll("system_monitor:info:$h");
            }, self::$cacheTime);

            if(!is_array($hash)) return $info[$h];
        }

        return $info;
    }

    static public function timeFormat($time){
        return lang("Uptime Format", [floor($time / (24*60*60)), floor(($time / (60*60)) % 24)
            , floor(($time / (60)) % 60), floor($time % 60)]);
    }

    static private function curl_get($url){
        $header = ['Accept: application/json', 'User-Agent: Mozilla/5.0'];
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

    // static public function getCollection($hash, $key){
    //     return Cache::remember("system_monitor:collection:$key:$hash", function () use ($hash, $key){
    //         return Cache::store('redis')->handler()
    //             ->zRangeByScore("system_monitor:collection:$key:$hash", 0, time()
    //                 , ['withscores' => TRUE]);
    //     }, self::$cacheTime);
    // }

    
    static public function getCollection($hash){
        return Cache::remember("system_monitor:collection:$hash", function () use ($hash){
            return Cache::store('redis')->handler()
                ->zRangeByScore("system_monitor:collection:$hash", 0, time()
                    , ['withscores' => TRUE]);
        }, self::$cacheTime);
    }

    static public function collectionFormat($data, $name){
        $k = [];
        $v = [];

        foreach ($data as $kk => $vv){
            $k[] = date('m-d H:i',$vv);
            foreach (json_decode($kk, true)[$name] as $kkk => $vvv)
                $v[$kkk][] = $vvv;
        }

        return [
            'time' => $k,
            'value' => $v
        ];
    }

    static public function diskFormat($data){
        $k = [];
        $v = [];

        foreach ($data as $kk => $vv){
            $k[] = date('m-d H:i',$vv);
            foreach (json_decode($kk, true)['Disk'] as $kkk => $vvv)
                $v[$kkk][] = $vvv['used'];
        }

        return [
            'time' => $k,
            'value' => $v
        ];
    }

    
    static public function memoryFormat($data){
        $k = [];
        $v = [];

        foreach ($data as $kk => $vv){
            $k[] = date('m-d H:i',$vv);
            foreach (json_decode($kk, true)['Memory'] as $kkk => $vvv)
                $v[$kkk][] = $vvv['used'];
        }

        return [
            'time' => $k,
            'value' => $v
        ];
    }

    static public function networkFormat($data){
        $k = [];
        $rx_packets = [];
        $rx_megabytes = [];
        $tx_packets = [];
        $tx_megabytes = [];

        foreach ($data as $kk => $vv){
            $k[] = date('m-d H:i',$vv);
            $j = json_decode($kk, true)['Network'];
            // $arr = explode(',',json_decode($kk, true)['value']);
            $rx_packets[] = (intval($j['RX']['packets'])*1.0)/1000;
            $tx_packets[] = (intval($j['TX']['packets'])*1.0)/1000;
            $rx_megabytes[] = intval(intval(intval($j['RX']['bytes'])*100.00)/1048576)*1.0/100;
            $tx_megabytes[] = intval(intval(intval($j['TX']['bytes'])*100.00)/1048576)*1.0/100;
        }

        return [
            'RX' => [
                'time' => $k,
                'packets' => $rx_packets,
                'megabytes' => $rx_megabytes
            ],
            'TX' => [
                'time' => $k,
                'packets' => $tx_packets,
                'megabytes' => $tx_megabytes
                ]
            ];
    }

    static public function getIPByHash($token){
        $hash = SystemMonitor::getHashes();
        if(empty($hash[$token]))
            throw new Exception("Wrong Token", 403);
        return $hash[$token];
    }

    static public function deleteInfo($hash){
        $ip = SystemMonitor::getIPByHash($hash);
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
        Cache::store('redis')->handler()->hDel("system_monitor:hashes", $hash);
    }

    static public function setDisplay($hash, $display){
        if (!empty($display))
            Cache::store('redis')->handler()
                ->sRem("system_monitor:hide", $hash);
        else
            Cache::store('redis')->handler()
                ->sAdd("system_monitor:hide", $hash);

        Cache::rm("system_monitor:hide");
    }

    static public function hashIsDisplay($hash){
        if(!Cache::has("system_monitor:hide")){
            $data = Cache::store('redis')->handler()
                ->sMembers("system_monitor:hide");
            Cache::set("system_monitor:hide",$data, self::$cacheTime);
        } else {
            $data = Cache::get("system_monitor:hide");
        }
        $data =  array_flip($data);
        return !isset($data[$hash]);
    }

    static public function getHide(){
        if(!Cache::has("system_monitor:hide")){
            $data = Cache::store('redis')->handler()
                ->sMembers("system_monitor:hide");
            Cache::set("system_monitor:hide",$data, self::$cacheTime);
        } else {
            $data = Cache::get("system_monitor:hide");
        }
        return $data;
    }

    static public function refreshCache(){
        foreach (self::getHashes() as $hash => $ip){
            self::getInfo($hash);
            self::getStat($hash);
            self::fetchIPInfo($ip);
            self::getCollection($hash, 'cpu');
            self::getCollection($hash, 'disk');
            self::getCollection($hash, 'memory');
            self::getCollection($hash, 'swap');
            self::getCollection($hash, 'network:RX');
            self::getCollection($hash, 'network:TX');
            self::getCollection($hash, 'thermal');
        }
    }

    static public function sortByCountry($arr){
        uasort($arr, "self::countryCmp");
        return $arr;
    }

    static private function countryCmp($a,$b){
        if ($a["IP"]["country_name"] == $b["IP"]["country_name"]) return 0;
        if ($a["IP"]["country_name"] == 'Private') return -1;
        return ($a["IP"]["country_name"]<$b["IP"]["country_name"])?-1:1;
    }
}
