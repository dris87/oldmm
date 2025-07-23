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

namespace All4One\AppBundle\Form\Offer;

use All4One\AutocompleteBundle\Form\Type\AutocompleteType;
use Common\CoreBundle\Entity\Offer\Offer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form used to create and manipulate firm colleagues.
 */
class SimpleSearchType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('keyword', null, [
                'label' => 'label.offer.search.keyword',
                'attr' => ['placeholder' => 'placeholder.offer.search.keyword'],
            ])
            ->add('locations', AutocompleteType::class, [
                'label' => 'label.offer.search.location',
                'descriptor' => 'dic_city_county.descriptor',
                'required' => false,
                'multiple' => true,
                'minimum_input_length' => 0,
                'placeholder' => 'placeholder.offer.search.location',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Offer::class,
            'csrf_protection' => false,
        ]);
    }
}
