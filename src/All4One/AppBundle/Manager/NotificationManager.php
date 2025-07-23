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

use Common\CoreBundle\Enumeration\Util\NotificationTypeEnum;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class NotificationManager.
 */
class NotificationManager
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * NotificationManager constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param string                    $token
     * @param NotificationTypeEnum|null $notificationTypeEnum
     *
     * @return array
     */
    public function createArray(string $token, NotificationTypeEnum $notificationTypeEnum = null)
    {
        if (null === $notificationTypeEnum) {
            $notificationTypeEnum = NotificationTypeEnum::create(NotificationTypeEnum::SUCCESS);
        }
        $title = $this->translator->trans('notification.'.$token.'.title');
        $message = $this->translator->trans('notification.'.$token.'.message');

        return [
            'title' => $title,
            'message' => $message,
            'type' => $notificationTypeEnum->getReadable(),
        ];
    }
}
