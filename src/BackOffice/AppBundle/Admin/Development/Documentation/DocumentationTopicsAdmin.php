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

use BackOffice\AppBundle\Admin\AbstractAdmin;
use Biplane\EnumBundle\Form\Type\EnumType;
use Common\CoreBundle\Enumeration\Development\Documentation\DocumentationTopicTypeEnum;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class BlogPostAdmin.
 */
class DocumentationTopicsAdmin extends AbstractAdmin
{
    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('title', 'text', [
                'label' => 'Cím',
            ])
            ->add('description', 'textarea', [
                'attr' => [
                    'class' => 'tinymce',
                    'data-theme' => 'lightgrey',
                ],
            ])
            ->add('icon', 'text', [
                'label' => 'Ikon(font awesome, without fa-)',
            ])
            ->add('color', ChoiceType::class, [
                'choices' => [
                    'Narancs' => 'orange',
                    'Teal' => 'primary',
                    'Lila' => 'purple',
                    'Zöld' => 'green',
                    'Kék' => 'blue',
                    'Rózsaszín' => 'pink',
                ],
            ])
            ->add('type', EnumType::class, [
                'label' => 'Tipus',
                'enum_class' => DocumentationTopicTypeEnum::class,
                'attr' => ['class' => 'enable-select2'],
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
            ->add(
                'title', null, [
                'label' => 'Cím',
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
            ->addIdentifier('color', 'text', [
                'label' => 'Szín',
                'sortable' => true,
                'sort_field_mapping' => ['fieldName' => 'color'],
            ])
            ->addIdentifier('icon', 'text', [
                'label' => 'FA Ikon',
                'sortable' => true,
                'sort_field_mapping' => ['fieldName' => 'icon'],
            ])

            ->add('type', 'trans', [
                'label' => 'Tipus',
                'template' => '@SonataAdmin/CRUD/Common/Enumeration/status_enum_list.html.twig',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
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
