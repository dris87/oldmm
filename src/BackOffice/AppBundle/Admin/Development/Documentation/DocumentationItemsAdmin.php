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

namespace BackOffice\AppBundle\Admin\Development\Documentation;

use All4One\AppBundle\Form\Type\CustomFormType;
use BackOffice\AppBundle\Admin\AbstractAdmin;
use Biplane\EnumBundle\Form\Type\EnumType;
use Common\CoreBundle\Entity\Development\Documentation\DocumentationItemAlert;
use Common\CoreBundle\Entity\Development\Documentation\DocumentationItemCode;
use Common\CoreBundle\Enumeration\Development\Documentation\DocumentationItemAlertTypeEnum;
use Common\CoreBundle\Enumeration\Development\Documentation\DocumentationItemCodeTypeEnum;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class DocumentationItemsAdmin.
 */
class DocumentationItemsAdmin extends AbstractAdmin
{
    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('Alapadatok')
            ->with('Tartalom', ['class' => 'col-md-12'])
            ->add('title', 'text', [
                'label' => 'Cím',
            ])
            ->add('description', 'textarea', [
                'attr' => [
                    'class' => 'tinymce',
                    'data-theme' => 'advanced',
                ],
            ])
            ->add('group', null, [
                'label' => 'Csoport',
            ])
            ->end()
            ->with('Kódrészletek', ['class' => 'col-md-6'])
            ->add('codes', CollectionType::class, [
                'label' => 'Kódrészlet',
                'entry_type' => CustomFormType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => DocumentationItemCode::class,
                    'fields' => [
                        'title' => [
                            'type' => TextType::class,
                            'options' => [
                                'label' => 'Cím',
                            ],
                        ],
                        'snippet' => [
                            'type' => TextareaType::class,
                            'options' => [
                                'label' => 'Kód',
                                'attr' => ['rows' => 10],
                            ],
                        ],
                        'type' => [
                            'type' => EnumType::class,
                            'options' => [
                                'label' => 'Tipus',
                                'enum_class' => DocumentationItemCodeTypeEnum::class,
                                'attr' => ['class' => 'enable-select2'],
                            ],
                        ],
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => true,
            ])
            ->end()
            ->with('Felszólítások', ['class' => 'col-md-6'])
            ->add('alerts', CollectionType::class, [
                'label' => 'Felszólítások',
                'entry_type' => CustomFormType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'data_class' => DocumentationItemAlert::class,
                    'fields' => [
                        'title' => [
                            'type' => TextType::class,
                            'options' => [
                                'label' => 'Cím',
                            ],
                        ],
                        'description' => [
                            'type' => 'textarea',
                            'options' => [
                                'label' => 'Leírás',
                                'attr' => [
                                    'class' => 'tinymce',
                                    'data-theme' => 'lightgrey',
                                ],
                            ],
                        ],
                        'icon' => [
                            'type' => TextType::class,
                            'options' => [
                                'label' => 'Ikon',
                            ],
                        ],
                        'type' => [
                            'type' => EnumType::class,
                            'options' => [
                                'label' => 'Tipus',
                                'enum_class' => DocumentationItemAlertTypeEnum::class,
                                'attr' => ['class' => 'enable-select2'],
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
            ->add(
                'title', null, [
                'label' => 'Cím',
            ])
            ->add(
                'group', null, [
                'label' => 'Csoport',
            ]);
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, [
                'label' => 'ID',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
                'sortable' => true,
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
            ->addIdentifier('title', 'text', [
                'label' => 'Cím',
                'sortable' => true,
                'sort_field_mapping' => ['fieldName' => 'title'],
            ])
            ->addIdentifier('group', 'text', [
                'label' => 'Csoport',
                'sortable' => true,
                'sort_field_mapping' => ['fieldName' => 'group'],
            ])
            ->add('_action', null, [
                'label' => 'Opciók',
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
        ;
    }
}
