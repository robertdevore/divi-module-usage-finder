# Module Usage Finder for Divi

**Module Usage Finder for Divi** is a WordPress plugin that allows you to easily find content on your website that is using specific Divi modules. Whether you’re working on a large site with multiple pages and posts or simply want to manage your Divi module usage more effectively, this plugin helps you quickly identify where each module is being used.

## Features

- **Search for Divi Modules:** Quickly find any Divi module usage across all public post types, including custom post types.
- **Easy Navigation:** Easily navigate to the post or page where the module is used directly from the results.
- **Integration:** Seamlessly integrates into the Divi admin menu for quick access.

## Installation

1. **Download the Plugin:**
   - Clone or download this repository to your local machine.

2. **Upload to WordPress:**
   - Upload the `module-usage-finder-for-divi` folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress admin by navigating to **Plugins > Add New** and uploading the ZIP file.

3. **Activate the Plugin:**
   - Go to the **Plugins** screen in your WordPress admin and activate the `Module Usage Finder for Divi` plugin.

4. **Access the Plugin:**
   - Navigate to the **Divi** tab in your WordPress admin panel and click on **Module Finder** to start using the plugin.

## How to Use

1. **Select a Module:**
   - On the **Module Finder** settings page, select the Divi module you want to search for from the dropdown menu.

2. **View Results:**
   - After selecting a module, click the **Search** button. The plugin will display a list of all posts, pages, or custom post types where the selected module is used.

3. **Edit or View Content:**
   - You can directly navigate to the content by clicking on the links provided in the search results.

## Extending the Plugin

### Searching for Custom Divi Modules

If you are using custom Divi modules that aren't included in the default list, you'll need to add them to the plugin's module list using the `mufd_get_all_modules` filter. Here's how you can do that:

#### Example:

```php
function custom_mufd_modules( $modules ) {
    // Add your custom module to the list
    $modules['et_pb_custom_module'] = esc_html__( 'Custom Module', 'module-usage-finder-for-divi' );

    return $modules;
}
add_filter( 'mufd_get_all_modules', 'custom_mufd_modules' );

```
