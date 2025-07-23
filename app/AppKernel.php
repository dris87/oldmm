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

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class AppKernel.
 */
class AppKernel extends Kernel
{
    const CONFIG_EXTS = '.yaml';

    /**
     * AppKernel constructor.
     *
     * @param string $environment
     * @param bool   $debug
     */
    public function __construct($environment, $debug)
    {
        date_default_timezone_set('Europe/Budapest');

        parent::__construct($environment, $debug);
    }

    /**
     * @return array|\Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     */
    public function registerBundles()
    {
        $bundles = [
            /* Symfony bundles */
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            /* Doctrine bundles */
            new Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            /* Sensio bundles */
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            /* Utility bundles */
            new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            /* Cron bundles */
            new Frcho\Bundle\CrontaskBundle\FrchoCrontaskBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Fkr\CssURLRewriteBundle\FkrCssURLRewriteBundle(),
            /* Sonata bundles */
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
            new Sonata\UserBundle\SonataUserBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            /* Common bundles */
            new Common\SzamlazzhuBundle\All4OneSzamlazzhuBundle(),
            new Common\CoreBundle\CommonCoreBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Misd\PhoneNumberBundle\MisdPhoneNumberBundle(),
            new Biplane\EnumBundle\BiplaneEnumBundle(),
            new Rollerworks\Bundle\PasswordStrengthBundle\RollerworksPasswordStrengthBundle(),
            /* All4One bundles */
            new All4One\AutocompleteBundle\All4OneAutocompleteBundle(),
            new All4One\SettingBundle\All4OneSettingBundle(),
            new All4One\NewsBundle\All4OneNewsBundle(),
            new All4One\AppBundle\All4OneAppBundle(),
            new All4One\RobotsTxtBundle\All4OneRobotsTxtBundle(),
            new Spirit\SpiritModelBundle\SpiritSpiritModelBundle(),
            new Spirit\ModelManagerBundle\SpiritModelManagerBundle(),
            /* Ujallas bundles */
            new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
            new WhiteOctober\BreadcrumbsBundle\WhiteOctoberBreadcrumbsBundle(),
            new Vich\UploaderBundle\VichUploaderBundle(),
            new Presta\ImageBundle\PrestaImageBundle(),
            new Presta\SitemapBundle\PrestaSitemapBundle(),
            new Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle(),
            new Payum\Bundle\PayumBundle\PayumBundle(),
            /* BackOffice Bundles */
            new Application\Sonata\UserBundle\ApplicationSonataUserBundle(),
            new BackOffice\AppBundle\BackOfficeAppBundle(),
        ];
        $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
        if (in_array($this->getEnvironment(), ['dev', 'test', 'prod'], true)) {
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            //$bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Symfony\Bundle\WebServerBundle\WebServerBundle();
            $bundles[] = new DAMA\DoctrineTestBundle\DAMADoctrineTestBundle();
        }

        return $bundles;
    }

    /**
     * @return string
     */
    public function getRootDir()
    {
        return __DIR__;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->getProjectDir().'/var/cache/'.$this->getEnvironment();
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return $this->getProjectDir().'/var/log/'.$this->getEnvironment();
    }

    /**
     * @param LoaderInterface $loader
     *
     * @throws Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $confDir = $this->getProjectDir().'/app/config';
        $env = $this->getEnvironment();

        $loader->load($confDir.'/packages/services'.self::CONFIG_EXTS);
        $loader->load($confDir.'/config'.self::CONFIG_EXTS);

        $loader->load($confDir.'/parameters'.self::CONFIG_EXTS);
        $loader->load($confDir.'/packages/*'.self::CONFIG_EXTS, 'glob');
        if (is_dir($confDir.'/packages/'.$env)) {
            $loader->load($confDir.'/packages/'.$env.'/**/*'.self::CONFIG_EXTS, 'glob');
        }
    }
}
