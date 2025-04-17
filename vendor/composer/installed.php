<?php return array(
    'root' => array(
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'reference' => 'e05d230166568e3e8e6496dddc40ea2fa7a345b6',
        'name' => 'iwp/mu',
        'dev' => true,
    ),
    'versions' => array(
        'instawp/connect-helpers' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'type' => 'library',
            'install_path' => __DIR__ . '/../instawp/connect-helpers',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'reference' => 'b236dbe7c628c78c5755ee2cad01dbe8f9c93f3a',
            'dev_requirement' => false,
        ),
        'iwp/mu' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'reference' => 'e05d230166568e3e8e6496dddc40ea2fa7a345b6',
            'dev_requirement' => false,
        ),
        'wp-cli/wp-config-transformer' => array(
            'pretty_version' => 'v1.4.1',
            'version' => '1.4.1.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../wp-cli/wp-config-transformer',
            'aliases' => array(),
            'reference' => '9da378b5a4e28bba3bce4ff4ff04a54d8c9f1a01',
            'dev_requirement' => false,
        ),
    ),
);
