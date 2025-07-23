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

namespace All4One\AppBundle\Manager;

/**
 * This manager is responsible for logging
 * actions in managers. Mostly Errors and warnings
 * will be logged.
 *
 * Class AbstractManager
 */
abstract class AbstractManager
{
    /**
     * Logs some warning
     * Ex.: No translation found, or no file found to render etc...
     *
     * @param $message
     */
    protected function logWarning($message)
    {
    }
}
