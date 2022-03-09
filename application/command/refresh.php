<?php

namespace app\command;

use app\common\lib\SystemMonitor;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class refresh extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('refresh:cache')->setDescription('Refresh cache');
        // 设置参数
        
    }

    protected function execute(Input $input, Output $output)
    {
    	// 指令输出
    	$output->writeln('Refreshing');
        SystemMonitor::refreshCache();
        $output->writeln('Refresh OK.');
    }
}
