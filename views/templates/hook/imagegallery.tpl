{* *
* 2007-2019 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2019 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
* *}

{if count($images) > 0}
    <h2>{$galleryTitle}</h2>

    <div class="webstrum-gallery splide" style="background-color: {$galleryColor}; border-radius: {$galleryCorners}; margin-top: 1rem">
        <div class="splide__track">
            <ul class="splide__list">

                {foreach $images as $image}
                    <li class="splide__slide" style="padding: 2rem">
                        <a href="{$image.url}" target="_blank">
                            <img src="{$image.url}" style="height: 400px;width: 100%;object-fit: contain" />
                        </a>
                    </li>
                {/foreach}

            </ul>
        </div>
    </div>
{/if}