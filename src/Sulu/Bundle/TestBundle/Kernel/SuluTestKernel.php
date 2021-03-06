<?php

/*
 * This file is part of the Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\TestBundle\Kernel;

use Sulu\Bundle\TestBundle\SuluTestBundle;
use Sulu\Component\HttpKernel\SuluKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class SuluTestKernel extends SuluKernel
{
    public function registerBundles()
    {
        $bundles = [
            // Dependencies
            new \Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Doctrine\Bundle\PHPCRBundle\DoctrinePHPCRBundle(),
            new \FOS\RestBundle\FOSRestBundle(),
            new \JMS\SerializerBundle\JMSSerializerBundle(),
            new \Liip\ThemeBundle\LiipThemeBundle(),
            new \Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Dubture\FFmpegBundle\DubtureFFmpegBundle(),

            // Sulu
            new \Sulu\Bundle\CoreBundle\SuluCoreBundle(),
            new \Sulu\Bundle\PersistenceBundle\SuluPersistenceBundle(),
            new \Sulu\Bundle\AdminBundle\SuluAdminBundle(),
            new \Sulu\Bundle\ContentBundle\SuluContentBundle(),
            new \Sulu\Bundle\ContactBundle\SuluContactBundle(),
            new \Sulu\Bundle\SecurityBundle\SuluSecurityBundle(),
            new \Sulu\Bundle\WebsiteBundle\SuluWebsiteBundle(),
            new \Sulu\Bundle\TestBundle\SuluTestBundle(),
            new \Sulu\Bundle\TagBundle\SuluTagBundle(),
            new \Sulu\Bundle\MediaBundle\SuluMediaBundle(),
            new \Sulu\Bundle\CategoryBundle\SuluCategoryBundle(),
            new \Sulu\Bundle\HttpCacheBundle\SuluHttpCacheBundle(),
            new \Sulu\Bundle\SnippetBundle\SuluSnippetBundle(),
            new \Sulu\Bundle\WebsocketBundle\SuluWebsocketBundle(),
            new \Sulu\Bundle\LocationBundle\SuluLocationBundle(),
            new \Sulu\Bundle\DocumentManagerBundle\SuluDocumentManagerBundle(),
            new \Sulu\Bundle\ResourceBundle\SuluResourceBundle(),
        ];

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(SuluTestBundle::getConfigDir() . '/config.php');
    }

    public function getCacheDir()
    {
        return $this->rootDir . '/cache/' . $this->getContext() . '/' . $this->environment;
    }

    /**
     * {@inheritdoc}
     *
     * Add the Sulu environment to the container name
     */
    protected function getContainerClass()
    {
        return $this->name . ucfirst($this->getContext()) . ucfirst($this->environment) . ($this->debug ? 'Debug' : '') . 'ProjectContainer';
    }
}
