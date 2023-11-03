<?php

namespace app\common\lib;

use think\Exception;
use think\facade\Cache;
use think\facade\Log;

class SystemMonitor
{
    static private $url = "http://ip-api.com/json/";
    static private $cacheTime = 300;

    static public function fetchIPInfo($ip){
        $info = [];
        $ips = is_array($ip)?$ip:[$ip];
        foreach ($ips as $v){
            $ip_without_cidr_24 = strrev(strstr(strrev($v), '.')). "0";
            try {
                //For Multicast IP use TTL
                $info[$v] = Cache::store('flag')->remember($v, function () use ($ip_without_cidr_24){
                    $data = json_decode(self::curl_get(self::$url."$ip_without_cidr_24?fields=country,countryCode&lang="),true);
                    if($data == null) throw new Exception("Failed to query country code");
                    return $data;
                }, 24*60*60);
                Log::info($ip_without_cidr_24);
            }
            catch (Exception $e){
                Log::error($e->getMessage());
                $info[$v] = [
                    'countryCode' => '',
                    'country' => ''
                ];
            }

            if(!is_array($ip)) return $info[$v];
        }

        return $info;
    }

    static public function getUUIDs(){
        return Cache::remember('system_monitor:hashes', function (){
            return Cache::store('redis')->handler()
                ->hGetAll("system_monitor:hashes");
        }, self::$cacheTime);
    }

    static public function setUUID($uuid, $ip){
        return Cache::store('redis')->handler()
                ->hSet("system_monitor:hashes", $uuid, $ip);
    }

    static public function getLatest($uuid) {
        $stat = [];
        $uuids = is_array($uuid)?$uuid:[$uuid];

        foreach ($uuids as $h){
            $info = array_keys(self::getCollection($h));
            $stat[$h] = array_pop($info);

            if(!is_array($uuid)) return $stat[$h];
        }

        return $stat;
    }

    static public function getInfo($uuid) {
        $info = [];
        $uuids = is_array($uuid)?$uuid:[$uuid];

        foreach ($uuids as $u){
            $info[$u] = Cache::remember("system_monitor:info:$u", function () use ($u){
                return Cache::store('redis')->handler()->hGetAll("system_monitor:info:$u");
            }, self::$cacheTime);

            if(!is_array($uuid)) return $info[$u];
        }

        return $info;
    }

    static public function setInfo($uuid, $data) {
        try {
            Cache::store('redis')->handler()->hSetAll("system_monitor:info:$uuid", $data);
            return ['code' => 200, 'message'=> "OK"];
        } catch (Exception $e) {
            Log::error($e -> getMessage());
            return ['code' => 500, 'message'=> "Fail."];
        }
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
        if (curl_error($curl) || curl_getinfo($curl,CURLINFO_HTTP_CODE) != 200)
            Log::error(curl_error($curl));
        else
            curl_close($curl);

        return $data;
    }

    static public function getCollection($uuid){
        return Cache::remember("system_monitor:collection:$uuid", function () use ($uuid){
            return Cache::store('redis')->handler()
                ->zRangeByScore("system_monitor:collection:$uuid", 0, time()
                    , ['withscores' => TRUE]);
        }, self::$cacheTime);
    }

    static public function setCollection($uuid, $data){
        try {
            Cache::store('redis')->handler()
                ->zAdd("system_monitor:collection:$uuid", time(), $data);
            return ['code' => 200, 'message'=> "OK"];
        } catch (Exception $e) {
            Log::error($e -> getMessage());
            return ['code' => 500, 'message'=> "Fail."];
        }
        
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

    static public function getIPByUUID($uuid){
        $uuids = SystemMonitor::getUUIDs();
        if(empty($uuids[$uuid]))
            throw new Exception("Wrong Token", 403);
        return $uuids[$uuid];
    }

    static public function deleteInfo($uuid){
        $ip = SystemMonitor::getIPByUUID($uuid);
        Cache::rm("system_monitor:collection:$uuid");
        Cache::rm("system_monitor:info:$uuid");
        Cache::rm("system_monitor:hashes");
        Cache::store('redis')->rm("system_monitor:collection:$uuid");
        Cache::store('redis')->rm("system_monitor:info:$uuid");
        Cache::store('redis')->handler()->hDel("system_monitor:hashes", $uuid);
    }

    static public function setDisplay($uuid, $display){
        if (!empty($display))
            Cache::store('redis')->handler()
                ->sRem("system_monitor:hide", $uuid);
        else
            Cache::store('redis')->handler()
                ->sAdd("system_monitor:hide", $uuid);

        Cache::rm("system_monitor:hide");
    }

    static public function UUIDIsDisplay($uuid){
        if(!Cache::has("system_monitor:hide")){
            $data = Cache::store('redis')->handler()
                ->sMembers("system_monitor:hide");
            Cache::set("system_monitor:hide",$data, self::$cacheTime);
        } else {
            $data = Cache::get("system_monitor:hide");
        }
        $data =  array_flip($data);
        return !isset($data[$uuid]);
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
        foreach (self::getUUIDs() as $uuid => $ip){
            self::getInfo($uuid);
            self::fetchIPInfo($ip);
            self::getCollection($uuid);
        }
    }

    static public function sortByCountry($arr){
        uasort($arr, "self::countryCmp");
        return $arr;
    }

    static private function countryCmp($a,$b){
        if ($a["IP"]["country"] == $b["IP"]["country"]) return 0;
        if ($a["IP"]["country"] == 'Private') return -1;
        return ($a["IP"]["country"]<$b["IP"]["country"])?-1:1;
    }
}
