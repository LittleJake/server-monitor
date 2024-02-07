<?php

namespace app\common\lib;

use think\Exception;
use think\facade\Cache;
use think\facade\Env;
use think\facade\Log;
use think\exception\HttpException;

class SystemMonitor
{
    static private $url = "http://ip-api.com/json/";
    static private $cacheTime = 300;

    static public function fetchIPInfo($ip)
    {
        $info = [];
        $ips = is_array($ip) ? $ip : [$ip];
        foreach ($ips as $v) {
            $ip_without_cidr_24 = strrev(strstr(strrev($v), '.')) . "0";
            try {
                //For Multicast IP use TTL
                $info[$v] = Cache::store('flag')->remember($v, function () use ($ip_without_cidr_24) {
                    $data = json_decode(self::curlGet(self::$url . "$ip_without_cidr_24?fields=country,countryCode&lang="), true);
                    if ($data == null) throw new Exception("Failed to query country code");
                    return $data;
                }, 24 * 60 * 60);
            } catch (Exception $e) {
                Log::error($e->getMessage());
                $info[$v] = [
                    'countryCode' => '',
                    'country' => ''
                ];
            }

            if (!is_array($ip)) return $info[$v];
        }

        return $info;
    }

    static public function getUUIDs()
    {
        return Cache::remember('system_monitor:hashes', function () {
            return Cache::store('redis')->handler()
                ->hGetAll("system_monitor:hashes");
        }, self::$cacheTime);
    }

    static public function setUUID($uuid, $ip)
    {
        try {
            Cache::store('redis')->handler()->hSet("system_monitor:hashes", $uuid, $ip);
            Cache::store('redis')->handler()->expire("system_monitor:hashes", Env::get("MONITOR.DATA_TIMEOUT"));
            $uuids = SystemMonitor::getUUIDs();
            if (empty($uuids[$uuid])) {
                Cache::rm("system_monitor:hashes");
                return ['code' => 200, 'message' => "Authorization OK, welcome aboard."];
            }
    
            return ['code' => 200, 'message' => "OK"];
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return ['code' => 500, 'message' => "Fail."];
        }
    }

    static public function getLatest($uuid)
    {
        $stat = [];
        $uuids = is_array($uuid) ? $uuid : [$uuid];

        foreach ($uuids as $h) {
            $info = array_keys(self::getCollection($h));
            $stat[$h] = array_pop($info);

            if (!is_array($uuid)) return $stat[$h];
        }

        return $stat;
    }

    static public function getInfo($uuid)
    {
        $info = [];
        $uuids = is_array($uuid) ? $uuid : [$uuid];

        foreach ($uuids as $u) {
            $info[$u] = Cache::remember("system_monitor:info:$u", function () use ($u) {
                return Cache::store('redis')->handler()->hGetAll("system_monitor:info:$u");
            }, self::$cacheTime);

            if (!is_array($uuid)) return $info[$u];
        }

        return $info;
    }

    static public function setInfo($uuid, $data)
    {
        try {
            Cache::store('redis')->handler()->hMset("system_monitor:info:$uuid", $data);
            Cache::store('redis')->handler()->expire("system_monitor:info:$uuid", Env::get("MONITOR.DATA_TIMEOUT"));
            return ['code' => 200, 'message' => "OK"];
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return ['code' => 500, 'message' => "Fail."];
        }
    }

    static public function timeFormat($time)
    {
        return lang("Uptime Format", [
            floor($time / (24 * 60 * 60)), floor(($time / (60 * 60)) % 24), floor(($time / (60)) % 60), floor($time % 60)
        ]);
    }

    static private function curlGet($url)
    {
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
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, TRUE);
        //执行命令
        $data = curl_exec($curl);

        // 显示错误信息
        if (curl_error($curl) || curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200)
            Log::error(curl_error($curl));
        else
            curl_close($curl);

