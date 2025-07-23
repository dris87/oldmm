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

namespace BackOffice\AppBundle\Admin\Migration;

use BackOffice\AppBundle\Admin\AbstractAdmin;
use Biplane\EnumBundle\Form\Type\EnumType;
use Common\CoreBundle\Enumeration\Migration\MigrationAlgorithmEnum;
use Common\CoreBundle\Enumeration\Migration\MigrationFrequencyEnum;
use Common\CoreBundle\Enumeration\Migration\MigrationStatusEnum;
use Common\CoreBundle\Enumeration\Migration\MigrationSyncTypeEnum;
use Common\CoreBundle\Enumeration\Migration\MigrationTypeEnum;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * Class MigrationAdmin.
 */
class OfferMigrationAdmin extends AbstractAdmin
{
    /**
     * @var string
     */
    protected $baseRouteName = 'admin.migration.offer';

    /**
     * @param ShowMapper $showMapper
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Személyes adatok', ['class' => 'col-md-12'])
            ->add('name')
            ->add('url')
            ->add('status')
            ->add('type')
            ->add('syncType')
            ->add('algorithm')
            ->add('firm')
            ->add('lastExecuted')
            ->end()
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Források', ['class' => 'col-md-4'])
            ->add('name', 'text', [
                'label' => 'Név',
            ])
            ->add('url', 'text', [
                'label' => 'URL',
            ])
            ->add('status', EnumType::class, [
                'label' => 'label.status',
                'enum_class' => MigrationStatusEnum::class,
                'attr' => ['class' => 'enable-select2'],
            ])
            ->add('type', EnumType::class, [
                'label' => 'Forrás tipusa',
                'enum_class' => MigrationTypeEnum::class,
                'attr' => ['class' => 'enable-select2'],
            ])
            ->end()
            ->with('Futtatás', ['class' => 'col-md-4'])
            ->add('syncType', EnumType::class, [
                'label' => 'Futás tipusa',
                'enum_class' => MigrationSyncTypeEnum::class,
                'attr' => ['class' => 'enable-select2'],
            ])
            ->add('algorithm', EnumType::class, [
                'label' => 'Algoritmus tipus',
                'enum_class' => MigrationAlgorithmEnum::class,
                'attr' => ['class' => 'enable-select2'],
            ])
            ->add('frequency', EnumType::class, [
                'label' => 'Gyakoriság',
                'enum_class' => MigrationFrequencyEnum::class,
                'attr' => ['class' => 'enable-select2'],
            ])
            ->end()
            ->with('Kapcsolódó', ['class' => 'col-md-4'])
            ->add('firm', ModelAutocompleteType::class, [
                'property' => 'name',
                'minimum_input_length' => 0,
                'label' => 'Cég',
            ])
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
            ->add('name', 'doctrine_orm_string', [
                'label' => 'Név',
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
            ->addIdentifier('name', 'text', [
                'label' => 'Név',
                'sortable' => true,
                'route' => ['name' => 'show'],
                'sort_field_mapping' => ['fieldName' => 'name'],
                'sort_parent_association_mappings' => [],
            ])
            ->add('status', 'trans', [
                'label' => 'Státusz',
                'template' => '@SonataAdmin/CRUD/Common/Enumeration/status_enum_list.html.twig',
                'sortable' => true,
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('type', 'trans', [
                'label' => 'Forrás tipus',
                'template' => '@SonataAdmin/CRUD/Common/Enumeration/status_enum_list.html.twig',
                'sortable' => true,
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('syncType', 'trans', [
                'label' => 'Futás tipus',
                'template' => '@SonataAdmin/CRUD/Common/Enumeration/status_enum_list.html.twig',
                'sortable' => true,
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('algorithm', 'trans', [
                'label' => 'Algoritmus',
                'sortable' => true,
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('frequency', 'trans', [
                'label' => 'Gyakoriság',
                'sortable' => true,
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('lastExecuted', null, [
                'label' => 'Utolsó futás ideje',
                'format' => 'Y-m-d H:i',
                'sortable' => true,
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
