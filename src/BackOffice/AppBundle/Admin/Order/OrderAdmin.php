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

namespace BackOffice\AppBundle\Admin\Order;

use BackOffice\AppBundle\Admin\AbstractAdmin;
use Common\CoreBundle\Enumeration\Firm\Order\FirmOrderStatusEnum;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Form\Type\DateRangePickerType;

/**
 * Class OrderAdmin.
 */
class OrderAdmin extends AbstractAdmin
{
    /**
     * @param string $name
     *
     * @return string|null
     */
    public function getTemplate($name)
    {
        switch ($name) {
            case 'list':
                return 'BackOfficeAppBundle::order/firm_order_list.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['list']);
        $collection->add('accept', $this->getRouterIdParameter().'/accept');
        $collection->add('deny', $this->getRouterIdParameter().'/deny');
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
            ->add('status', 'doctrine_orm_string', [
                'label' => 'Státusz',
            ], 'choice', [
                'choices' => array_flip(FirmOrderStatusEnum::getReadables()),
            ])
            ->add('firm.name', null, [
                'label' => 'Cég neve',
            ])
            ->add('createdAt', 'doctrine_orm_date_range', [
                'label' => 'Kelte',
                'field_type' => DateRangePickerType::class,
            ])

        ;
    }
}
