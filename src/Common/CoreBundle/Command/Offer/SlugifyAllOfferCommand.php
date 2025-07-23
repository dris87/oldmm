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

namespace Common\CoreBundle\Command\Offer;

use All4One\AppBundle\Manager\OfferManager;
use Common\CoreBundle\Entity\Offer\Offer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A console command that set a slug to each offer
 *
 * To use this command, open a terminal window, enter into your project
 * directory and execute the following:
 *
 *     $ php bin/console app:offer:slugify-all
 *
 * To output detailed information, increase the command verbosity:
 *
 *     $ php bin/console app:offer:slugify-all -vv
 *
 * Class ActivateLastOrderCommand
 */
class SlugifyAllOfferCommand extends Command
{
    /**
     * To make your command lazily loaded, configure the $defaultName static property,
     * so it will be instantiated only when the command is actually called.
     *
     * @var string
     */
    protected static $defaultName = 'app:offer:slugify-all';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var OfferManager
     */
    private $offerManager;

    /**
     * SlugifyAllOfferCommand constructor.
     * @param EntityManagerInterface $em
     * @param OfferManager $offerManager
     */
    public function __construct(EntityManagerInterface $em, OfferManager $offerManager)
    {
        parent::__construct();

        $this->entityManager = $em;
        $this->offerManager = $offerManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Slugifies all offers')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $offers = $this->entityManager->getRepository('CommonCoreBundle:Offer\Offer')->findAll();
        // before we should empty all of our slugs!!!
        $this->offerManager->clearAllSlug();

        /** @var Offer $offer */
        foreach ($offers as $offer) {
            try {
                $this->offerManager->save($offer);
                $output->writeln('Offer #'.$offer->getId().' slugified. title:'.$offer->getTitle().', slug:'.$offer->getSlug());
                //$this->entityManager->detach($offer);
            } catch (ORMInvalidArgumentException $ex) {
                $output->writeln($ex->getTraceAsString());
            } catch (\Exception $ex) {
                $output->writeln($ex->getTraceAsString());
            }
        }
    }
}
