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

namespace All4One\AppBundle\Traits;

use Symfony\Component\Form\FormInterface;

/**
 * Trait ControllerUtilsTrait.
 */
trait ControllerUtilsTrait
{
    /**
     * Generate an array contains a key -> value with the errors
     * where the key is the name of the form field.
     *
     * @param FormInterface $form
     *
     * @return array
     */
    protected function getErrorMessages(FormInterface $form)
    {
        $errors = [];
        if ($form->count()) {
            foreach ($form as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = $this->getErrorMessages($child);
                }
            }
        }
        foreach ($form->getErrors() as $key => $error) {
            $template = $error->getMessageTemplate();
            $parameters = $error->getMessageParameters();

            foreach ($parameters as $var => $value) {
                $template = str_replace($var, $value, $template);
            }

            $errors[$key] = $template;
        }

        return $errors;
    }
}
