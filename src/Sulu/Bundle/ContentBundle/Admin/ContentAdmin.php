<?php

/*
 * This file is part of the Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ContentBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Navigation\Navigation;
use Sulu\Bundle\AdminBundle\Navigation\NavigationItem;
use Sulu\Component\PHPCR\SessionManager\SessionManagerInterface;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Webspace\Webspace;

class ContentAdmin extends Admin
{
    /**
     * @var WebspaceManagerInterface
     */
    private $webspaceManager;

    /**
     * @var SecurityCheckerInterface
     */
    private $securityChecker;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * The prefix for the security context, the key of the webspace has to be appended.
     *
     * @var string
     */
    private $securityContextPrefix = 'sulu.webspaces.';

    public function __construct(
        WebspaceManagerInterface $webspaceManager,
        SecurityCheckerInterface $securityChecker,
        SessionManagerInterface $sessionManager,
        $title
    ) {
        $this->webspaceManager = $webspaceManager;
        $this->securityChecker = $securityChecker;
        $this->sessionManager = $sessionManager;

        $rootNavigationItem = new NavigationItem($title);

        $section = new NavigationItem('navigation.webspaces');

        $rootNavigationItem->addChild($section);

        /** @var Webspace $webspace */
        foreach ($this->webspaceManager->getWebspaceCollection() as $webspace) {
            if ($this->securityChecker->hasPermission($this->securityContextPrefix . $webspace->getKey(), 'view')) {
                $webspaceItem = new NavigationItem($webspace->getName());
                $webspaceItem->setIcon('bullseye');

                $indexUuid = $this->sessionManager->getContentNode($webspace->getKey())->getIdentifier();

                $indexPageItem = new NavigationItem('navigation.webspaces.index-page');
                $indexPageItem->setAction(
                    'content/contents/' . $webspace->getKey() . '/edit:' . $indexUuid . '/details'
                );
                $webspaceItem->addChild($indexPageItem);

                $contentItem = new NavigationItem('navigation.webspaces.content');
                $contentItem->setAction('content/contents/' . $webspace->getKey());
                $webspaceItem->addChild($contentItem);

                $section->addChild($webspaceItem);
            }
        }

        $this->setNavigation(new Navigation($rootNavigationItem));
    }

    /**
     * {@inheritdoc}
     */
    public function getCommands()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getJsBundleName()
    {
        return 'sulucontent';
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityContexts()
    {
        $webspaceContexts = [];
        foreach ($this->webspaceManager->getWebspaceCollection() as $webspace) {
            /* @var Webspace $webspace */
            $webspaceContexts[] = $this->securityContextPrefix . $webspace->getKey();
        }

        return [
            'Sulu' => [
                'Webspaces' => $webspaceContexts,
            ],
        ];
    }
}
