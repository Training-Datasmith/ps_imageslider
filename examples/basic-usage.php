<?php

declare(strict_types=1);

/**
 * Example: Working with the ps_imageslider PrestaShop module.
 *
 * ps_imageslider renders a configurable image carousel/slider on the homepage
 * or other hook positions. Each slide has an image, title, description, and
 * an optional CTA link. Slides are managed from the Back Office.
 *
 * This file documents common usage patterns.
 */

// --- The module renders via displayHome hook ---
// PrestaShop dispatches the hook automatically when rendering the homepage.
// No direct PHP call is needed for standard usage.

// --- Widget invocation in Smarty/Twig template ---
// {widget name="ps_imageslider" hook="displayHome"}

// --- Querying slide data programmatically ---
// The module stores slides in the ps_imageslider table.
// Access via the module's internal configuration:
//
// $slides = Db::getInstance()->executeS(
//     'SELECT * FROM `' . _DB_PREFIX_ . 'imageslider`
//      WHERE `id_shop` = ' . (int) Context::getContext()->shop->id . '
//        AND `active` = 1
//      ORDER BY `position` ASC'
// );
//
// foreach ($slides as $slide) {
//     echo $slide['title'] . ': ' . $slide['url'] . "\n";
//     $imageUrl = _MODULE_DIR_ . 'ps_imageslider/images/' . $slide['image'];
//     echo "Image: $imageUrl\n";
// }

// --- Back Office configuration ---
// Modules > Image Slider:
//   - Add/edit/delete slides
//   - Set slide title, caption, link URL, and target (_self or _blank)
//   - Upload slide images (recommended: 1110x340px for Classic theme)
//   - Set slide display order (drag-and-drop)
//   - Slider speed and pause duration

// --- Template override ---
// themes/{theme}/modules/ps_imageslider/views/templates/hook/ps_imageslider.tpl

// --- Slider JavaScript ---
// The module uses a lightweight custom slider (not a third-party library).
// The script is injected via displayHeader hook.
