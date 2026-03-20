# Architecture: ps_imageslider

## Purpose

A PrestaShop front-office module that displays an image carousel/slider on the homepage
or other widget positions. Each slide has a configurable image, title, description, link,
and per-language content.

## Directory Structure

```
ps_imageslider.php   # Main module class (WidgetInterface)
src/
  Entity/            # Doctrine entity for slider slides
  Form/              # Back-office forms for slide management
  Repository/        # Doctrine repository for slides
views/templates/      # Smarty/Twig templates for slider rendering
translations/         # Translation files
upgrade/              # Database migration scripts
tests/                # PHPStan and unit tests
```

## Key Design Decisions

Slides are stored as Doctrine entities with per-language content (title, description)
via a related translation entity. The back-office management interface uses Symfony Form
components (PrestaShop's form framework). The front-office rendering uses a CSS/JS slider
library (e.g., Swiper or similar) included as a module asset.

## Extension Points

Override template in theme for custom slider initialization and styling.
Configure transitions, timing, and display options in the module back-office settings.
