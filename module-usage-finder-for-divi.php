<?php
/**
 * Plugin Name:       Module Usage Finder for Divi
 * Plugin URI:        https://github.com/deviodigital/module-usage-finder-for-divi
 * Description:       Quickly find what content on your website is using specific modules
 * Version:           1.0.1
 * Author:            Devio Digital
 * Author URI:        https://deviodigital.com
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       module-usage-finder-for-divi
 * Domain Path:       /languages
 * Update URI:        https://github.com/deviodigital/module-usage-finder-for-divi/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    wp_die();
}

// Define current plugin version.
define( 'MUFD_PLUGIN_VERSION', '1.0.1' );

require 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/deviodigital/module-usage-finder-for-divi/',
	__FILE__,
	'module-usage-finder-for-divi'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch( 'main' );

/**
 * Register the settings page as a sub-menu under the Divi tab in the WordPress admin menu.
 * 
 * @since  1.0.0
 * @return void
 */
function mufd_admin_menu() {
    add_submenu_page(
        'et_divi_options',
        esc_attr__( 'Module Usage Finder for Divi', 'module-usage-finder-for-divi' ),
        esc_attr__( 'Module Finder', 'module-usage-finder-for-divi' ),
        'manage_options',
        'divi-module-finder',
        'mufd_settings_page'
    );
}
add_action( 'admin_menu', 'mufd_admin_menu', 11 );

/**
 * Display the settings page.
 * 
 * @since  1.0.0
 * @return void
 */
function mufd_settings_page() {
    // Retrieve all possible Divi module shortcodes.
    $modules = mufd_get_all_modules();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Divi Module Usage Finder', 'module-usage-finder-for-divi' ); ?></h1>

        <?php if ( empty( $modules ) ) : ?>
            <p><strong><?php esc_html_e( 'No Divi modules found.', 'module-usage-finder-for-divi' ); ?></strong> <?php esc_html_e( 'Please ensure that the Divi Builder is active and working correctly.', 'module-usage-finder-for-divi' ); ?></p>
        <?php else : ?>
            <form method="post" style="margin-top:24px;">
                <?php wp_nonce_field( 'mufd_module_search', 'mufd_nonce' ); ?>
                <label for="selected_module"><?php esc_html_e( 'Select a Divi Module:', 'module-usage-finder-for-divi' ); ?></label>
                <select name="selected_module" id="selected_module">
                    <?php foreach ( $modules as $module => $module_name ) : ?>
                        <option value="<?php echo esc_attr( $module ); ?>"
                            <?php selected( isset( $_POST['selected_module'] ) ? $_POST['selected_module'] : '', $module ); ?>>
                            <?php echo esc_html( $module_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" value="Search" class="button button-primary">
            </form>
        <?php endif; ?>

        <?php 
        // Handle form submission.
        if ( isset( $_POST['selected_module'] ) && ! empty( $modules ) ) {
            // Verify the nonce before processing the form.
            if ( ! isset( $_POST['mufd_nonce'] ) || ! wp_verify_nonce( $_POST['mufd_nonce'], 'mufd_module_search' ) ) {
                echo '<p><strong>' . esc_html__( 'Security check failed. Please try again.', 'module-usage-finder-for-divi' ) . '</strong></p>';
                return;
            }

            // Get module values.
            $selected_module = sanitize_text_field( $_POST['selected_module'] );
            $results         = mufd_search_module_usage( $selected_module );
            ?>
            <?php if ( '' != $selected_module ) { ?>
            <h2><?php echo esc_html__( 'Results for module', 'module-usage-finder-for-divi' ) . ' "' . esc_html( $modules[ $selected_module ] ) . '"'; ?></h2>
            <?php } ?>
            <table class="widefat" style="margin-top:24px;">
                <?php if ( ! empty( $results ) && '' != $selected_module ) : ?>
                    <thead>
                        <tr>
                            <td><?php esc_html_e( 'Title', 'module-usage-finder-for-divi' ); ?></td>
                            <td><?php esc_html_e( 'Link', 'module-usage-finder-for-divi' ); ?></td>
                            <td><?php esc_html_e( 'Post Type', 'module-usage-finder-for-divi' ); ?></td>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $results as $result ) : ?>
                        <tr>
                            <td><?php echo esc_html( $result->post_title ); ?> (<a href="<?php echo edit_post_link( $result->ID ); ?>"><?php esc_html_e( 'Edit', 'module-usage-finder-for-divi' ) ?></a>)</td>
                            <td><a href="<?php echo esc_url( get_permalink( $result->ID ) ); ?>"><?php echo esc_url( get_permalink( $result->ID ) ); ?></a></td>
                            <td><?php echo get_post_type_object( get_post_type( $result->ID ) )->labels->singular_name; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                <?php else : ?>
                    <tbody>
                        <tr>
                            <td colspan="3"><?php esc_html_e( 'No content found using this module.', 'module-usage-finder-for-divi' ); ?></td>
                        </tr>
                    </tbody>
                <?php endif; ?>
            </table>
        <?php } ?>
    </div>
    <?php
}

