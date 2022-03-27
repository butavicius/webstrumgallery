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

namespace WebstrumGallery\Repository;

use Doctrine\Orm\EntityRepository;
use WebstrumGallery\Entity\WebstrumGalleryImage;

class ImageRepository extends EntityRepository
{
    /**
     * Inserts image into database.
     * 
     * @return WebstrumGalleryImage inserted image.
     */
    public function insert(int $productId, string $filename): WebstrumGalleryImage
    {
        $image = new WebstrumGalleryImage();

        $image->setProductId($productId);
        $image->setFilename($filename);

        $em = $this->getEntityManager();
        $em->persist($image);
        $em->flush();

        return $image;
    }

    public function delete(WebstrumGalleryImage $image): void
    {
        $em = $this->getEntityManager();
        if ($image) {
            $em->remove($image);
            $em->flush();
        }
    }
}
