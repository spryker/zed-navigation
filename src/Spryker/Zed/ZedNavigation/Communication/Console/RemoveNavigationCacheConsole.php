<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ZedNavigation\Communication\Console;

use Spryker\Zed\Kernel\Communication\Console\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \Spryker\Zed\ZedNavigation\Business\ZedNavigationFacadeInterface getFacade()
 * @method \Spryker\Zed\ZedNavigation\Communication\ZedNavigationCommunicationFactory getFactory()
 */
class RemoveNavigationCacheConsole extends Console
{
    /**
     * @var string
     */
    public const COMMAND_NAME = 'navigation:cache:remove';

    /**
     * @var string
     */
    public const DESCRIPTION = 'Removes the navigation cache';

    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();
        $this->setName(static::COMMAND_NAME);
        $this->setDescription(static::DESCRIPTION);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getMessenger()->info('Remove navigation cache');
        $this->getFacade()->removeNavigationCache();

        return static::CODE_SUCCESS;
    }
}
