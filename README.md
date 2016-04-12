[![Build Status](https://travis-ci.org/MatTheCat/silex-asset-provider.svg?branch=master)](https://travis-ci.org/MatTheCat/silex-asset-provider)

# AssetServiceProvider

The *AssetServiceProvider* leverages the [Symfony asset component](http://symfony.com/doc/current/components/asset/introduction.html) for Silex.

## Parameters

- **assets**: Packages configuration.

The **assets** parameter follows the same convention than the [Symfony Framework Bundle assets configuration](http://symfony.com/doc/current/reference/configuration/framework.html#assets).
A package configuration is defined by an associative array with the following optional keys:

- **base_path**
- **base_urls**
- **version**
- **version_format**

These keys under **assets** define the default package configuration.

You can add packages adding a **packages** key under **assets** which value is an associative array with packages name as keys and their configuration as values.

## Services

- **asset.packages**: An instance of `Symfony\Component\Asset\Packages`.

## Registering

```php
use MatTheCat\Asset\Silex\Provider\AssetServiceProvider;

$app->register(new AssetServiceProvider());
```

## Twig extension

You'll be able to use Twig `asset` function by requiring `symfony/twig-bridge` starting from 2.7.0.