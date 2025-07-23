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

namespace All4One\AppBundle\Form;

use All4One\AutocompleteBundle\Form\Type\AutocompleteType;
use Common\CoreBundle\Entity\Firm\Firm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form used to create and manipulate firms.
 */
class FirmType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('taxNumber', null, [
                'attr' => ['autofocus' => true, 'placeholder' => 'placeholder.tax_number'],
                'label' => 'label.tax_number',
            ])
            ->add('name', null, [
                'label' => 'page.firm.registration.label.firm_name',
                'attr' => ['placeholder' => 'placeholder.firm.name'],
            ])
            ->add('nameLong', null, [
                'label' => 'page.firm.registration.label.firm_name_long',
                'attr' => ['placeholder' => 'placeholder.firm.name_long'],
            ])->add('location', AutocompleteType::class, [
                'label' => 'page.firm.registration.label.location',
                'descriptor' => 'dic_location.descriptor',
                'minimum_input_length' => 0,
                'required' => true,
                'placeholder' => 'placeholder.firm.location',
            ])
            ->add('representative', null, [
                'label' => 'page.firm.registration.label.representative',
                'attr' => ['placeholder' => 'placeholder.firm.representative'],
            ])
            ->add('street', null, [
                'label' => 'label.street',
                'attr' => ['placeholder' => 'placeholder.street'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Firm::class,
            'csrf_protection' => false,
        ]);
    }
}
