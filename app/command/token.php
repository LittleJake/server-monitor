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
	        	-> addArgument('cmd', Argument::OPTIONAL, "Command: add, del, get, list, import")
            -> addOption('uuid', null, Option::VALUE_OPTIONAL, 'Device UUID')
            -> addOption('file', null, Option::VALUE_REQUIRED, 'Token list file for import')
            -> setDescription('Node token operation');
        // 设置参数

    }

    protected function execute(Input $input, Output $output)
    {
        
        $cmd = trim($input->getArgument('cmd'));
        $cachedTokens = Cache::store('token')->has("node_token")?json_decode(Cache::store("token")->get("node_token"), true):[];
        if (!is_array($cachedTokens)) {
            throw new \RuntimeException('Token cache is invalid.');
        }
        $node_token = $cachedTokens;

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
            case "import":
                $file = $input->getOption('file');
                if (!$file || !is_file($file) || !is_readable($file)) {
                    throw new \RuntimeException('Token list file not found or unreadable.');
                }

                $lines = file($file, FILE_IGNORE_NEW_LINES);
                if ($lines === false) {
                    throw new \RuntimeException('Unable to read token list file.');
                }

                $imported = [];
                foreach ($lines as $index => $line) {
                    if ($index === 0) {
                        $line = preg_replace('/^\xEF\xBB\xBF/', '', $line);
                    }
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }

                    $separator = strpos($line, ':');
                    if ($separator === false) {
                        throw new \RuntimeException('Invalid token list format at line ' . ($index + 1) . '.');
                    }

                    $uuid = trim(substr($line, 0, $separator));
                    $token = trim(substr($line, $separator + 1));
                    if ($uuid === '' || $token === '' || isset($imported[$uuid])) {
                        throw new \RuntimeException('Invalid or duplicate UUID at line ' . ($index + 1) . '.');
                    }

                    $imported[$uuid] = $token;
                }

                if (!$imported) {
                    throw new \RuntimeException('No token entries found in file.');
                }

                foreach ($imported as $uuid => $token) {
                    $node_token[$uuid] = $token;
                }
                $output->writeln('Imported ' . count($imported) . ' token(s).');
                break;
            case "help":
                $output->writeln("Node UUID token manager");
                $output->writeln("");
                $output->writeln("add --uuid <UUID>  -  Add UUID token.");
                $output->writeln("del --uuid <UUID>  -  Delete existing UUID token.");
                $output->writeln("list               -  List existing UUID token.");
                $output->writeln("import --file <PATH> - Import UUID: Token list.");
                break;
            default:
                $output->writeln("No command specify. Using `php think token help`");
                exit(1);
        }
        Cache::store("token")->set("node_token", json_encode($node_token));
    }
}
