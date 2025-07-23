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

use All4One\AppBundle\Manager\OfferManager;
use Common\CoreBundle\Entity\Dictionary\DicCity;
use Common\CoreBundle\Entity\Dictionary\DicCounty;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Entity\Offer\OfferDictionaryRelation;
use Common\CoreBundle\Enumeration\Offer\OfferStatusEnum;
use Sonata\AdminBundle\Controller\CRUDController as BaseController;
use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bundle\TwigBundle\Command\DebugCommand;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class OfferCRUDController extends BaseController
{
    /**
     * @var OfferManager
     */
    protected $offerManager;

    /**
     * OfferCRUDController constructor.
     *
     * @param OfferManager $offerManager
     */
    public function __construct(OfferManager $offerManager)
    {
        $this->offerManager = $offerManager;
    }

    /**
     * @return null|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \ReflectionException
     */
    public function createAction()
    {
        $request = $this->getRequest();
        // the key used to lookup the template
        $templateKey = 'edit';

        $this->admin->checkAccess('create');

        $class = new \ReflectionClass($this->admin->hasActiveSubClass() ? $this->admin->getActiveSubClass() : $this->admin->getClass());

        if ($class->isAbstract()) {
            return $this->renderWithExtraParams(
                '@SonataAdmin/CRUD/select_subclass.html.twig',
                [
                    'base_template' => $this->getBaseTemplate(),
                    'admin' => $this->admin,
                    'action' => 'create',
                ],
                null
            );
        }

        $newObject = $this->admin->getNewInstance();

        $preResponse = $this->preCreate($request, $newObject);
        if (null !== $preResponse) {
            return $preResponse;
        }

        $this->admin->setSubject($newObject);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($newObject);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                /** @var Offer $submittedObject */
                $submittedObject = $form->getData();
                $this->admin->setSubject($submittedObject);
                $this->admin->checkAccess('create', $submittedObject);

                try {
                    if ($form->has('locationsText') && $form->get('locationsText')->getData()) {
                        $locationsText = $form->get('locationsText')->getData();
                        $locationNames = array_map('trim', explode(',', $locationsText));
                        
                        // Először ellenőrizzük az összes települést
                        $locationRepository = $this->getDoctrine()
                            ->getRepository('CommonCoreBundle:Dictionary\DicCity');
                        
                        $foundLocations = [];
                        $notFoundLocations = [];
                        
                        foreach ($locationNames as $locationName) {
                            if (empty($locationName)) continue;
                            
                            // Keresés a dic_city táblában
                            $location = $locationRepository->createQueryBuilder('c')
                                ->where('LOWER(c.value) = LOWER(:name)')
                                ->setParameter('name', $locationName)
                                ->setMaxResults(1)
                                ->getQuery()
                                ->getOneOrNullResult();
                            
                            if ($location) {
                                $foundLocations[] = $location;
                            } else {
                                $notFoundLocations[] = $locationName;
                            }
                        }
                        
                        // Ha van olyan település, amit nem találtunk
                        if (!empty($notFoundLocations)) {
                            $errorMessage = 'A következő települések nem találhatók az adatbázisban: ';
                            $errorMessage .= implode(', ', $notFoundLocations);
                            
                            $this->addFlash('sonata_flash_error', $errorMessage);
                            
                            // Ne folytassuk a mentést
                            $isFormValid = false;
                        } else {
                            // Ha minden települést megtaláltuk, akkor hozzáadjuk őket
                            foreach ($foundLocations as $location) {
                                // Csak akkor adjuk hozzá, ha még nincs benne
                                if (!$submittedObject->getLocations()->contains($location)) {
                                    $submittedObject->addLocation($location);
                                }
                            }
                        }
                    }

                    // Kép feltöltés kezelése
                    if ($form->has('leadImg')) {
                        $uploadedFile = $form->get('leadImg')->getData();
                        if ($uploadedFile) {
                            try {
                                $filename = $this->handleImageUpload($uploadedFile, $submittedObject);
                                $submittedObject->setLeadImg($filename);
                            } catch (\Exception $e) {
                                $this->addFlash('sonata_flash_error', $e->getMessage());
                                $isFormValid = false;
                            }
                        }
                    }

                   if ($isFormValid) {
                        // Létrehozáskor mindig true-val hívjuk a save-et, mivel ekkor mindig új slug-ot kell generálni
                        $forceClone = $form->has('locationsText') && $form->get('locationsText')->getData();

                        $newObject = $this->offerManager->save($submittedObject, true, $forceClone);
                        $this->admin->setSubject($newObject);

                        if ($this->isXmlHttpRequest()) {
                            return $this->renderJson([
                                'result' => 'ok',
                                'objectId' => $this->admin->getNormalizedIdentifier($newObject),
                            ], 200, []);
                        }

                        $this->addFlash(
                            'sonata_flash_success',
                            $this->trans(
                                'flash_create_success',
                                ['%name%' => $this->escapeHtml($this->admin->toString($newObject))],
                                'SonataAdminBundle'
                            )
                        );

                        // redirect to edit mode
                        return $this->redirectTo($newObject);
                    }

                } catch (ModelManagerException $e) {
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash(
                        'sonata_flash_error',
                        $this->trans(
                            'flash_create_error',
                            ['%name%' => $this->escapeHtml($this->admin->toString($newObject))],
                            'SonataAdminBundle'
                        )
                    );
                }
            } elseif ($this->isPreviewRequested()) {
                // pick the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $formView = $form->createView();
        // set the theme for the current Admin Form
        $this->setFormTheme($formView, $this->admin->getFormTheme());

        return $this->renderWithExtraParams($this->admin->getTemplate($templateKey), [
            'action' => 'create',
            'form' => $formView,
            'object' => $newObject,
            'objectId' => null,
        ], null);
    }

    /**
     * @param null $id
     * @return null|RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function editAction($id = null)
    {
        $request = $this->getRequest();
        // the key used to lookup the template
        $templateKey = 'edit';
        
        $id = $request->get($this->admin->getIdParameter());
        $existingObject = $this->admin->getObject($id);

        if (!$existingObject) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }
       
        //$this->checkParentChildAssociation($request, $existingObject);

        $this->admin->checkAccess('edit', $existingObject);

        $preResponse = $this->preEdit($request, $existingObject);
        if (null !== $preResponse) {
            return $preResponse;
        }

        $this->admin->setSubject($existingObject);
        $objectId = $this->admin->getNormalizedIdentifier($existingObject);

        /** @var $form Form */
        $form = $this->admin->getForm();
        $form->setData($existingObject);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
             
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                $submittedObject = $form->getData();
                $this->admin->setSubject($submittedObject);

                try {
                     // Települések szöveges feldolgozása
                    if ($form->has('locationsText') && $form->get('locationsText')->getData()) {
                        $locationsText = $form->get('locationsText')->getData();
                        $locationNames = array_map('trim', explode(',', $locationsText));
                        
                        // Először ellenőrizzük az összes települést
                        $locationRepository = $this->getDoctrine()
                            ->getRepository('CommonCoreBundle:Dictionary\DicCity');
                        
                        $foundLocations = [];
                        $notFoundLocations = [];
                        
                        foreach ($locationNames as $locationName) {
                            if (empty($locationName)) continue;
                            
                            // Keresés a dic_city táblában
                            $location = $locationRepository->createQueryBuilder('c')
                                ->where('LOWER(c.value) = LOWER(:name)')
                                ->setParameter('name', $locationName)
                                ->setMaxResults(1)
                                ->getQuery()
                                ->getOneOrNullResult();
                            
                            if ($location) {
                                $foundLocations[] = $location;
                            } else {
                                $notFoundLocations[] = $locationName;
                            }
                        }
                        
                        // Ha van olyan település, amit nem találtunk
                        if (!empty($notFoundLocations)) {
                            $errorMessage = 'A következő települések nem találhatók az adatbázisban: ';
                            $errorMessage .= implode(', ', $notFoundLocations);
                            
                            $this->addFlash('sonata_flash_error', $errorMessage);
                            
                            // Ne folytassuk a mentést
                            $isFormValid = false;
                        } else {
                            // Ha minden települést megtaláltuk, akkor hozzáadjuk őket
                            foreach ($foundLocations as $location) {
                                // Csak akkor adjuk hozzá, ha még nincs benne
                                if (!$submittedObject->getLocations()->contains($location)) {
                                    $submittedObject->addLocation($location);
                                }
                            }
                        }
                    }

                    // Kép feltöltés kezelése
                    if ($form->has('leadImg')) {
                        $uploadedFile = $form->get('leadImg')->getData();
                        if ($uploadedFile) {
                            try {
                                $filename = $this->handleImageUpload($uploadedFile, $submittedObject);
                                $submittedObject->setLeadImg($filename);
                            } catch (\Exception $e) {
                                $this->addFlash('sonata_flash_error', $e->getMessage());
                                $isFormValid = false;
                            }
                        }
                    }

                     if ($isFormValid) {

                        // Ellenőrizzük a checkbox állapotát szerkesztéskor
                        $updateSlug = $form->has('updateSlug') ? $form->get('updateSlug')->getData() : false;
                        
                        // Ha a szöveges mezőből jöttek települések, akkor forceClone = true
                        $forceClone = $form->has('locationsText') && $form->get('locationsText')->getData();

                        // Átadjuk a checkbox értékét a save metódusnak
                        $existingObject = $this->offerManager->save($submittedObject, $updateSlug, $forceClone);

                        if ($this->isXmlHttpRequest()) {
                            return $this->renderJson([
                                'result' => 'ok',
                                'objectId' => $objectId,
                                'objectName' => $this->escapeHtml($this->admin->toString($existingObject)),
                            ], 200, []);
                        }

                        $this->addFlash(
                            'sonata_flash_success',
                            $this->trans(
                                'flash_edit_success',
                                ['%name%' => $this->escapeHtml($this->admin->toString($existingObject))],
                                'SonataAdminBundle'
                            )
                        );

                        // redirect to edit mode
                        return $this->redirectTo($existingObject);
                    }
                } catch (ModelManagerException $e) {
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                } catch (LockException $e) {
                    $this->addFlash('sonata_flash_error', $this->trans('flash_lock_error', [
                        '%name%' => $this->escapeHtml($this->admin->toString($existingObject)),
                        '%link_start%' => '<a href="'.$this->admin->generateObjectUrl('edit', $existingObject).'">',
                        '%link_end%' => '</a>',
                    ], 'SonataAdminBundle'));
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash(
                        'sonata_flash_error',
                        $this->trans(
                            'flash_edit_error',
                            ['%name%' => $this->escapeHtml($this->admin->toString($existingObject))],
                            'SonataAdminBundle'
                        )
                    );
                }
            } elseif ($this->isPreviewRequested()) {
                // enable the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $formView = $form->createView();
        // set the theme for the current Admin Form
        $this->setFormTheme($formView, $this->admin->getFormTheme());

        // NEXT_MAJOR: Remove this line and use commented line below it instead
        $template = $this->admin->getTemplate($templateKey);
        // $template = $this->templateRegistry->getTemplate($templateKey);

        return $this->renderWithExtraParams($template, [
            'action' => 'edit',
            'form' => $formView,
            'object' => $existingObject,
            'objectId' => $objectId,
        ], null);
    }

    /**
     * @param $id
     * @return RedirectResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function acceptAction($id)
    {
        /** @var Offer $submittedObject */
        $submittedObject = $this->admin->getSubject();

        if (!$submittedObject) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }

        $submittedObject->setStatus(OfferStatusEnum::create(OfferStatusEnum::ACTIVE));

        $this->offerManager->save($submittedObject);

        $this->addFlash('sonata_flash_success', 'Sikeresen elfogadva!');

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    public function denyAction($id)
    {
        /** @var Offer $object */
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }

        $status = OfferStatusEnum::DENIED;
        $object->setStatus(OfferStatusEnum::create($status));
        $em = $this->getDoctrine()->getManager();
        $em->persist($object);
        $em->flush();

        $this->addFlash('sonata_flash_error', 'Sikeresen elutasítva!');

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * Sets the admin form theme to form view. Used for compatibility between Symfony versions.
     *
     * @param string   $theme
     * @param FormView $formView
     */
    private function setFormTheme(FormView $formView, $theme)
    {
        $twig = $this->get('twig');

        // BC for Symfony < 3.2 where this runtime does not exists
        if (!method_exists(AppVariable::class, 'getToken')) {
            $twig->getExtension(FormExtension::class)->renderer->setTheme($formView, $theme);

            return;
        }

        // BC for Symfony < 3.4 where runtime should be TwigRenderer
        if (!method_exists(DebugCommand::class, 'getLoaderPaths')) {
            $twig->getRuntime(TwigRenderer::class)->setTheme($formView, $theme);

            return;
        }

        $twig->getRuntime(FormRenderer::class)->setTheme($formView, $theme);
    }


    public function batchActionMinimalPackageEnable(ProxyQueryInterface $query)
{
    $this->admin->checkAccess('edit');
    
    $missingEmails = []; // Itt gyűjtjük a hiányzó e-mail címmel rendelkező hirdetéseket
    
    // Először ellenőrizzük, hogy minden hirdetésnek van-e e-mail címe
    foreach ($query->execute() as $offer) {
        if (empty($offer->getMinimalEmail())) {
            $missingEmails[] = $offer;
        }
    }
    
    // Ha van legalább egy hirdetés e-mail cím nélkül, hibaüzenetet adunk vissza
    if (!empty($missingEmails)) {
        $errorMessage = 'Az alábbi hirdetések nem rendelkeznek e-mail címmel: ';
        $offerList = [];
        
        // Maximum 5 hirdetés adatait jelenítjük meg a hibaüzenetben
        foreach (array_slice($missingEmails, 0, 5) as $offer) {
            $offerList[] = $offer->getTitle() . ' (ID: ' . $offer->getId() . ')';
        }
        
        $errorMessage .= implode(', ', $offerList);
        
        // Ha több mint 5 hirdetés van e-mail cím nélkül, jelezzük ezt is
        if (count($missingEmails) > 5) {
            $errorMessage .= ' és még ' . (count($missingEmails) - 5) . ' hirdetés';
        }
        
        $errorMessage .= '. Kérjük, töltse ki az e-mail címeket.';
        
        $this->addFlash('sonata_flash_error', $errorMessage);
        
        // Visszadobjuk a felhasználót a lista nézetre
        return new RedirectResponse(
            $this->admin->generateUrl('list', 
                ['filter' => $this->admin->getFilterParameters()])
        );
    }
    
    // Ha minden hirdetésnek van e-mail címe, végrehajtjuk a mentést
    foreach ($query->execute() as $offer) {
        $offer->setMinimalPackage(1);
    }
    
    $this->getDoctrine()->getManager()->flush();
    
    $this->addFlash('sonata_flash_success', 'Minimal csomag sikeresen bekapcsolva');
    
    return new RedirectResponse(
        $this->admin->generateUrl('list', 
            ['filter' => $this->admin->getFilterParameters()])
    );
}


    public function batchActionMinimalPackageDisable(ProxyQueryInterface $query)
    {
        $this->admin->checkAccess('edit');
        
        foreach ($query->execute() as $offer) {
            $offer->setMinimalPackage(0);
        }
        
        $this->getDoctrine()->getManager()->flush();
        
        $this->addFlash('sonata_flash_success', 'Minimal csomag sikeresen kikapcsolva');
        
        return new RedirectResponse(
            $this->admin->generateUrl('list', 
                ['filter' => $this->admin->getFilterParameters()])
        );
    }

    public function batchActionDeactivateOffers(ProxyQueryInterface $query)
    {
        $this->admin->checkAccess('edit');
        
        foreach ($query->execute() as $offer) {
            $offer->setStatus(OfferStatusEnum::create(OfferStatusEnum::INACTIVE));
        }
        
        $this->getDoctrine()->getManager()->flush();
        
        $this->addFlash('sonata_flash_success', 'A kiválasztott hirdetések sikeresen inaktiválva lettek.');
        
        return new RedirectResponse(
            $this->admin->generateUrl('list', 
                ['filter' => $this->admin->getFilterParameters()])
        );
    }

    public function batchActionActivateOffers(ProxyQueryInterface $query)
    {
        $this->admin->checkAccess('edit');
        
        foreach ($query->execute() as $offer) {
            $offer->setStatus(OfferStatusEnum::create(OfferStatusEnum::ACTIVE));
        }
        
        $this->getDoctrine()->getManager()->flush();
        
        $this->addFlash('sonata_flash_success', 'A kiválasztott hirdetések sikeresen aktiválva lettek.');
        
        return new RedirectResponse(
            $this->admin->generateUrl('list', 
                ['filter' => $this->admin->getFilterParameters()])
        );
    }

   /**
 * Duplikálja a hirdetést
 * 
 * @param int $id
 * @return RedirectResponse
 * @throws \Exception
 */
