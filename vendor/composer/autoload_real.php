<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitbf92acb65b55e911e36b432aa0ff622f
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInitbf92acb65b55e911e36b432aa0ff622f', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitbf92acb65b55e911e36b432aa0ff622f', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitbf92acb65b55e911e36b432aa0ff622f::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
