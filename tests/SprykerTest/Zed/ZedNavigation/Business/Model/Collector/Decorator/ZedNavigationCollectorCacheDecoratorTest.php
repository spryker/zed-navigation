<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ZedNavigation\Business\Model\Collector\Decorator;

use Spryker\Zed\ZedNavigation\Business\Model\Collector\Decorator\ZedNavigationCollectorCacheDecorator;
use SprykerTest\Zed\ZedNavigation\Business\ZedNavigationBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ZedNavigation
 * @group Business
 * @group Model
 * @group Collector
 * @group Decorator
 * @group ZedNavigationCollectorCacheDecoratorTest
 * Add your own group annotations below this line
 */
class ZedNavigationCollectorCacheDecoratorTest extends ZedNavigationBusinessTester
{
    /**
     * @var string
     */
    protected const NAVIGATION_TYPE_MAIN = 'main';

    public function testIfCacheIsNotEnabledGetNavigationMustReturnNavigationFromCollector(): void
    {
        //prepare
        $expectedNavigation = [['key' => 'value']];
        $navigationCacheMock = $this->getZedNavigationCacheMock();
        $navigationCollectorMock = $this->getZedNavigationCollectorMock();
        $configMock = $this->getZedNavigationConfigMock();
        $navigationCollectorCacheDecorator = new ZedNavigationCollectorCacheDecorator(
            $navigationCollectorMock,
            $navigationCacheMock,
            $configMock,
        );

        //assert
        $navigationCacheMock
            ->expects($this->never())
            ->method('getNavigation');
        $navigationCollectorMock
            ->expects($this->once())
            ->method('getNavigation')
            ->willReturn($expectedNavigation);
        $configMock
            ->expects($this->once())
            ->method('isNavigationCacheEnabled')
            ->willReturn(false);

        //act
        $navigation = $navigationCollectorCacheDecorator->getNavigation(static::NAVIGATION_TYPE_MAIN);

        //assert
        $this->assertSame(
            $expectedNavigation,
            $navigation,
        );
    }

    public function testIfCacheIsEnabledGetNavigationMustReturnNavigationFromCache(): void
    {
        //prepare
        $expectedNavigation = [['key' => 'value']];
        $navigationCacheMock = $this->getZedNavigationCacheMockWithReturn($expectedNavigation);
        $navigationCollectorMock = $this->getZedNavigationCollectorMock();
        $configMock = $this->getZedNavigationConfigMock();
        $navigationCollectorCacheDecorator = new ZedNavigationCollectorCacheDecorator(
            $navigationCollectorMock,
            $navigationCacheMock,
            $configMock,
        );

        //assert
        $navigationCollectorMock
            ->expects($this->never())
            ->method('getNavigation');
        $configMock
            ->expects($this->once())
            ->method('isNavigationCacheEnabled')
            ->willReturn(true);

        //act
        $navigation = $navigationCollectorCacheDecorator->getNavigation(static::NAVIGATION_TYPE_MAIN);

        //assert
        $this->assertSame(
            $expectedNavigation,
            $navigation,
        );
    }

    public function testReturnsCollectedNavigationWhenCacheIsEnabledButCacheDoesNotExists(): void
    {
        //prepare
        $expectedNavigation = [['key' => 'value']];
        $navigationCacheMock = $this->getZedNavigationCacheMockWithReturn($expectedNavigation, false);
        $navigationCollectorMock = $this->getZedNavigationCollectorMock();
        $configMock = $this->getZedNavigationConfigMock();
        $navigationCollectorCacheDecorator = new ZedNavigationCollectorCacheDecorator(
            $navigationCollectorMock,
            $navigationCacheMock,
            $configMock,
        );

        //assert
        $configMock
            ->expects($this->once())
            ->method('isNavigationCacheEnabled')
            ->willReturn(true);
        $navigationCacheMock
            ->expects($this->once())
            ->method('setNavigation')
            ->with($this->equalTo($expectedNavigation));
        $navigationCollectorMock
            ->expects($this->once())
            ->method('getNavigation')
            ->willReturn($expectedNavigation);

        //act
        $navigation = $navigationCollectorCacheDecorator->getNavigation(static::NAVIGATION_TYPE_MAIN);

        //assert
        $this->assertSame(
            $expectedNavigation,
            $navigation,
        );
    }
}
