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

namespace BackOffice\AppBundle\Admin\Employee;

use All4One\AppBundle\Form\Type\CustomFormType;
use BackOffice\AppBundle\Admin\AbstractAdmin;
use Biplane\EnumBundle\Form\Type\EnumType;
use Common\CoreBundle\Entity\Dictionary\DicCategory;
use Common\CoreBundle\Entity\Dictionary\DicCompanyHelp;
use Common\CoreBundle\Entity\Dictionary\DicDrivingLicense;
use Common\CoreBundle\Entity\Dictionary\DicEducationLevel;
use Common\CoreBundle\Entity\Dictionary\DicExperience;
use Common\CoreBundle\Entity\Dictionary\DicItExperience;
use Common\CoreBundle\Entity\Dictionary\DicItExperienceLevel;
use Common\CoreBundle\Entity\Dictionary\DicJobForm;
use Common\CoreBundle\Entity\Dictionary\DicLanguage;
use Common\CoreBundle\Entity\Dictionary\DicLanguageLevel;
use Common\CoreBundle\Entity\Dictionary\DicLifeStyle;
use Common\CoreBundle\Entity\Dictionary\DicLocation;
use Common\CoreBundle\Entity\Dictionary\DicMarketStatus;
use Common\CoreBundle\Entity\Dictionary\DicPersonalStrength;
use Common\CoreBundle\Entity\Dictionary\DicSchool;
use Common\CoreBundle\Entity\Dictionary\DicShift;
use Common\CoreBundle\Entity\Dictionary\DicSoftwareExperience;
use Common\CoreBundle\Entity\Dictionary\DicSoftwareExperienceLevel;
use Common\CoreBundle\Entity\Dictionary\DicSupport;
use Common\CoreBundle\Entity\Dictionary\Dictionary;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCvDictionaryRelation;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCvEducation;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCvExperience;
use Common\CoreBundle\Enumeration\Employee\Cv\EmployeeCvWillToMoveEnum;
use Common\CoreBundle\Enumeration\Employee\Cv\EmployeeCvWillToTravelEnum;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Form\Type\DatePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class EmployeeCvAdmin.
 */
class EmployeeCvAdmin extends AbstractAdmin
{
    /**
     * @var string
     */
    protected $parentAssociationMapping = 'employee';

