<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInited96d9dea87c425a161585f6fd7dc8ef
{
    public static $files = array (
        'c65d09b6820da036953a371c8c73a9b1' => __DIR__ . '/..' . '/facebook/graph-sdk/src/Facebook/polyfills.php',
    );

    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Facebook\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Facebook\\' => 
        array (
            0 => __DIR__ . '/..' . '/facebook/graph-sdk/src/Facebook',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInited96d9dea87c425a161585f6fd7dc8ef::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInited96d9dea87c425a161585f6fd7dc8ef::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInited96d9dea87c425a161585f6fd7dc8ef::$classMap;

        }, null, ClassLoader::class);
    }
}
