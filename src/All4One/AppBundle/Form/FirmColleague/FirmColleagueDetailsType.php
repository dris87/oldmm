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

namespace All4One\AppBundle\Form\FirmColleague;

use All4One\AutocompleteBundle\Form\Type\AutocompleteType;
use Common\CoreBundle\Entity\Firm\FirmColleague;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FirmColleagueDetailsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', null, [
                'attr' => ['autofocus' => true, 'placeholder' => 'placeholder.first_name'],
                'label' => 'label.name',
            ])
            ->add('lastName', null, [
                'label' => 'label.empty',
                'attr' => ['placeholder' => 'placeholder.last_name'],
            ])
            ->add('phoneNumber', PhoneNumberType::class,
                [
                    'label' => 'label.phone_number',
                    'default_region' => 'HU',
                    'format' => PhoneNumberFormat::NATIONAL,
                    'attr' => ['placeholder' => 'placeholder.phone_number'],
                ]
            )
            ->add('position', AutocompleteType::class, [
                'label' => 'page.firm_colleague.registration.position',
                'descriptor' => 'dic_position.descriptor',
                'minimum_input_length' => 0,
                'required' => true,
                'placeholder' => 'placeholder.colleague_position',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FirmColleague::class,
            'csrf_protection' => false,
        ]);
    }
}
