<?php
/**
 * Plugin Name:         ArrayPress - Geocoding Tester
 * Plugin URI:          https://arraypress.com/plugins/geocoding-tester
 * Description:         A plugin to test and demonstrate the Maps.co Geocoding API integration.
 * Author:              ArrayPress
 * Author URI:          https://arraypress.com
 * License:             GNU General Public License v2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:         arraypress-geocoding
 * Domain Path:         /languages/
 * Requires PHP:        7.4
 * Requires at least:   6.7.1
 * Version:             1.0.0
 */

namespace ArrayPress\Geocoding;

defined( 'ABSPATH' ) || exit;

/**
 * Include required files and initialize the Plugin class if available.
 */
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Plugin class to handle all the functionality
 */
class Plugin {

	/**
	 * Instance of GeocodingClient
	 *
	 * @var Client|null
	 */
	private ?Client $client = null;

	/**
	 * Initialize the plugin
	 */
	public function __construct() {
		// Initialize client if API key is set
		$api_key = get_option( 'geocoding_api_key' );
		if ( $api_key ) {
			$this->client = new Client( $api_key );
		}

		// Hook into WordPress
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Add admin menu pages
	 */
	public function add_admin_menu() {
		add_management_page(
			'Geocoding Tester',
			'Geocoding Tester',
			'manage_options',
			'geocoding-tester',
			[ $this, 'render_admin_page' ]
		);
	}

	/**
	 * Register plugin settings
	 */
	public function register_settings() {
		register_setting( 'geocoding_settings', 'geocoding_api_key' );

		add_settings_section(
			'geocoding_settings_section',
			'API Settings',
			null,
			'geocoding-tester'
		);

		add_settings_field(
			'geocoding_api_key',
			'Maps.co API Key',
			[ $this, 'render_api_key_field' ],
			'geocoding-tester',
			'geocoding_settings_section'
		);
	}

	/**
	 * Render API key field
	 */
	public function render_api_key_field() {
		$api_key = get_option( 'geocoding_api_key' );
		echo '<input type="text" name="geocoding_api_key" value="' . esc_attr( $api_key ) . '" class="regular-text">';
	}

	/**
	 * Render admin page
	 */
	public function render_admin_page() {
		// Get test parameters
		$test_type = isset( $_POST['test_type'] ) ? sanitize_text_field( $_POST['test_type'] ) : 'forward';
		$address   = isset( $_POST['address'] ) ? sanitize_text_field( $_POST['address'] ) : '';
		$latitude  = isset( $_POST['latitude'] ) ? (float) $_POST['latitude'] : '';
		$longitude = isset( $_POST['longitude'] ) ? (float) $_POST['longitude'] : '';

		$results = null;

		// Process form submission
		if ( $this->client && isset( $_POST['submit'] ) ) {
			if ( $test_type === 'forward' && ! empty( $address ) ) {
				$results = $this->client->geocode( $address );
			} elseif ( $test_type === 'reverse' && ! empty( $latitude ) && ! empty( $longitude ) ) {
				$results = $this->client->reverse_geocode( $latitude, $longitude );
			}
		}

		// Render the page
		?>
        <div class="wrap">
            <h1>Geocoding Tester</h1>

            <!-- Settings Form -->
			<?php $this->render_settings_form(); ?>

            <hr>

            <!-- Test Interface -->
			<?php $this->render_test_interface( $test_type, $address, $latitude, $longitude ); ?>

            <!-- Results Section -->
			<?php $this->render_results( $results ); ?>
        </div>

		<?php $this->render_js(); ?>
		<?php
	}

	/**
	 * Render the settings form
	 */
	private function render_settings_form() {
		?>
        <form method="post" action="options.php">
			<?php
			settings_fields( 'geocoding_settings' );
			do_settings_sections( 'geocoding-tester' );
			submit_button( 'Save API Key' );
			?>
        </form>
		<?php
	}

	/**
	 * Render the test interface
	 */
	private function render_test_interface( $test_type, $address, $latitude, $longitude ) {
		?>
        <h2>Test Options</h2>
        <form method="post">
            <table class="form-table">
                <!-- Test Type Selection -->
                <tr>
                    <th scope="row">Test Type</th>
                    <td>
                        <label>
                            <input type="radio" name="test_type" value="forward"
								<?php checked( $test_type, 'forward' ); ?>>
                            Forward Geocoding (Address to Coordinates)
                        </label>
                        <br>
                        <label>
                            <input type="radio" name="test_type" value="reverse"
								<?php checked( $test_type, 'reverse' ); ?>>
                            Reverse Geocoding (Coordinates to Address)
                        </label>
                    </td>
                </tr>

                <!-- Forward Geocoding Fields -->
                <tr class="forward-fields" style="<?php echo $test_type === 'reverse' ? 'display:none;' : ''; ?>">
                    <th scope="row"><label for="address">Address</label></th>
                    <td>
                        <input type="text" name="address" id="address"
                               value="<?php echo esc_attr( $address ); ?>"
                               class="regular-text">
                        <p class="description">Enter a full address (e.g., "1600 Pennsylvania Avenue NW, Washington,
                            DC")</p>
                    </td>
                </tr>

                <!-- Reverse Geocoding Fields -->
                <tr class="reverse-fields" style="<?php echo $test_type === 'forward' ? 'display:none;' : ''; ?>">
                    <th scope="row"><label for="latitude">Coordinates</label></th>
                    <td>
                        <input type="number" step="any" name="latitude" id="latitude"
                               value="<?php echo esc_attr( $latitude ); ?>"
                               placeholder="Latitude"
                               style="width: 150px;">
                        <input type="number" step="any" name="longitude" id="longitude"
                               value="<?php echo esc_attr( $longitude ); ?>"
                               placeholder="Longitude"
                               style="width: 150px;">
                        <p class="description">Enter latitude and longitude coordinates</p>
                    </td>
                </tr>
            </table>

			<?php submit_button( 'Run Test', 'primary', 'submit', false ); ?>
        </form>
		<?php
	}

	/**
	 * Render the results section
	 */
	private function render_results( $results ) {
		if ( ! $results ) {
			return;
		}

		?>
        <h2>Results</h2>
		<?php

		if ( is_wp_error( $results ) ) {
			?>
            <div class="notice notice-error">
                <p><?php echo esc_html( $results->get_error_message() ); ?></p>
            </div>
			<?php
			return;
		}

		$this->render_location_result( $results );
		$this->render_debug_info( $results );
	}

	/**
	 * Render a single location result
	 */
	private function render_location_result( Location $results ) {
		?>
        <table class="widefat striped">
            <tbody>
            <!-- Basic Information -->
            <tr>
                <th>Coordinates</th>
                <td>
                    Latitude: <?php echo esc_html( $results->get_latitude() ); ?><br>
                    Longitude: <?php echo esc_html( $results->get_longitude() ); ?>
                </td>
            </tr>

            <tr>
                <th>Display Name</th>
                <td><?php echo esc_html( $results->get_display_name() ); ?></td>
            </tr>

            <!-- Display Name -->
            <tr>
                <th>Formatted Address</th>
                <td><?php echo esc_html( $results->get_display_name() ); ?></td>
            </tr>

            <!-- Address Components -->
            <tr>
                <th>Address Components</th>
                <td>
					<?php if ( $address = $results->get_address() ): ?>
						<?php foreach ( $address as $component => $value ): ?>
							<?php
							$label = ucwords( str_replace( '_', ' ', $component ) );
							echo esc_html( $label ) . ': ' . esc_html( $value ) . '<br>';
							?>
						<?php endforeach; ?>
					<?php else: ?>
                        <em>No detailed address components available.</em>
					<?php endif; ?>
                </td>
            </tr>

            <!-- OpenStreetMap Information -->
            <tr>
                <th>OSM Information</th>
                <td>
                    Place ID: <?php echo esc_html( $results->get_place_id() ); ?><br>
                    OSM Type: <?php echo esc_html( $results->get_osm_type() ); ?><br>
                    OSM ID: <?php echo esc_html( $results->get_osm_id() ); ?>
                </td>
            </tr>

            <!-- License Information -->
            <tr>
                <th>License</th>
                <td><?php echo esc_html( $results->get_license() ); ?></td>
            </tr>

            <!-- Bounding Box -->
			<?php if ( $results->has_bounding_box() ): ?>
                <tr>
                    <th>Bounding Box</th>
                    <td>
						<?php
						$bbox = $results->get_bounding_box();
						echo 'Min Latitude: ' . esc_html( $bbox['min_lat'] ) . '<br>';
						echo 'Max Latitude: ' . esc_html( $bbox['max_lat'] ) . '<br>';
						echo 'Min Longitude: ' . esc_html( $bbox['min_lon'] ) . '<br>';
						echo 'Max Longitude: ' . esc_html( $bbox['max_lon'] );
						?>
                    </td>
                </tr>
			<?php endif; ?>
            </tbody>
        </table>
		<?php
	}

	/**
	 * Render JavaScript for the page
	 */
	private function render_js() {
		?>
        <script>
            jQuery(document).ready(function ($) {
                $('input[name="test_type"]').change(function () {
                    if ($(this).val() === 'forward') {
                        $('.forward-fields').show();
                        $('.reverse-fields').hide();
                    } else {
                        $('.forward-fields').hide();
                        $('.reverse-fields').show();
                    }
                });
            });
        </script>
		<?php
	}

	/**
	 * Render debug information
	 */
	private function render_debug_info( $results ) {
		if ( $results instanceof Location ) {
			?>
            <div class="debug-info" style="background: #f5f5f5; padding: 15px; margin-top: 20px;">
                <h3>Raw Response Data:</h3>
                <pre style="background: #fff; padding: 10px; overflow: auto;">
                    <?php print_r( $results->get_raw_data() ); ?>
                </pre>
            </div>
			<?php
		}
	}
}

new Plugin();