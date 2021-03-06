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
        $this->version = '0.1.0';
        $this->author = 'Simas Butavičius';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->confirmUninstall = $this->l('This will remove all module images permanently.');

        parent::__construct();

        $this->displayName = $this->l('Webstrum Gallery');
        $this->description = $this->l('This module will display an additional gallery in product page.');

        $this->ps_versions_compliancy = array('min' => '1.7.7.0', 'max' => _PS_VERSION_);

        $this->installer = new ModuleInstaller();
    }

    public function install()
    {
        if (!parent::install())
            return false;

        Configuration::updateValue('WEBSTRUMGALLERY_TITLE', 'Webstrum Gallery');
        Configuration::updateValue('WEBSTRUMGALLERY_COLOR', '#ffd700');
        Configuration::updateValue('WEBSTRUMGALLERY_CORNERS', '1rem');

        return $this->installer->install($this);
    }

    public function uninstall()
    {
        if (!parent::uninstall())
            return false;

        Configuration::deleteByName('WEBSTRUMGALLERY_TITLE');
        Configuration::deleteByName('WEBSTRUMGALLERY_COLOR');
        Configuration::deleteByName('WEBSTRUMGALLERY_CORNERS');

        return $this->installer->uninstall($this);
    }

    /**
     * Displays image upload form in Back Office
     */
    public function hookDisplayAdminProductsMainStepLeftColumnBottom($context)
    {

        $imageService = $this->get('webstrum_gallery.service.image_service');
        $productId = $context['id_product'];
        $images = $imageService->getProductImages((int) $productId);

        $galleryColor = Configuration::get('WEBSTRUMGALLERY_COLOR');
        $galleryTitle = Configuration::get('WEBSTRUMGALLERY_TITLE');


        return $this
            ->get('twig')
            ->render(
                '@Modules/webstrumgallery/views/templates/hook/imageuploadform.html.twig',
                [
                    'productId' => $productId,
                    'images' => $images,
                    'galleryColor' => $galleryColor,
                    'galleryTitle' => $galleryTitle,
                ]
            );
    }

    /**
     * Displays image gallery in Front Office
     */
    public function hookDisplayFooterProduct($context)
    {

        $imageService = $this->get('webstrum_gallery.service.image_service');
        $productId = $context['product']->id;
        $images = $imageService->getProductImages($productId);

        $galleryColor = Configuration::get('WEBSTRUMGALLERY_COLOR');
        $galleryTitle = Configuration::get('WEBSTRUMGALLERY_TITLE');
        $galleryCorners = Configuration::get('WEBSTRUMGALLERY_CORNERS');

        $this->context->smarty->assign(
            [
                'images' => $images,
                'galleryColor' => $galleryColor,
                'galleryTitle' => $galleryTitle,
                'galleryCorners' => $galleryCorners,
            ]
        );

        return $this->display(__FILE__, 'views/templates/hook/imagegallery.tpl');
    }

    /**
     * Registers CSS for Back Office
     */
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path . 'views/css/back.css');
    }

    /**
     * Registers CSS and JS for Front Office
     */
    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'webstrum-gallery-splide-css',
            $this->_path . 'views/css/splide.min.css',
            [
                'media' => 'all',
                'priority' => 1000,
            ]
        );

        $this->context->controller->registerJavascript(
            'webstrum-gallery-splide-js',
            $this->_path . 'views/js/splide.min.js',
            [
                'position' => 'bottom',
                'priority' => 1000,
            ]
        );

        $this->context->controller->registerJavascript(
            'webstrum-gallery-front-js',
            $this->_path . 'views/js/front.js',
            [
                'position' => 'bottom',
                'priority' => 999,
            ]
        );
    }

    /**
     * Deletes images when product is deleted
     */
    public function hookActionProductDelete($context)
    {
        $imageService = $this->get('webstrum_gallery.service.image_service');
        $productId = $context['id_product'];

        $imageService->deleteProductImages($productId);
    }

    // TODO: Consider extracting configuration page form generation to separate class
    /**
     * This method handles the module's configuration page
     * @return string The page's HTML content 
     */
    public function getContent()
    {
        $output = '';

        // this part is executed only when the form is submitted
        if (Tools::isSubmit('submit' . $this->name)) {
            // retrieve the value set by the user
            $color = (string) Tools::getValue('WEBSTRUMGALLERY_COLOR');
            $title = (string) Tools::getValue('WEBSTRUMGALLERY_TITLE');
            $corners = (string) Tools::getValue('WEBSTRUMGALLERY_CORNERS');

            // check that the value is valid
            if (empty($title)) {
                // invalid value, show an error
                $output = $this->displayError($this->l('Invalid title. Must not be empty.'));
            } else {
                // value is ok, update it and display a confirmation message
                Configuration::updateValue('WEBSTRUMGALLERY_COLOR', $color);
                Configuration::updateValue('WEBSTRUMGALLERY_TITLE', $title);
                Configuration::updateValue('WEBSTRUMGALLERY_CORNERS', $corners);
                $output = $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        // display any message, then the form
        return $output . $this->displayConfigurationForm();
    }

    /**
     * Builds the configuration form
     * @return string HTML code
     */
    public function displayConfigurationForm()
    {
        // Init Fields form array
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Gallery Title'),
                        'name' => 'WEBSTRUMGALLERY_TITLE',
                        'required' => true,
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->l('Gallery Color'),
                        'name' => 'WEBSTRUMGALLERY_COLOR',
                        'class' => 'wg-colorpicker',
                        'required' => true,
                    ],
                    [
                        'type' => 'radio',
                        'label' => $this->l('Gallery corners'),
                        'label' => $this->l('Do you want the gallery to have rounded or sharp corners?'),
                        'name' => 'WEBSTRUMGALLERY_CORNERS',
                        'required' => true,
                        'values' => [
                            [
                                'id' => '0',
                                'value' => '0',
                                'label' => $this->l('Sharp'),
                            ],
                            [
                                'id' => '1rem',
                                'value' => '1rem',
                                'label' => $this->l('Rounded'),
                            ],
                            [
                                'id' => '2rem',
                                'value' => '2rem',
                                'label' => $this->l('Very round'),
                            ],
                        ]
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;

        // Default language
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');

        // Load current value into the form
        $helper->fields_value['WEBSTRUMGALLERY_TITLE'] = Tools::getValue('WEBSTRUMGALLERY_TITLE', Configuration::get('WEBSTRUMGALLERY_TITLE'));
        $helper->fields_value['WEBSTRUMGALLERY_COLOR'] = Tools::getValue('WEBSTRUMGALLERY_COLOR', Configuration::get('WEBSTRUMGALLERY_COLOR'));
        $helper->fields_value['WEBSTRUMGALLERY_CORNERS'] = Tools::getValue('WEBSTRUMGALLERY_CORNERS', Configuration::get('WEBSTRUMGALLERY_CORNERS'));

        return $helper->generateForm([$form]);
    }
}
