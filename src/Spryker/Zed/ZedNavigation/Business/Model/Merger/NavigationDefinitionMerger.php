<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ZedNavigation\Business\Model\Merger;

/**
 * Reproduces `Laminas\Config\Config::merge()`, which navigation definitions were merged with before
 * `laminas/laminas-config` was dropped: string keys recurse or overwrite, integer keys append.
 *
 * `array_merge_recursive()` is NOT equivalent - it turns two scalars under the same string key into
 * a list instead of letting the later one win, which would corrupt every overridden navigation
 * label.
 */
class NavigationDefinitionMerger implements NavigationDefinitionMergerInterface
{
    /**
     * @param array<mixed> $navigationDefinition
     * @param array<mixed> $navigationDefinitionToMerge
     *
     * @return array<mixed>
     */
    public function merge(array $navigationDefinition, array $navigationDefinitionToMerge): array
    {
        foreach ($navigationDefinitionToMerge as $key => $value) {
            if (!array_key_exists($key, $navigationDefinition)) {
                $navigationDefinition[$key] = $value;

                continue;
            }

            if (is_int($key)) {
                $navigationDefinition[] = $value;

                continue;
            }

            if (is_array($value) && is_array($navigationDefinition[$key])) {
                $navigationDefinition[$key] = $this->merge($navigationDefinition[$key], $value);

                continue;
            }

            $navigationDefinition[$key] = $value;
        }

        return $navigationDefinition;
    }
}