/**
 * Manually define all possible Divi module shortcodes.
 *
 * @since  1.0.0
 * @return array An array of Divi modules with their shortcode as key and name as value.
 */
function mufd_get_all_modules() {
    $modules = [
        ''                                  => esc_html__( '--', 'module-usage-finder-for-divi' ),
        'et_pb_accordion'                   => esc_html__( 'Accordion Module', 'module-usage-finder-for-divi' ),
        'et_pb_accordion_item'              => esc_html__( 'Accordion Item Module', 'module-usage-finder-for-divi' ),
        'et_pb_audio'                       => esc_html__( 'Audio Module', 'module-usage-finder-for-divi' ),
        'et_pb_counters'                    => esc_html__( 'Bar Counters Module', 'module-usage-finder-for-divi' ),
        'et_pb_counter'                     => esc_html__( 'Bar Counters Item Module', 'module-usage-finder-for-divi' ),
        'et_pb_blog'                        => esc_html__( 'Blog Module', 'module-usage-finder-for-divi' ),
        'et_pb_blurb'                       => esc_html__( 'Blurb Module', 'module-usage-finder-for-divi' ),
        'et_pb_button'                      => esc_html__( 'Button Module', 'module-usage-finder-for-divi' ),
        'et_pb_circle_counter'              => esc_html__( 'Circle Counter Module', 'module-usage-finder-for-divi' ),
        'et_pb_code'                        => esc_html__( 'Code Module', 'module-usage-finder-for-divi' ),
        'et_pb_comments'                    => esc_html__( 'Comments Module', 'module-usage-finder-for-divi' ),
        'et_pb_contact_form'                => esc_html__( 'Contact Form Module', 'module-usage-finder-for-divi' ),
        'et_pb_contact_field'               => esc_html__( 'Contact Form Item Module', 'module-usage-finder-for-divi' ),
        'et_pb_countdown_timer'             => esc_html__( 'Countdown Timer Module', 'module-usage-finder-for-divi' ),
        'et_pb_cta'                         => esc_html__( 'CTA Module', 'module-usage-finder-for-divi' ),
        'et_pb_divider'                     => esc_html__( 'Divider Module', 'module-usage-finder-for-divi' ),
        'et_pb_filterable_portfolio'        => esc_html__( 'Filterable Portfolio Module', 'module-usage-finder-for-divi' ),
        'et_pb_gallery'                     => esc_html__( 'Gallery Module', 'module-usage-finder-for-divi' ),
        'et_pb_image'                       => esc_html__( 'Image Module', 'module-usage-finder-for-divi' ),
        'et_pb_login'                       => esc_html__( 'Login Module', 'module-usage-finder-for-divi' ),
        'et_pb_map'                         => esc_html__( 'Map Module', 'module-usage-finder-for-divi' ),
        'et_pb_map_pin'                     => esc_html__( 'Map Pin Module', 'module-usage-finder-for-divi' ),
        'et_pb_menu'                        => esc_html__( 'Menu Module', 'module-usage-finder-for-divi' ),
        'et_pb_number_counter'              => esc_html__( 'Number Counter Module', 'module-usage-finder-for-divi' ),
        'et_pb_portfolio'                   => esc_html__( 'Portfolio Module', 'module-usage-finder-for-divi' ),
        'et_pb_post_content'                => esc_html__( 'Post Content Module', 'module-usage-finder-for-divi' ),
        'et_pb_post_slider'                 => esc_html__( 'Post Slider Module', 'module-usage-finder-for-divi' ),
        'et_pb_post_title'                  => esc_html__( 'Post Title Module', 'module-usage-finder-for-divi' ),
        'et_pb_post_nav'                    => esc_html__( 'Posts Navigation Module', 'module-usage-finder-for-divi' ),
        'et_pb_pricing_tables'              => esc_html__( 'Pricing Tables Module', 'module-usage-finder-for-divi' ),
        'et_pb_pricing_table'               => esc_html__( 'Pricing Tables Item Module', 'module-usage-finder-for-divi' ),
        'et_pb_search'                      => esc_html__( 'Search Module', 'module-usage-finder-for-divi' ),
        'et_pb_sidebar'                     => esc_html__( 'Sidebar Module', 'module-usage-finder-for-divi' ),
        'et_pb_signup'                      => esc_html__( 'Signup Module', 'module-usage-finder-for-divi' ),
        'et_pb_signup_custom_field'         => esc_html__( 'Signup Custom Field Module', 'module-usage-finder-for-divi' ),
        'et_pb_slider'                      => esc_html__( 'Slider Module', 'module-usage-finder-for-divi' ),
        'et_pb_slide'                       => esc_html__( 'Slider Item Module', 'module-usage-finder-for-divi' ),
        'et_pb_social_media_follow'         => esc_html__( 'Social Media Follow Module', 'module-usage-finder-for-divi' ),
        'et_pb_social_media_follow_network' => esc_html__( 'Social Media Follow Network Module', 'module-usage-finder-for-divi' ),
        'et_pb_tabs'                        => esc_html__( 'Tabs Module', 'module-usage-finder-for-divi' ),
        'et_pb_tab'                         => esc_html__( 'Tab Module', 'module-usage-finder-for-divi' ),
        'et_pb_team_member'                 => esc_html__( 'Team Member Module', 'module-usage-finder-for-divi' ),
        'et_pb_testimonial'                 => esc_html__( 'Testimonial Module', 'module-usage-finder-for-divi' ),
        'et_pb_text'                        => esc_html__( 'Text Module', 'module-usage-finder-for-divi' ),
        'et_pb_toggle'                      => esc_html__( 'Toggle Module', 'module-usage-finder-for-divi' ),
        'et_pb_video'                       => esc_html__( 'Video Module', 'module-usage-finder-for-divi' ),
        'et_pb_video_slider'                => esc_html__( 'Video Slider Module', 'module-usage-finder-for-divi' ),
        'et_pb_video_slider_item'           => esc_html__( 'Video Slider Item Module', 'module-usage-finder-for-divi' ),
        'et_pb_icon'                        => esc_html__( 'Icon Module', 'module-usage-finder-for-divi' ),
        'et_pb_heading'                     => esc_html__( 'Heading Module', 'module-usage-finder-for-divi' ),
    ];

    return apply_filters( 'mufd_get_all_modules', $modules );
}

