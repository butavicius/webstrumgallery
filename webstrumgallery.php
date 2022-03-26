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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class WebstrumGallery extends Module
{
    private ModuleInstaller $installer;

    public function __construct()
    {
        $this->name = 'webstrumgallery';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Simas Butavičius';
        $this->need_instance = 0;
        $this->bootstrap = false;

        parent::__construct();

        $this->displayName = $this->l('Webstrum Gallery');
        $this->description = $this->l('This module will display an additional gallery in product page.');

        $this->ps_versions_compliancy = array('min' => '1.7.7.0', 'max' => _PS_VERSION_);

        $this->installer = new ModuleInstaller($this);
    }

    public function install()
    {
        if (!parent::install())
            return false;

        return $this->installer->install($this);
    }

    public function uninstall()
    {
        if (!parent::uninstall())
            return false;

        return $this->installer->uninstall($this);
    }

    // TODO:
    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */

    // TODO: Looks like addJS and addCSS are deprecated. Change to
    // registerStyleSheet, registerJavaScript. We will probably include some JS
    // image slider/gallery to render images on FO.

    // public function hookHeader()
    // {
    //     $this->context->controller->addJS($this->_path . '/views/js/front.js');
    //     $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    // }

    public function hookDisplayAdminProductsMainStepLeftColumnBottom($context)
    {
        // TODO:

        // Instead of uploading to /img/p as product normally does, upload to /modules/webstrumgallery/img/{productId}/{imgId}
        // Figure out how to use same thumbnail generation tools as per original product gallery

        $productId = $context['id_product'];
        return $this->get('twig')->render('@Modules/webstrumgallery/views/templates/hook/imageuploadform.html.twig', ['productId' => $productId, 'images' => [], 'editable' => true]);
    }
}
