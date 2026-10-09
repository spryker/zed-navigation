<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ZedNavigation\Business\Strategy;

use Spryker\Zed\ZedNavigation\Business\Model\Merger\NavigationDefinitionMergerInterface;
use Spryker\Zed\ZedNavigation\ZedNavigationConfig;

class NavigationFullMergeStrategy implements NavigationMergeStrategyInterface
{
    public function __construct(
        protected NavigationDefinitionMergerInterface $navigationDefinitionMerger
    ) {
    }

    public function getMergeStrategy(): string
    {
        return ZedNavigationConfig::FULL_MERGE_STRATEGY;
    }

    /**
     * @param array<mixed> $navigationDefinition
     * @param array<mixed> $rootDefinition
     * @param array<mixed> $coreNavigationDefinition
     *
     * @return array<mixed>
     */
    public function mergeNavigation(
        array $navigationDefinition,
        array $rootDefinition,
        array $coreNavigationDefinition
    ): array {
        return $this->navigationDefinitionMerger->merge($navigationDefinition, $rootDefinition);
    }
}
