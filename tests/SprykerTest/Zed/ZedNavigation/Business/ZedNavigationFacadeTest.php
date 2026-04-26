<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ZedNavigation\Business;

use Generated\Shared\Transfer\NavigationItemCollectionTransfer;
use Generated\Shared\Transfer\NavigationItemTransfer;
use Spryker\Zed\ZedNavigation\Business\Model\ZedNavigationBuilder;
use Spryker\Zed\ZedNavigation\Communication\Plugin\BackofficeNavigationItemCollectionFilterPlugin;
use Spryker\Zed\ZedNavigationExtension\Dependency\Plugin\NavigationItemFilterPluginInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ZedNavigation
 * @group Business
 * @group Facade
 * @group ZedNavigationFacadeTest
 * Add your own group annotations below this line
 */
class ZedNavigationFacadeTest extends ZedNavigationBusinessTester
{
    public function testBuildNavigationShouldReturnArrayWithMenuAsKey(): void
    {
        $navigation = $this->getFacade()->buildNavigation('');

        $this->assertArrayHasKey('menu', $navigation);
    }

    public function testBuildNavigationKeepsParentEntriesWithAclRestrictedChildren(): void
    {
        // Arrange
        $this->provideNavigationItemCollectionFilterPlugins([]);
        $this->provideNavigationItemFilterPlugins([$this->buildAclDeniedChildrenFilterPlugin()]);

        $facade = $this->getFacadeWithCustomNavigationFileName('nested_navigation.xml');

        // Act
        $navigation = $facade->buildNavigation('');

        // Assert
        $menu = $navigation[ZedNavigationBuilder::MENU];
        $this->assertArrayHasKey('Dashboard', $menu);
        $this->assertArrayHasKey('Users', $menu);
    }

    public function testFilterNavigationItemCollectionByRouteAccessibility(): void
    {
        // Arrange
        $navigationItemCollectionTransfer = $this->getNavigationWithoutFilterPlugins();
        $navigationItemCount = $navigationItemCollectionTransfer->getNavigationItems()->count();

        $this->provideNavigationItemCollectionFilterPlugins([new BackofficeNavigationItemCollectionFilterPlugin()]);
        $this->provideRouterFacade();

        $facade = $this->getFacadeWithCustomNavigationFile();

        // Act
        $navigationItemCollectionTransfer = $facade->filterNavigationItemCollectionByBackofficeRouteAccessibility($navigationItemCollectionTransfer);

        // Assert
        $this->assertEquals(2, $navigationItemCount);
        $this->assertEquals(1, $navigationItemCollectionTransfer->getNavigationItems()->count());
        $this->assertNotEquals($navigationItemCount, $navigationItemCollectionTransfer->getNavigationItems()->count());
    }

    /**
     * Mirrors the CC-38089 ACL scenario: parent endpoints are allowed, but every
     * child route used in the fixture is denied. `dashboard-widget-gui:index:index`
     * is the denied child of `<dashboard>`; `acl:role:index` and `acl:group:index`
     * are the denied children of the `<users>` container.
     */
    protected function buildAclDeniedChildrenFilterPlugin(): NavigationItemFilterPluginInterface
    {
        $deniedRouteKeys = [
            'dashboard-widget-gui:index:index',
            'acl:role:index',
            'acl:group:index',
        ];

        $plugin = $this->createMock(NavigationItemFilterPluginInterface::class);
        $plugin->method('isVisible')->willReturnCallback(
            static function (NavigationItemTransfer $navigationItemTransfer) use ($deniedRouteKeys): bool {
                $routeKey = sprintf(
                    '%s:%s:%s',
                    $navigationItemTransfer->getModule(),
                    $navigationItemTransfer->getController(),
                    $navigationItemTransfer->getAction(),
                );

                return !in_array($routeKey, $deniedRouteKeys, true);
            },
        );

        return $plugin;
    }

    protected function getNavigationWithoutFilterPlugins(): NavigationItemCollectionTransfer
    {
        $zedNavigationConfigMock = $this->buildZedNavigationConfigMock();
        $zedNavigationBusinessFactoryMock = $this->buildZedNavigationBusinessFactoryMock($zedNavigationConfigMock);

        $this->provideNavigationItemCollectionFilterPlugins([]);

        $navigationCollector = $zedNavigationBusinessFactoryMock->createNavigationCollector();
        $defaultNavigationType = $zedNavigationConfigMock->getDefaultNavigationType();
        $navigationItemCollectionTransfer = new NavigationItemCollectionTransfer();

        $navigation = $navigationCollector->getNavigation($defaultNavigationType);

        return $this->mapNavigationItemsToNavigationItemCollectionTransfer($navigation, $navigationItemCollectionTransfer);
    }
}
