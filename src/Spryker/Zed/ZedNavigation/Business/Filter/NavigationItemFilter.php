<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ZedNavigation\Business\Filter;

use Generated\Shared\Transfer\NavigationItemCollectionTransfer;
use Generated\Shared\Transfer\NavigationItemTransfer;
use Spryker\Zed\ZedNavigation\Business\Model\Formatter\MenuFormatter;

class NavigationItemFilter implements NavigationItemFilterInterface
{
    /**
     * @var array<\Spryker\Zed\ZedNavigationExtension\Dependency\Plugin\NavigationItemFilterPluginInterface>
     */
    protected $navigationItemFilterPlugins;

    /**
     * @var array<\Spryker\Zed\ZedNavigationExtension\Dependency\Plugin\NavigationItemCollectionFilterPluginInterface>
     */
    protected $navigationItemCollectionFilterPlugins;

    /**
     * @param array<\Spryker\Zed\ZedNavigationExtension\Dependency\Plugin\NavigationItemFilterPluginInterface> $navigationItemFilterPlugins
     * @param array<\Spryker\Zed\ZedNavigationExtension\Dependency\Plugin\NavigationItemCollectionFilterPluginInterface> $navigationItemCollectionFilterPlugins
     */
    public function __construct(array $navigationItemFilterPlugins, array $navigationItemCollectionFilterPlugins)
    {
        $this->navigationItemFilterPlugins = $navigationItemFilterPlugins;
        $this->navigationItemCollectionFilterPlugins = $navigationItemCollectionFilterPlugins;
    }

    /**
     * {@inheritDoc}
     *
     * @param array<mixed> $navigationItems
     *
     * @return array<mixed>
     */
    public function filterNavigationItems(array $navigationItems): array
    {
        $navigationItemCollectionTransfer = new NavigationItemCollectionTransfer();
        $navigationItemCollectionTransfer = $this->mapNavigationItemsToNavigationItemCollectionTransfer(
            $navigationItems,
            $navigationItemCollectionTransfer,
        );
        $navigationItemCollectionTransfer = $this->applyNavigationItemCollectionFilterPlugins(
            $navigationItemCollectionTransfer,
        );

        return $this->filterNavigationItemsByNavigationItemCollectionTransfer(
            $navigationItems,
            $navigationItemCollectionTransfer,
        );
    }

    protected function applyNavigationItemCollectionFilterPlugins(
        NavigationItemCollectionTransfer $navigationItemCollectionTransfer
    ): NavigationItemCollectionTransfer {
        foreach ($this->navigationItemCollectionFilterPlugins as $navigationItemCollectionFilterPlugin) {
            $navigationItemCollectionTransfer = $navigationItemCollectionFilterPlugin->filter(
                $navigationItemCollectionTransfer,
            );
        }

        return $navigationItemCollectionTransfer;
    }

    /**
     * @param array<mixed> $navigationItems
     *
     * @return array<mixed>
     */
    protected function filterNavigationItemsByNavigationItemCollectionTransfer(
        array $navigationItems,
        NavigationItemCollectionTransfer $navigationItemCollectionTransfer
    ): array {
        if (!$navigationItemCollectionTransfer->getNavigationItems()->count()) {
            return [];
        }

        $filteredNavigationItems = [];

        foreach ($navigationItems as $navigationItem) {
            if ($this->hasRouteKeys($navigationItem) && $this->isRouteNavigationItemVisible($navigationItem, $navigationItemCollectionTransfer)) {
                $filteredNavigationItems[] = $navigationItem;
            }

            $filteredNestedNavigationItem = $this->filterNestedNavigationItem($navigationItem, $navigationItemCollectionTransfer);

            if ($filteredNestedNavigationItem !== null) {
                $filteredNavigationItems[] = $filteredNestedNavigationItem;
            }
        }

        return $filteredNavigationItems;
    }

