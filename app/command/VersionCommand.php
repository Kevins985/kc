<?php

namespace app\command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VersionCommand extends Command
{
    protected static $defaultName = 'version';
    protected static $defaultDescription = 'Show webman version';

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $installed_file = base_path() . '/vendor/composer/installed.php';
        if (is_file($installed_file)) {
            $version_info = include $installed_file;
        }
        $webman_version = defined('WEBMAN_VERSION') ? WEBMAN_VERSION : '< 1.2.0';
        $output->writeln("Webman $webman_version");
        $webman_framework_version = $version_info['versions']['workerman/webman-framework']['pretty_version'] ?? '';
        $output->writeln("Webman-framework $webman_framework_version");
        return self::SUCCESS;
    }
}
