<?php
/**
 * Plugin Name:       Divi Module Usage Finder
 * Plugin URI:        https://deviodigital.com
 * Description:       Quickly find what content on your website is using specific modules
 * Version:           1.0.0
 * Author:            Devio Digital
 * Author URI:        https://deviodigital.com
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       divi-module-usage-finder
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    wp_die();
}

// Define current plugin version.
define( 'DMUF_PLUGIN_VERSION', '1.0.0' );

/**
 * Register the settings page as a sub-menu under the Divi tab in the WordPress admin menu.
 * 
 * @since 1.0.0
 */
function dmuf_admin_menu() {
    add_submenu_page(
        'et_divi_options',                                                    // Parent slug (Divi tab)
        esc_attr__( 'Divi Module Usage Finder', 'divi-module-usage-finder' ), // Page title
        esc_attr__( 'Module Finder', 'divi-module-usage-finder' ),            // Menu title
        'manage_options',                                                     // Capability
        'divi-module-finder',                                                 // Menu slug
        'dmuf_settings_page'                                                  // Callback function
    );
}
add_action( 'admin_menu', 'dmuf_admin_menu', 11 );

/**
 * Display the settings page.
 * 
 * @since 1.0.0
 */
function dmuf_settings_page() {
    // Retrieve all possible Divi module shortcodes.
    $modules = dmuf_get_all_modules();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Divi Module Usage Finder', 'divi-module-usage-finder' ) ?></h1>
        
        <?php if ( empty( $modules ) ) : ?>
            <p><strong><?php esc_html_e( 'No Divi modules found.', 'divi-module-usage-finder' ); ?></strong> <?php esc_html_e( 'Please ensure that the Divi Builder is active and working correctly.', 'divi-module-usage-finder' ); ?></p>
        <?php else : ?>
            <form method="post" style="margin-top:24px;">
                <?php wp_nonce_field( 'dmuf_module_search', 'dmuf_nonce' ); ?>
                <label for="selected_module"><?php esc_html_e( 'Select a Divi Module:', 'divi-module-usage-finder' ); ?></label>
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
            if ( ! isset( $_POST['dmuf_nonce'] ) || ! wp_verify_nonce( $_POST['dmuf_nonce'], 'dmuf_module_search' ) ) {
                echo '<p><strong>' . esc_html__( 'Security check failed. Please try again.', 'divi-module-usage-finder' ) . '</strong></p>';
                return;
            }

            // Get module values.
            $selected_module = sanitize_text_field( $_POST['selected_module'] );
            $results         = dmuf_search_module_usage( $selected_module );
            ?>
            <?php if ( '' != $selected_module ) { ?>
            <h2><?php echo esc_html__( 'Results for module', 'divi-module-usage-finder' ) . ' "' . esc_html( $modules[ $selected_module ] ) . '"'; ?></h2>
            <?php } ?>
            <table class="widefat" style="margin-top:24px;">
                <?php if ( ! empty( $results ) && '' != $selected_module ) : ?>
                    <thead>
                        <tr>
                            <td><?php esc_html_e( 'Title', 'divi-module-usage-finder' ); ?></td>
                            <td><?php esc_html_e( 'Link', 'divi-module-usage-finder' ); ?></td>
                            <td><?php esc_html_e( 'Post Type', 'divi-module-usage-finder' ); ?></td>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $results as $result ) : ?>
                        <tr>
                            <td><?php echo esc_html( $result->post_title ); ?> (<a href="<?php echo edit_post_link( $result->ID ); ?>"><?php esc_html_e( 'Edit', 'divi-module-usage-finder' ) ?></a>)</td>
                            <td><a href="<?php echo esc_url( get_permalink( $result->ID ) ); ?>"><?php echo esc_url( get_permalink( $result->ID ) ); ?></a></td>
                            <td><?php echo get_post_type_object( get_post_type( $result->ID ) )->labels->singular_name; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                <?php else : ?>
                    <tbody>
                        <tr>
                            <td colspan="3"><?php esc_html_e( 'No content found using this module.', 'divi-module-usage-finder' ); ?></td>
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
function dmuf_get_all_modules() {
    $modules = [
        ''                                  => esc_html__( '--', 'divi-module-usage-finder' ),
        'et_pb_accordion'                   => esc_html__( 'Accordion Module', 'divi-module-usage-finder' ),
        'et_pb_accordion_item'              => esc_html__( 'Accordion Item Module', 'divi-module-usage-finder' ),
        'et_pb_audio'                       => esc_html__( 'Audio Module', 'divi-module-usage-finder' ),
        'et_pb_counters'                    => esc_html__( 'Bar Counters Module', 'divi-module-usage-finder' ),
        'et_pb_counter'                     => esc_html__( 'Bar Counters Item Module', 'divi-module-usage-finder' ),
        'et_pb_blog'                        => esc_html__( 'Blog Module', 'divi-module-usage-finder' ),
        'et_pb_blurb'                       => esc_html__( 'Blurb Module', 'divi-module-usage-finder' ),
        'et_pb_button'                      => esc_html__( 'Button Module', 'divi-module-usage-finder' ),
        'et_pb_circle_counter'              => esc_html__( 'Circle Counter Module', 'divi-module-usage-finder' ),
        'et_pb_code'                        => esc_html__( 'Code Module', 'divi-module-usage-finder' ),
        'et_pb_comments'                    => esc_html__( 'Comments Module', 'divi-module-usage-finder' ),
        'et_pb_contact_form'                => esc_html__( 'Contact Form Module', 'divi-module-usage-finder' ),
        'et_pb_contact_field'               => esc_html__( 'Contact Form Item Module', 'divi-module-usage-finder' ),
        'et_pb_countdown_timer'             => esc_html__( 'Countdown Timer Module', 'divi-module-usage-finder' ),
        'et_pb_cta'                         => esc_html__( 'CTA Module', 'divi-module-usage-finder' ),
        'et_pb_divider'                     => esc_html__( 'Divider Module', 'divi-module-usage-finder' ),
        'et_pb_filterable_portfolio'        => esc_html__( 'Filterable Portfolio Module', 'divi-module-usage-finder' ),
        'et_pb_gallery'                     => esc_html__( 'Gallery Module', 'divi-module-usage-finder' ),
        'et_pb_image'                       => esc_html__( 'Image Module', 'divi-module-usage-finder' ),
        'et_pb_login'                       => esc_html__( 'Login Module', 'divi-module-usage-finder' ),
        'et_pb_map'                         => esc_html__( 'Map Module', 'divi-module-usage-finder' ),
        'et_pb_map_pin'                     => esc_html__( 'Map Pin Module', 'divi-module-usage-finder' ),
        'et_pb_menu'                        => esc_html__( 'Menu Module', 'divi-module-usage-finder' ),
        'et_pb_number_counter'              => esc_html__( 'Number Counter Module', 'divi-module-usage-finder' ),
        'et_pb_portfolio'                   => esc_html__( 'Portfolio Module', 'divi-module-usage-finder' ),
        'et_pb_post_content'                => esc_html__( 'Post Content Module', 'divi-module-usage-finder' ),
        'et_pb_post_slider'                 => esc_html__( 'Post Slider Module', 'divi-module-usage-finder' ),
        'et_pb_post_title'                  => esc_html__( 'Post Title Module', 'divi-module-usage-finder' ),
        'et_pb_post_nav'                    => esc_html__( 'Posts Navigation Module', 'divi-module-usage-finder' ),
        'et_pb_pricing_tables'              => esc_html__( 'Pricing Tables Module', 'divi-module-usage-finder' ),
        'et_pb_pricing_table'               => esc_html__( 'Pricing Tables Item Module', 'divi-module-usage-finder' ),
        'et_pb_search'                      => esc_html__( 'Search Module', 'divi-module-usage-finder' ),
        'et_pb_sidebar'                     => esc_html__( 'Sidebar Module', 'divi-module-usage-finder' ),
        'et_pb_signup'                      => esc_html__( 'Signup Module', 'divi-module-usage-finder' ),
        'et_pb_signup_custom_field'         => esc_html__( 'Signup Custom Field Module', 'divi-module-usage-finder' ),
        'et_pb_slider'                      => esc_html__( 'Slider Module', 'divi-module-usage-finder' ),
        'et_pb_slide'                       => esc_html__( 'Slider Item Module', 'divi-module-usage-finder' ),
        'et_pb_social_media_follow'         => esc_html__( 'Social Media Follow Module', 'divi-module-usage-finder' ),
        'et_pb_social_media_follow_network' => esc_html__( 'Social Media Follow Network Module', 'divi-module-usage-finder' ),
        'et_pb_tabs'                        => esc_html__( 'Tabs Module', 'divi-module-usage-finder' ),
        'et_pb_tab'                         => esc_html__( 'Tab Module', 'divi-module-usage-finder' ),
        'et_pb_team_member'                 => esc_html__( 'Team Member Module', 'divi-module-usage-finder' ),
        'et_pb_testimonial'                 => esc_html__( 'Testimonial Module', 'divi-module-usage-finder' ),
        'et_pb_text'                        => esc_html__( 'Text Module', 'divi-module-usage-finder' ),
        'et_pb_toggle'                      => esc_html__( 'Toggle Module', 'divi-module-usage-finder' ),
        'et_pb_video'                       => esc_html__( 'Video Module', 'divi-module-usage-finder' ),
        'et_pb_video_slider'                => esc_html__( 'Video Slider Module', 'divi-module-usage-finder' ),
        'et_pb_video_slider_item'           => esc_html__( 'Video Slider Item Module', 'divi-module-usage-finder' ),
        'et_pb_icon'                        => esc_html__( 'Icon Module', 'divi-module-usage-finder' ),
        'et_pb_heading'                     => esc_html__( 'Heading Module', 'divi-module-usage-finder' ),
    ];

    return apply_filters( 'dmuf_get_all_modules', $modules );
}

/**
 * Search for pages/posts using the selected Divi module shortcode.
 *
 * @param string $module The shortcode of the Divi module.
 * 
 * @since  1.0.0
 * @return array|object|null The results from the database query.
 */
function dmuf_search_module_usage( $module ) {
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
