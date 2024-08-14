=== Divi Module Usage Finder ===
Contributors: deviodigital
Donate link: https://deviodigital.com
Tags: Divi, module, usage, finder, shortcode
Requires at least: 5.2
Tested up to: 6.6.2
Stable tag: 1.0.0
License: GPL-3.0+
License URI: https://www.gnu.org/licenses/gpl-3.0.txt

Easily find content on your website that is using specific Divi modules.

== Description ==

Divi Module Usage Finder allows you to quickly identify which content (posts, pages, or custom post types) on your website is utilizing specific Divi modules. This plugin is a must-have for anyone working with Divi, as it provides an easy way to track and manage the usage of different modules across your site.

= Features =
* Search for any Divi module usage across all public post types.
* Easily navigate to the post or page where the module is used.
* Integrated directly into the Divi admin menu for seamless access.
* Secure and efficient, with nonce verification for form submissions.

== Installation ==

1. Upload the `divi-module-usage-finder` folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to the 'Divi' tab in your WordPress admin panel and click on 'Module Finder' submenu item to start using the plugin.

== Frequently Asked Questions ==

= What does this plugin do? =
This plugin helps you locate content on your WordPress site that uses specific Divi modules. It's ideal for tracking down where certain modules are implemented, especially in large sites.

= Is this plugin safe to use? =
Yes, this plugin includes security measures such as nonce verification to ensure that form submissions are secure and protected from CSRF attacks.

= Can I search for custom Divi modules? =
Yes, you can search for custom Divi modules, but you need to add them to the plugin's module list using a custom filter. You can use the `dmuf_get_all_modules` filter to extend the plugin's functionality and include your custom modules. Here's an example of how to do that:

```php
function custom_dmuf_modules( $modules ) {
    // Add your custom module to the list
    $modules['et_pb_custom_module'] = esc_html__( 'Custom Module', 'divi-module-usage-finder' );

    return $modules;
}
add_filter( 'dmuf_get_all_modules', 'custom_dmuf_modules' );
```

== Screenshots ==

1. **Module Selection Screen**: Easily select the Divi module you want to search for.
2. **Search Results**: View the list of posts, pages, or custom post types using the selected module.

== Changelog ==

= 1.0.0 =
* Initial release of Divi Module Usage Finder.

== License ==

This plugin is licensed under the GPL-3.0+. For more information, see [GPL-3.0+](https://www.gnu.org/licenses/gpl-3.0.txt).
