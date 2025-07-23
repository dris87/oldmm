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

use All4One\AppBundle\Form\Type\CollectionType;
use All4One\AppBundle\Form\Type\CustomFormType;
use All4One\AutocompleteBundle\Form\Type\AutocompleteType;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCv;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCvDictionaryRelation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CvOtherType.
 */
class CvOtherType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EmployeeCv::class,
            'csrf_protection' => false,
            'validation_groups' => ['other'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hobby', null, [
                'label' => 'label.employee.registration.hobby',
                'attr' => ['placeholder' => 'placeholder.employee.registration.hobby'],
                'required' => false,
            ])
            ->add('drivingLicenses', AutocompleteType::class, [
                'descriptor' => 'dic_driving_license.descriptor',
                'label' => 'label.employee.registration.driving_licenses',
                'placeholder' => 'placeholder.employee.registration.driving_licenses',
                'multiple' => true,
                'required' => false,
            ])
            ->add('personalStrengths', AutocompleteType::class, [
                'descriptor' => 'dic_personal_strength.descriptor',
                'label' => 'label.employee.registration.personal_strengths',
                'placeholder' => 'placeholder.employee.registration.personal_strengths',
                'multiple' => true,
                'required' => false,
            ])
            ->add('itExperiences', CollectionType::class, [
                'label' => false,
                'entry_type' => CustomFormType::class,
                'entry_options' => [
                    'data_class' => EmployeeCvDictionaryRelation::class,
                    'fields' => [
                        'dictionary' => [
                            'type' => AutocompleteType::class,
                            'options' => [
                                'descriptor' => 'dic_it_experience.descriptor',
                                'label' => 'label.employee.registration.it_experience',
                                'attr' => ['formGroupClass' => 'col-sm-6'],
                            ],
                        ],
                        'level' => [
                            'type' => AutocompleteType::class,
                            'options' => [
                                'descriptor' => 'dic_it_experience_level.descriptor',
                                'label' => 'label.employee.registration.it_experience_level',
                                'attr' => ['formGroupClass' => 'col-sm-6'],
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
            ->add('softwareExperiences', CollectionType::class, [
                'label' => false,
                'entry_type' => CustomFormType::class,
                'entry_options' => [
                    'data_class' => EmployeeCvDictionaryRelation::class,
                    'fields' => [
                        'dictionary' => [
                            'type' => AutocompleteType::class,
                            'options' => [
                                'descriptor' => 'dic_software_experience.descriptor',
                                'label' => 'label.employee.registration.software_experience',
                                'attr' => ['formGroupClass' => 'col-sm-6'],
                            ],
                        ],
                        'level' => [
                            'type' => AutocompleteType::class,
                            'options' => [
                                'descriptor' => 'dic_software_experience_level.descriptor',
                                'label' => 'label.employee.registration.software_experience_level',
                                'attr' => ['formGroupClass' => 'col-sm-6'],
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
            ->add('languages', CollectionType::class, [
                'label' => false,
                'entry_type' => CustomFormType::class,
                'entry_options' => [
                    'data_class' => EmployeeCvDictionaryRelation::class,
                    'fields' => [
                        'dictionary' => [
                            'type' => AutocompleteType::class,
                            'options' => [
                                'descriptor' => 'dic_language.descriptor',
                                'label' => 'label.employee.registration.language_level',
                                'attr' => ['formGroupClass' => 'col-sm-6'],
                            ],
                        ],
                        'level' => [
                            'type' => AutocompleteType::class,
                            'options' => [
                                'descriptor' => 'dic_language_level.descriptor',
                                'label' => 'label.employee.registration.language_level',
                                'attr' => ['formGroupClass' => 'col-sm-6'],
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
        ;
    }
}
