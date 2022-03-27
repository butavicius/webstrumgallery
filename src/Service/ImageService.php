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
use Symfony\Component\Filesystem\Filesystem;
use WebstrumGallery\Repository\ImageRepository;
use WebstrumGallery\Entity\WebstrumGalleryImage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use PrestaShop\PrestaShop\Core\Image\Exception\ImageOptimizationException;
use PrestaShop\PrestaShop\Core\Image\Uploader\Exception\MemoryLimitException;
use PrestaShop\PrestaShop\Core\Image\Uploader\Exception\UploadedImageConstraintException;

class ImageService
{
    // TODO: Extract to some config file?
    private string $galleryPath = _PS_MODULE_DIR_ . 'webstrumgallery/uploads/';
    private ImageRepository $imageRepository;
    private Filesystem $filesystem;

    public function __construct(ImageRepository $imageRepository)
    {
        $this->imageRepository = $imageRepository;
        $this->filesystem = new Filesystem();
    }

    /**
     * Uploads file to Webstrum Gallery.
     */
    public function upload(UploadedFile $image, int $productId): int
    {
        $this->validateFile($image);

        $filename = $this->saveToFileSystem($image);
        $id = $this->saveToDatabase($filename, $productId);

        return $id;
    }

    /**
     * Deletes file from Webstrum Gallery.
     */
    public function delete(int $imageId): void
    {
        $image = $this->imageRepository->find($imageId);

        $this->deleteFromFileSystem($image);
        $this->deleteFromDatabase($image);
    }

    /**
     * Updates image positions for product's Webstrum Gallery
     */
    public function updatePositions(int $productId, array $positions)
    {
        $this->imageRepository->updatePositions($productId, $positions);
    }

    /**
     * Gets product images in a format suitable for view templates
     */
    public function getProductImages(int $productId): array
    {
        $images = $this->imageRepository->findByProductId($productId);

        $mapper = function (WebstrumGalleryImage $image) {
            return [
                'url' => _MODULE_DIR_ . "webstrumgallery/uploads/" . $image->getFilename(),
                'id' => $image->getId(),

                // If position has not been set yet by reordering images, keep
                // image at the end. Using id for position ensures that.
                'position' => $image->getPosition() > 0 ? $image->getPosition() : $image->getId(),
            ];
        };

        $mappedImages = array_map($mapper, $images);

        // Sort by position
        usort($mappedImages, function (array $a, array $b) {
            return $a['position'] - $b['position'];
        });

        // TODO: Use DTOs
        // TODO: Extract mapping logic to separate function
        return $mappedImages;
    }

    /**
     * Saves image file to Webstrum Gallery module's upload folder.
     * 
     * @return string saved filename with extension 
     * @throws ImageOptimizationException
     */
    private function saveToFileSystem(UploadedFile $image)
    {
        $temporaryLocation = $image->getPathname();
        $extension = $image->guessExtension();
        $newFilename = Uuid::uuid4()->toString();
        $destination = "{$this->galleryPath}{$newFilename}.{$extension}";

        // TODO: Refactor to not use legacy ImageManager class (see adapter)
        if (!\ImageManager::resize($temporaryLocation, $destination)) {
            throw new ImageOptimizationException(
                'An error occurred while uploading the image. Check your directory permissions.'
            );
        }

        return "{$newFilename}.{$extension}";
    }

    /**
     * Deletes image file from Webstrum Gallery module's upload folder.
     */
    private function deleteFromFileSystem(WebstrumGalleryImage $image): void
    {
        // TODO: Extract this to some configuration file for single source of truth
        $imagePath = _PS_MODULE_DIR_ . "webstrumgallery/uploads/" . $image->getFilename();
        $this->filesystem->remove($imagePath);
    }

    /**
     * Inserts database record for image.
     */
    private function saveToDatabase(string $filename, int $productId): int
    {
        $insertedImage = $this->imageRepository->insert($productId, $filename);

        return $insertedImage->getId();
    }

    /**
     * Deletes database record of image.
     */
    private function deleteFromDatabase(WebstrumGalleryImage $image): void
    {
        $this->imageRepository->delete($image);
    }

    /**
     * Checks if image is allowed to be uploaded.
     *
     * @throws UploadedImageConstraintException
     * @throws MemoryLimitException
     */
    private function validateFile(UploadedFile $file)
    {
        // Check that file does not exceed allowed upload size
        $maxFileSize = \Tools::getMaxUploadSize();
        if ($maxFileSize > 0 && $file->getSize() > $maxFileSize) {
            throw new UploadedImageConstraintException(
                sprintf(
                    'Max file size allowed is "%s" bytes. Uploaded file size is "%s".',
                    $maxFileSize,
                    $file->getSize()
                ),
                UploadedImageConstraintException::EXCEEDED_SIZE
            );
        }

        // Check that file is actually image
        if (
            !\ImageManager::isRealImage($file->getPathname(), $file->getClientMimeType())
            || !\ImageManager::isCorrectImageFileExt($file->getClientOriginalName())
            || preg_match('/\%00/', $file->getClientOriginalName()) // prevent null byte injection
        ) {
            throw new UploadedImageConstraintException(
                sprintf(
                    'Image format "%s", not recognized, allowed formats are: .gif, .jpg, .png',
                    $file->getClientOriginalExtension()
                ),
                UploadedImageConstraintException::UNRECOGNIZED_FORMAT
            );
        }

        // Check that there's enough memory for operation
        if (!\ImageManager::checkImageMemoryLimit($file->getPathname())) {
            throw new MemoryLimitException('Cannot upload image due to memory restrictions');
        }
    }
}
