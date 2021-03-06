<?php

/*
 * This file is part of the Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Content\Compat;

use Sulu\Bundle\DocumentManagerBundle\Bridge\DocumentInspector;
use Sulu\Component\Content\Compat\Structure\LegacyPropertyFactory;
use Sulu\Component\Content\Compat\Structure\StructureBridge;
use Sulu\Component\Content\Extension\ExtensionInterface;
use Sulu\Component\Content\Extension\ExtensionManager;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactory;
use Sulu\Component\Content\Metadata\StructureMetadata as NewStructure;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * generates subclasses of structure to match template definitions.
 * this classes will be cached in Symfony cache.
 */
class StructureManager extends ContainerAware implements StructureManagerInterface
{
    private $structureFactory;
    private $extensionManager;
    private $inspector;
    private $propertyFactory;
    private $typeMap;

    /**
     * @param StructureMetadataFactory $structureFactory
     * @param ExtensionManager $extensionManager
     * @param DocumentInspector $inspector
     * @param LegacyPropertyFactory $propertyFactory
     * @param array $typeMap
     */
    public function __construct(
        StructureMetadataFactory $structureFactory,
        ExtensionManager $extensionManager,
        DocumentInspector $inspector,
        LegacyPropertyFactory $propertyFactory,
        array $typeMap
    ) {
        $this->structureFactory = $structureFactory;
        $this->extensionManager = $extensionManager;
        $this->inspector = $inspector;
        $this->propertyFactory = $propertyFactory;
        $this->typeMap = $typeMap;
    }

    /**
     * {@inheritdoc}
     */
    public function getStructure($key, $type = Structure::TYPE_PAGE)
    {
        return $this->wrapStructure($type, $this->structureFactory->getStructureMetadata($type, $key));
    }

    /**
     * {@inheritdoc}
     */
    public function getStructures($type = Structure::TYPE_PAGE)
    {
        $wrappedStructures = [];
        $structures = $this->structureFactory->getStructures($type);

        foreach ($structures as $structure) {
            $wrappedStructures[] = $this->wrapStructure($type, $structure);
        }

        return $wrappedStructures;
    }

    /**
     * {@inheritdoc}
     */
    public function addExtension(ExtensionInterface $extension, $template = 'all')
    {
        $this->extensionManager->addExtension($extension, $template);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions($key)
    {
        return $this->extensionManager->getExtensions($key);
    }

    /**
     * {@inheritdoc}
     */
    public function hasExtension($key, $name)
    {
        return $this->extensionManager->hasExtension($key, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension($key, $name)
    {
        return $this->extensionManager->getExtension($key, $name);
    }

    /**
     * Wrap the given Structure with a legacy (bridge) structure.
     *
     * @param Structure
     *
     * @return StructureBridge
     */
    public function wrapStructure($type, NewStructure $structure)
    {
        if (!isset($this->typeMap[$type])) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid legacy type "%s", known types: "%s"',
                $type, implode('", "', array_keys($this->typeMap))
            ));
        }

        $class = $this->typeMap[$type];

        return new $class($structure, $this->inspector, $this->propertyFactory);
    }
}
