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

namespace All4One\AppBundle\Form\Type;

use All4One\AppBundle\Form\EventSubscriber\RequiredCollectionSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType as BaseCollectionType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class CollectionType.
 */
class CollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['required']) {
            $requiredSubscriber = new RequiredCollectionSubscriber(
                $options['entry_type'],
                $options['entry_options']
            );

            $builder->addEventSubscriber($requiredSubscriber);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BaseCollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'required_collection';
    }
}
