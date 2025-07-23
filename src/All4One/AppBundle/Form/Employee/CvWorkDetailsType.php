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
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCv;
use Common\CoreBundle\Enumeration\Employee\Cv\EmployeeCvWillToMoveEnum;
use Common\CoreBundle\Enumeration\Employee\Cv\EmployeeCvWillToTravelEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form used to create and manipulate employee cv details.
 */
class CvWorkDetailsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('searchCategories', AutocompleteType::class, [
                'label' => 'page.employee.cv.work_details.search_categories.label',
                'descriptor' => 'dic_child_category.descriptor',
                'minimum_input_length' => 0,
                'required' => true,
                'multiple' => true,
                'placeholder' => 'page.employee.cv.work_details.search_categories.placeholder',
            ])
            ->add('shifts', AutocompleteType::class, [
                'label' => 'page.employee.cv.work_details.shifts.label',
                'descriptor' => 'dic_shift.descriptor',
                'minimum_input_length' => 0,
                'required' => true,
                'multiple' => true,
                'placeholder' => 'page.employee.cv.work_details.shifts.placeholder',
            ])
            ->add('jobForms', AutocompleteType::class, [
                'label' => 'page.employee.cv_work_details.job_form.label',
                'descriptor' => 'dic_job_form.descriptor',
                'minimum_input_length' => 0,
                'required' => true,
                'multiple' => true,
                'placeholder' => 'page.employee.cv.work_details.job_form.placeholder',
            ])->add('marketStatuses', AutocompleteType::class, [
                'label' => 'page.employee.cv_work_details.market_status.label',
                'descriptor' => 'dic_market_status.descriptor',
                'minimum_input_length' => 0,
                'required' => true,
                'multiple' => true,
                'placeholder' => 'page.employee.cv.work_details.market_status.placeholder',
            ])->add('jobComment', null, [
                'label' => 'label.job_comment',
                'attr' => ['placeholder' => 'placeholder.job_comment'],
            ])->add('willToTravelDistance', null, [
                'label' => 'label.travel_to_distance',
                'attr' => ['placeholder' => 'placeholder.travel_to_distance'],
            ])->add('willToTravelLocations', AutocompleteType::class, [
                'label' => 'label.empty',
                'descriptor' => 'dic_city_county.descriptor',
                'required' => true,
                'multiple' => true,
                'minimum_input_length' => 0,
                'placeholder' => 'placeholder.travel_to_location',
            ])->add('willToTravel', EnumType::class,
                [
                    'label' => 'label.empty',
                    'enum_class' => EmployeeCvWillToTravelEnum::class,
                    'multiple' => false,
                    'expanded' => true,
                ]
            )->add('willToMove', EnumType::class,
                [
                    'label' => 'label.empty',
                    'enum_class' => EmployeeCvWillToMoveEnum::class,
                    'multiple' => false,
                    'expanded' => true,
                ]
            )->add('willToMoveLocations', AutocompleteType::class, [
                'label' => 'label.empty',
                'descriptor' => 'dic_city_county.descriptor',
                'required' => true,
                'multiple' => true,
                'minimum_input_length' => 0,
                'placeholder' => 'placeholder.move_to_location',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EmployeeCv::class,
            'csrf_protection' => false,
            'validation_groups' => function (FormInterface $form) {
                $groups = ['Default'];

                /** @var EmployeeCv $cv */
                $cv = $form->getData();

                if (EmployeeCvWillToMoveEnum::create(EmployeeCvWillToMoveEnum::BY_LOCATION) == $cv->getWillToMove()) {
                    $groups[] = 'willToMoveLocation';
                }

                if (EmployeeCvWillToMoveEnum::create(EmployeeCvWillToMoveEnum::ANYWHERE) == $cv->getWillToMove()) {
                    $cv->clearWillToMoveLocations();
                }

                if (EmployeeCvWillToTravelEnum::create(EmployeeCvWillToTravelEnum::BY_LOCATION) == $form->getData()->getWillToTravel()) {
                    $groups[] = 'willToTravelLocation';
                    $cv->setWillToTravelDistance(null);
                }

                if (EmployeeCvWillToTravelEnum::create(EmployeeCvWillToTravelEnum::BY_DISTANCE) == $form->getData()->getWillToTravel()) {
                    $groups[] = 'willToTravelDistance';
                    $cv->clearWillToTravelLocation();
                }

                return $groups;
            },
        ]);
    }
}
