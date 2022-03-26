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

namespace WebstrumGallery\Install;

use Db;
use Module;

class Installer
{
    /**
     * Install module.
     */
    public function install(Module $module): bool
    {
        if (!$this->registerHooks($module))
            return false;

        if (!$this->installDatabase())
            return false;

        return true;
    }

    /**
     * Uninstall module.
     */
    public function uninstall(): bool
    {
        return $this->uninstallDatabase();
    }

    /**
     * Register hooks for the module.
     */
    private function registerHooks(Module $module): bool
    {
        $hooks = [
            'displayAdminProductsMainStepLeftColumnBottom',
            'displayProductExtraContent'
        ];

        return $module->registerHook($hooks);
    }

    /**
     * Create database tables for the module.
     */
    private function installDatabase(): bool
    {
        // TODO: Specify FK for product so records get deleted when product is deleted?

        $queries = [
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'webstrumgallery_image` (
              `id_wg_image` int(11) NOT NULL AUTO_INCREMENT,
              `id_product` int(11) NOT NULL,
              `filename` varchar(64) NOT NULL,
              PRIMARY KEY (`id_wg_image`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;',
        ];

        // TODO: Create another table for storing gallery image order.

        return $this->executeQueries($queries);
    }

    /**
     * Delete module's database tables.
     */
    private function uninstallDatabase(): bool
    {
        // TODO: Delete table for storing gallery image order.

        $queries = [
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'webstrumgallery_image`;',
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
}