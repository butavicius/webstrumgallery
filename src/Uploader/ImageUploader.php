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

namespace WebstrumGallery\Uploader;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use PrestaShop\PrestaShop\Core\Image\Exception\ImageOptimizationException;
use PrestaShop\PrestaShop\Core\Image\Uploader\Exception\MemoryLimitException;
use PrestaShop\PrestaShop\Core\Image\Uploader\Exception\UploadedImageConstraintException;
use Ramsey\Uuid\Uuid;

class ImageUploader
{
    // TODO: Extract to some config file?
    private string $galleryPath = _PS_MODULE_DIR_ . 'webstrumgallery/uploads/';

    /**
     * Upload file to Webstrum Gallery path
     * 
     * @return string uploaded image filename including extension
     */
    public function upload(UploadedFile $image): string
    {
        $filename = Uuid::uuid4()->toString();
        $extension = $image->guessExtension();
        $destination = "{$this->galleryPath}{$filename}.{$extension}";

        $this->checkImageIsAllowedForUpload($image);

        if (!\ImageManager::resize($image->getPathname(), $destination)) {
            throw new ImageOptimizationException(
                'An error occurred while uploading the image. Check your directory permissions.'
            );
        }

        return "{$filename}.{$extension}";
    }

    /**
     * Check if image is allowed to be uploaded.
     *
     * @throws UploadedImageConstraintException
     * @throws MemoryLimitException
     */
    protected function checkImageIsAllowedForUpload(UploadedFile $image)
    {
        // Check that file does not exceed allowed upload size
        $maxFileSize = \Tools::getMaxUploadSize();
        if ($maxFileSize > 0 && $image->getSize() > $maxFileSize) {
            throw new UploadedImageConstraintException(
                sprintf(
                    'Max file size allowed is "%s" bytes. Uploaded image size is "%s".',
                    $maxFileSize,
                    $image->getSize()
                ),
                UploadedImageConstraintException::EXCEEDED_SIZE
            );
        }

        // Check that file is actually image
        if (
            !\ImageManager::isRealImage($image->getPathname(), $image->getClientMimeType())
            || !\ImageManager::isCorrectImageFileExt($image->getClientOriginalName())
            || preg_match('/\%00/', $image->getClientOriginalName()) // prevent null byte injection
        ) {
            throw new UploadedImageConstraintException(
                sprintf(
                    'Image format "%s", not recognized, allowed formats are: .gif, .jpg, .png',
                    $image->getClientOriginalExtension()
                ),
                UploadedImageConstraintException::UNRECOGNIZED_FORMAT
            );
        }

        // Check that there's enough memory for operation
        if (!\ImageManager::checkImageMemoryLimit($image->getPathname())) {
            throw new MemoryLimitException('Cannot upload image due to memory restrictions');
        }
    }
}
