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

/**
 * images product management
 */

window.webstrumGalleryImagesProduct = (function () {
  const dropZoneElem = $('#wg-product-images-dropzone');
  const expanderElem = $('#wg-product-images-container .dropzone-expander');

  function checkDropzoneMode() {
    if (!dropZoneElem.find('.dz-preview:not(.openfilemanager)').length) {
      dropZoneElem.removeClass('dz-started');
      dropZoneElem.find('.dz-preview.openfilemanager').hide();
    } else {
      dropZoneElem.find('.dz-preview.openfilemanager').show();
    }
  }

  return {
    toggleExpand() {
      if (expanderElem.hasClass('expand')) {
        dropZoneElem.css('height', 'auto');
        expanderElem.removeClass('expand').addClass('compress');
      } else {
        dropZoneElem.css('height', '');
        expanderElem.removeClass('compress').addClass('expand');
      }
    },
    displayExpander() {
      expanderElem.show();
    },
    hideExpander() {
      expanderElem.hide();
    },
    shouldDisplayExpander() {
      const oldHeight = dropZoneElem.css('height');

      dropZoneElem.css('height', '');
      const closedHeight = dropZoneElem.outerHeight();
      const realHeight = dropZoneElem[0].scrollHeight;

      if (oldHeight !== '0px') {
        dropZoneElem.css('height', oldHeight);
      }

      return (realHeight > closedHeight) && dropZoneElem.find('.dz-preview:not(.openfilemanager)').length;
    },
    updateExpander() {
      if (this.shouldDisplayExpander()) {
        this.displayExpander();
      }
    },
    initExpander() {
      if (this.shouldDisplayExpander()) {
        this.displayExpander();
        expanderElem.addClass('expand');
      }

      const self = this;
      $(document).on('click', '#wg-product-images-container .dropzone-expander', () => {
        self.toggleExpand();
      });
    },
    init() {
      Dropzone.autoDiscover = false;
      const errorElem = $('#wg-product-images-dropzone-error');

      // on click image, display custom form
      $(document).on('click', '#wg-product-images-dropzone .dz-preview', function () {
        if (!$(this).attr('data-id')) {
          return;
        }
        formImagesProduct.form($(this).attr('data-id'));
      });

      const dropzoneOptions = {
        url: dropZoneElem.attr('url-upload'),
        paramName: 'form[file]',
        maxFilesize: dropZoneElem.attr('data-max-size'),
        addRemoveLinks: true,
        clickable: '.openfilemanager',
        thumbnailWidth: 250,
        thumbnailHeight: null,
        acceptedFiles: 'image/*',
        timeout: 0,
        dictRemoveFile: translate_javascripts.Delete,
        dictFileTooBig: translate_javascripts.ToLargeFile,
        dictCancelUpload: translate_javascripts.Delete,
        sending() {
          checkDropzoneMode();
          expanderElem.addClass('expand').click();
          errorElem.html('');
        },
        queuecomplete() {
          checkDropzoneMode();
          dropZoneElem.sortable('enable');
          imagesProduct.updateExpander();
        },
        processing() {
          dropZoneElem.sortable('disable');
        },
        success(file, response) {
          // manage error on uploaded file
          if (response.error !== 0) {
            errorElem.append($('<p></p>').text(`${file.name}: ${response.error}`));
            this.removeFile(file);
            return;
          }

          // define id image to file preview
          $(file.previewElement).attr('data-id', response.id);
          $(file.previewElement).attr('url-update', response.url_update);
          $(file.previewElement).attr('url-delete', response.url_delete);
          $(file.previewElement).addClass('ui-sortable-handle');
          if (response.cover === 1) {
            imagesProduct.updateDisplayCover(response.id);
          }
        },
        error(file, response) {
          let message = '';

          if ($.type(response) === 'undefined') {
            return;
          } if ($.type(response) === 'string') {
            message = response;
          } else if (response.message) {
            // eslint-disable-next-line
            message = response.message;
          }

          if (message === '') {
            return;
          }

          // append new error
          errorElem.append($('<p></p>').text(`${file.name}: ${message}`));

          // remove uploaded item
          this.removeFile(file);
        },
        init() {
          // if already images uploaded, mask drop file message
          if (dropZoneElem.find('.dz-preview:not(.openfilemanager)').length) {
            dropZoneElem.addClass('dz-started');
          } else {
            dropZoneElem.find('.dz-preview.openfilemanager').hide();
          }

          // init sortable
          dropZoneElem.sortable({
            items: 'div.dz-preview:not(.disabled)',
            opacity: 0.9,
            containment: 'parent',
            distance: 32,
            tolerance: 'pointer',
            cursorAt: {
              left: 64,
              top: 64,
            },
            cancel: '.disabled',
            stop() {
              let sort = {};
              $.each(dropZoneElem.find('.dz-preview:not(.disabled)'), (index, value) => {
                if (!$(value).attr('data-id')) {
                  sort = false;
                  return;
                }
                sort[$(value).attr('data-id')] = index + 1;
              });

              // if sortable ok, update it
              if (sort) {
                $.ajax({
                  type: 'POST',
                  url: dropZoneElem.attr('url-position'),
                  data: {
                    json: JSON.stringify(sort),
                  },
                });
              }
            },
            start(event, ui) {
              // init zindex
              dropZoneElem.find('.dz-preview').css('zIndex', 1);
              ui.item.css('zIndex', 10);
            },
          });

          dropZoneElem.disableSelection();
          imagesProduct.initExpander();
        },
      };

      dropZoneElem.dropzone(jQuery.extend(dropzoneOptions));
    },
    updateDisplayCover(idImage) {
      $('#wg-product-images-dropzone .dz-preview .iscover').remove();
      $(`#wg-product-images-dropzone .dz-preview[data-id="${idImage}"]`)
        .append(`<div class="iscover">${translate_javascripts.Cover}</div>`);
    },
    checkDropzoneMode() {
      checkDropzoneMode();
    },
    getOlderImageId() {
      // eslint-disable-next-line
      return Math.min.apply(Math, $('.dz-preview').map(function () {
        return $(this).data('id');
      }));
    },
  };
}());