    /**
     * EmployeeCvAdmin constructor.
     *
     * @param $code
     * @param $class
     * @param $baseControllerName
     */
    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('Álláskeresési adatok')
            ->with('Adatok')
                ->add('searchCategories', ModelAutocompleteType::class, [
                    'label' => 'page.employee.cv.work_details.search_categories.label',
                    'minimum_input_length' => 0,
                    'required' => true,
                    'multiple' => true,
                    'placeholder' => 'page.employee.cv.work_details.search_categories.placeholder',
                    'property' => 'value',
                    'class' => DicCategory::class,
                ], [
                    'admin_code' => 'admin.dic_subcategory',
                ])
                ->add('shifts', EntityType::class, [
                    'class' => DicShift::class,
                    'choice_label' => 'value',
                    'label' => 'page.employee.cv.work_details.shifts.label',
                    'required' => true,
                    'multiple' => true,
                    'placeholder' => 'page.employee.cv.work_details.shifts.placeholder',
                ])
                ->add('jobForms', EntityType::class, [
                    'class' => DicJobForm::class,
                    'choice_label' => 'value',
                    'label' => 'page.employee.cv_work_details.job_form.label',
                    'multiple' => true,
                    'required' => true,
                ])
                ->add('marketStatuses', ModelAutocompleteType::class, [
                    'label' => 'page.employee.cv_work_details.market_status.label',
                    'minimum_input_length' => 0,
                    'required' => true,
                    'multiple' => true,
                    'placeholder' => 'page.employee.cv.work_details.market_status.placeholder',
                    'property' => 'value',
                    'class' => DicMarketStatus::class,
                ], [
                    'admin_code' => 'admin.dic_market_status',
                ])
                ->add('jobComment', null, [
                    'label' => 'label.job_comment',
                    'attr' => ['placeholder' => 'placeholder.job_comment'],
                ])
            ->end()
            ->with('Utazási hajlandóság')
                ->add('willToTravel', EnumType::class,
                    [
                        'label' => false,
                        'enum_class' => EmployeeCvWillToTravelEnum::class,
                        'multiple' => false,
                        'expanded' => true,
                    ]
                )
                ->add('willToTravelDistance', null, [
                    'label' => 'Kilométer',
                    'attr' => ['placeholder' => 'placeholder.travel_to_distance'],
                ])
                ->add('willToTravelLocations', ModelAutocompleteType::class, [
                    'label' => 'Település',
                    'required' => true,
                    'multiple' => true,
                    'minimum_input_length' => 0,
                    'placeholder' => 'placeholder.travel_to_location',
                    'property' => 'value',
                    'class' => Dictionary::class,
                ], [
                    'admin_code' => 'admin.dic_location',
                ])
            ->end()
            ->with('Költözési hajlandóság')
                ->add('willToMove', EnumType::class,
                    [
                        'label' => false,
                        'enum_class' => EmployeeCvWillToMoveEnum::class,
                        'multiple' => false,
                        'expanded' => true,
                        'required' => false,
                    ]
                )
                ->add('willToMoveLocations', ModelAutocompleteType::class, [
                    'label' => 'Település',
                    'required' => false,
                    'multiple' => true,
                    'minimum_input_length' => 0,
                    'placeholder' => 'placeholder.travel_to_location',
                    'property' => 'value',
                    'class' => Dictionary::class,
                ], [
                    'admin_code' => 'admin.dic_location',
                ])
                ->end()
            ->end()
            ->tab('Extra igények')
            ->with('Igények')
                ->add('salaryFrom', MoneyType::class, [
                    'label' => 'label.salary_from',
                    'currency' => 'huf',
                    'attr' => ['placeholder' => 'placeholder.salary_from'],
                ])
                ->add('salaryTo', MoneyType::class, [
                    'label' => 'label.salary_to',
                    'currency' => 'huf',
                    'attr' => ['placeholder' => 'placeholder.salary_to'],
                ])
                ->add('cafeteria', CheckboxType::class, [
                    'label' => 'label.cafeteria',
                    'required' => false,
                ])
                ->add('lifeStyles', ModelAutocompleteType::class, [
                    'label' => 'Életvitel',
                    'minimum_input_length' => 0,
                    'required' => true,
                    'multiple' => true,
                    'placeholder' => 'placeholder.life_style',
                    'property' => 'value',
                    'class' => DicLifeStyle::class,
                ], [
                    'admin_code' => 'admin.dic_life_style',
                ])
                ->add('companyHelps', ModelAutocompleteType::class, [
                    'label' => 'label.company_help',
                    'minimum_input_length' => 0,
                    'required' => true,
                    'multiple' => true,
                    'placeholder' => 'placeholder.company_help',
                    'property' => 'value',
                    'class' => DicCompanyHelp::class,
                ], [
                    'admin_code' => 'admin.dic_company_help',
                ])

                ->add('supports', ModelAutocompleteType::class, [
                    'label' => 'label.support',
                    'minimum_input_length' => 0,
                    'required' => true,
                    'multiple' => true,
                    'placeholder' => 'placeholder.support',
                    'property' => 'value',
                    'class' => DicSupport::class,
                ], [
                    'admin_code' => 'admin.dic_support',
                ])
                ->add('extraComment', null, [
                    'label' => 'label.extra_comment',
                    'attr' => ['placeholder' => 'placeholder.extra_comment'],
                ])
            ->end()
            ->end()

