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

namespace Spirit\SpiritModelBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Spirit\SpiritModelBundle\Manager\SpiritModelManager;
use Spirit\SpiritModelBundle\Model\SpiritContainerModelInterface;

/**
 * Description of SpiritContainerModelSubscriber.
 *
 * @author sipee
 */
class SpiritContainerModelSubscriber implements EventSubscriber
{
    /**
     * @var SpiritModelManager
     */
    private $spirit_manager;

    /**
     * @param SpiritModelManager $spirit_manager
     */
    public function __construct(SpiritModelManager $spirit_manager)
    {
        $this->spirit_manager = $spirit_manager;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'preUpdate',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->processEntity($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->processEntity($args->getEntity());
    }

    /**
     * @param object $entity
     */
    private function processEntity($entity)
    {
        if ($entity instanceof SpiritContainerModelInterface) {
            $this->spirit_manager->addSpiritRelations($entity);
        }
    }
}
