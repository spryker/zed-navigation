<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ZedNavigation\Business\Resolver;

use Spryker\Zed\ZedNavigation\Business\Strategy\NavigationMergeStrategyInterface;
use Spryker\Zed\ZedNavigation\ZedNavigationConfig;

class MergeNavigationStrategyResolver implements MergeNavigationStrategyResolverInterface
{
    /**
     * @var \Spryker\Zed\ZedNavigation\Business\Strategy\NavigationMergeStrategyInterface[]
     */
    protected $navigationMergeStrategies;

    /**
     * @var \Spryker\Zed\ZedNavigation\ZedNavigationConfig
     */
    protected $zedNavigationConfig;

    /**
     * @param \Spryker\Zed\ZedNavigation\Business\Strategy\NavigationMergeStrategyInterface[] $navigationMergeStrategies
     * @param \Spryker\Zed\ZedNavigation\ZedNavigationConfig $zedNavigationConfig
     */
    public function __construct(array $navigationMergeStrategies, ZedNavigationConfig $zedNavigationConfig)
    {
        $this->navigationMergeStrategies = $navigationMergeStrategies;
        $this->zedNavigationConfig = $zedNavigationConfig;
    }

    /**
     * @param string $mergeStrategy
     *
     * @return \Spryker\Zed\ZedNavigation\Business\Strategy\NavigationMergeStrategyInterface|null
     */
    public function resolve(string $mergeStrategy): ?NavigationMergeStrategyInterface
    {
        foreach ($this->navigationMergeStrategies as $navigationMergeStrategy) {
            if ($navigationMergeStrategy->getMergeStrategy() === $this->zedNavigationConfig->getMergeStrategy()) {
                return $navigationMergeStrategy;
            }
        }

        return null;
    }
}