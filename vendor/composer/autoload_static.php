<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb6a6a83efa2bf5b25eab09b1641355d3
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Pecee\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Pecee\\' => 
        array (
            0 => __DIR__ . '/..' . '/pecee/simple-router/src/Pecee',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb6a6a83efa2bf5b25eab09b1641355d3::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb6a6a83efa2bf5b25eab09b1641355d3::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb6a6a83efa2bf5b25eab09b1641355d3::$classMap;

        }, null, ClassLoader::class);
    }
}
