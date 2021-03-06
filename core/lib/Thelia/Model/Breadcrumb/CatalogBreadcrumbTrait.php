<?php

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Thelia\Model\Breadcrumb;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;
use Thelia\Core\Template\Loop\CategoryPath;
use Thelia\Core\Translation\Translator;
use Thelia\Tools\URL;

trait CatalogBreadcrumbTrait
{
    public function getBaseBreadcrumb(Router $router, ContainerInterface $container, $categoryId)
    {
        $translator = Translator::getInstance();
        $catalogUrl = $router->generate('admin.catalog', [], Router::ABSOLUTE_URL);
        $breadcrumb = [
            $translator->trans('Home') => URL::getInstance()->absoluteUrl('/admin'),
            $translator->trans('Catalog') => $catalogUrl,
        ];

        // Todo stop using loop in php
        $categoryPath = new CategoryPath(
            $container,
            $container->get('request_stack'),
            $container->get('event_dispatcher'),
            $container->get('thelia.securityContext'),
            Translator::getInstance(),
            $container->getParameter('Thelia.parser.loops'),
            $container->getParameter('kernel.environment')
        );
        $categoryPath->initializeArgs([
                'category' => $categoryId,
                'visible' => '*',
            ]);

        $results = $categoryPath->buildArray();

        foreach ($results as $result) {
            $breadcrumb[$result['TITLE']] = sprintf('%s?category_id=%d', $catalogUrl, $result['ID']);
        }

        return $breadcrumb;
    }

    public function getProductBreadcrumb(Router $router, ContainerInterface $container, $tab, $locale)
    {
        if (!method_exists($this, 'getProduct')) {
            return null;
        }

        /** @var \Thelia\Model\Product $product */
        $product = $this->getProduct();

        $breadcrumb = $this->getBaseBreadcrumb($router, $container, $product->getDefaultCategoryId());

        $product->setLocale($locale);

        $breadcrumb[$product->getTitle()] = sprintf(
            '%s?product_id=%d&current_tab=%s',
            $router->generate('admin.products.update', [], Router::ABSOLUTE_URL),
            $product->getId(),
            $tab
        );

        return $breadcrumb;
    }

    public function getCategoryBreadcrumb(Router $router, ContainerInterface $container, $tab, $locale)
    {
        if (!method_exists($this, 'getCategory')) {
            return null;
        }

        /** @var \Thelia\Model\Category $category */
        $category = $this->getCategory();
        $breadcrumb = $this->getBaseBreadcrumb($router, $container, $this->getParentId());

        $category->setLocale($locale);

        $breadcrumb[$category->getTitle()] = sprintf(
            '%s?category_id=%d&current_tab=%s',
            $router->generate(
                'admin.categories.update',
                [],
                Router::ABSOLUTE_URL
            ),
            $category->getId(),
            $tab
        );

        return $breadcrumb;
    }
}
