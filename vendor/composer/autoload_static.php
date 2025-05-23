<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb089b39062c629af36ad3fbc0c9cdb59
{
    public static $files = array (
        'ac949ce40a981819ba132473518a9a31' => __DIR__ . '/..' . '/wp-cli/wp-config-transformer/src/WPConfigTransformer.php',
    );

    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'InstaWP\\Connect\\Helpers\\' => 24,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'InstaWP\\Connect\\Helpers\\' => 
        array (
            0 => __DIR__ . '/..' . '/instawp/connect-helpers/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'InstaWP\\Connect\\Helpers\\Activator' => __DIR__ . '/..' . '/instawp/connect-helpers/src/Activator.php',
        'InstaWP\\Connect\\Helpers\\AutoUpdatePluginFromGitHub' => __DIR__ . '/..' . '/instawp/connect-helpers/src/AutoUpdatePluginFromGitHub.php',
        'InstaWP\\Connect\\Helpers\\Cache' => __DIR__ . '/..' . '/instawp/connect-helpers/src/Cache.php',
        'InstaWP\\Connect\\Helpers\\Curl' => __DIR__ . '/..' . '/instawp/connect-helpers/src/Curl.php',
        'InstaWP\\Connect\\Helpers\\DatabaseManager' => __DIR__ . '/..' . '/instawp/connect-helpers/src/DatabaseManager.php',
        'InstaWP\\Connect\\Helpers\\Deactivator' => __DIR__ . '/..' . '/instawp/connect-helpers/src/Deactivator.php',
        'InstaWP\\Connect\\Helpers\\DebugLog' => __DIR__ . '/..' . '/instawp/connect-helpers/src/DebugLog.php',
        'InstaWP\\Connect\\Helpers\\Helper' => __DIR__ . '/..' . '/instawp/connect-helpers/src/Helper.php',
        'InstaWP\\Connect\\Helpers\\Installer' => __DIR__ . '/..' . '/instawp/connect-helpers/src/Installer.php',
        'InstaWP\\Connect\\Helpers\\Inventory' => __DIR__ . '/..' . '/instawp/connect-helpers/src/Inventory.php',
        'InstaWP\\Connect\\Helpers\\Option' => __DIR__ . '/..' . '/instawp/connect-helpers/src/Option.php',
        'InstaWP\\Connect\\Helpers\\Uninstaller' => __DIR__ . '/..' . '/instawp/connect-helpers/src/Uninstaller.php',
        'InstaWP\\Connect\\Helpers\\Updater' => __DIR__ . '/..' . '/instawp/connect-helpers/src/Updater.php',
        'InstaWP\\Connect\\Helpers\\WPConfig' => __DIR__ . '/..' . '/instawp/connect-helpers/src/WPConfig.php',
        'InstaWP\\Connect\\Helpers\\WPScanner' => __DIR__ . '/..' . '/instawp/connect-helpers/src/WPScanner.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb089b39062c629af36ad3fbc0c9cdb59::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb089b39062c629af36ad3fbc0c9cdb59::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb089b39062c629af36ad3fbc0c9cdb59::$classMap;

        }, null, ClassLoader::class);
    }
}
