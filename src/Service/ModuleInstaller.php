<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

declare(strict_types=1);

namespace WebstrumGallery\Service;

use Db;
use Module;
use Symfony\Component\Filesystem\Filesystem;

class ModuleInstaller
{
    // TODO: Extract path constant to some config file 
    // (there is duplication in ImageService class)
    private $uploadFolder = _PS_MODULE_DIR_ . "webstrumgallery/uploads";
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * Install module.
     */
    public function install(Module $module): bool
    {
        if (!$this->registerHooks($module))
            return false;

        if (!$this->installDatabase())
            return false;

        $this->createUploadDir();

        return true;
    }

    /**
     * Uninstall module.
     */
    public function uninstall(): bool
    {
        $this->removeUploadDir();
        return $this->uninstallDatabase();
    }

    /**
     * Register hooks for the module.
     */
    private function registerHooks(Module $module): bool
    {
        $hooks = [
            'displayAdminProductsMainStepLeftColumnBottom',
            'displayFooterProduct',
            'actionFrontControllerSetMedia',
            'displayBackOfficeHeader',
            'actionProductDelete'
        ];

        return $module->registerHook($hooks);
    }

    /**
     * Create database tables for the module.
     */
    private function installDatabase(): bool
    {
        $queries = [
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'webstrum_gallery_image`
            (
                `id_wg_image` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_product` int(10) UNSIGNED NOT NULL,
                `filename` varchar(64) NOT NULL,
                `position` int(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`id_wg_image`),
                FOREIGN KEY (`id_product`)
                  REFERENCES `' . _DB_PREFIX_ . 'product`(`id_product`)
                  ON DELETE CASCADE
            )   ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;',
        ];

        return $this->executeQueries($queries);
    }

    /**
     * Delete module's database tables.
     */
    private function uninstallDatabase(): bool
    {
        $queries = [
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'webstrum_gallery_image`;',
        ];

        return $this->executeQueries($queries);
    }

    /**
     * A helper that executes multiple database queries.
     */
    private function executeQueries(array $queries): bool
    {
        foreach ($queries as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    // TODO: Refactor error handling. What if access is denied?
    /**
     * Creates upload folder
     */
    private function createUploadDir()
    {
        $this->filesystem->mkdir($this->uploadFolder);
    }

    /**
     * Removes upload folder
     */
    private function removeUploadDir()
    {
        $this->filesystem->remove($this->uploadFolder);
    }
}