    /**
     * @param array<mixed> $navigationItem
     *
     * @return array<mixed>|null
     */
    protected function filterNestedNavigationItem(
        array $navigationItem,
        NavigationItemCollectionTransfer $navigationItemCollectionTransfer
    ): ?array {
        if (!$this->hasNestedNavigationItems($navigationItem)) {
            return null;
        }

        $nestedNavigationItems = $this->filterNavigationItemsByNavigationItemCollectionTransfer(
            $navigationItem[MenuFormatter::PAGES],
            $navigationItemCollectionTransfer,
        );

        if (!$nestedNavigationItems) {
            return null;
        }

        $navigationItem[MenuFormatter::PAGES] = $nestedNavigationItems;

        return $navigationItem;
    }

    /**
     * @param array<mixed> $navigationItem
     */
    protected function isRouteNavigationItemVisible(
        array $navigationItem,
        NavigationItemCollectionTransfer $navigationItemCollectionTransfer
    ): bool {
        $navigationItemKey = $this->getNavigationItemKey($navigationItem);

        if (!$navigationItemCollectionTransfer->getNavigationItems()->offsetExists($navigationItemKey)) {
            return false;
        }

        $navigationItemTransfer = $navigationItemCollectionTransfer->getNavigationItems()
            ->offsetGet($navigationItemKey);

        return $this->isNavigationItemVisible($navigationItemTransfer);
    }

    /**
     * @param array<mixed> $navigationItems
     */
    protected function mapNavigationItemsToNavigationItemCollectionTransfer(
        array $navigationItems,
        NavigationItemCollectionTransfer $navigationItemCollectionTransfer
    ): NavigationItemCollectionTransfer {
        foreach ($navigationItems as $navigationItem) {
            if ($this->hasRouteKeys($navigationItem)) {
                $navigationItemTransfer = (new NavigationItemTransfer())
                    ->fromArray($navigationItem, true)
                    ->setModule($navigationItem[MenuFormatter::BUNDLE] ?? null);
                $navigationItemCollectionTransfer->addNavigationItem(
                    $this->getNavigationItemKey($navigationItem),
                    $navigationItemTransfer,
                );
            }

            if ($this->hasNestedNavigationItems($navigationItem)) {
                $navigationItemCollectionTransfer = $this->mapNavigationItemsToNavigationItemCollectionTransfer(
                    $navigationItem[MenuFormatter::PAGES],
                    $navigationItemCollectionTransfer,
                );
            }
        }

        return $navigationItemCollectionTransfer;
    }

    /**
     * @param array<string, mixed> $navigationItem
     */
    protected function hasNestedNavigationItems(array $navigationItem): bool
    {
        return isset($navigationItem[MenuFormatter::PAGES]);
    }

    /**
     * Navigation items that represent a Zed route carry bundle/controller/action keys.
     * URI-only entries (e.g. external links, separators) do not, and are identified by their URI instead.
     *
     * @param array<string, mixed> $navigationItem
     */
    protected function hasRouteKeys(array $navigationItem): bool
    {
        return isset(
            $navigationItem[MenuFormatter::BUNDLE],
            $navigationItem[MenuFormatter::CONTROLLER],
            $navigationItem[MenuFormatter::ACTION],
        );
    }

    protected function isNavigationItemVisible(NavigationItemTransfer $navigationItemTransfer): bool
    {
        foreach ($this->navigationItemFilterPlugins as $navigationItemFilterPlugin) {
            if (!$navigationItemFilterPlugin->isVisible($navigationItemTransfer)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string> $navigationItem
     */
    protected function getNavigationItemKey(array $navigationItem): string
    {
        if ($this->hasRouteKeys($navigationItem)) {
            return sprintf(
                '%s:%s:%s',
                $navigationItem[MenuFormatter::BUNDLE],
                $navigationItem[MenuFormatter::CONTROLLER],
                $navigationItem[MenuFormatter::ACTION],
            );
        }

        return $navigationItem[MenuFormatter::URI];
    }
}
