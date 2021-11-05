<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ZedNavigation\Business\Model\Extractor;

use Spryker\Zed\ZedNavigation\Business\Model\Formatter\MenuFormatter;

class PathExtractor implements PathExtractorInterface
{
    /**
     * @var string
     */
    public const URI = 'uri';

    /**
     * @var string
     */
    public const LABEL = 'label';

    /**
     * @var string
     */
    public const TITLE = 'title';

    /**
     * @param array $menu
     *
     * @return array
     */
    public function extractPathFromMenu(array $menu)
    {
        $filteredMenu = array_filter($menu, function ($branch) {
            return isset($branch[MenuFormatter::IS_ACTIVE]);
        });
        $path = [];
        $this->extractActiveNodes($filteredMenu, $path);

        return $path;
    }

    /**
     * @param array $nodes
     * @param array $path
     *
     * @return void
     */
    protected function extractActiveNodes(array $nodes, array &$path)
    {
        foreach ($nodes as $child) {
            if (isset($child[MenuFormatter::IS_ACTIVE])) {
                $activeNode = $child;
                $path[] = $this->formatNode($activeNode);
                if (isset($child[MenuFormatter::CHILDREN])) {
                    $this->extractActiveNodes($child[MenuFormatter::CHILDREN], $path);
                }

                break;
            }
        }
    }

    /**
     * @param array $node
     *
     * @return array
     */
    protected function formatNode(array $node)
    {
        return [
            static::URI => $node[MenuFormatter::URI],
            static::LABEL => $node[MenuFormatter::LABEL],
            static::TITLE => $node[MenuFormatter::TITLE],
        ];
    }
}
