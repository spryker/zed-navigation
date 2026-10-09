<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ZedNavigation\Business\Model\Collector;

use ErrorException;
use Spryker\Service\UtilEncoding\Exception\XmlReadException;
use Spryker\Zed\ZedNavigation\Business\Model\Merger\NavigationDefinitionMergerInterface;
use Spryker\Zed\ZedNavigation\Business\Model\SchemaFinder\ZedNavigationSchemaFinderInterface;
use Spryker\Zed\ZedNavigation\Business\Resolver\MergeNavigationStrategyResolverInterface;
use Spryker\Zed\ZedNavigation\Dependency\Util\ZedNavigationToUtilEncodingInterface;
use Spryker\Zed\ZedNavigation\ZedNavigationConfig;

class ZedNavigationCollector implements ZedNavigationCollectorInterface
{
    /**
     * @var \Spryker\Zed\ZedNavigation\Business\Model\SchemaFinder\ZedNavigationSchemaFinderInterface
     */
    protected $navigationSchemaFinder;

    /**
     * @var \Spryker\Zed\ZedNavigation\Business\Resolver\MergeNavigationStrategyResolverInterface
     */
    protected $mergeNavigationStrategyResolver;

    /**
     * @var \Spryker\Zed\ZedNavigation\ZedNavigationConfig
     */
    protected $zedNavigationConfig;

    /**
     * @var array|null
     */
    protected $navigationDefinition;

    public function __construct(
        ZedNavigationSchemaFinderInterface $navigationSchemaFinder,
        MergeNavigationStrategyResolverInterface $mergeNavigationStrategyResolver,
        ZedNavigationConfig $zedNavigationConfig,
        protected ZedNavigationToUtilEncodingInterface $utilEncodingService,
        protected NavigationDefinitionMergerInterface $navigationDefinitionMerger
    ) {
        $this->navigationSchemaFinder = $navigationSchemaFinder;
        $this->mergeNavigationStrategyResolver = $mergeNavigationStrategyResolver;
        $this->zedNavigationConfig = $zedNavigationConfig;
    }

    /**
     * A missing or unreadable root navigation schema is not an error: the navigation is then built
     * from the module schemas alone. Previously a blanket `catch (Exception)` around the file read
     * also absorbed the undefined-key warning for an unconfigured navigation type, so both cases
     * are now handled explicitly instead.
     *
     * @param string $navigationType
     *
     * @return array<mixed>
     */
    protected function readRootNavigationDefinition(string $navigationType): array
    {
        $rootNavigationSchemaPaths = $this->zedNavigationConfig->getRootNavigationSchemaPaths();
        if (!isset($rootNavigationSchemaPaths[$navigationType])) {
            return [];
        }

        try {
            return $this->utilEncodingService->convertXmlFileToArray($rootNavigationSchemaPaths[$navigationType]);
        } catch (XmlReadException $exception) {
            return [];
        }
    }

    /**
     * @param string $navigationType
     *
     * @throws \ErrorException
     *
     * @return array
     */
    public function getNavigation(string $navigationType): array
    {
        if ($this->navigationDefinition !== null) {
            return $this->navigationDefinition;
        }

        $navigationDefinition = $this->readRootNavigationDefinition($navigationType);
        $rootDefinition = $navigationDefinition;

        $coreNavigationDefinition = [];
        $fileNamePattern = $this->zedNavigationConfig->getNavigationSchemaFileNamePatterns()[$navigationType];
        foreach ($this->navigationSchemaFinder->getSchemaFiles($fileNamePattern) as $moduleNavigationFile) {
            if (!file_exists($moduleNavigationFile->getPathname())) {
                throw new ErrorException('Navigation-File does not exist: ' . $moduleNavigationFile);
            }
            $definitionFromFile = $this->utilEncodingService->convertXmlFileToArray($moduleNavigationFile->getPathname());
            $navigationDefinition = $this->navigationDefinitionMerger->merge($navigationDefinition, $definitionFromFile);
            $coreNavigationDefinition = $this->navigationDefinitionMerger->merge($coreNavigationDefinition, $definitionFromFile);
        }

        $navigationMergeStrategy = $this->mergeNavigationStrategyResolver->resolve();

        return $navigationMergeStrategy->mergeNavigation(
            $navigationDefinition,
            $rootDefinition,
            $coreNavigationDefinition,
        );
    }
}
