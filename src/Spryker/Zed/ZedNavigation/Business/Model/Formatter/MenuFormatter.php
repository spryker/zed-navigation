<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ZedNavigation\Business\Model\Formatter;

use Spryker\Shared\Url\UrlBuilderInterface;
use Spryker\Zed\ZedNavigation\Business\Exception\ZedNavigationXmlException;
use Spryker\Zed\ZedNavigation\Business\Model\Validator\MenuLevelValidatorInterface;
use Spryker\Zed\ZedNavigation\Business\Model\Validator\UrlUniqueValidatorInterface;

class MenuFormatter implements MenuFormatterInterface
{
    /**
     * @var string
     */
    public const VISIBLE = 'visible';

    /**
     * @var string
     */
    public const URI = 'uri';

    /**
     * @var string
     */
    public const ID = 'id';

    /**
     * @var string
     */
    public const ATTRIBUTES = 'attributes';

    /**
     * @var string
     */
    public const LABEL = 'label';

    /**
     * @var string
     */
    public const PAGES = 'pages';

    /**
     * @var string
     */
    public const CONTROLLER = 'controller';

    /**
     * @var string
     */
    public const INDEX = 'index';

    /**
     * @var string
     */
    public const ACTION = 'action';

    /**
     * @var string
     */
    public const BUNDLE = 'bundle';

    /**
     * @var string
     */
    public const CHILDREN = 'children';

    /**
     * @var string
     */
    public const TITLE = 'title';

    /**
     * @var string
     */
    public const ICON = 'icon';

    /**
     * @var string
     */
    public const SHORTCUT = 'shortcut';

    /**
     * @var string
     */
    public const IS_ACTIVE = 'is_active';

    /**
     * @var string
     */
    public const CHILD_IS_ACTIVE = 'child_is_active';

    /**
     * @var string
     */
    protected const TYPE = 'type';

    /**
     * @var \Spryker\Zed\ZedNavigation\Business\Model\Validator\UrlUniqueValidatorInterface
     */
    protected $urlUniqueValidator;

    /**
     * @var \Spryker\Zed\ZedNavigation\Business\Model\Validator\MenuLevelValidatorInterface
     */
    protected $menuLevelValidator;

    /**
     * @var \Spryker\Shared\Url\UrlBuilderInterface
     */
    protected $urlBuilder;

