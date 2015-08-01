<?php

namespace MatTheCat\Tests\Silex\Provider;

use MatTheCat\Asset\Silex\Provider\AssetServiceProvider;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;

class AssetServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyConfiguration()
    {
        $app = new Application();
        $app->register(new AssetServiceProvider());

        $this->assertInstanceOf('Symfony\Component\Asset\Packages', $app['asset.packages']);

        $defaultPackage = $app['asset.packages']->getPackage();

        $this->assertInstanceOf(
            'Symfony\Component\Asset\PathPackage',
            $defaultPackage
        );
        $this->assertAttributeSame('/', 'basePath', $defaultPackage);
        $this->assertAttributeInstanceOf(
            'Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy',
            'versionStrategy',
            $defaultPackage
        );
    }

    public function testFullConfiguration()
    {
        $app = new Application();
        $app->register(new AssetServiceProvider(), [
            'assets' => [
                'base_urls' => ['//exemple.com'],
                'version' => '1.0.0',
                'version_format' => 'v%2$s/%1$s',
            ],
        ]);

        $defaultPackage = $app['asset.packages']->getPackage();

        $this->assertInstanceOf('Symfony\Component\Asset\UrlPackage', $defaultPackage);
        $this->assertAttributeSame(['//exemple.com'], 'baseUrls', $defaultPackage);

        $versionStrategy = $this->getObjectAttribute($defaultPackage, 'versionStrategy');

        $this->assertInstanceOf(
            'Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy',
            $versionStrategy
        );

        $this->assertAttributeSame('1.0.0', 'version', $versionStrategy);
        $this->assertAttributeSame('v%2$s/%1$s', 'format', $versionStrategy);
    }

    public function testBasePathAndUrls()
    {
        $app = new Application();
        $app->register(new AssetServiceProvider(), [
            'assets' => [
                'base_path' => '/',
                'base_urls' => ['//exemple.com'],
            ],
        ]);

        $this->setExpectedException('LogicException');

        $app->offsetGet('asset.packages');
    }

    public function testPackages()
    {
        $app = new Application();
        $app->register(new AssetServiceProvider(), [
            'assets' => [
                'packages' => [
                    'first' => [],
                    'second' => [],
                ],
            ],
        ]);

        $this->assertAttributeCount(2, 'packages', $app['asset.packages']);
    }

    public function testInheritedDefaultPackageVersion()
    {
        $app = new Application();
        $app->register(new AssetServiceProvider(), [
            'assets' => [
                'version' => '1.0.0',
                'packages' => [
                    'first' => [],
                    'second' => ['version' => null],
                ],
            ],
        ]);

        $firstPackageVersionStrategy = $this->getObjectAttribute(
            $app['asset.packages']->getPackage('first'),
            'versionStrategy'
        );

        $this->assertInstanceOf(
            'Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy',
            $firstPackageVersionStrategy
        );
        $this->assertAttributeSame('1.0.0', 'version', $firstPackageVersionStrategy);

        $this->assertAttributeInstanceOf(
            'Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy',
            'versionStrategy',
            $app['asset.packages']->getPackage('second')
        );
    }

    public function testTwigExtension()
    {
        $app = new Application();
        $assetServiceProvider = new AssetServiceProvider();
        $app->register($assetServiceProvider);
        $app->register(new TwigServiceProvider());

        $assetServiceProvider->boot($app);

        $this->assertInstanceOf('Symfony\Bridge\Twig\Extension\AssetExtension', $app['twig']->getExtension('asset'));
    }
}
