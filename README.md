# Geocoding Demo Plugin

This is a demonstration WordPress plugin showing how to implement the [Maps.co Geocoding Library](https://github.com/arraypress/geocoding) in a WordPress environment. The plugin provides a testing interface in the WordPress admin area to explore Maps.co geocoding capabilities.

## About

This plugin demonstrates the integration of the [Geocoding Library](https://github.com/arraypress/geocoding) with WordPress, showing developers how to implement both forward and reverse geocoding features in their own plugins.

## Installation

1. Download or fork this repository
2. Place it in your WordPress plugins directory
3. Run `composer install` in the plugin directory
4. Activate the plugin in WordPress
5. Navigate to Tools > Geocoding Tester to configure your API key

## Features Demonstrated

The admin interface allows testing of:
- Forward geocoding (convert addresses to coordinates)
- Reverse geocoding (convert coordinates to addresses)
- Detailed location information display including:
    - Formatted addresses
    - Address components
    - Coordinates
    - Bounding boxes
    - OpenStreetMap metadata
    - Debug information

## Requirements

- PHP 7.4 or later
- WordPress 6.7.1 or later
- Maps.co API key

## Contributions

Contributions to this library are highly appreciated. Raise issues on GitHub or submit pull requests for bug fixes or new features. Share feedback and suggestions for improvements.

## License: GPLv2 or later

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.