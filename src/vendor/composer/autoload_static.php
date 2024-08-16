<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitfd8e931fecab81cb0db0789466a481c5
{
    public static $prefixLengthsPsr4 = array (
        'v' => 
        array (
            'vennv\\vapm\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'vennv\\vapm\\' => 
        array (
            0 => __DIR__ . '/../..' . '/vennv/vapm',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitfd8e931fecab81cb0db0789466a481c5::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitfd8e931fecab81cb0db0789466a481c5::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitfd8e931fecab81cb0db0789466a481c5::$classMap;

        }, null, ClassLoader::class);
    }
}
