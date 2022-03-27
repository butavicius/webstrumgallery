# Webstrum Gallery

This module adds an image gallery (in addition to the main one) on product page. The gallery is displayed on the bottom of product page (the yellow thing :grin:).

<img src="screenshots/frontend.png" height="800px" />

## Installation:
1) Clone repository:
```
cd /path/to/prestashop/modules;
git clone https://github.com/butavicius/webstrumgallery.git;
```
2) Go to prestashop Back Office->Modules->Module Catalog, search for "Webstrum Gallery". 
3) Click "Install".


## Features:
* Slider-type gallery. You can click on arrows or swipe through images.
* Drag-and-drop uploaded images around to reorder them.
* Customizable style.

## Usage:

### Upload and arrange images in Back Office product page:

<img src="screenshots/backend.png" height="400px" />

### Configure gallery to suit your theme:
In Back Office->Modules->Module manager, search for "Webstrum Gallery" and click "Configure".
* Change Gallery's title. 
* Pick gallery background color.
* Choose style - sharp or round gallery element corners. 

<img src="screenshots/configuration.png" height="300px" />

## Dev notes / TODO's:
* Needs unit/controller integration tests (PHPUnit).
* Needs refactoring, see "// TODO:" comments.
* Code needs to be linted and checked against PrestaShop code standards.
* Code needs to be statically checked for common (PHPStan).

## Feature ideas:
* Could use some themes/variants.
* Could use more sophisticated image optimisation.
