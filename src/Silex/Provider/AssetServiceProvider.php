<?php

namespace MatTheCat\Asset\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;

class AssetServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['assets'] = [];

        $app['asset.packages'] = $app->share(function (Application $app) {
            $defaultVersionConfiguration = array_intersect_key($app['assets'], ['version' => null, 'version_format' => null]);
            $packages = [];

            if (isset($app['assets']['packages'])) {
                foreach ($app['assets']['packages'] as $packageName => $packageConfiguration) {
                    $packages[$packageName] = $this->buildPackage($packageConfiguration + $defaultVersionConfiguration);
                }
            }

            return new Packages($this->buildPackage($app['assets']), $packages);
        });
    }

    public function boot(Application $app)
    {
        if (isset($app['twig']) && class_exists('Symfony\Bridge\Twig\Extension\AssetExtension')) {
            $app['twig'] = $app->share($app->extend('twig', function (\Twig_Environment $twig, Application $app) {
                $twig->addExtension(new AssetExtension($app['asset.packages']));

                return $twig;
            }));
        }
    }

    private function buildPackage(array $configuration)
    {
        $versionStrategy = isset($configuration['version']) ?
            new StaticVersionStrategy(
                $configuration['version'],
                isset($configuration['version_format']) ?
                    $configuration['version_format'] :
                    null
            ) :
            new EmptyVersionStrategy();

        if (!isset($configuration['base_urls'])) {
            return new PathPackage(isset($configuration['base_path']) ? $configuration['base_path'] : '/', $versionStrategy);
        }

        if (isset($configuration['base_path'])) {
            throw new \LogicException('An asset package cannot have base URLs and base paths.');
        }

        return new UrlPackage($configuration['base_urls'], $versionStrategy);
    }
}
