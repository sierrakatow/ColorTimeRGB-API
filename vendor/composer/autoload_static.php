<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitbad973adf5cc57df28b2da011531193b
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Dotenv\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Dotenv\\' => 
        array (
            0 => __DIR__ . '/..' . '/vlucas/phpdotenv/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitbad973adf5cc57df28b2da011531193b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitbad973adf5cc57df28b2da011531193b::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
