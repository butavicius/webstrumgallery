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

use WebstrumGallery\Service\ImageUploader;
use Symfony\Component\HttpFoundation\Request;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ImageController extends FrameworkBundleAdminController
{
    private ImageUploader $imageUploader;

    public function __construct(ImageUploader $imageUploader)
    {
        parent::__construct();
        $this->imageUploader = $imageUploader;
    }

    /**
     * Uploads image to gallery
     */
    public function uploadAction(int $productId, Request $request): JsonResponse
    {
        $requestImage = $request->files->get('wg-image');

        try {
            $this->imageUploader->upload($requestImage, $productId);

            return $this->json(['error' => 0]);
        } catch (\Throwable $th) {
            return $this->json(['error' => 1, 'message' => $th->getMessage()]);
        }
    }

    /**
     * Deletes image
     */
    public function deleteImage($productId): JsonResponse
    {
        return $this->json(['status' => 'success', 'productId' => $productId]);
    }
}
