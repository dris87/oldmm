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

namespace All4One\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomFormType.
 */
class CustomFormType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'fields' => [],
            'callback' => null,
        ]);

        $resolver->setAllowedTypes('fields', ['array']);
        $resolver->setAllowedTypes('callback', ['null', 'closure']);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['fields'] as $name => $field) {
            if (!isset($field['options']['attr']['formGroupClass'])) {
                $field['options']['attr']['formGroupClass'] = 'col-sm-12';
            }
            $builder->add(
                $name,
                isset($field['type'])
                    ? $field['type']
                    : null,
                isset($field['options'])
                    ? $field['options']
                    : []
            );
        }

        $callback = $options['callback'];
        if ($callback instanceof \Closure) {
            $callback($builder);
        }
    }
}