        return $data;
    }

    static public function getCollection($uuid)
    {
        return Cache::remember("system_monitor:collection:$uuid", function () use ($uuid) {
            return Cache::store('redis')->handler()
                ->zRangeByScore(
                    "system_monitor:collection:$uuid",
                    0,
                    time(),
                    ['withscores' => TRUE]
                );
        }, self::$cacheTime);
    }

    static public function setCollection($uuid, $data)
    {
        try {
            Cache::store('redis')->handler()
                ->zAdd("system_monitor:collection:$uuid", time(), $data);
            Cache::store('redis')->handler()
                ->zRemRangeByScore("system_monitor:collection:$uuid", 0, time() - Env::get("MONITOR.DATA_TIMEOUT"));
            Cache::store('redis')->handler()->expire("system_monitor:collection:$uuid", Env::get("MONITOR.DATA_TIMEOUT"));
            return ['code' => 200, 'message' => "OK"];
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return ['code' => 500, 'message' => "Fail."];
        }
    }

    static public function collectionFormat($data, $name)
    {
        $time = [];
        $value = [];

        switch (strtolower($name)) {
            case "thermal":
            case "battery":
            case "load":
                foreach ($data as $data_json => $data_time) {
                    $sensors = json_decode($data_json, true)[$name];
                    if (count($sensors) > 0)
                        $time[] = date('m-d H:i', $data_time);
                    //extract $name from collection.
                    foreach ($sensors as $sensor => $sensor_value)
                        $value[$sensor][] = $sensor_value;
                }
                break;

            case "disk":
            case "memory":
                //in case of missing mounting point periodically.
                $mount_points = [];
                foreach ($data as $data_json => $_)
                    $mount_points = array_unique(array_merge($mount_points, array_keys(json_decode($data_json, true)[$name])));

                foreach ($data as $data_json => $data_time) {
                    $time[] = date('m-d H:i', $data_time);
                    $disk_data = json_decode($data_json, true)[$name];

                    foreach ($mount_points as $_ => $mount_point) {
                        if (empty($disk_data[$mount_point]))
                            $value[$mount_point][] = 0;
                        else
                            $value[$mount_point][] = $disk_data[$mount_point]['used'];
                    }
                }
                break;
            case "network":
                $rx_packets = [];
                $rx_megabytes = [];
                $tx_packets = [];
                $tx_megabytes = [];

                foreach ($data as $data_json => $data_time) {
                    $time[] = date('m-d H:i', $data_time);
                    $network_data = json_decode($data_json, true)[$name];
                    $rx_packets[] = (intval($network_data['RX']['packets']) * 1.0) / 1000;
                    $tx_packets[] = (intval($network_data['TX']['packets']) * 1.0) / 1000;
                    $rx_megabytes[] = intval(intval(intval($network_data['RX']['bytes']) * 100.00) / 1048576) * 1.0 / 100;
                    $tx_megabytes[] = intval(intval(intval($network_data['TX']['bytes']) * 100.00) / 1048576) * 1.0 / 100;
                }

                return [
                    'RX' => [
                        'time' => $time,
                        'packets' => $rx_packets,
                        'megabytes' => $rx_megabytes
                    ],
                    'TX' => [
                        'time' => $time,
                        'packets' => $tx_packets,
                        'megabytes' => $tx_megabytes
                    ]
                ];
        }


        return [
            'time' => $time,
            'value' => $value
        ];
    }

    static public function getIPByUUID($uuid)
    {
        $uuids = SystemMonitor::getUUIDs();
        if (empty($uuids[$uuid]))
            throw new HttpException(403, "Wrong Token");
        return $uuids[$uuid];
    }

    static public function deleteInfo($uuid)
    {
        $ip = SystemMonitor::getIPByUUID($uuid);
        Cache::rm("system_monitor:collection:$uuid");
        Cache::rm("system_monitor:info:$uuid");
        Cache::rm("system_monitor:hashes");
        Cache::store('redis')->rm("system_monitor:collection:$uuid");
        Cache::store('redis')->rm("system_monitor:info:$uuid");
        Cache::store('redis')->handler()->hDel("system_monitor:hashes", $uuid);
    }

    static public function setHide($uuid, $hide)
    {
        if (!empty($hide))
            Cache::store('redis')->handler()
                ->sRem("system_monitor:hide", $uuid);
        else
            Cache::store('redis')->handler()
                ->sAdd("system_monitor:hide", $uuid);

        Cache::rm("system_monitor:hide");
    }

    static public function setDisplayName($uuid, $name)
    {
        if (!empty($name)) {
            Cache::store('redis')->handler()
                ->hSet("system_monitor:name", $uuid, $name);
        } else {
            Cache::store('redis')->handler()
                ->hDel("system_monitor:name", $uuid);
        }

        Cache::rm("system_monitor:name");
    }

    static public function getDisplayName($uuid)
    {
        $name = Cache::remember("system_monitor:name", function () {
            return Cache::store('redis')->handler()->hGetAll("system_monitor:name");
        }, self::$cacheTime);

        if (!is_array($uuid)) {
            return $name[$uuid];
        } else {
            $uuids = array_flip($uuid);
            return array_intersect_key($name, $uuids);
        }
    }

    static public function UUIDIsDisplay($uuid)
    {
        $data =  array_flip(self::getHide());
        return !isset($data[$uuid]);
    }

    static public function getHide()
    {
        return  Cache::remember("system_monitor:hide", function () {
            return Cache::store('redis')->handler()->sMembers("system_monitor:hide");
        }, self::$cacheTime);
    }

    static public function getOnline()
    {
        $time = time();
        $uuids = array_keys(SystemMonitor::getUUIDs());
        $infos = SystemMonitor::getInfo($uuids);

        $data = array_filter($infos, function ($uuid) use ($infos, $time) {
            return !empty($infos[$uuid]['Update Time']) && $time - intval($infos[$uuid]['Update Time']) <= Env::get("MONITOR.OFFLINE_THRESHOLD");
        }, ARRAY_FILTER_USE_KEY);

        return $data;
    }

    static public function getOffline()
    {
        $time = time();
        $uuids = array_keys(SystemMonitor::getUUIDs());
        $infos = SystemMonitor::getInfo($uuids);

        $data = array_filter($infos, function ($uuid) use ($infos, $time) {
            return !empty($infos[$uuid]['Update Time']) && ($time - intval($infos[$uuid]['Update Time']) > Env::get("MONITOR.OFFLINE_THRESHOLD"));
        }, ARRAY_FILTER_USE_KEY);

        return $data;
    }

    static public function refreshCache()
    {
        foreach (self::getUUIDs() as $uuid => $_) {
            self::getInfo($uuid);
            self::getCollection($uuid);
        }
    }

    static public function sortByCountry($arr)
    {
        uasort($arr, "self::countryCmp");
        return $arr;
    }

    static private function countryCmp($a, $b)
    {
        if ($a["Country"] == $b["Country"]) return 0;
        if ($a["Country"] == 'Private') return -1;
        return ($a["Country"] < $b["Country"]) ? -1 : 1;
    }
}