window.webstrumGalleryFormImagesProduct = (function () {
  const dropZoneElem = $('#wg-product-images-dropzone');
  const formZoneElem = $('#wg-product-images-form-container');

  // default state
  formZoneElem.hide();

  formZoneElem.magnificPopup({
    delegate: 'a.open-image',
    type: 'image',
  });

  function toggleColDropzone(enlarge) {
    const smallCol = 'col-md-8';
    const largeCol = 'col-md-12';

    if (enlarge === true) {
      dropZoneElem.removeClass(smallCol).addClass(largeCol);
    } else {
      dropZoneElem.removeClass(largeCol).addClass(smallCol);
    }
  }

  return {
    form(id) {
      dropZoneElem.find('.dz-preview.active').removeClass('active');
      dropZoneElem.find(`.dz-preview[data-id='${id}']`).addClass('active');
      if (!imagesProduct.shouldDisplayExpander()) {
        dropZoneElem.css('height', 'auto');
      }
      $.ajax({
        url: dropZoneElem.find(`.dz-preview[data-id='${id}']`).attr('url-update'),
        success(response) {
          formZoneElem.find('#wg-product-images-form').html(response);
          form.switchLanguage($('#wg-form_switch_language').val());
        },
        complete() {
          toggleColDropzone(false);
          formZoneElem.show();
          dropZoneElem.addClass('d-none d-md-block');
        },
      });
    },
    send(id) {
      $.ajax({
        type: 'POST',
        url: dropZoneElem.find(`.dz-preview[data-id='${id}']`).attr('url-update'),
        data: formZoneElem.find('textarea, input').serialize(),
        beforeSend() {
          formZoneElem.find('.actions button').prop('disabled', 'disabled');
          formZoneElem.find('ul.text-danger').remove();
          formZoneElem.find('*.has-danger').removeClass('has-danger');
        },
        success() {
          if (formZoneElem.find('#wg-form_image_cover:checked').length) {
            imagesProduct.updateDisplayCover(id);
          }
        },
        error(response) {
          if (response && response.responseText) {
            $.each(jQuery.parseJSON(response.responseText), (key, errors) => {
              let html = '<ul class="list-unstyled text-danger">';
              $.each(errors, (errorsKey, error) => {
                html += `<li>${error}</li>`;
              });
              html += '</ul>';

              $(`#wg-form_image_${key}`).parent().append(html);
              $(`#wg-form_image_${key}`).parent().addClass('has-danger');
            });
          }
        },
        complete() {
          formZoneElem.find('.actions button').removeAttr('disabled');
        },
      });
    },
    delete(id) {
      modalConfirmation.create(translate_javascripts['Are you sure you want to delete this item?'], null, {
        onContinue() {
          $.ajax({
            url: dropZoneElem.find(`.dz-preview[data-id="${id}"]`).attr('url-delete'),
            complete() {
              formZoneElem.find('.close').click();
              const wasCover = !!dropZoneElem.find(`.dz-preview[data-id="${id}"] .iscover`).length;
              dropZoneElem.find(`.dz-preview[data-id="${id}"]`).remove();
              $(`.images .product-combination-image [value=${id}]`).parent().remove();
              imagesProduct.checkDropzoneMode();
              if (wasCover === true) {
                // The controller will choose the oldest image as the new cover.
                imagesProduct.updateDisplayCover(imagesProduct.getOlderImageId());
              }
            },
          });
        },
      }).show();
    },
    close() {
      toggleColDropzone(true);
      dropZoneElem.removeClass('d-none d-md-block');
      dropZoneElem.css('height', '');
      formZoneElem.find('#product-images-form').html('');
      formZoneElem.hide();
      dropZoneElem.find('.dz-preview.active').removeClass('active');
    },
  };
}());