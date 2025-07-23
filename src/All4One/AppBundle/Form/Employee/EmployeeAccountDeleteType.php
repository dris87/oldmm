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

use Common\CoreBundle\Entity\Employee\DeletedEmployee;
use Common\CoreBundle\Entity\Employee\DeletedEmployeeReason;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form used to create and manipulate employees.
 */
class EmployeeAccountDeleteType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reason', EntityType::class, [
                'label' => 'form.employee.account.delete.label.reason',
                'class' => DeletedEmployeeReason::class,
                'multiple' => false,
                'expanded' => true,
                'choice_label' => 'description',
                'choice_value' => 'id',
            ])
            ->add('reasonDescription', TextareaType::class, [
                'label' => 'form.employee.account.delete.label.reason_description',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DeletedEmployee::class,
            'csrf_protection' => false,
            'validation_groups' => ['Deletion'],
        ]);
    }
}
