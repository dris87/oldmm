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

namespace All4One\AppBundle\Form\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * Class RequiredCollectionSubscriber.
 */
class RequiredCollectionSubscriber implements EventSubscriberInterface
{
    protected $type;
    protected $options;

    /**
     * @param string $type
     * @param array  $options
     */
    public function __construct(string $type, array $options = [])
    {
        $this->type = $type;
        $this->options = $options;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
            FormEvents::SUBMIT => ['onSubmit', 50],
        ];
    }

    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();

        $this->notAllowEmptyForm($form);
    }

    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        $this->notAllowEmptyForm($form);
    }

    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        $this->notAllowEmptyForm($form);
    }

    /**
     * @param FormEvent     $event
     * @param FormInterface $form
     */
    protected function notAllowEmptyForm(FormInterface $form)
    {
        if (!$form->isEmpty()) {
            return;
        }

        $form->add(0, $this->type, array_replace([
            'property_path' => '[0]',
        ], $this->options));
    }
}
