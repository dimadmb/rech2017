<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new BaseBundle\BaseBundle(),
            new AdminBundle\AdminBundle(),
			
			new Liip\ImagineBundle\LiipImagineBundle(),

			new Ivory\CKEditorBundle\IvoryCKEditorBundle(),
			new FM\ElfinderBundle\FMElfinderBundle(),	

			new FOS\UserBundle\FOSUserBundle(),
			
			
			new Lsw\MemcacheBundle\LswMemcacheBundle(),
            new CruiseBundle\CruiseBundle(),
            new LoadBundle\LoadBundle(),
			
			new Dimadmb\SimpleHtmlDomBundle\DimadmbSimpleHtmlDomBundle(),
			
			new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new ManagerBundle\ManagerBundle(),
			
			new Liuggio\ExcelBundle\LiuggioExcelBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
