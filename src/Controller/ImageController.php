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

namespace WebstrumGallery\Controller;

use WebstrumGallery\Uploader\ImageUploader;
use Symfony\Component\HttpFoundation\Request;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use WebstrumGallery\Repository\ImageRepository;

class ImageController extends FrameworkBundleAdminController
{
    public function uploadAction(string $productId, Request $request): JsonResponse
    {
        // TODO: Configure Symfony DI for ImageUploader
        $imageUploader = new ImageUploader();

        /** @var ImageRepository $imageRepository */
        $imageRepository = $this->get(
            'webstrum_gallery.repository.image_repository'
        );

        $requestImage = $request->files->get('wg-image');

        // Upload image to filesystem
        $filename = $imageUploader->upload($requestImage);

        // Insert image info to database
        // TODO: Can you change var type in controller signature?
        $imageRepository->insert((int) $productId, $filename);

        return $this->json(
            [
                'productId' => $productId,
                'filename' => $filename
            ]
        );
    }
}
