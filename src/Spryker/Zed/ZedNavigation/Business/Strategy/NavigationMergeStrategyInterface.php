<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ZedNavigation\Business\Strategy;

interface NavigationMergeStrategyInterface
{
    public function getMergeStrategy(): string;

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
    ): array;
}
