<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ZedNavigation\Business\Model\Cache;

interface ZedNavigationCacheInterface
{
    /**
     * @return bool
     */
    public function isEnabled();

    public function setNavigation(array $navigation, string $cacheFilePath): void;

    public function getNavigation(string $cacheFilePath): array;

    public function hasContent(string $cacheFilePath): bool;

    public function removeCache(string $cacheFilePath): void;
}
