<?php

namespace app\command;

use support\extend\Event;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EventListCommand extends Command
{
    protected function configure()
    {
        $this->setName('event:list')
            ->setDescription('显示所有的事件列表');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $headers = ['id', 'event_name', 'callback'];
        $rows = [];
        foreach (Event::list() as $id => $item) {
            $event_name = $item[0];
            $callback = $item[1];
            if (is_array($callback) && is_object($callback[0])) {
                $callback[0] = get_class($callback[0]);
            }
            $cb = $callback instanceof \Closure ? 'Closure' : (is_array($callback) ? json_encode($callback) : var_export($callback, 1));
            $rows[] = [$id, $event_name, $cb];
        }

        $table = new Table($output);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->render();
        return self::SUCCESS;
    }

}
