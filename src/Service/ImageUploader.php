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

use Ramsey\Uuid\Uuid;
use WebstrumGallery\Service\ImageValidator;
use WebstrumGallery\Repository\ImageRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use PrestaShop\PrestaShop\Core\Image\Exception\ImageOptimizationException;

class ImageUploader
{
    // TODO: Extract to some config file?
    private string $galleryPath = _PS_MODULE_DIR_ . 'webstrumgallery/uploads/';
    private ImageRepository $imageRepository;
    private ImageValidator $imageValidator;

    public function __construct(ImageRepository $imageRepository, ImageValidator $imageValidator)
    {
        $this->imageRepository = $imageRepository;
        $this->imageValidator = $imageValidator;
    }

    /**
     * Uploads file to Webstrum Gallery.
     */
    public function upload(UploadedFile $image, int $productId): int
    {
        $this->imageValidator->validate($image);

        $filename = $this->saveToFileSystem($image);
        $id = $this->saveToDatabase($filename, $productId);

        return $id;

    }

    /**
     * Saves uploaded image to disk.
     * 
     * @return string saved filename (UUID) with extension 
     * @throws ImageOptimizationException
     */
    private function saveToFileSystem(UploadedFile $image)
    {
        $temporaryLocation = $image->getPathname();
        $extension = $image->guessExtension();
        $newFilename = Uuid::uuid4()->toString();
        $destination = "{$this->galleryPath}{$newFilename}.{$extension}";

        if (!\ImageManager::resize($temporaryLocation, $destination)) {
            throw new ImageOptimizationException(
                'An error occurred while uploading the image. Check your directory permissions.'
            );
        }

        return "{$newFilename}.{$extension}";
    }

    /**
     * Inserts database record for uploaded image.
     */
    private function saveToDatabase(string $filename, int $productId): int
    {
        $insertedImage = $this->imageRepository->insert($productId, $filename);

        return $insertedImage->getId();
    }
}
