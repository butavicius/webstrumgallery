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

use WebstrumGallery\Service\ImageService;
use Symfony\Component\HttpFoundation\Request;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ImageController extends FrameworkBundleAdminController
{
    private ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        parent::__construct();
        $this->imageService = $imageService;
    }

    /**
     * Uploads image to Webstrum Gallery.
     * 
     * @return string JSON object with property error = 0 on success, 1 on failure
     */
    public function uploadAction(int $productId, Request $request): JsonResponse
    {
        $requestImage = $request->files->get('wg-image');

        try {
            $imageId = $this->imageService->upload($requestImage, $productId);

            return $this->json([
                'error' => 0,
                'url_delete' => $this->generateUrl('admin_product_wg_image_delete', ['imageId' => $imageId]),
                'id' => $imageId

            ]);
        } catch (\Throwable $th) {
            return $this->json(['error' => 1, 'message' => $th->getMessage()]);
        }
    }

    /**
     * Deletes image from Webstrum Gallery.
     * 
     * @return JsonResponse with property error = 0 on success, 1 on failure
     */
    public function deleteAction(int $imageId): JsonResponse
    {
        try {
            $this->imageService->delete($imageId);
            return $this->json([
                'error' => 0
            ]);
        } catch (\Throwable $th) {
            return $this->json([
                'error' => 1,
                'message' => $th->getMessage()
            ]);
        }
    }

    /**
     * Updates image positions in Webstrum Gallery.
     * 
     * @return JsonResponse with property error = 0 on success, 1 on failure
     */
    public function updatePositionsAction(int $productId, Request $request): JsonResponse
    {
        // We get strange request shape from Dropzone.js library. In the end
        // $positions is array keys are imageId's and values are image position:
        // ["imageId" => "position", "imageId2" => "position2"...]
        $positions = get_object_vars(json_decode($request->request->all()['json']));

        try {
            $this->imageService->updatePositions($productId, $positions);
            return $this->json([
                'error' => 0
            ]);
        } catch (\Throwable $th) {
            return $this->json([
                'error' => 1,
                'message' => $th->getMessage()
            ]);
        }
    }
}
