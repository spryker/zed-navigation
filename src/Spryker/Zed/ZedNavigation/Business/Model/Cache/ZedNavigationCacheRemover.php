<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ZedNavigation\Business\Model\Cache;

use Spryker\Zed\ZedNavigation\ZedNavigationConfig;

class ZedNavigationCacheRemover
{
    /**
     * @var \Spryker\Zed\ZedNavigation\Business\Model\Cache\ZedNavigationCacheInterface
     */
    protected $navigationCache;

    /**
     * @var \Spryker\Zed\ZedNavigation\ZedNavigationConfig
     */
    protected $zedNavigationConfig;

    public function __construct(
        ZedNavigationCacheInterface $navigationCache,
        ZedNavigationConfig $zedNavigationConfig
    ) {
        $this->navigationCache = $navigationCache;
        $this->zedNavigationConfig = $zedNavigationConfig;
    }

    public function removeNavigationCache(): void
    {
        foreach ($this->zedNavigationConfig->getCacheFilePaths() as $cacheFileName) {
            $this->navigationCache->removeCache($cacheFileName);
        }
    }
}
