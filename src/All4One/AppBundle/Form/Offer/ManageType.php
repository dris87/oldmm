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

use All4One\AppBundle\Form\Type\CollectionType;
use All4One\AppBundle\Form\Type\CustomFormType;
use All4One\AppBundle\Form\Type\DateTimeRangeType;
use All4One\AutocompleteBundle\Form\Type\AutocompleteType;
use Common\CoreBundle\Entity\Dictionary\DicAdvantage;
use Common\CoreBundle\Entity\Dictionary\DicDetail;
use Common\CoreBundle\Entity\Dictionary\DicExpectation;
use Common\CoreBundle\Entity\Dictionary\DicTask;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Entity\Offer\OfferDictionaryRelation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form used to create and manipulate offers.
 */
class ManageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Offer::class,
            'error_mapping' => [
                'applicableDateRange' => 'applicableDateRange.from',
                'applicableFromDate' => 'applicableDateRange.from',
                'expireDate' => 'applicableDateRange.until',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'attr' => [
                    'autofocus' => true,
                    'placeholder' => 'label.offer.manage.title.placeholder',
                ],
                'label' => 'label.offer.manage.title',
            ])
            ->add('categories', AutocompleteType::class, [
                'descriptor' => 'dic_child_category.descriptor',
                'label' => 'label.offer.manage.category',
                'multiple' => true,
                'placeholder' => 'label.offer.manage.category.placeholder',
            ])
            ->add('locations', AutocompleteType::class, [
                'descriptor' => 'dic_city_county.descriptor',
                'label' => 'label.offer.manage.locations',
                'multiple' => true,
                'placeholder' => 'label.offer.manage.locations.placeholder',
            ])
            ->add('numberOfEmployee', IntegerType::class, [
                'label' => 'label.offer.manage.number_of_employee',
                'attr' => ['placeholder' => 'label.offer.manage.number_of_employee.placeholder'],
            ])
            ->add('anonim', CheckboxType::class, [
                'required' => true,
                'label' => 'label.offer.manage.anonim',
            ])
            ->add('lead', TextareaType::class, [
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'label.offer.manage.lead.placeholder',
                ],
                'label' => 'label.offer.manage.lead',
            ])
            ->add('tasks', CollectionType::class, [
                'label' => false,
                'entry_type' => CustomFormType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => DicTask::class,
                    'fields' => [
                        'value' => [
                            'type' => TextType::class,
                            'options' => [
                                'label' => 'label.offer.manage.task',
                                'attr' => ['placeholder' => 'label.offer.manage.task.placeholder'],
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => true,
            ])
            ->add('details', CollectionType::class, [
                'label' => false,
                'entry_type' => CustomFormType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => DicDetail::class,
                    'fields' => [
                        'value' => [
                            'type' => TextType::class,
                            'options' => [
                                'label' => 'label.offer.manage.detail',
                                'attr' => ['placeholder' => 'label.offer.manage.detail.placeholder'],
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
            ->add('advantages', CollectionType::class, [
                'label' => false,
                'entry_type' => CustomFormType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => DicAdvantage::class,
                    'fields' => [
                        'value' => [
                            'type' => TextType::class,
                            'options' => [
                                'label' => 'label.offer.manage.advantage',
                                'attr' => ['placeholder' => 'label.offer.manage.advantage.placeholder'],
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
            ->add('shifts', AutocompleteType::class, [
                'descriptor' => 'dic_shift.descriptor',
                'label' => 'label.offer.manage.shifts',
                'placeholder' => 'label.offer.manage.shifts.placeholder',
                'multiple' => true,
                'required' => true,
            ])
            ->add('jobForms', AutocompleteType::class, [
                'descriptor' => 'dic_job_form.descriptor',
                'label' => 'label.offer.manage.job_forms',
                'placeholder' => 'label.offer.manage.job_forms.placeholder',
                'multiple' => true,
                'required' => true,
            ])
            ->add('drivingLicenses', AutocompleteType::class, [
                'descriptor' => 'dic_driving_license.descriptor',
                'label' => 'label.offer.manage.driving_licenses',
                'placeholder' => 'label.offer.manage.driving_licenses.placeholder',
                'multiple' => true,
                'required' => false,
            ])
            ->add('personalStrengths', AutocompleteType::class, [
                'descriptor' => 'dic_personal_strength.descriptor',
                'label' => 'label.offer.manage.personal_strengths',
                'placeholder' => 'label.offer.manage.personal_strengths.placeholder',
                'multiple' => true,
                'required' => false,
            ])
            ->add('minEducation', AutocompleteType::class, [
                'descriptor' => 'dic_education_level.descriptor',
                'label' => 'label.offer.manage.min_education',
                'placeholder' => 'label.offer.manage.min_education.placeholder',
                'multiple' => false,
                'required' => true,
            ])
            ->add('educations', CollectionType::class, [
                'label' => 'label.offer.manage.educations',
                'entry_type' => CustomFormType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => OfferDictionaryRelation::class,
                    'fields' => [
                        'dictionary' => [
                            'type' => AutocompleteType::class,
                            'options' => [
                                'descriptor' => 'dic_category.descriptor',
                                'label' => 'label.offer.manage.education',
                                'attr' => ['formGroupClass' => 'col-sm-6'],
                            ],
                        ],
                        'level' => [
                            'type' => AutocompleteType::class,
                            'options' => [
                                'descriptor' => 'dic_education_level.descriptor',
                                'label' => 'label.offer.manage.education_level',
                                'attr' => ['formGroupClass' => 'col-sm-6'],
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
            ->add('experiences', CollectionType::class, [
                'label' => false,
                'entry_type' => CustomFormType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => OfferDictionaryRelation::class,
                    'fields' => [
                        'dictionary' => [
                            'type' => AutocompleteType::class,
                            'options' => [
                                'descriptor' => 'dic_experience.descriptor',
                                'label' => 'label.offer.manage.experience',
                                'attr' => ['formGroupClass' => 'col-sm-6'],
                            ],
                        ],
                        'level' => [
                            'type' => AutocompleteType::class,
                            'options' => [
                                'descriptor' => 'dic_experience_level.descriptor',
                                'label' => 'label.offer.manage.experience_level',
                                'attr' => ['formGroupClass' => 'col-sm-6'],
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
            ->add('itExperiences', CollectionType::class, [
                'label' => false,
                'entry_type' => CustomFormType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => OfferDictionaryRelation::class,
                    'fields' => [
                        'dictionary' => [
                            'type' => AutocompleteType::class,
                            'options' => [
                                'descriptor' => 'dic_it_experience.descriptor',
                                'label' => 'label.offer.manage.it_experience',
                                'attr' => ['formGroupClass' => 'col-sm-6'],
                            ],
                        ],
                        'level' => [
                            'type' => AutocompleteType::class,
                            'options' => [
                                'descriptor' => 'dic_it_experience_level.descriptor',
                                'label' => 'label.offer.manage.it_experience_level',
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
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => OfferDictionaryRelation::class,
                    'fields' => [
                        'dictionary' => [
                            'type' => AutocompleteType::class,
                            'options' => [
                                'descriptor' => 'dic_software_experience.descriptor',
                                'label' => 'label.offer.manage.software_experience',
                                'attr' => ['formGroupClass' => 'col-sm-6'],
                            ],
                        ],
                        'level' => [
                            'type' => AutocompleteType::class,
                            'options' => [
                                'descriptor' => 'dic_software_experience_level.descriptor',
                                'label' => 'label.offer.manage.software_experience_level',
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
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => OfferDictionaryRelation::class,
                    'fields' => [
                        'dictionary' => [
                            'type' => AutocompleteType::class,
                            'options' => [
                                'descriptor' => 'dic_language.descriptor',
                                'label' => 'label.offer.manage.language_level',
                                'attr' => ['formGroupClass' => 'col-sm-6'],
                            ],
                        ],
                        'level' => [
                            'type' => AutocompleteType::class,
                            'options' => [
                                'descriptor' => 'dic_language_level.descriptor',
                                'label' => 'label.offer.manage.language_level',
                                'attr' => ['formGroupClass' => 'col-sm-6'],
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])->add('expectations', CollectionType::class, [
                'label' => 'label.offer.manage.expectations',
                'entry_type' => CustomFormType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => DicExpectation::class,
                    'fields' => [
                        'value' => [
                            'type' => TextType::class,
                            'options' => [
                                'label' => 'label.offer.manage.expectation',
                                'attr' => ['placeholder' => 'label.offer.manage.expectation.placeholder'],
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
            ->add('applicableDateRange', DateTimeRangeType::class, [
                'label' => false,
                'error_bubbling' => false,
                'field_options' => [],
                'from_options' => [
                    'label' => 'label.offer.manage.from',
                    'format' => 'yyyy-MM-dd',
                    'required' => true,
                    'viewMode' => 'days',
                ],
                'until_options' => [
                    'label' => 'label.offer.manage.until',
                    'format' => 'yyyy-MM-dd',
                    'required' => true,
                    'viewMode' => 'days',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'label.offer.manage.save',
                'validation_groups' => 'save',
                'attr' => ['class' => 'btn btn-success btn-lg  btn-block'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'label.offer.manage.submit',
                'validation_groups' => ['submit'],
                'attr' => ['class' => 'btn btn-primary btn-lg  btn-block'],
            ])
        ;
    }
}
