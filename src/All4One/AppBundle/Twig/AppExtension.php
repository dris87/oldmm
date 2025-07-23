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

namespace All4One\AppBundle\Twig;

use All4One\AppBundle\Form\Offer\AdvancedSearchType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class AppExtension.
 */
class AppExtension extends AbstractExtension
{
    /**
     * @var array
     */
    private $localeCodes;

    /**
     * @var
     */
    private $locales;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * AppExtension constructor.
     *
     * @param $locales
     * @param FormFactoryInterface $formFactory
     * @param RouterInterface      $router
     */
    public function __construct($locales, FormFactoryInterface $formFactory, RouterInterface $router)
    {
        $this->localeCodes = explode('|', $locales);
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('locales', [$this, 'getLocales']),
            new TwigFunction('advanced_offer_search', [$this, 'getAdvancedSearch']),
            new TwigFunction('get_social_links', [$this, 'getSocialLinks']),
        ];
    }

    /**
     * Takes the list of codes of the locales (languages) enabled in the
     * application and returns an array with the name of each locale written
     * in its own language (e.g. English, Français, Español, etc.).
     *
     * @return array
     */
    public function getLocales(): array
    {
        if (null !== $this->locales) {
            return $this->locales;
        }

        $this->locales = [];
        foreach ($this->localeCodes as $localeCode) {
            $this->locales[] = ['code' => $localeCode, 'name' => Intl::getLocaleBundle()->getLocaleName($localeCode, $localeCode)];
        }

        return $this->locales;
    }

    /**
     * TODO: wire this to db.
     *
     * @return array
     */
    public function getSocialLinks(): array
    {
        // This should be stroed in db
        $links = [
            [
                'href' => 'https://www.facebook.com/mumi.hu/',
                'fa' => 'facebook-official',
            ],
            [
                'href' => 'https://www.youtube.com/channel/UCIzkIJCIj6Dp6PF5P-QAlAA',
                'fa' => 'youtube',
            ],
            // [
            //     'href' => 'https://www.linkedin.com/company/%C3%BAj%C3%A1ll%C3%A1s.hu',
            //     'fa' => 'linkedin',
            // ],
            // [
            //     'href' => 'https://plus.google.com/u/0/105278626268739263260',
            //     'fa' => 'google-plus',
            // ],
        ];

        return $links;
    }

    /**
     * @return \Symfony\Component\Form\FormView
     */
    public function getAdvancedSearch()
    {
        return $this->formFactory->createNamed(
            'mobile-search',
            AdvancedSearchType::class, null,
            [
                'action' => $this->router->generate('list_offers', [], UrlGeneratorInterface::ABSOLUTE_PATH),
                'method' => 'GET',
            ]
        )->createView();
    }
}
