<?php

/*
 * This file is part of the `All4One/Ujallas.hu` project.
 *
 * (c) https://ujallas.hu
 *
 * Developed by: Ferencz Dávid Tamás <fdt0712@gmail.com>
 * Contributed: Sipos Zoltán <sipiszoty@gmail.com>, Pintér Szilárd <leaderlala00@gmail.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Common\CoreBundle\Command\Cron;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronTaskDefaultCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('crontasks:default')->setDescription('Creates the commands by default in database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $container = $this->getContainer();
        $defaultCommands = [
            [
                'name' => 'Example asset symlinking task',
                'interval' => 2 /* Run once every 2 minutes */,
                'range' => 'minutes',
                'commands' => 'assets:install --symlink web',
                'isHide' => false, /*isHide == true this command don't show in view schedule task*/
                'enabled' => true,
            ],
            [
                'name' => 'Example asset  task',
                'interval' => 1 /* Run once every hour */,
                'range' => 'hours',
                'commands' => 'cache:clear',
                'enabled' => false,
            ],
        ];

        $container->get('frcho.crontask_default')->setArrayCommands($defaultCommands);
    }
}
