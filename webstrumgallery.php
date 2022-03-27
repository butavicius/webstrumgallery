<?php

/**
 * 2007-2022 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2022 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

declare(strict_types=1);

use WebstrumGallery\Service\ModuleInstaller;
use WebstrumGallery\Service\ImageService;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class WebstrumGallery extends Module
{
    private ModuleInstaller $installer;
    private ImageService $imageService;

    // TODO: How does the autowiring work here if WebstrumGallery is not in services.yml ?
    public function __construct()
    {
        $this->name = 'webstrumgallery';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Simas Butavičius';
        $this->need_instance = 0;
        $this->bootstrap = false;
        $this->confirmUninstall = "This will remove all module images permanently.";

        parent::__construct();

        $this->displayName = $this->l('Webstrum Gallery');
        $this->description = $this->l('This module will display an additional gallery in product page.');

        $this->ps_versions_compliancy = array('min' => '1.7.7.0', 'max' => _PS_VERSION_);
        $this->installer = new ModuleInstaller();
        $this->imageService = $this->get('webstrum_gallery.service.image_service');
    }

    public function install()
    {
        if (!parent::install())
            return false;

        dump($this->installer);

        return $this->installer->install($this);
    }

    public function uninstall()
    {
        if (!parent::uninstall())
            return false;

        dump($this->installer);

        return $this->installer->uninstall($this);
    }

    /**
     * Displays image upload form template
     */
    public function hookDisplayAdminProductsMainStepLeftColumnBottom($context)
    {
        $productId = $context['id_product'];
        $images = $this->imageService->getProductImages((int) $productId);

        return $this
            ->get('twig')
            ->render('@Modules/webstrumgallery/views/templates/hook/imageuploadform.html.twig', ['productId' => $productId, 'images' => $images]);
    }

    /**
     * Displays image gallery template
     */
    public function hookDisplayFooterProduct($context)
    {
        $productId = $context['product']->id;
        $images = $this->imageService->getProductImages($productId);
        $this->context->smarty->assign(['images' => $images]);

        return $this->display(__FILE__, 'views/templates/hook/imagegallery.tpl');
    }

    /**
     * Registers CSS and JS for front office
     */
    public function hookActionFrontControllerSetMedia() {
        $this->context->controller->registerStylesheet(
            'webstrum-gallery-style',
            $this->_path.'views/css/splide.min.css',
            [
                'media' => 'all',
                'priority' => 1000,
            ]
        );

        $this->context->controller->registerJavascript(
            'webstrum-gallery-splide',
            $this->_path.'views/js/splide.min.js',
            [
                'position' => 'bottom',
                'priority' => 1000,
            ]
        );

        $this->context->controller->registerJavascript(
            'webstrum-gallery-front',
            $this->_path.'views/js/front.js',
            [
                'position' => 'bottom',
                'priority' => 999,
            ]
        );
    }
}
