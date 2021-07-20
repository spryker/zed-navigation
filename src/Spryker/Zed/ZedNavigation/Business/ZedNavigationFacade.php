<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ZedNavigation\Business;

use Generated\Shared\Transfer\NavigationItemCollectionTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \Spryker\Zed\ZedNavigation\Business\ZedNavigationBusinessFactory getFactory()
 */
class ZedNavigationFacade extends AbstractFacade implements ZedNavigationFacadeInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $pathInfo
     * @param string|null $navigationType
     *
     * @return array
     */
    public function buildNavigation($pathInfo, ?string $navigationType = null)
    {
        return $this->getFactory()->createNavigationBuilder()->build($pathInfo, $navigationType);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return void
     */
    public function writeNavigationCache()
    {
        $this->getFactory()->createNavigationCacheBuilder()->writeNavigationCache();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return void
     */
    public function removeNavigationCache(): void
    {
        $this->getFactory()->createNavigationCacheRemover()->removeNavigationCache();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\NavigationItemCollectionTransfer $navigationItemCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\NavigationItemCollectionTransfer
     */
    public function filterNavigationItemCollectionByBackofficeRouteAccessibility(
        NavigationItemCollectionTransfer $navigationItemCollectionTransfer
    ): NavigationItemCollectionTransfer {
        return $this->getFactory()
            ->createBackofficeNavigationItemCollectionRouterFilter()
            ->filterNavigationItemCollectionByRouteAccessibility($navigationItemCollectionTransfer);
    }
}
