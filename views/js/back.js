/**
 * 2007-2022 PrestaShop
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2022 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */

// TODO: Check if window.Dropzone is available and if not, load it. For now
// we're relying on prestashop core to load it for us, but their dependencies
// might change in the future

// TODO: Refactor this mess

$(document).ready(() => {
  window.webstrumGalleryImagesProduct.init();
});

window.webstrumGalleryImagesProduct = (function () {
  const dropZoneElem = $("#wg-product-images-dropzone");

  function checkDropzoneMode() {
    if (!dropZoneElem.find(".dz-preview:not(.openfilemanager)").length) {
      dropZoneElem.removeClass("dz-started");
      dropZoneElem.find(".dz-preview.openfilemanager").hide();
    } else {
      dropZoneElem.find(".dz-preview.openfilemanager").show();
    }
  }

  return {
    init() {
      Dropzone.autoDiscover = false;
      const errorElem = $("#wg-product-images-dropzone-error");

      const dropzoneOptions = {
        url: dropZoneElem.attr("url-upload"),
        paramName: "wg-image",
        maxFilesize: dropZoneElem.attr("data-max-size"),
        addRemoveLinks: true,
        clickable: ".wg-openfilemanager",
        thumbnailWidth: 250,
        thumbnailHeight: null,
        acceptedFiles: "image/*",
        timeout: 0,
        dictRemoveFile: translate_javascripts.Delete,
        dictFileTooBig: translate_javascripts.ToLargeFile,
        dictCancelUpload: translate_javascripts.Delete,
        sending() {
          checkDropzoneMode();
          errorElem.html("");
        },
        queuecomplete() {
          checkDropzoneMode();
          dropZoneElem.sortable("enable");
          imagesProduct.updateExpander();
        },
        processing() {
          dropZoneElem.sortable("disable");
        },
        success(file, response) {
          // manage error on uploaded file
          if (response.error !== 0) {
            errorElem.append(
              $("<p></p>").text(`${file.name}: ${response.error}`)
            );
            this.removeFile(file);
            return;
          }

          // define id image to file preview
          $(file.previewElement).attr("data-id", response.id);
          // $(file.previewElement).addClass("ui-sortable-handle");

          // Attach delete handler to "delete" text element
          $(file.previewElement)
            .find(".dz-remove")
            .first()
            .on("click", () => {
              $.ajax({
                type: "DELETE",
                url: response.url_delete,
              });
            });
        },
        error(file, response) {
          let message = "";

          if ($.type(response) === "undefined") {
            return;
          }
          if ($.type(response) === "string") {
            message = response;
          } else if (response.message) {
            // eslint-disable-next-line
            message = response.message;
          }

          if (message === "") {
            return;
          }

          // append new error
          errorElem.append($("<p></p>").text(`${file.name}: ${message}`));

          // remove uploaded item
          this.removeFile(file);
        },
        init() {
          // if already images uploaded, mask drop file message
          if (dropZoneElem.find(".dz-preview:not(.openfilemanager)").length) {
            dropZoneElem.addClass("dz-started");
          } else {
            dropZoneElem.find(".dz-preview.openfilemanager").hide();
          }

          // Attach delete handlers to images
          dropZoneElem.find(".dz-image-preview").each((index, imageElement) => {
            const removeElement = $(imageElement).find(".dz-remove").first();

            removeElement.on("click", () => {
              $.ajax({
                type: "DELETE",
                url: $(imageElement).attr("url-delete"),
                success: () => {
                  imageElement.remove();
                },
              });
            });
          });

          // init sortable
          dropZoneElem.sortable({
            items: "div.dz-preview:not(.disabled)",
            opacity: 0.9,
            containment: "parent",
            distance: 32,
            tolerance: "pointer",
            cursorAt: {
              left: 64,
              top: 64,
            },
            cancel: ".disabled",
            stop() {
              let sort = {};
              $.each(
                dropZoneElem.find(".dz-preview:not(.disabled)"),
                (index, value) => {
                  if (!$(value).attr("data-id")) {
                    sort = false;
                    return;
                  }
                  sort[$(value).attr("data-id")] = index + 1;
                }
              );

              // if sortable ok, update it
              if (sort) {
                $.ajax({
                  type: "POST",
                  url: dropZoneElem.attr("url-position"),
                  data: {
                    json: JSON.stringify(sort),
                  },
                });
              }
            },
            start(event, ui) {
              // init zindex
              dropZoneElem.find(".dz-preview").css("zIndex", 1);
              ui.item.css("zIndex", 10);
            },
          });

          dropZoneElem.disableSelection();
          imagesProduct.initExpander();
        },
      };

      dropZoneElem.dropzone(jQuery.extend(dropzoneOptions));
    },
    checkDropzoneMode() {
      checkDropzoneMode();
    },
    getOlderImageId() {
      // eslint-disable-next-line
      return Math.min.apply(
        Math,
        $(".dz-preview").map(function () {
          return $(this).data("id");
        })
      );
    },
  };
})();