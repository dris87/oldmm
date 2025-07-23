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

namespace All4One\AppBundle\Form\Employee;

use All4One\AutocompleteBundle\Form\Type\AutocompleteType;
use Biplane\EnumBundle\Form\Type\EnumType;
use Common\CoreBundle\Entity\Employee\Employee;
use Common\CoreBundle\Enumeration\User\UserGenderEnum;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Presta\ImageBundle\Form\Type\ImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

/**
 * Defines the form used to create and manipulate employees.
 */
class EmployeeType extends AbstractType
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
                'label' => false,
                'attr' => ['placeholder' => 'placeholder.last_name'],
            ])
            ->add('gender', EnumType::class, [
                    'label' => 'label.gender',
                    'enum_class' => UserGenderEnum::class,
                    'attr' => ['class' => 'enable-select2'],
                ]
            )
            ->add('birthDate', BirthdayType::class, [
                'years' => range(date('Y') - 12, date('Y') - 100),
                'label' => 'label.birth_date',
                'attr' => [
                    'class' => 'enable-select2',
                ],
                'format' => 'yyyyMMMMdd',
                'required' => true,
                'input' => 'datetime',
                'placeholder' => [
                    'year' => 'placeholder.birth_year',
                    'month' => 'placeholder.birth_month',
                    'day' => 'placeholder.birth_day',
                ],
            ])
            ->add('phoneNumber', PhoneNumberType::class,
                [
                    'label' => 'label.phone_number',
                    'default_region' => 'HU',
                    'format' => PhoneNumberFormat::NATIONAL,
                    'attr' => ['placeholder' => 'placeholder.phone_number'],
                ]
            )
            ->add('nationality', AutocompleteType::class, [
                'label' => 'label.nationality',
                'descriptor' => 'dic_nationality.descriptor',
                'minimum_input_length' => 0,
                'multiple' => true,
                'required' => true,
                'placeholder' => 'placeholder.nationality',
            ])
            ->add('pictureFile', ImageType::class, [
                'label' => 'Fénykép',
                'cropper_options' => [
                    'aspectRatio' => '1',
                    'viewMode' => '1',
                ],
                'upload_mimetype' => 'image/jpg',
                'upload_button_icon' => 'fa fa-cloud-upload',
                'enable_remote' => false,
                'aspect_ratios' => [['value' => '1', 'checked' => true]],
                'max_width' => '400',
                'max_height' => '400',
                'preview_width' => '115',
                'preview_height' => '115',
            ])
            ->add('location', AutocompleteType::class, [
                'label' => 'label.location',
                'descriptor' => 'dic_location.descriptor',
                'minimum_input_length' => 0,
                'required' => true,
                'placeholder' => 'placeholder.location',
            ])
            ->add('email', EmailType::class, [
                'label' => 'label.email',
                'attr' => ['placeholder' => 'placeholder.employee.email'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'label.password',
                    'attr' => ['placeholder' => 'placeholder.password'],
                ],
                'second_options' => [
                    'label' => 'label.empty',
                    'attr' => ['placeholder' => 'placeholder.password_repeat'],
                ],
            ])->add('aszfAccepted', CheckboxType::class, [
                'mapped' => false,
                'label' => 'label.aszf_accept',
                'constraints' => new IsTrue(),
            ])->add('anyAccepted', CheckboxType::class, [
                'mapped' => false,
                'label' => 'label.any_accept',
                'constraints' => new IsTrue(),
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Employee::class,
            'csrf_protection' => false,
            'validation_groups' => ['Default', 'registration'],
        ]);
    }
}
