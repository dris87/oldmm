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

namespace All4One\AppBundle\Manager;

use Common\CoreBundle\Doctrine\Repository\Offer\OfferCandidateRepository;
use Common\CoreBundle\Doctrine\Repository\Offer\OfferRepository;
use Common\CoreBundle\Entity\Dictionary\DicCity;
use Common\CoreBundle\Entity\Dictionary\DicCounty;
use Common\CoreBundle\Entity\Dictionary\DicLocation;
use Common\CoreBundle\Entity\Dictionary\Dictionary;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Entity\Offer\OfferCandidate;
use Common\CoreBundle\Entity\Offer\OfferDictionaryRelation;
use Common\CoreBundle\Enumeration\Offer\OfferStatusEnum;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class OfferManager.
 */
class OfferManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * OfferManager constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Offer $offer
     * @return mixed
     */
    public function getDuplicatedSlugs(Offer $offer){

        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('o')
            ->from('CommonCoreBundle:Offer\Offer', 'o')
            ->where('o.slug = :slug')
                ->setParameter('slug',$offer->getSlug())
            ->andWhere('o.id <> :id')
                ->setParameter('id', $offer->getId())
            ->orderBy('o.id');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return mixed
     */
    public function clearAllSlug(){
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->update('CommonCoreBundle:Offer\Offer', 'o')
            ->set('o.slug', ':slug')
            ->setParameter('slug', '')
            ->getQuery()->execute();
    }

    /**
     * Returns the number of time this slug was used
     *
     * @param Offer $offer
     * @param $slug
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countSlug(Offer $offer, $slug){
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('count(o.id)')
            ->from('CommonCoreBundle:Offer\Offer', 'o')
            ->andWhere('( o.slug LIKE :slugCounted OR o.slug = :slug)')
                ->setParameter('slugCounted', $slug . '--%')
                ->setParameter('slug', $slug);
        if( !empty($offer->getId()) ){
            $queryBuilder
                ->andWhere('o.id <> :id')
                ->setParameter('id',$offer->getId());
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

   /**
     * @param Offer $offer
     * @param bool $updateSlug Ha igaz, frissíti a slug-ot a title változtatásakor
     * @param bool $forceClone Ha igaz, státusztól függetlenül klónoz (szöveges települések esetén)
     * @param bool $skipLocationProcessing Ha igaz, nem dolgozza fel a lokációkat (duplikálás esetén)
     * @return Offer
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function save(Offer $offer, $updateSlug = true, $forceClone = false, $skipLocationProcessing = false)
    {
        // Memory limit növelés nagy klónozáshoz
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        
        $status = $offer->getStatus();
        // make sure we have the correct status
        $offer->setStatus($status);

        // Klónozás történik ha:
        // 1. Vagy a státusz ACTIVE/WAITING
        // 2. Vagy a $forceClone paraméter true (szöveges települések esetén)
        // 3. ÉS nem duplikálásból jön ($skipLocationProcessing false)
        if( !$skipLocationProcessing && ($forceClone || in_array($status, [
            OfferStatusEnum::create(OfferStatusEnum::ACTIVE),
            OfferStatusEnum::create(OfferStatusEnum::WAITING)
        ])) ) {
            // get all locations and loop through
            if (count($locations = $offer->getLocations()) > 1) {
                foreach ($locations as $key => $location) {
                    if ($key === 0) continue;
                    
                    // Klón létrehozása és persist (DE NEM FLUSH!)
                    $cloneOffer = $this->cloneOfferOptimized($offer, $location);
                    $this->entityManager->persist($cloneOffer);
                }
            }
        }

        // Csak akkor generálj új slug-ot, ha a $updateSlug paraméter igaz vagy nincs még slug beállítva
        if ($updateSlug || empty($offer->getSlug())) {
            $slug = $offer->generateSlug();

            if( ( $countSlug = $this->countSlug($offer, $slug) ) > 0 ){
                $slug = $slug . '--' . ( (int)$countSlug++ );
            }

            $offer->setSlug($slug);
        }
        
        // save the object
        $this->entityManager->persist($offer);
        
        // EGY flush az összes objektumra (eredeti + összes klón)
        $this->entityManager->flush();
       
        return $offer;
    }

    // 2. Új optimalizált klón metódus hozzáadása:

    /**
     * Optimalizált klón metódus - nem hív save()-et, csak létrehozza az objektumot
     * UUID alapú slug generálással az egyediség garantálásához
     */
    private function cloneOfferOptimized(Offer $submittedObject, Dictionary $location)
    {
        $cloneObject = new Offer();
        $cloneObject->setStatus($submittedObject->getStatus());

        $submittedDictionaryRelations = $submittedObject->getDictionaryRelations();

        foreach ($submittedDictionaryRelations as $dictionaryRelation) {
            $dictionary = $dictionaryRelation->getDictionary();
            if ($dictionary instanceof DicCity || $dictionary instanceof DicCounty) {
                // Skip city/county
            } else {
                $odr = new OfferDictionaryRelation();
                $odr->setOffer($cloneObject);
                $odr->setDictionary($dictionary);
                $odr->setDiscriminator($dictionaryRelation->getDiscriminator());
                $odr->setLevel($dictionaryRelation->getLevel());
                $cloneObject->addDictionaryRelation($odr);
            }
        }

        $cloneObject->addLocation($location);
        $submittedObject->removeLocation($location);

        foreach ($submittedObject->getWorkLocations() as $workLocation) {
            $cloneObject->addWorkLocation($workLocation);
        }

        $cloneObject
            ->setFirm($submittedObject->getFirm())
            ->setTitle($submittedObject->getTitle())
            ->setLead($submittedObject->getLead())
            ->setAnonim($submittedObject->getAnonim())
            ->setNumberOfEmployee($submittedObject->getNumberOfEmployee())
            ->setApplicableFromDate($submittedObject->getApplicableFromDate())
            ->setExpireDate($submittedObject->getExpireDate())
            ->setLocale($submittedObject->getLocale())
            ->setMinimalPackage($submittedObject->getMinimalPackage())
            ->setMinimalEmail($submittedObject->getMinimalEmail())
            ->setMinimalTitle($submittedObject->getMinimalTitle())
            ->setMinimalCity($submittedObject->getMinimalCity())
            ->setMinimalUrl($submittedObject->getMinimalUrl())
            ->setMinimalWithoutCv($submittedObject->getMinimalWithoutCv())
            ->setofferExaltationUntil($submittedObject->getofferExaltationUntil())
            ->setLeadImg($submittedObject->getLeadImg())
        ;

        // UUID alapú egyedi slug generálás (nincs szükség DB ellenőrzésre)
        $baseSlug = $cloneObject->generateSlug();
        $uniqueId = substr(str_replace('-', '', uniqid('', true)), 0, 8); // 8 karakteres egyedi ID
        $slug = $baseSlug . '-' . $uniqueId;
        $cloneObject->setSlug($slug);

        return $cloneObject;
    }

    /**
     * @param Offer $submittedObject
     * @param Dictionary $location
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function cloneOffer(Offer $submittedObject, Dictionary $location){

        /* @var $cloneObject Offer */
        $cloneObject = new Offer();

        $cloneObject->setStatus($submittedObject->getStatus());

        $submittedDictionaryRelations = $submittedObject->getDictionaryRelations();

        /* @var $dictionaryRelation OfferDictionaryRelation */
        foreach ($submittedDictionaryRelations as $dictionaryRelation) {
            $dictionary = $dictionaryRelation->getDictionary();
            if ($dictionary instanceof DicCity || $dictionary instanceof DicCounty) {
            } else {
                $odr = new OfferDictionaryRelation();
                $odr->setOffer($cloneObject);
                $odr->setDictionary($dictionary);
                $odr->setDiscriminator($dictionaryRelation->getDiscriminator());
                $odr->setLevel($dictionaryRelation->getLevel());
                $cloneObject->addDictionaryRelation($odr);
            }
        }

        $cloneObject->addLocation($location);
        $submittedObject->removeLocation($location);

        foreach ($submittedObject->getWorkLocations() as $workLocation) {
            $cloneObject->addWorkLocation($workLocation);
        }
    
        $cloneObject
            ->setFirm($submittedObject->getFirm())
            ->setTitle($submittedObject->getTitle())
            ->setLead($submittedObject->getLead())
            ->setAnonim($submittedObject->getAnonim())
            ->setNumberOfEmployee($submittedObject->getNumberOfEmployee())
            ->setApplicableFromDate($submittedObject->getApplicableFromDate())
            ->setExpireDate($submittedObject->getExpireDate())
            ->setLocale($submittedObject->getLocale())
            ->setMinimalPackage($submittedObject->getMinimalPackage())
            ->setMinimalEmail($submittedObject->getMinimalEmail())
            ->setMinimalTitle($submittedObject->getMinimalTitle())
            ->setMinimalCity($submittedObject->getMinimalCity())
            ->setMinimalUrl($submittedObject->getMinimalUrl())
            ->setMinimalWithoutCv($submittedObject->getMinimalWithoutCv())
            ->setofferExaltationUntil($submittedObject->getofferExaltationUntil())
            ->setLeadImg($submittedObject->getLeadimg())
        ;

        // Ez a klónozott objektum csak egy helyszínt tartalmaz,
        // így a save metódus nem fogja újra meghívni ezt a metódust
        // Mindig generáljunk új slug-ot a klónozott objektumoknak
        $this->save($cloneObject, true);
    }

    /**
     * @param Offer $offer
     */
    public function delete(Offer $offer)
    {
        $this->entityManager->remove($offer);
        $this->entityManager->flush();
    }

    /**
     * @param OfferCandidate $offerCandidate
     *
     * @return mixed
     */
    public function saveCandidate(OfferCandidate $offerCandidate)
    {
        $this->entityManager->persist($offerCandidate);
        $this->entityManager->flush();

        return $offerCandidate;
    }

    /**
     * @return OfferRepository
     */
    public function getRepository()
    {
        /** @var OfferRepository $repository */
        $repository = $this->entityManager->getRepository('CommonCoreBundle:Offer\Offer');

        return $repository;
    }

    /**
     * @return OfferCandidateRepository
     */
    public function getCandidateRepository()
    {
        /** @var OfferCandidateRepository $repository */
        $repository = $this->entityManager->getRepository('CommonCoreBundle:Offer\OfferCandidate');

        return $repository;
    }
}