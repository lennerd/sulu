<?php

/*
 * This file is part of the Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\SecurityBundle\Admin;

use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationItem;
use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationProviderInterface;

class RolesContentNavigationProvider implements ContentNavigationProviderInterface
{
    public function getNavigationItems(array $options = [])
    {
        $details = new ContentNavigationItem('content-navigation.security.details');
        $details->setAction('details');
        $details->setComponent('roles@sulusecurity');
        $details->setComponentOptions(['display' => 'form']);

        return [$details];
    }
}