/**
 * Search for pages/posts using the selected Divi module shortcode.
 *
 * @param string $module The shortcode of the Divi module.
 * 
 * @since  1.0.0
 * @return array|object|null The results from the database query.
 */
function mufd_search_module_usage( $module ) {
    global $wpdb;

    // Retrieve all public post types, both default and custom.
    $post_types = get_post_types( [ 'public' => true ] );

    // Convert the array of post types into a comma-separated list for the SQL query.
    $post_types_list = "'" . implode( "','", array_map( 'esc_sql', $post_types ) ) . "'";

    // Prepare the SQL query.
    $query = $wpdb->prepare(
        "SELECT ID, post_title 
        FROM $wpdb->posts
        WHERE post_content LIKE %s 
        AND post_status = 'publish' 
        AND post_type IN ($post_types_list)",
        '%' . $wpdb->esc_like( '[' . $module ) . '%'
    );

    return $wpdb->get_results( $query );
}

/**
 * Helper function to handle WordPress.com environment checks.
 *
 * @param string $plugin_slug     The plugin slug.
 * @param string $learn_more_link The link to more information.
 * 
 * @since  1.1.0
 * @return bool
 */
function wp_com_plugin_check( $plugin_slug, $learn_more_link ) {
    // Check if the site is hosted on WordPress.com.
    if ( defined( 'IS_WPCOM' ) && IS_WPCOM ) {
        // Ensure the deactivate_plugins function is available.
        if ( ! function_exists( 'deactivate_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // Deactivate the plugin if in the admin area.
        if ( is_admin() ) {
            deactivate_plugins( $plugin_slug );

            // Add a deactivation notice for later display.
            add_option( 'wpcom_deactivation_notice', $learn_more_link );

            // Prevent further execution.
            return true;
        }
    }

    return false;
}

/**
 * Auto-deactivate the plugin if running in an unsupported environment.
 *
 * @since  1.1.0
 * @return void
 */
function wpcom_auto_deactivation() {
    if ( wp_com_plugin_check( plugin_basename( __FILE__ ), 'https://robertdevore.com/why-this-plugin-doesnt-support-wordpress-com-hosting/' ) ) {
        return; // Stop execution if deactivated.
    }
}
add_action( 'plugins_loaded', 'wpcom_auto_deactivation' );

/**
 * Display an admin notice if the plugin was deactivated due to hosting restrictions.
 *
 * @since  1.1.0
 * @return void
 */
function wpcom_admin_notice() {
    $notice_link = get_option( 'wpcom_deactivation_notice' );
    if ( $notice_link ) {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                echo wp_kses_post(
                    sprintf(
                        __( 'My Plugin has been deactivated because it cannot be used on WordPress.com-hosted websites. %s', 'module-usage-finder-for-divi' ),
                        '<a href="' . esc_url( $notice_link ) . '" target="_blank" rel="noopener">' . __( 'Learn more', 'module-usage-finder-for-divi' ) . '</a>'
                    )
                );
                ?>
            </p>
        </div>
        <?php
        delete_option( 'wpcom_deactivation_notice' );
    }
}
add_action( 'admin_notices', 'wpcom_admin_notice' );

/**
 * Prevent plugin activation on WordPress.com-hosted sites.
 *
 * @since  1.1.0
 * @return void
 */
function wpcom_activation_check() {
    if ( wp_com_plugin_check( plugin_basename( __FILE__ ), 'https://robertdevore.com/why-this-plugin-doesnt-support-wordpress-com-hosting/' ) ) {
        // Display an error message and stop activation.
        wp_die(
            wp_kses_post(
                sprintf(
                    '<h1>%s</h1><p>%s</p><p><a href="%s" target="_blank" rel="noopener">%s</a></p>',
                    __( 'Plugin Activation Blocked', 'module-usage-finder-for-divi' ),
                    __( 'This plugin cannot be activated on WordPress.com-hosted websites. It is restricted due to concerns about WordPress.com policies impacting the community.', 'module-usage-finder-for-divi' ),
                    esc_url( 'https://robertdevore.com/why-this-plugin-doesnt-support-wordpress-com-hosting/' ),
                    __( 'Learn more', 'module-usage-finder-for-divi' )
                )
            ),
            esc_html__( 'Plugin Activation Blocked', 'module-usage-finder-for-divi' ),
            [ 'back_link' => true ]
        );
    }
}
register_activation_hook( __FILE__, 'wpcom_activation_check' );

/**
 * Add a deactivation flag when the plugin is deactivated.
 *
 * @since  1.1.0
 * @return void
 */
function wpcom_deactivation_flag() {
    add_option( 'wpcom_deactivation_notice', 'https://robertdevore.com/why-this-plugin-doesnt-support-wordpress-com-hosting/' );
}
register_deactivation_hook( __FILE__, 'wpcom_deactivation_flag' );
