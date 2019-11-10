# laravel-observers-observer
This package should be a help for every undocumented or poorly documented Laravel project using any model-related events defined in observers or models booting methods. 

## Work in progress informations
> The package is under development right now. In the nearest future these data will be combined, entire package will be tested and of course it will be published to Packagist.

## Installation
Please use Composer:
```bash
composer require krzysztofrewak/laravel-observers-observer --dev
```

## Usage
Please run:
```bash
php artisan observers:list
```

It should list all registered models (vendor included) and theirs events stored in:
* static boot listeners,
* `$dispatchesEvents` array in model,
* observers booted from service providers.

![Example usage](https://i.imgur.com/mjvCyca.png)