<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Cache;

class token extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('token:generate')
            ->setDescription('generate admin token.');
        // 设置参数

    }

    protected function execute(Input $input, Output $output)
    {
        $str = "0123456789abcdefghijklnmopqrstuvwxyzABCDEFGHIJKLNMOPQRSTUVWXYZ";
        $token = '';
        for($i = 64;$i;$i--)
            $token .= substr($str, rand(0,strlen($str)-1), 1);

        $cache = new Cache([
            'type' => 'complex',
            'default' => ['type' => '', 'expire' => 0, 'prefix' => '', 'path' => '',],
            'token' => ['type' => 'file', 'expire' => 0, 'prefix' => 'token', 'path' => './runtime/cache/',]]);
        $cache->store('token')->set('ADMIN.TOKEN', $token);
        // 指令输出
        $output->writeln("The new token is: \n$token\nPlease keep it nice and safe.");
    }
}
