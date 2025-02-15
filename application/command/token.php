<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Cache;

class Token extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('token')
        	-> addArgument('cmd', Argument::OPTIONAL, "Command: add, del, get, list")
            -> addOption('uuid', null, Option::VALUE_OPTIONAL, 'Device UUID')
            -> setDescription('Node token operation');
        // 设置参数

    }

    protected function execute(Input $input, Output $output)
    {
        
        $cmd = trim($input->getArgument('cmd'));
        $node_token = Cache::store('token')->has("node_token")?json_decode(Cache::store("token")->get("node_token"), true):[];

        switch ($cmd) {
            case "add":
                if ($input->hasOption('uuid')) {
                    $uuid = trim($input->getOption('uuid'));
                    $str = "0123456789abcdefghijklnmopqrstuvwxyzABCDEFGHIJKLNMOPQRSTUVWXYZ";
                    $token = '';
                    for($i = 128;$i;$i--)
                        $token .= substr($str, random_int(0,strlen($str)-1), 1);

                    $node_token[$uuid] = $token;

                    $output->writeln("The token is: \n$token\nPlease keep it nice and safe.");
                } else {
                    $output->writeln("No UUID specify.");
                }
                break;
            case "del":
                if ($input->hasOption('uuid')) {
                    $uuid = trim($input->getOption('uuid'));
                    try {
                        $node_token[$uuid];
                        unset($node_token[$uuid]);
                        $output->writeln("OK");
                    }catch (\Exception $e) {
                        $output->writeln("No match UUID found.");
                    }
                } else {
                    $output->writeln("No UUID specify.");
                }
                break;
            case "list":
                foreach ($node_token as $key => $value) 
                    $output->writeln("$key: $value");
                exit(0);
            case "help":
                $output->writeln("Node UUID token manager");
                $output->writeln("");
                $output->writeln("add --uuid <UUID>  -  Add UUID token.");
                $output->writeln("del --uuid <UUID>  -  Delete existing UUID token.");
                $output->writeln("list               -  List existing UUID token.");
                break;
            default:
                $output->writeln("No command specify. Using `php think token help`");
                exit(1);
        }
        Cache::store("token")->set("node_token", json_encode($node_token));
    }
}