    /**
     * @param \Spryker\Zed\ZedNavigation\Business\Model\Validator\UrlUniqueValidatorInterface $urlUniqueValidator
     * @param \Spryker\Zed\ZedNavigation\Business\Model\Validator\MenuLevelValidatorInterface $menuLevelValidator
     * @param \Spryker\Shared\Url\UrlBuilderInterface $urlBuilder
     */
    public function __construct(
        UrlUniqueValidatorInterface $urlUniqueValidator,
        MenuLevelValidatorInterface $menuLevelValidator,
        UrlBuilderInterface $urlBuilder
    ) {
        $this->urlUniqueValidator = $urlUniqueValidator;
        $this->menuLevelValidator = $menuLevelValidator;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param array $pages
     * @param string $pathInfo
     * @param bool $includeInvisible
     *
     * @return array
     */
    public function formatMenu(array $pages, $pathInfo, $includeInvisible = false)
    {
        $formattedPages = $this->formatPages($pages, $pathInfo, 1, $includeInvisible);
        unset($formattedPages[self::CHILD_IS_ACTIVE]);

        return $formattedPages;
    }

    /**
     * @param array $pages
     * @param string $pathInfo
     * @param int $currentLevel
     * @param bool $includeInvisible
     *
     * @return array
     */
    protected function formatPages(array $pages, $pathInfo, $currentLevel = 1, $includeInvisible = false)
    {
        $formattedPages = [];
        $currentLevel++;

        foreach ($pages as $page) {
            $formattedPage = $this->formatPage($page);

            if (isset($page[self::PAGES]) && !empty($page[self::PAGES])) {
                $this->menuLevelValidator->validate($currentLevel, $formattedPage[self::TITLE]);
                $children = $this->formatPages($page[self::PAGES], $pathInfo, $currentLevel, $includeInvisible);
            }

            if (isset($children[self::CHILD_IS_ACTIVE]) || $pathInfo === $formattedPage[self::URI]) {
                $formattedPages[self::CHILD_IS_ACTIVE] = true;
                $formattedPage[self::IS_ACTIVE] = true;
            }

            if (!empty($children)) {
                unset($children[self::CHILD_IS_ACTIVE]);
                if (!empty($children)) {
                    $formattedPage[self::CHILDREN] = $children;
                }
                $children = [];
            }

            if ($includeInvisible) {
                $formattedPages[$formattedPage[self::TITLE]] = $formattedPage;
            } elseif (!isset($page[self::VISIBLE]) || (isset($page[self::VISIBLE]) && $page[self::VISIBLE])) {
                $formattedPages[$formattedPage[self::TITLE]] = $formattedPage;
            }
        }

        return $formattedPages;
    }

    /**
     * @param array $page
     *
     * @return string|null
     */
    protected function getUri(array $page)
    {
        if (isset($page[self::URI]) && !empty($page[self::URI])) {
            return $page[self::URI];
        }

        $action = $this->getPageAction($page);
        $controller = $this->getPageController($page, $action);

        if (!isset($page[self::BUNDLE])) {
            return null;
        }

        return $this->urlBuilder->build($page[self::BUNDLE], $controller, $action);
    }

    /**
     * @param array $page
     *
     * @return array
     */
    protected function formatPage(array $page)
    {
        $formattedPage = [];

        $url = $this->getUri($page);
        $formattedPage[self::URI] = $url;
        $label = $this->getPageLabel($page);
        $title = $this->getPageTitle($page);
        $pageTitleAndLabel = $this->formatTitleAndLabel($label, $title);
        $formattedPage = array_merge($formattedPage, $pageTitleAndLabel);

        if (isset($page[self::ICON])) {
            $formattedPage[self::ICON] = $page[self::ICON];
        }

        if (isset($page[self::SHORTCUT]) && strlen($page[self::SHORTCUT]) === 1) {
            $formattedPage[self::SHORTCUT] = $page[self::SHORTCUT];
        }

        $formattedPage[static::TYPE] = $page[static::TYPE] ?? null;

        return $formattedPage;
    }

    /**
     * @param string|null $label
     * @param string|null $title
     *
     * @throws \Spryker\Zed\ZedNavigation\Business\Exception\ZedNavigationXmlException
     *
     * @return array
     */
    protected function formatTitleAndLabel($label, $title)
    {
        if ($label === null && $title === null) {
            throw new ZedNavigationXmlException('"label" or "title" is missing for navigation menu item');
        }

        return [
            self::LABEL => $label ?? $title,
            self::TITLE => $title ?? $label,
        ];
    }

    /**
     * @param array $page
     *
     * @return string|null
     */
    protected function getPageLabel(array $page)
    {
        return $page[self::LABEL] ?? null;
    }

    /**
     * @param array $page
     *
     * @return string|null
     */
    protected function getPageTitle(array $page)
    {
        return $page[self::TITLE] ?? null;
    }

    /**
     * @param array $page
     *
     * @return string|null
     */
    protected function getPageAction(array $page)
    {
        $pageAction = null;
        if (isset($page[self::ACTION]) && $page[self::ACTION] !== self::INDEX) {
            $pageAction = $page[self::ACTION];
        }

        return $pageAction;
    }

    /**
     * @param array $page
     * @param string|null $action
     *
     * @return string|null
     */
    protected function getPageController(array $page, $action)
    {
        $pageController = null;
        if (
            isset($page[self::CONTROLLER]) &&
            (
                $page[self::CONTROLLER] !== self::INDEX || $action !== null
            )
        ) {
            $pageController = $page[self::CONTROLLER];
        }

        return $pageController;
    }
}
