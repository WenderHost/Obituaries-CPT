<?php
/**
 * Plugin Name: Obituaries Custom Post Type
 * Plugin URI:
 * Description: Provides an obituaries custom post type.
 * Version: 1.0.0
 * Author: Michael Wender
 * Author URI: http://michaelwender.com
 * License: GPL2
 */

class ObituariesCPT {

	public function __construct() {
		$this->plugin_dir_url = plugin_dir_url( __FILE__ );
		$this->plugin_dir_path = plugin_dir_path( __FILE__ );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'save_post', array( $this, 'save_obit_data' ) );

		add_filter( 'the_excerpt_rss', array( $this, 'modify_feed' ) );
		add_filter( 'the_content_feed', array( $this, 'modify_feed' ) );

		add_image_size( 'obit-profile-pic', 226, 9999 );
	}

	/**
	 * Enqueue scripts for the WP admin.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $hook WordPress admin page hook.
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook ) {
		$allowed_pages = array( 'post-new.php', 'post.php', 'page.php', 'edit.php' );
		if ( !in_array( $hook, $allowed_pages ) )
			return;

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'datepicker-styles', $this->plugin_dir_url . 'lib/css/smoothness/jquery-ui-1.7.2.custom.css' );
		wp_enqueue_script( 'admin-javascript', $this->plugin_dir_url . 'lib/js/admin.js', array( 'jquery' ) );
		wp_enqueue_style( 'admin-styles', $this->plugin_dir_url . 'lib/css/admin.css' );
	}

	/**
	 * Returns valid fields for Obit CPT meta forms.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $type Specify the form type.
	 * @return array Array of valid fields.
	 */
	private function get_valid_fields( $type = '' ) {
		switch ( $type ) {
		case 'preceedors':
			$fields = array( 'preceedor_name' => '', 'preceedor_relation' => '', 'preceedor_relation_text' => '' );
			break;
		case 'survivors':
			$fields = array( 'survivor_name' => '', 'survivor_relation' => '', 'survivor_relation_text' => '' );
			break;
		case 'services':
			$fields = array( 'service_type' => '', 'service_date' => '', 'start_time' => '', 'end_time' => '', 'service_location' => '', 'service_address' => '', 'city' => '', 'state' => '', 'zip' => '', 'map_url' => '', 'notes' => '' );
			break;
		default:
			$fields = array( 'lastname' => '', 'birthdate' => '', 'deathdate' => '', 'birthplace' => '', 'birthcity' => '', 'birthstate' => '', 'birthcountry' => '', 'deathplace' => '', 'deathcity' => '', 'deathstate' => '', 'deathcountry' => '', 'address' => '', 'address_city' => '', 'address_state' => '', 'zipcode' => '', 'gender' => '', 'maiden' => '', 'occupation' => '', 'hobbies' => '' );
			break;
		}
		return $fields;
	}

	/**
	 * Adds Obit CPT metabox and callback.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function metabox_init() {
		add_meta_box( 'obit-meta', 'Obituary Information', array( $this, 'metabox_for_obits' ), 'obituary', 'normal', 'low' );
	}

	/**
	 * Metabox interface for Obit CPT.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function metabox_for_obits() {
		global $post;
		$obit_info = get_post_meta( $post->ID, '_obit_info', true );
		if ( is_array( $obit_info ) ) {
			foreach ( $obit_info as $key => $value ) {
				$$key = $value;
			}
		}
?>
	<input type="hidden" id="obit_info" name="obit_info" value="true" />
<table class="form-table">
	<col width="18%" /><col width="82%" />
	<tr>
		<th scope="row"><strong>Last Name:</strong></th>
		<td><label for="lastname"><input class="medium" id="lastname" type="text" name="lastname" value="<?php echo $lastname ?>" /><br />Used as a key for database searches.</label></td>
	</tr>
	<tr>
		<th><strong>Birth Date:</strong></th>
		<td><label for="birthdate"><input class="datepicker" id="_birthdate" type="text" name="_birthdate" value="<?php echo date( 'm/d/Y', strtotime( $birthdate ) ) ?>" /><input type="hidden" id="birthdate" name="birthdate" value="<?php echo $birthdate ?>" /></label>
		</td>
	</tr>
	<tr>
		<th><strong>Death Date:</strong></th>
		<td><label for="deathdate"><input class="datepicker" id="_deathdate" type="text" name="_deathdate" value="<?php echo date( 'm/d/Y', strtotime( $deathdate ) ) ?>" /><input type="hidden" id="deathdate" name="deathdate" value="<?php echo $deathdate ?>" /></label></td>
	</tr>
	<tr>
		<th scope="row"><strong>Birth Place:</strong></th>
		<td>
		<div class="rightfields">
			Additional fields:<br />
			<label for="birthcity">Birth City:</label><input class="medium" id="birthcity" type="text" name="birthcity" value="<?php echo $birthcity ?>" /><br />
			<label for="birthstate">Birth State:</label><input class="medium" id="birthstate" type="text" name="birthstate" value="<?php echo $birthstate ?>" /><br />
			<label for="birthcountry">Birth Country:</label><input class="medium" id="birthcountry" type="text" name="birthcountry" value="<?php echo $birthcountry ?>" />
		</div>
		<label for="birthplace"><input class="medium" id="birthplace" type="text" name="birthplace" value="<?php echo $birthplace ?>" /><br />Typically: <em>City, State</em>; however, you can list a location (e.g. St. Mary's Hospital) and list the city and state in the fields to the right.</label></td>
	</tr>
	<tr>
		<th scope="row"><strong>Death Place:</strong></th>
		<td>
		<div class="rightfields">
			Additional fields:<br />
			<label for="deathcity">Death City:</label><input class="medium" id="deathcity" type="text" name="deathcity" value="<?php echo $deathcity ?>" /><br />
			<label for="deathstate">Death State:</label><input class="medium" id="deathstate" type="text" name="deathstate" value="<?php echo $deathstate ?>" /><br />
			<label for="deathcountry">Death Country:</label><input class="medium" id="deathcountry" type="text" name="deathcountry" value="<?php echo $deathcountry ?>" />
		</div>
		<label for="deathplace"><input class="medium" id="deathplace" type="text" name="deathplace" value="<?php echo $deathplace ?>" /><br />Typically: <em>City, State</em>; however, you can list a location (e.g. St. Mary's Hospital) and list the city and state in the fields to the right.</label></td>
	</tr>
	<tr>
		<th scope="row">Address:</th>
		<td><input class="large" id="address" type="text" name="address" value="<?php echo $address ?>" /><br />
		<label>City:</label> <input type="text" class="medium" id="address_city" name="address_city" value="<?php echo $address_city ?>" />, <label>State:</label> <input type="text" class="small" id="address_state" name="address_state" value="<?php echo $address_state ?>" /> <label>Zip:</label> <input type="text" class="small" id="zipcode" name="zipcode" value="<?php echo $zipcode ?>" /></td>
	</tr>
	<tr>
		<th scope="row">Gender:</th>
		<td><select name="gender"><?php
		$genders = array( 'Male' => 'M', 'Female' => 'F' );
		echo '<option value="">Select a gender:</option>';
		foreach ( $genders as $genderopt => $genderval ) {
			echo '<option value="'.$genderval.'"';
			if ( $gender == $genderval ) echo ' selected="selected"';
			echo '>'.$genderopt.'</option>';
		}
		?></select></td>
	</tr>
	<tr>
		<th scope="row"><strong>Maiden Name:</strong></th>
		<td><label for="maiden"><input class="medium" id="maiden" type="text" name="maiden" value="<?php echo $maiden ?>" /></label></td>
	</tr>
	<tr>
		<th scope="row"><strong>Occupation:</strong></th>
		<td><label for="occupation"><textarea class="medium" id="occupation" type="text" name="occupation"><?php echo esc_textarea( $occupation ) ?></textarea></label></td>
	</tr>
	<tr>
		<th scope="row"><strong>Hobbies:</strong></th>
		<td><label for="hobbies"><textarea class="medium" id="hobbies" type="text" name="hobbies"><?php echo esc_textarea( $hobbies ) ?></textarea></label></td>
	</tr>
	<tr>
		<th scope="row">Services:</th>
		<td id="services"><?php
		$services = get_post_meta( $post->ID, '_obit_services', true );
		for ( $x = 0; $x < 5; $x++ ) {
			$service = '';
			if ( isset( $services[$x] ) ) {
				$service = $services[$x];
			} else {
				unset( $service );
			}
			?><table class="service-table"<?php if ( !isset( $service ) && $x > 0 ) echo ' style="display: none"' ?>>
			<col width="12%" /><col width="22%" /><col width="22%" /><col width="22%" /><col width="22%" />
			<tr>
				<th>Type:</th>
				<td><input class="medium" id="service_type[]" type="text" name="service_type[]" value="<?php echo $service['service_type'] ?>" /></td>
				<th colspan="3">Date: <input class="datepicker small" id="_service_date[<?php echo $x ?>]" type="text" name="_service_date[<?php echo $x ?>]" value="<?php echo ( isset( $service ) )? date( 'm/d/Y', strtotime( $service['service_date'] ) ) : date( 'm/d/Y', strtotime( substr( current_time( 'mysql' ), 0, 10 ) ) )  ?>" /><input type="hidden" id="service_date[<?php echo $x ?>]" name="service_date[<?php echo $x ?>]" value="<?php echo ( isset( $service ) )? $service['service_date'] : substr( current_time( 'mysql' ), 0, 10 ) ?>" /> Start: <input class="small" id="start_time[]" type="text" name="start_time[]" value="<?php echo $service['start_time'] ?>" /> End: <input class="small" id="end_time[]" type="text" name="end_time[]" value="<?php echo $service['end_time'] ?>" /></th>
			</tr>
			<tr>
				<th>Location:</th>
				<td colspan="4"><input class="large" id="service_location[]" type="text" name="service_location[]" value="<?php echo $service['service_location'] ?>" /></td>
			</tr>
			<tr>
				<th>Address:</th>
				<td colspan="4"><input class="large" id="service_address[]" type="text" name="service_address[]" value="<?php echo $service['service_address'] ?>" /></td>
			</tr>
			<tr>
				<th>City:</th>
				<td><input class="medium" id="city[]" type="text" name="city[]" value="<?php echo $service['city'] ?>" /></td>
				<th style="text-align: right">State/Zip:</th>
				<td colspan="2"><input class="small" id="state[]" type="text" name="state[]" value="<?php echo $service['state'] ?>" /> / <input class="small" id="zip[]" type="text" name="zip[]" value="<?php echo $service['zip'] ?>" /></td>
			</tr>
			<tr>
				<th>Map URL:</th>
				<td colspan="4"><input class="large" id="map_url[]" type="text" name="map_url[]" value="<?php echo $service['map_url'] ?>" /></td>
			</tr>
			<tr>
				<th>Notes:</th>
				<td colspan="4"><textarea class="medium" id="notes[]" type="text" name="notes[]"><?php echo esc_textarea( $service['notes'] ) ?></textarea></td>
			</tr>
			</table>
			<?php
		}
		?></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><a href="#" id="addservice">Add a Service</a> <span class="border-left" id="remove"<?php if ( count( $services ) < 2 ) echo ' style="display: none"' ?>><a href="#" id="removeservice">Remove a Service</a></span></td>
	</tr>
	<tr>
		<th scope="row">Survived by:</th>
		<td><?php
			$survivors = get_post_meta( $post->ID, '_obit_survivors', true );
		//echo '<pre>$survivors = '.print_r( $survivors, true ).'</pre>';
		for ( $x = 0; $x < 21; $x++ ) {
			$survivor = '';
			if ( isset( $survivors[$x] ) ) {
				$survivor = $survivors[$x];
			} else {
				unset( $survivor );
			}
			?><table class="survivor-table"<?php if ( !isset( $survivor ) && $x > 0 ) echo ' style="display: none"' ?>>
			<col width="35%" /><col width="20%" /><col width="25%" />
			<tr>
				<th>Name: <input class="medium" id="survivor_name[]" type="text" name="survivor_name[]" value="<?php echo $survivor['survivor_name'] ?>" /></th>
				<th>Relation: <select name="survivor_relation[]" id="survivor_relation[]"><?php
				$options = array( 'Spouse', 'Mother', 'Father', 'Sister', 'Brother', 'Daugher', 'Son', 'other' );
			echo '<option value="">select...</option>';
			foreach ( $options as $option ) {
				echo '<option value="'.$option.'"';
				if ( $option == $survivor['survivor_relation'] ) echo ' selected="selected"';
				echo '>'.$option.'</option>';
			}
			?></select></th>
				<th>Relation Text: <input class="small" id="survivor_relation_text[]" type="text" name="survivor_relation_text[]" value="<?php echo $survivor['survivor_relation_text'] ?>" /></th>
			</tr>
			</table><?php
		}
		?></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><a href="#" id="addsurvivor">Add a Survivor</a> <span class="border-left" id="removesurvivorspan"<?php if ( count( $survivors ) < 2 ) echo ' style="display: none"' ?>><a href="#" id="removesurvivor">Remove a Survivor</a></span></td>
	</tr>
	<tr>
		<th scope="row">Preceeded by:</th>
		<td><?php
			$preceedors = get_post_meta( $post->ID, '_obit_preceedors', true );
		//echo '<pre>$preceedors = '.print_r( $preceedors, true ).'</pre>';
		for ( $x = 0; $x < 21; $x++ ) {
			$preceedor = '';
			if ( isset( $preceedors[$x] ) ) {
				$preceedor = $preceedors[$x];
			} else {
				unset( $preceedor );
			}
			?><table class="preceedor-table"<?php if ( !isset( $preceedor ) && $x > 0 ) echo ' style="display: none"' ?>>
			<col width="35%" /><col width="20%" /><col width="25%" />
			<tr>
				<th>Name: <input class="medium" id="preceedor_name[]" type="text" name="preceedor_name[]" value="<?php echo $preceedor['preceedor_name'] ?>" /></th>
				<th>Relation: <select name="preceedor_relation[]" id="preceedor_relation[]"><?php
				// other
				$options = array( 'Spouse', 'Mother', 'Father', 'Sister', 'Brother', 'Daugher', 'Son', 'other' );
			echo '<option value="">select...</option>';
			foreach ( $options as $option ) {
				echo '<option value="'.$option.'"';
				if ( $option == $preceedor['preceedor_relation'] ) echo ' selected="selected"';
				echo '>'.$option.'</option>';
			}
			?></select></th>
				<th>Relation Text: <input class="small" id="preceedor_relation_text[]" type="text" name="preceedor_relation_text[]" value="<?php echo $preceedor['preceedor_relation_text'] ?>" /></th>
			</tr>
			</table><?php
		}
		?></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><a href="#" id="addpreceedor">Add a Preceedor</a> <span class="border-left" id="removepreceedorspan"<?php if ( count( $preceedors ) < 2 ) echo ' style="display: none"' ?>><a href="#" id="removepreceedor">Remove a Preceedor</a></span></td>
	</tr>
</table>
	<?php
	}

	/**
	 * Only show the post excerpt for obit feed RSS.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $content Obit content.
	 * @return string $content Obit excerpt.
	 */
	private function modify_feed( $content ) {
		global $post;
		$content = apply_filters( 'get_the_excerpt', $post->post_excerpt );
		return $content;
	}

	/**
	 * Registers Obit CPT.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_post_types() {
		$labels = array(
			'singular_name' => 'Obituary',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Obituary',
			'edit_item' => 'Edit Obituary',
			'new_item' => 'New Obituary',
			'view_item' => 'View Obituary',
			'search_items' => 'Search Obituaries',
			'not_found' => 'No obituaries found',
			'not_found_in_trash' => 'No obituaries found in Trash',
			'parent_item_colon' => 'Parent Obituary'
		);

		register_post_type( 'obituary', array(
				'label' => 'Obituaries',
				'labels' => $labels,
				'public' => true,
				'exclude_from_search' => false,
				'show_ui' => true,
				'hierarchical' => false,
				'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'comments', 'custom-fields', 'publicize' ),
				'menu_position' => 20,
				'show_in_nav_menus' => false,
				'register_meta_box_cb' => array( $this, 'metabox_init' ),
				'menu_icon' => $this->plugin_dir_url . 'lib/img/icon.obit.png',
				'has_archive' => true,
				'rewrite' => array( 'slug' => 'obits' )
			) );
	}

	/**
	 * Save obit CPT meta data.
	 *
	 * @since 1.0.0
	 *
	 * @param int     $postid Current post ID.
	 * @return void
	 */
	public function save_obit_data( $postid ) {
		// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $postid;

		// Save obit info
		$valid_fields = $this->get_valid_fields();
		$obit_info = get_post_meta( $postid, '_obit_info', true );
		if ( $_POST['obit_info'] == true ) {
			foreach ( $valid_fields as $field => $default ) {
				( isset( $_POST[$field] ) && !empty( $_POST[$field] ) )? $obit_info[$field] = $_POST[$field] : $obit_info[$field] = $default;
			}
		}

		$update_string = ' ($postid = '.$postid.', data: '.$obit_info.')';
		if ( get_post_meta( $postid, '_obit_info' ) == '' )
			add_post_meta( $postid, '_obit_info', $obit_info );
		elseif ( $obit_info != get_post_meta( $postid, '_obit_info', true ) )
			update_post_meta( $postid, '_obit_info', $obit_info );
		elseif ( $obit_info == '' )
			delete_post_meta( $postid, '_obit_info', get_post_meta( $postid, '_obit_info', true ) );

		// Save relations data
		$valid_fields = $this->get_valid_fields( 'survivors' );
		for ( $x = 0; $x < 19; $x++ ) {
			if ( !empty( $_POST['survivor_name'][$x] ) ) {
				foreach ( $valid_fields as $key => $value ) {
					$survivors_meta[$x][$key] = $_POST[$key][$x];
				}
			}
		}
		update_post_meta( $postid, '_obit_survivors', $survivors_meta );

		$valid_fields = $this->get_valid_fields( 'preceedors' );
		for ( $x = 0; $x < 19; $x++ ) {
			if ( !empty( $_POST['preceedor_name'][$x] ) ) {
				foreach ( $valid_fields as $key => $value ) {
					$preceedors_meta[$x][$key] = $_POST[$key][$x];
				}
			}
		}
		update_post_meta( $postid, '_obit_preceedors', $preceedors_meta );

		// Save services data
		$valid_fields = $this->get_valid_fields( 'services' );
		for ( $x = 0; $x < 5; $x++ ) {
			if ( !empty( $_POST['service_type'][$x] ) ) {
				foreach ( $valid_fields as $key => $value ) {
					$services_meta[$x][$key] = $_POST[$key][$x];
				}
			}
		}
		update_post_meta( $postid, '_obit_services', $services_meta );
	}
}

require_once plugin_dir_path( __FILE__ ) . 'lib/fns/fns.widgets.php';

if ( class_exists( 'ObituariesCPT' ) ) {
	// Installation and uninstallation hooks
	// register_activation_hook(__FILE__, array('ObituariesCPT', 'activate'));
	// register_deactivation_hook(__FILE__, array('ObituariesCPT', 'deactivate'));

	// instantiate the plugin class
	$ObituariesCPT = new ObituariesCPT();
}

require_once plugin_dir_path( __FILE__ ) . 'lib/classes/obituaries-importer.php';
