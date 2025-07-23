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

namespace BackOffice\AppBundle\Admin\User;

use BackOffice\AppBundle\Admin\AbstractAdmin;
use Biplane\EnumBundle\Form\Type\EnumType;
use Common\CoreBundle\Enumeration\User\DeletedUserReasonStatusEnum;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Class DeletedUserReasonAdmin.
 */
class DeletedUserReasonAdmin extends AbstractAdmin
{
    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('description', TextareaType::class, [
                'label' => 'Leírás',
                'attr' => ['rows' => 5],
            ])
            ->add('status', EnumType::class, [
                'label' => 'Státusz',
                'enum_class' => DeletedUserReasonStatusEnum::class,
                'attr' => ['class' => 'enable-select2'],
            ])
            ->add('position', null, [
                'label' => 'Pozíció',
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
                'description', null, [
                'label' => 'Leírás',
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
            ->addIdentifier('description', 'text', [
                'label' => 'Leírás',
                'sortable' => true,
                'template' => '@SonataAdmin/CRUD/Common/raw_html_list.html.twig',
                'sort_field_mapping' => ['fieldName' => 'description'],
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
            ->add('position', 'trans', [
                'label' => 'Pozíció',
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