            ->tab('Végzettség')
            ->with('Végzettség')
                ->add('educations', CollectionType::class, [
                    'label' => 'label.offer.manage.educations',
                    'entry_type' => CustomFormType::class,
                    'error_bubbling' => false,
                    'entry_options' => [
                        'data_class' => EmployeeCvEducation::class,
                        'fields' => [
                            'category' => [
                                'type' => EntityType::class,
                                'options' => [
                                    'class' => DicCategory::class,
                                    'choice_label' => 'value',
                                    'label' => 'Végzettség megnevezése',
                                    'placeholder' => 'Kérem válasszon!',
                                    'multiple' => false,
                                    'expanded' => false,
                                ],
                            ],
                            'educationLevel' => [
                                'type' => EntityType::class,
                                'options' => [
                                    'class' => DicEducationLevel::class,
                                    'choice_label' => 'value',
                                    'label' => 'Végzettség szintje',
                                    'placeholder' => 'Kérem válasszon!',
                                    'multiple' => false,
                                    'expanded' => false,
                                ],
                            ],
    //                        'location' => [
    //                            'type' => ModelAutocompleteType::class,
    //                            'options' => [
    //                                'class' => DicLocation::class,
    ////                                'class' => $dicAdmin->getClass(),
    //                                'property' => 'text',
    //                                'label' => 'Város',
    //                                'placeholder' => 'Kérem válasszon!',
    //                                'multiple' => false,
    //                                'required' => false,
    //                                'model_manager' => $this->modelManager,
    //                            ], [
    //                                'admin_code' => 'admin.dic_full_location',
    //                            ]
    //                        ],

                            'location' => [
                                'type' => EntityType::class,
                                'options' => [
                                    'class' => DicLocation::class,
                                    'label' => 'Város',
                                    'placeholder' => 'Kérem válasszon!',
                                    'multiple' => false,
                                    'required' => false,
                                ],
                            ],
                            'school' => [
                                'type' => EntityType::class,
                                'options' => [
                                    'class' => DicSchool::class,
                                    'choice_label' => 'value',
                                    'label' => 'Intézmény neve',
                                    'placeholder' => 'Kérem válasszon!',
                                    'multiple' => false,
                                    'required' => false,
                                ],
                            ],
                            'fromDate' => [
                                'type' => DatePickerType::class,
                                'options' => [
                                    'label' => 'Tanulmány kezdete',
                                    'format' => 'yyyy-MM',
                                    'required' => false,
                                    'dp_view_mode' => 'years',
                                ],
                            ],
                            'toDate' => [
                                'type' => DatePickerType::class,
                                'options' => [
                                    'label' => 'Tanulmány vége',
                                    'format' => 'yyyy-MM',
                                    'required' => false,
                                    'dp_view_mode' => 'years',
                                ],
                            ],
                            'inProgress' => [
                                'type' => CheckboxType::class,
                                'options' => [
                                    'label' => 'Jelenleg is tart',
                                    'required' => false,
                                ],
                            ],
                            'comment' => [
                                'type' => TextareaType::class,
                                'options' => [
                                    'label' => 'Megjegyzés',
                                    'required' => false,
                                ],
                            ],
                        ],
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'required' => false,
                ])
            ->end()
            ->end()

            ->tab('Tapasztalatok')
            ->with('Tapasztalatok')
                ->add('experiences', CollectionType::class, [
                    'label' => 'Tapasztalatok',
                    'entry_type' => CustomFormType::class,
                    'entry_options' => [
                        'data_class' => EmployeeCvExperience::class,
                        'fields' => [
                            'companyName' => [
                                'type' => TextType::class,
                                'options' => [
                                    'label' => 'Cég neve',
                                    'required' => false,
                                ],
                            ],
                            'experience' => [
                                'type' => EntityType::class,
                                'options' => [
                                    'class' => DicExperience::class,
                                    'choice_label' => 'value',
                                    'label' => 'Kategória',
                                    'placeholder' => 'Kérem válasszon!',
                                    'multiple' => false,
                                    'required' => false,
                                ],
                            ],
                            'location' => [
                                'type' => EntityType::class,
                                'options' => [
                                    'class' => DicLocation::class,
                                    'label' => 'Város',
                                    'placeholder' => 'Kérem válasszon!',
                                    'multiple' => false,
                                    'required' => false,
                                ],
                            ],
                            'fromDate' => [
                                'type' => DatePickerType::class,
                                'options' => [
                                    'label' => 'Munkavégzés kezdete',
                                    'format' => 'yyyy-MM',
                                    'required' => false,
                                    'dp_view_mode' => 'years',
                                ],
                            ],
                            'toDate' => [
                                'type' => DatePickerType::class,
                                'options' => [
                                    'label' => 'Munkavégzés vége',
                                    'format' => 'yyyy-MM',
                                    'required' => false,
                                    'dp_view_mode' => 'years',
                                ],
                            ],
                            'inProgress' => [
                                'type' => CheckboxType::class,
                                'options' => [
                                    'label' => 'Jelenleg is tart',
                                    'required' => false,
                                ],
                            ],
                            'comment' => [
                                'type' => TextareaType::class,
                                'options' => [
                                    'label' => 'Megjegyzés',
                                    'required' => false,
                                ],
                            ],
                        ],
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'required' => true,
                ])
            ->end()
            ->end()

            ->tab('Egyéb adatok')
            ->with('Egyéb adatok')
                ->add('drivingLicenses', EntityType::class, [
                    'class' => DicDrivingLicense::class,
                    'choice_label' => 'value',
                    'label' => 'label.employee.registration.driving_licenses',
                    'expanded' => false,
                    'multiple' => true,
                    'required' => false,
                ])
                ->add('personalStrengths', EntityType::class, [
                    'class' => DicPersonalStrength::class,
                    'choice_label' => 'value',
                    'label' => 'label.employee.registration.personal_strengths',
                    'expanded' => false,
                    'multiple' => true,
                    'required' => false,
                ])
                ->add('hobby', TextType::class, [
                    'label' => 'label.employee.registration.hobby',
                    'required' => false,
                ])
                ->add('itExperiences', CollectionType::class, [
                    'label' => 'IT szakismeretek',
                    'entry_type' => CustomFormType::class,
                    'entry_options' => [
                        'data_class' => EmployeeCvDictionaryRelation::class,
                        'fields' => [
                            'dictionary' => [
                                'type' => EntityType::class,
                                'options' => [
                                    'class' => DicItExperience::class,
                                    'choice_label' => 'value',
                                    'label' => 'label.employee.registration.it_experience',
                                    'expanded' => false,
                                    'required' => false,
                                ],
                            ],
                            'level' => [
                                'type' => EntityType::class,
                                'options' => [
                                    'class' => DicItExperienceLevel::class,
                                    'choice_label' => 'value',
                                    'label' => 'label.employee.registration.it_experience_level',
                                    'required' => false,
                                ],
                            ],
                        ],
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'required' => false,
                ])
                ->add('softwareExperiences', CollectionType::class, [
                    'label' => 'Számítógépes ismeretek',
                    'entry_type' => CustomFormType::class,
                    'entry_options' => [
                        'data_class' => EmployeeCvDictionaryRelation::class,
                        'fields' => [
                            'dictionary' => [
                                'type' => EntityType::class,
                                'options' => [
                                    'class' => DicSoftwareExperience::class,
                                    'choice_label' => 'value',
                                    'label' => 'label.employee.registration.software_experience',
                                    'expanded' => false,
                                    'multiple' => false,
                                    'required' => false,
                                ],
                            ],
                            'level' => [
                                'type' => EntityType::class,
                                'options' => [
                                    'class' => DicSoftwareExperienceLevel::class,
                                    'choice_label' => 'value',
                                    'label' => 'label.employee.registration.software_experience_level',
                                    'required' => false,
                                ],
                            ],
                        ],
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'required' => false,
                ])
                ->add('languages', CollectionType::class, [
                    'label' => 'Nyelvismeretek',
                    'entry_type' => CustomFormType::class,
                    'entry_options' => [
                        'data_class' => EmployeeCvDictionaryRelation::class,
                        'fields' => [
                            'dictionary' => [
                                'type' => EntityType::class,
                                'options' => [
                                    'class' => DicLanguage::class,
                                    'choice_label' => 'value',
                                    'label' => 'label.employee.registration.language',
                                    'expanded' => false,
                                    'multiple' => false,
                                    'required' => false,
                                ],
                            ],
                            'level' => [
                                'type' => EntityType::class,
                                'options' => [
                                    'class' => DicLanguageLevel::class,
                                    'choice_label' => 'value',
                                    'label' => 'label.employee.registration.language_level',
                                    'required' => false,
                                ],
                            ],
                        ],
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'required' => false,
                ])
            ->end()
            ->end()
        ;
    }

    /**
     * @param ListMapper $list
     */
    protected function configureListFields(ListMapper $list)
    {
        parent::configureListFields($list);

        $list
            ->addIdentifier('id', null, [
                'label' => 'ID',
                'sortable' => true,
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('createdAt', null, [
                'label' => 'Létrehozás ideje',
                'format' => 'Y-m-d H:i',
                'sortable' => true,
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('updatedAt', null, [
                'label' => 'Utolsó modósítás ideje',
                'format' => 'Y-m-d H:i',
                'sortable' => true,
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('status', 'trans', [
                'label' => 'Státusz',
                'template' => '@SonataAdmin/CRUD/Common/Enumeration/status_enum_list.html.twig',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('educationsCount', null, [
                'label' => 'Végzettségek',
                'sortable' => true,
                'sort_field_mapping' => ['fieldName' => 'id'],
                'sort_parent_association_mappings' => [],
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('experienceCount', null, [
                'label' => 'Tapasztatalok',
                'header_style' => 'text-align: center',
                'sortable' => true,
                'sort_field_mapping' => ['fieldName' => 'id'],
                'sort_parent_association_mappings' => [],
                'row_align' => 'center',
            ])
            ->add('name', null, [
                'label' => 'Név',
                'sortable' => true,
                'sort_parent_association_mappings' => [],
                'row_align' => 'center',
            ])
            ->add('_action', null, [
                'label' => 'Opciók',
                'actions' => [
                    'generate_full' => [
                        'template' => '@SonataAdmin/CRUD/EmployeeList/pdf_generate.html.twig',
                    ],
                    'generate_light' => [
                        'template' => '@SonataAdmin/CRUD/EmployeeList/pdf_generate_light.html.twig',
                    ],
                    'edit' => [],
                ],
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
        ;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'id', null, [
                'label' => 'ID',
            ])
            ->add('withOwnerName', 'doctrine_orm_callback', [
                'callback' => function ($queryBuilder, $alias, $field, $value) {
                    if (!$value || '' == $value['value']) {
                        return;
                    }
                    $queryBuilder->leftJoin($alias.'.employee', 'e');
                    $queryBuilder->andWhere('( CONCAT(e.firstName, CONCAT(\' \', e.lastName)) LIKE :term OR  '.$alias.'.name LIKE :term)')->setParameter(':term', '%'.$value['value'].'%');
                    //$queryBuilder->andWhere('CONCAT('.$alias.'.lastName, CONCAT(\' \', '.$alias.'.firstName)) LIKE :term')->setParameter(':term', '%'.$value['value'].'%');

                    return true;
                },
                'field_type' => 'text',
                'label' => 'Név',
                'show_filter' => true,
            ])
            ->add('name', 'doctrine_orm_string', [
                'label' => 'Név',
            ]);
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);

        $collection->add('generate_full', $this->getRouterIdParameter().'/generate_full');
        $collection->add('generate_light', $this->getRouterIdParameter().'/generate_light');

        if ($this->isChild()) {
            return;
        }

        $collection->clear();
    }
}
