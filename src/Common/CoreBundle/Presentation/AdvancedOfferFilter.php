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

namespace Common\CoreBundle\Presentation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class AdvancedOfferFilter.
 */
class AdvancedOfferFilter
{
    /**
     * @var string
     */
    private $title = '';

    /**
     * @var string
     */
    private $keyword = '';

    /**
     * @var Collection
     */
    private $categories;

    /**
     * @var Collection
     */
    private $locations;

    /**
     * @var Collection
     */
    private $shifts;

    /**
     * @var Collection
     */
    private $jobForms;

    /**
     * @var Collection
     */
    private $languages;

    /**
     * @var Collection
     */
    private $drivingLicenses;

    /**
     * AdvancedOfferFilter constructor.
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->shifts = new ArrayCollection();
        $this->jobForms = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->drivingLicenses = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     *
     * @return AdvancedOfferFilter
     */
    public function setTitle(string $title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @param string|null $keyword
     *
     * @return AdvancedOfferFilter
     */
    public function setKeyword(string $keyword = null)
    {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param Collection|null $categories
     *
     * @return AdvancedOfferFilter
     */
    public function setCategories(Collection $categories = null)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * @param Collection|null $locations
     *
     * @return AdvancedOfferFilter
     */
    public function setLocations(Collection $locations = null)
    {
        $this->locations = $locations;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getShifts()
    {
        return $this->shifts;
    }

    /**
     * @param Collection|null $shifts
     *
     * @return AdvancedOfferFilter
     */
    public function setShifts(Collection $shifts = null)
    {
        $this->shifts = $shifts;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getJobForms()
    {
        return $this->jobForms;
    }

    /**
     * @param Collection|null $jobForms
     *
     * @return AdvancedOfferFilter
     */
    public function setJobForms(Collection $jobForms = null)
    {
        $this->jobForms = $jobForms;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @param Collection|null $languages
     *
     * @return AdvancedOfferFilter
     */
    public function setLanguages(Collection $languages = null)
    {
        $this->languages = $languages;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getDrivingLicenses()
    {
        return $this->drivingLicenses;
    }

    /**
     * @param Collection|null $drivingLicenses
     *
     * @return AdvancedOfferFilter
     */
    public function setDrivingLicenses(Collection $drivingLicenses = null)
    {
        $this->drivingLicenses = $drivingLicenses;

        return $this;
    }
}
