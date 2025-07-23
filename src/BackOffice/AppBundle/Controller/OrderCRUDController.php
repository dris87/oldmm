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

namespace BackOffice\AppBundle\Controller;

use Common\CoreBundle\Entity\Firm\Order\FirmOrder;
use Common\CoreBundle\Enumeration\Firm\Order\FirmOrderStatusEnum;
use Sonata\AdminBundle\Controller\CRUDController as BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderCRUDController extends BaseController
{
    public function listAction()
    {
        $request = $this->getRequest();

        $this->admin->checkAccess('list');

        $preResponse = $this->preList($request);
        if (null !== $preResponse) {
            return $preResponse;
        }

        if ($listMode = $request->get('_list_mode')) {
            $this->admin->setListMode($listMode);
        }

        $datagrid = $this->admin->getDatagrid();
        $formView = $datagrid->getForm()->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('Symfony\Bridge\Twig\Extension\FormExtension')->renderer->setTheme($formView, $this->admin->getFilterTheme());

        return $this->renderWithExtraParams($this->admin->getTemplate('list'), [
            'action' => 'list',
            'form' => $formView,
            'datagrid' => $datagrid,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
            'export_formats' => $this->has('sonata.admin.admin_exporter') ?
                $this->get('sonata.admin.admin_exporter')->getAvailableFormats($this->admin) :
                $this->admin->getExportFormats(),
        ], null);
    }

    public function acceptAction($id)
    {
        /** @var FirmOrder $object */
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }

        $object->setStatus(FirmOrderStatusEnum::create(FirmOrderStatusEnum::PAID));
        $em = $this->getDoctrine()->getManager();
        $em->persist($object);
        $em->flush();

        $this->addFlash('sonata_flash_success', 'Sikeresen elfogadva!');

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    public function denyAction($id)
    {
        /** @var FirmOrder $object */
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }

        $object->setStatus(FirmOrderStatusEnum::create(FirmOrderStatusEnum::CANCELLED));
        $em = $this->getDoctrine()->getManager();
        $em->persist($object);
        $em->flush();

        $this->addFlash('sonata_flash_error', 'Sikeresen elutasítva!');

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
