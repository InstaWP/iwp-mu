<?php return array(
    'root' => array(
        'name' => 'iwp/mu',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => 'f78592cdaa8f37f7b3cce4ecb69c4fc7a3a0ce28',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'instawp/connect-helpers' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '2c081214ead38242162d4256852073efe375f984',
            'type' => 'library',
            'install_path' => __DIR__ . '/../instawp/connect-helpers',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'iwp/mu' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => 'f78592cdaa8f37f7b3cce4ecb69c4fc7a3a0ce28',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'wp-cli/wp-config-transformer' => array(
            'pretty_version' => 'v1.4.2',
            'version' => '1.4.2.0',
            'reference' => 'b78cab1159b43eb5ee097e2cfafe5eab573d2a8a',
            'type' => 'library',
            'install_path' => __DIR__ . '/../wp-cli/wp-config-transformer',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
