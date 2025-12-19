# InstaWP MU Plugin

A lightweight MU-plugin for WordPress sites created through InstaWP.

## Features

- **Welcome Dashboard** - Custom welcome panel with site credentials
- **Partner Plugins** - One-click installation of recommended plugins
- **Site Management** - Quick link to InstaWP dashboard
- **Expiry Information** - Cached site status from InstaWP API

## Installation

Copy `iwp-mu.php` to your WordPress `wp-content/mu-plugins/` directory.

## Structure

This is a single-file plugin with no external dependencies:

```
iwp-mu/
├── iwp-mu.php    # All functionality in one file (~625 lines)
└── README.md
```

## Configuration

The plugin reads configuration from the `iwp_welcome_details` WordPress option:

```php
[
    'site' => [
        'username' => 'admin',
        'password' => 'password123',
        'manage_site_url' => 'https://app.instawp.io/sites/123'
    ],
    'partners' => [
        [
            'name' => 'Plugin Name',
            'logo_url' => 'https://...',
            'description' => 'Plugin description',
            'slug' => 'plugin-slug',
            'plugin_file' => 'plugin-slug/plugin.php',
            'cta_text' => 'Learn More',
            'cta_link' => 'https://...'
        ]
    ]
]
```

## Version

- **v2.0.0** - Consolidated to single file, removed dependencies
- **v1.0.0** - Original multi-file version

Know more about [InstaWP](https://instawp.com)
