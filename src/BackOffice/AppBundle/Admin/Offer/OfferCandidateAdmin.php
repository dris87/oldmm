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

namespace BackOffice\AppBundle\Admin\Offer;

use BackOffice\AppBundle\Admin\AbstractAdmin;
use Biplane\EnumBundle\Form\Type\EnumType;
use Common\CoreBundle\Enumeration\Offer\OfferCandidateStatusEnum;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Class OfferCandidateAdmin.
 */
class OfferCandidateAdmin extends AbstractAdmin
{
    /**
     * @var string
     */
    protected $baseRouteName = 'candidate';

    /**
     * @var string
     */
    protected $parentAssociationMapping = 'offer';

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

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('status', EnumType::class, [
                'label' => 'label.status',
                'enum_class' => OfferCandidateStatusEnum::class,
                'attr' => ['class' => 'enable-select2'],
            ])
            ->add('offer', ModelAutocompleteType::class, [
                'property' => 'title',
                'label' => 'Hirdetés',
                'minimum_input_length' => 0,
            ])
            ->add('employeeCv', ModelAutocompleteType::class, [
                'property' => 'withOwnerName',
                'label' => 'Önéletrajz',
                'minimum_input_length' => 0,
            ])
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
            ->add('offer', ModelAutocompleteType::class, [
                'property' => 'name',
                'label' => 'Hirdetés',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
                'sortable' => true,
                'template' => '@SonataAdmin/CRUD/Common/offer_link_list.html.twig',
            ])
            ->add('employeeCv', ModelAutocompleteType::class, [
                'property' => 'withOwnerName',
                'label' => 'Önéletrajz',
                'header_style' => 'text-align: center',
                'row_align' => 'center',
                'sortable' => true,
                'template' => '@SonataAdmin/CRUD/Common/employee_cv_link_list.html.twig',
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

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);

        if ($this->isChild()) {
            return;
        }

        $collection->clear();
    }
}
