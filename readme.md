# laravel-observers-observer
This package should be a help for every undocumented or poorly documented Laravel project using any model-related events defined in observers or models booting methods. 

## Work in progress informations
The package is under development right now. At this moment only working features are finding models and finding model-related events. In the nearest future these data will be combined, entire package will be more configurable and tested and of course it will be published to Packagist.

## Installation
Please use Composer:
```bash
composer require krzysztofrewak/laravel-observers-observer
```

## Usage
Please run:
```bash
php artisan observers:list
php artisan observers:list --exclude-vendor
```
