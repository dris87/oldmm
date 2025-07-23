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

namespace Spirit\SpiritModelBundle\Manager;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Proxy;
//use Spirit\ModelManagerBundle\Manager\ModelManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Spirit\SpiritModelBundle\Model\SpiritModelInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Description of SpiritEntityManager.
 *
 * @author Sipos Zoltán <sipiszoty@gmail.com>
 */
class SpiritModelManager
{
    /**
     * @var ModelManagerInterface
     */
    protected $model_manager;

    /**
     * @var PropertyAccessorInterface
     */
    protected $property_accessor;

    /**
     * SpiritModelManager constructor.
     *
     * @param EntityManagerInterface    $model_manager
     * @param PropertyAccessorInterface $property_accessor
     */
    public function __construct(EntityManagerInterface $model_manager, PropertyAccessorInterface $property_accessor)
    {
        $this->model_manager = $model_manager;

        $this->property_accessor = $property_accessor;
    }

    /**
     * @param object $model
     *
     * @return object
     */
    public function addSpiritRelations($model)
    {
        $this->model_stack = [$model];

        $model = $this->makeRelationsToSpirit($model);

        return $model;
    }

    /**
     * @param SpiritModelInterface $model
     *
     * @return SpiritModelInterface
     */
    public function createSpiritOf(SpiritModelInterface $model)
    {
        if (null !== $model->getOriginalId()) {
            return $model;
        }

        $last_spirit = $model->getLastSpirit();

        if (
            null !== $last_spirit &&
            (null === $last_spirit->getUpdatedAt() || $last_spirit->getUpdatedAt() >= $model->getUpdatedAt())
        ) {
            return $last_spirit;
        }

        $spirit = clone $model;

        /* @var $om \Doctrine\Common\Persistence\ObjectManager */
        $om = $this->model_manager;
        $om->persist($spirit);

        $spirit->setLastSpirit(null);
        $spirit->setOriginal($model);
        $model->setLastSpirit($spirit);

        $this->makeRelationsToSpirit($spirit);

        return $spirit;
    }

    /**
     * @param object $model
     */
    protected function makeRelationsToSpirit($model)
    {
        /* @var \Doctrine\Common\Persistence\ObjectManager $om */
        $om = $this->model_manager;
        /* @var \Doctrine\Common\Persistence\Mapping\ClassMetadata $metadata */
        $metadata = $om->getClassMetadata(get_class($model));

        foreach ($metadata->getAssociationNames() as $association_name) {
            $class = $metadata->getAssociationTargetClass($association_name);
            if (!is_a($class, SpiritModelInterface::class)) {
                continue;
            }

            /* @var \Doctrine\ORM\Mapping\ClassMetadataInfo $association */
            if ($metadata->isCollectionValuedAssociation($association_name)) {
                $this->makeSpiritCollectionAssociation($model, $association_name);
            } else { // if($metadata->isSingleValuedAssociation($association_name)
                $this->makeSpiritSingleAssociation($model, $association_name);
            }
        }
    }

    /**
     * @param object $model
     * @param string $association_name
     */
    protected function makeSpiritCollectionAssociation($model, $association_name)
    {
        $property_accessor = $this->property_accessor;

        $objects = $property_accessor->getValue($model, $association_name);

        if (($objects instanceof AbstractLazyCollection) && (false === ($objects->isInitialized()))) {
            return;
        }

        $collection = new ArrayCollection();

        foreach ($objects as $key => $object) {
            if (in_array($object, $this->model_stack, true)) {
                continue;
            }

            $spirit = $this->makeEntityToSpirit($object);

            $collection->set($key, $spirit);
        }

        $property_accessor->setValue($model, $association_name, $collection);
    }

    /**
     * @param object $model
     * @param string $association_name
     */
    protected function makeSpiritSingleAssociation($model, $association_name)
    {
        $property_accessor = $this->property_accessor;

        $object = $property_accessor->getValue($model, $association_name);

        if ($object instanceof Proxy) {
            return;
        }

        $spirit = $this->makeEntityToSpirit($object);

        if ($spirit !== $object) {
            $property_accessor->setValue($model, $association_name, $spirit);
        }
    }
}