public function duplicateAction($id)
{
    $object = $this->admin->getObject($id);

    if (!$object) {
        throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
    }

    $this->admin->checkAccess('create');

    try {
        $em = $this->getDoctrine()->getManager();
        
        // FONTOS: Ne használjuk a new Offer() konstruktort, ami meghívja az initializeModel-t
        // Helyette reflection-nel hozzuk létre az objektumot
        $reflectionClass = new \ReflectionClass(Offer::class);
        $newOffer = $reflectionClass->newInstanceWithoutConstructor();
        
        // Manuálisan inicializáljuk az alapvető mezőket
        $newOffer->setStatus(OfferStatusEnum::create(OfferStatusEnum::SAVED));
        $newOffer->setApplicableFromDate(new \DateTime());
        $newOffer->setExpireDate(new \DateTime('+1 month'));
        
        // Inicializáljuk a collection-öket üres ArrayCollection-nel
        $dictionaryRelationsProperty = $reflectionClass->getProperty('dictionaryRelations');
        $dictionaryRelationsProperty->setAccessible(true);
        $dictionaryRelationsProperty->setValue($newOffer, new ArrayCollection());
        
        $candidatesProperty = $reflectionClass->getProperty('candidates');
        $candidatesProperty->setAccessible(true);
        $candidatesProperty->setValue($newOffer, new ArrayCollection());
        
        // Alapadatok másolása
        $newOffer->setFirm($object->getFirm());
        $newOffer->setTitle($object->getTitle());
        $newOffer->setLead($object->getLead());
        $newOffer->setAnonim($object->getAnonim());
        $newOffer->setNumberOfEmployee($object->getNumberOfEmployee());
        $newOffer->setLocale($object->getLocale());
        
        // Minimal csomag adatok
        $newOffer->setMinimalPackage($object->getMinimalPackage());
        $newOffer->setMinimalEmail($object->getMinimalEmail());
        $newOffer->setMinimalTitle($object->getMinimalTitle());
        $newOffer->setMinimalCity($object->getMinimalCity());
        $newOffer->setMinimalUrl($object->getMinimalUrl());
        $newOffer->setMinimalWithoutCv($object->getMinimalWithoutCv());
        
        // Minimum végzettség
        if ($object->getMinEducation()) {
            $newOffer->setMinEducation($object->getMinEducation());
        }
        
        // Most inicializáljuk a model-t, hogy a collection-ök létrejöjjenek
        $newOffer->initializeModel();
        
        // Lokációk másolása - clear után
        $newOffer->getLocations()->clear();
        foreach ($object->getLocations() as $location) {
            $newOffer->addLocation($location);
        }
        
        // Munkavégzés helyszínei
        $newOffer->getWorkLocations()->clear();
        foreach ($object->getWorkLocations() as $workLocation) {
            $newOffer->addWorkLocation($workLocation);
        }
        
        // Dictionary kapcsolatok másolása (kivéve lokációkat)
        foreach ($object->getDictionaryRelations() as $relation) {
            $dictionary = $relation->getDictionary();
            if (!($dictionary instanceof DicCity) && !($dictionary instanceof DicCounty)) {
                $newRelation = new OfferDictionaryRelation();
                $newRelation->setOffer($newOffer);
                $newRelation->setDictionary($relation->getDictionary());
                $newRelation->setDiscriminator($relation->getDiscriminator());
                $newRelation->setLevel($relation->getLevel());
                $newOffer->addDictionaryRelation($newRelation);
            }
        }
        
        // Egyszerű kapcsolatok másolása
        $newOffer->getCategories()->clear();
        foreach ($object->getCategories() as $category) {
            $newOffer->addCategory($category);
        }
        
        $newOffer->getShifts()->clear();
        foreach ($object->getShifts() as $shift) {
            $newOffer->addShift($shift);
        }
        
        $newOffer->getJobForms()->clear();
        foreach ($object->getJobForms() as $jobForm) {
            $newOffer->addJobForm($jobForm);
        }
        
        $newOffer->getDrivingLicenses()->clear();
        foreach ($object->getDrivingLicenses() as $drivingLicense) {
            $newOffer->addDrivingLicense($drivingLicense);
        }
        
        $newOffer->getPersonalStrengths()->clear();
        foreach ($object->getPersonalStrengths() as $personalStrength) {
            $newOffer->addPersonalStrength($personalStrength);
        }

        // Kép másolása (ha van)
        if ($object->getLeadImg()) {
            $originalImagePath = $this->get('kernel')->getRootDir() . '/../web/offer_img/' . $object->getId() . '/' . $object->getLeadImg();
            
            if (file_exists($originalImagePath)) {
                $newImageDir = $this->get('kernel')->getRootDir() . '/../web/offer_img/' . $newOffer->getId();
                if (!file_exists($newImageDir)) {
                    mkdir($newImageDir, 0755, true);
                }
                
                $newImagePath = $newImageDir . '/' . $newOffer->getId() . '.' . pathinfo($object->getLeadImg(), PATHINFO_EXTENSION);
                copy($originalImagePath, $newImagePath);
                
                $newOffer->setLeadImg($newOffer->getId() . '.' . pathinfo($object->getLeadImg(), PATHINFO_EXTENSION));
            }
        }
        
        // Mentés
        $newOffer = $this->offerManager->save($newOffer, true, false, true);
        
        $this->addFlash(
            'sonata_flash_success',
            sprintf('A hirdetés sikeresen duplikálva lett: %s', $newOffer->getTitle())
        );
        
        return $this->redirectTo($newOffer);
        
    } catch (\Exception $e) {
        $this->addFlash(
            'sonata_flash_error',
            'Hiba történt a hirdetés duplikálása során: ' . $e->getMessage()
        );
        
        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
/**
 * Kép feltöltés és feldolgozás GD könyvtárral
 * 
 * @param UploadedFile $uploadedFile
 * @param Offer $offer
 * @return string|null
 */
private function handleImageUpload(UploadedFile $uploadedFile, Offer $offer)
{
    try {
        // Mappa létrehozása
        $uploadDir = $this->get('kernel')->getRootDir() . '/../web/offer_img/' . $offer->getId();
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Fájl neve és kiterjesztése
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $uploadedFile->guessExtension();
        $newFilename = $offer->getId() . '.' . $extension;  // Egyszerű: 123.jpg
        
        // Kép betöltése
        $imagePath = $uploadedFile->getPathname();
        $imageType = exif_imagetype($imagePath);
        
        // Kép resource létrehozása típus alapján
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($imagePath);
                break;
            default:
                throw new \Exception('Nem megfelelő formátum');
        }
        
        if (!$sourceImage) {
            throw new \Exception('Nem sikerült betölteni a képet');
        }
        
        // Eredeti méretek
        $originalWidth = imagesx($sourceImage);
        $originalHeight = imagesy($sourceImage);
        
        // Új méretek számítása (arány megtartása)
        $maxWidth = 1100;
        $maxHeight = 500;
        
        if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth = round($originalWidth * $ratio);
            $newHeight = round($originalHeight * $ratio);
        } else {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        }
        
        // Új kép létrehozása
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // PNG átlátszóság megtartása
        if ($imageType == IMAGETYPE_PNG) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Kép átméretezése
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        
        // Kép mentése
        $targetPath = $uploadDir . '/' . $newFilename;
        
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $targetPath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($newImage, $targetPath);
                break;
        }
        
        // Memória felszabadítása
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
        return 'offer_img/' . $offer->getId() . '/' . $newFilename;

    } catch (\Exception $e) {
        // Log the error
        error_log('Image upload error: ' . $e->getMessage());
        throw new \Exception('Hiba történt a kép feltöltése során: ' . $e->getMessage());
    }
}

/**
 * Kép törlése
 * 
 * @param int $id
 * @return RedirectResponse
 */
public function deleteImageAction($id)
{
    /** @var Offer $offer */
    $offer = $this->admin->getObject($id);

    if (!$offer) {
        throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
    }

    $this->admin->checkAccess('edit', $offer);

    try {
        $imagePath = $this->get('kernel')->getRootDir() . '/../web/' . $offer->getLeadImg();
        
        // Adatbázisból törlés
        $offer->setLeadImg(null);
        $this->offerManager->save($offer, false, false);
        
        $this->addFlash('sonata_flash_success', 'A kép sikeresen törölve lett.');
        
    } catch (\Exception $e) {
        $this->addFlash('sonata_flash_error', 'Hiba történt a kép törlése során: ' . $e->getMessage());
    }

    return $this->redirectTo($offer);
}
    
}