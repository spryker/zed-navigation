<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ZedNavigation\Business\Model\Merger;

interface NavigationDefinitionMergerInterface
{
    /**
     * @param array<mixed> $navigationDefinition
     * @param array<mixed> $navigationDefinitionToMerge
     *
     * @return array<mixed>
     */
    public function merge(array $navigationDefinition, array $navigationDefinitionToMerge): array;
}
