<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitfa2d0548eeda3ca96735e7aa1c7a5f65
{
    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'Rahabit\\Payment\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Rahabit\\Payment\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitfa2d0548eeda3ca96735e7aa1c7a5f65::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitfa2d0548eeda3ca96735e7aa1c7a5f65::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitfa2d0548eeda3ca96735e7aa1c7a5f65::$classMap;

        }, null, ClassLoader::class);
    }
}
