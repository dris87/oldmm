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
use Common\CoreBundle\Presentation\AdvancedOfferFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form used to create and manipulate firm colleagues.
 */
class AdvancedSearchType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('categories', AutocompleteType::class, [
                'label' => 'label.offer.search.category',
                'descriptor' => 'dic_category.descriptor',
                'required' => false,
                'multiple' => true,
                'minimum_input_length' => 0,
                'placeholder' => 'placeholder.offer.search.category',
            ])
            ->add('shifts', AutocompleteType::class, [
                'label' => 'label.offer.search.shifts',
                'descriptor' => 'dic_shift.descriptor',
                'minimum_input_length' => 0,
                'required' => false,
                'multiple' => true,
                'placeholder' => 'placeholder.offer.search.shift',
            ])
            ->add('job_forms', AutocompleteType::class, [
                'label' => 'label.offer.search.job_form',
                'descriptor' => 'dic_job_form.descriptor',
                'minimum_input_length' => 0,
                'required' => false,
                'multiple' => true,
                'placeholder' => 'placeholder.offer.search.job_form',
            ])
            ->add('languages', AutocompleteType::class, [
                'label' => 'label.offer.search.language',
                'descriptor' => 'dic_language.descriptor',
                'minimum_input_length' => 0,
                'required' => false,
                'multiple' => true,
                'placeholder' => 'placeholder.offer.search.language',
            ])
            ->add('driving_licenses', AutocompleteType::class, [
                'label' => 'label.offer.search.driving_license',
                'descriptor' => 'dic_driving_license.descriptor',
                'minimum_input_length' => 0,
                'required' => false,
                'multiple' => true,
                'placeholder' => 'placeholder.offer.search.driving_license',
            ])->add('submit', SubmitType::class, [
                'label' => 'label.offer.search.submit',
                'attr' => ['class' => 'btn btn-primary btn-lg  btn-block'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AdvancedOfferFilter::class,
            'csrf_protection' => false,
        ]);
    }

    public function getParent()
    {
        return SimpleSearchType::class;
    }
}
