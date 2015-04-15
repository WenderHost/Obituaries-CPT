<?php
class ObituariesImporter extends ObituariesCPT{

	public function __construct() {
		$this->obit_import_limit = 20; // set the limit of obits imported during each pass of the import script
		$this->obit_legacy_tables = array( 'obits_bio', 'obits_guestbook', 'obits_info', 'obits_photos', 'obits_preceded_in_death', 'obits_service', 'obits_survived_by', 'obits_videos', 'obits_documents', 'obits_stories' );

		add_action( 'admin_menu', array( $this, 'register_import_option_page' ) );
	}

	/**
	 * check_data()
	 */
	private function check_data() {
		global $wpdb;
		$wpdb->show_errors();
		$sql = 'SELECT * FROM
		'.$wpdb->prefix.'obits_bio AS bio,
		'.$wpdb->prefix.'obits_info AS info
		WHERE
			bio.userid=info.userid
		ORDER BY bio.userid ASC';
		$obits = $wpdb->get_results( $sql );
		if ( $obits ) {
			$basedir = obits_getinfo( 'basedir' );
			foreach ( $obits as $obit ) {
				$data_dir = trailingslashit( $basedir ).$obit->userid;
				if ( !file_exists( $data_dir ) ) {
					$missing_data[] = '<div class="updated"><p>No data directory found for <em>'.$obit->first.' '.$obit->last.'</em> <code>'.$obit->userid.'</code> (<code>'.$data_dir.'</code>).</p></div>';
				}
			}
			if ( count( $missing_data ) > 0 ) {
				echo '<p><strong>Missing Data Directories</strong><br />NOTE: The following obituaries are missing data directories. If imported, these obituaries will not have any associated images and/or video:</p>';
				echo implode( '', $missing_data );
			}
		} else {
			$GLOBALS['noobituaries'] = true;
			echo '<div class="updated"><p>No obituaries found. It appears that all obituaries have been imported. You may now <a href="'.wp_nonce_url( 'edit.php?post_type=obituary&page=import_obits&action=dropalltables', 'dropalltables' ).'">REMOVE ALL OBITUARY TABLES</a> from the database.</p></div>';
		}
	}

	/**
	 * check_tables() - checks to see if neccessary tables exist
	 */
	private function check_tables() {

		$missing = array();
		foreach ( $this->obit_legacy_tables as $table ) {
			$exists = $this->table_exists( $table );
			if ( $exists == false ) $missing[] = $table;
		}
		if ( count( $missing ) == 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Creates tables associated with legacy obituary data
	 *
	 * @since 1.0.0
	 *
	 * @param string  $table_suffix Optional. Table name suffix.
	 * @param array   $columns      Optional. Table columns.
	 * @return bool Returns TRUE if table was created. Fails if unable to create the table.
	 */
	private function create_table( $table_suffix = null, $columns = array() ) {
		global $wpdb;
		$table_name = $wpdb->prefix.$table_suffix;
		$sql = array();
		foreach ( $columns as $column ) {
			switch ( $column ) {
			case 'biography':
			case 'description':
				$sql[] = '`'.$column.'` TEXT NOT NULL';
				break;
			case 'userid':
				$sql[] = '`userid` INT(11) UNSIGNED NOT NULL';
				break;
			default:
				$sql[] = '`'.$column.'` VARCHAR(255) NOT NULL';
				break;
			}
		}
		$sql = 'CREATE TABLE `'.$table_name.'` ('."\n".implode( ",\n", $sql )."\n".') ENGINE = MYISAM DEFAULT CHARSET=utf8';
		//die( '<pre>'.print_r( $sql, true ).'</pre>' );
		$success = $wpdb->query( $sql );
		if ( $success == false ) {
			wp_die( '<h3>Could not create table '.$table_name.':</h3><pre>'.print_r( $sql, true ).'</pre>' );
		} else {
			return true;
		}
	}

	/**
	 * Removes tables associated with legacy obituary data
	 *
	 * @since 1.0.0
	 *
	 * @param string  $table_suffix Optional. Table name suffix.
	 * @return bool Returns TRUE if table was dropped.
	 */
	private function droptable( $table_suffix = null ) {
		if ( !empty( $table_suffix ) && stristr( $table_suffix, 'obits' ) ) {
			global $wpdb;
			$success = $wpdb->query( 'DROP TABLE '.$wpdb->prefix.$table_suffix );
			return ( $success == 1 )? true : false;
		}
	}

	/**
	 * $this->getinfo() - return various info about the obits data
	 */
	private function getinfo( $request = '' ) {
		switch ( $request ) {
		case 'basedir':
			$upload_dir = wp_upload_dir();
			$info = $upload_dir['basedir'].'/obits';
			break;
		case 'mediadir':
			$upload_dir = wp_upload_dir();
			$info = $upload_dir['basedir'].'/obits_media';
			break;
		}
		return $info;
	}

	/**
	 * Processes actions for Obituary import
	 *
	 * @since 1.0.0
	 *
	 * @param string  $action Optional. Switches the action performed by this method.
	 * @return void
	 */
	private function import_csv_actions( $action = null ) {
		if ( $action != null ) {
			switch ( $action ) {
			case 'dropalltables':
				if ( check_admin_referer( 'dropalltables' ) ) {

					foreach ( $this->obit_legacy_tables as $table_suffix ) {
						$status = $this->droptable( $table_suffix );
						if ( $status == true ) {
							echo '<div id="message" class="updated below-h2"><p>`<code>'.$wpdb->prefix.$table_suffix.'</code>` was dropped.</p></div>';
						} else {
							echo '<div id="message" class="error below-h2"><p>`<code>'.$wpdb->prefix.$table_suffix.'</code>` was not dropped.</p></div>';
						}
					}
				}
				break;
			case 'droptable':
				if ( !empty( $_GET['table'] ) && stristr( $_GET['table'], 'obits' ) && check_admin_referer( 'droptable' ) ) {
					$status = $this->droptable( $_GET['table'] );
					if ( $status == true ) {
						echo '<div id="message" class="updated below-h2"><p>`<code>'.$wpdb->prefix.$_GET['table'].'</code>` was dropped.</p></div>';
					} else {
						echo '<div id="message" class="error below-h2"><p>`<code>'.$wpdb->prefix.$_GET['table'].'</code>` was not dropped.</p></div>';
					}
				}
				break;
			case 'importobits':
				if ( check_admin_referer( 'importobits' ) ) {

				}
				break;
			case 'importtable':
				if ( !empty( $_GET['table'] ) && stristr( $_GET['table'], 'obits' ) && check_admin_referer( 'importtable' ) ) {
					$status = $this->importtable( $_GET['table'] );
					if ( $status == false ) {
						echo '<div id="message" class="error below-h2"><p>`<code>'.$wpdb->prefix.$_GET['table'].'</code>` was not imported.</p></div>';
					}
				}
				break;
			default:
				die( '<p>No action defined for `<code>'.$action.'</code>`.</p>' );
				break;
			}
		}

	}

	/**
	 * Import options page interface.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function import_option_page() {
		global $wpdb;
		?><div class="wrap">
<div id="icon-options-general" class="icon32"></div><h2>Import Obituaries</h2>
<?php
		$basedir = $this->getinfo( 'basedir' );
		$mediadir = $this->getinfo( 'mediadir' );
		if ( file_exists( $basedir ) && file_exists( $mediadir ) && is_writable( $mediadir ) ) {
			if ( $this->check_tables() ) {
				$this->check_data();
				if ( $_GET['action'] == 'importobits' ) {
					$this->import_csv_actions( $_GET['action'] );

					$sql = 'SELECT * FROM
    '.$wpdb->prefix.'obits_bio AS bio,
    '.$wpdb->prefix.'obits_info AS info
    WHERE
    	bio.userid=info.userid
    ORDER BY bio.userid ASC LIMIT '.OBIT_IMPORT_LIMIT;
					$wpdb->show_errors();
					$obits = $wpdb->get_results( $sql, ARRAY_A );
					if ( $obits ) {
						echo '<table class="widefat">';
						$x = 0;
						foreach ( $obits as $obit ) {

							$name = array();
							if ( !empty( $obit['first'] ) ) $name[] = trim( $obit['first'] );
							if ( !empty( $obit['middle'] ) ) $name[] = trim( $obit['middle'] );
							if ( !empty( $obit['last'] ) ) $name[] = trim( $obit['last'] );
							$obit_post = array(
								'post_status' => 'publish',
								'post_type' => 'obituary',
								'post_title' => implode( ' ', $name ),
								'post_content' => $obit['biography'],
								'post_date' => $obit['date_created']
							);
							$post_id = wp_insert_post( $obit_post );
							if ( $post_id ) {
								$this->obit_import_attachments( $post_id, $obit['userid'] );
								$this->obit_import_info( $post_id, $obit );
								$this->obit_import_services( $post_id, $obit['userid'] );
								$this->obit_import_comments( $post_id, $obit['userid'] );
								$this->obit_import_relations( $post_id, $obit['userid'] );
							}
							echo ( $x % 2 )? '<tr class="alternate">' : '<tr>';
							echo '<td>Imported: <em>'.implode( ' ', $name ).'</em></td></tr>';
							foreach ( $this->obit_legacy_tables as $table_suffix ) {
								$wpdb->query( 'DELETE FROM '.$wpdb->prefix.$table_suffix.' WHERE userid='.$obit['userid'] );
							}
							$x++;
						}
						echo '</table>';
					}
					echo '<h3>Continue Import</h3><p><a href="'.wp_nonce_url( 'edit.php?post_type=obituary&page=import_obits&action=importobits', 'importobits' ).'">Click here</a> to continue the import by importing the next ' . $this->obit_import_limit . ' obituaries.</p>';
				} else if ( $_GET['action'] == 'dropalltables' ) {
						$this->import_csv_actions( 'dropalltables' );
					} else {
					if ( $GLOBALS['noobituaries'] != true ) {
						echo '<h3>Start Import</h3><p><a href="'.wp_nonce_url( 'edit.php?post_type=obituary&page=import_obits&action=importobits', 'importobits' ).'">Click here</a> to start importing the next ' . $this->obit_import_limit . ' obituaries.</p>';
					}
				}
			} else {
				$basedir = obits_getinfo( 'basedir' );
				if ( file_exists( $basedir ) ) {
					if ( isset( $_GET['action'] ) && !empty( $_GET['action'] ) ) $this->import_csv_actions( $_GET['action'] );

					$dh = opendir( $basedir );
					echo '<table class="widefat"><col width="40%" /><col width="40%" /><col width="20%" />';
					echo '<thead><tr><th>CSV</th><th>Status</th><th>&nbsp;</th></tr></thead>';
					echo '<tfoot><tr><th>CSV</th><th>Status</th><th>&nbsp;</th></tr></tfoot>';
					echo '<tbody>';
					$obit_csv_files = array(); // used for counting csv files that are present
					while ( false !== ( $resource = readdir( $dh ) ) ) {
						if ( is_file( $basedir.'/'.$resource ) && ( substr( $resource, -3 ) == 'csv' ) ) {
							$table_suffix = substr( $resource, 0, -4 );
							( in_array( $table_suffix, $this->obit_legacy_tables ) )? $required = true : $required = false;
							$obit_csv_files[] = $table_suffix;
							$csvs[] = array(
								'filename' => $resource,
								'table_suffix' => $table_suffix,
								'required' => $required,
							);
						}
					}
					$x = 0;
					if ( count( $csvs ) > 0 ) {
						$missing_csv_files = array();
						$missing_tables = array();
						foreach ( $this->obit_legacy_tables as $table ) {
							if ( ! in_array( $table, $obit_csv_files ) ) $missing_csv_files[] = $table;
							if ( ! $this->table_exists( $table ) ) $missing_tables[] = $table;
						}
						if ( count( $missing_csv_files ) > 0 ) echo '<tr class="false"><td colspan="3"><p><strong class="red">IMPORTANT:</strong> The following required CSV files were not found: <code>'.implode( '.csv</code>, <code>', $missing_csv_files ).'.csv</code>.</p></td></tr>';
						if ( count( $missing_tables ) > 0 ) {
							echo '<tr class="false"><td colspan="3"><p><strong class="red">INCOMPLETE:</strong> The following required database tables are missing: <code>'.$wpdb->prefix.implode( '</code>, <code>'.$wpdb->prefix, $missing_tables ).'</code>. Select <strong>Import Table</strong> next to the corresponding CSV files below:</p></td></tr>';
						} else {
							echo '<tr class="true"><td colspan="3"><p><strong class="green">CONTINUE:</strong> All required database tables found. <a href="edit.php?post_type=obituary&page=import_obits">Proceed with importing the obituaries</a>.</p></td></tr>';
						}
						foreach ( $csvs as $csv ) {
							echo ( $x % 2 )? '<tr class="alternate">' : '<tr>';
							echo '<td>'.$csv['filename'].'</td>';
							echo '<td>';
							$exists = $this->table_exists( $csv['table_suffix'] );
							if ( $exists ) {
								echo '<strong class="green">OK:</strong> <code class="true">'.$wpdb->prefix.$csv['table_suffix'].'</code> exists.';
							} else {
								echo 'Table `<code class="false">'.$wpdb->prefix.$csv['table_suffix'].'</code>` not found.';
							}
							if ( $csv['required'] == true ) echo ' <code>*Required</code>';
							echo '</td><td style="text-align: right">';
							if ( $exists ) {
								echo '<a href="'.wp_nonce_url( 'edit.php?post_type=obituary&page=import_obits&action=droptable&table='.$csv['table_suffix'], 'droptable' ).'">Drop Table</a>';
							} else {
								echo '<a href="'.wp_nonce_url( 'edit.php?post_type=obituary&page=import_obits&action=importtable&table='.$csv['table_suffix'], 'importtable' ).'">Import Table</a>';
							}
							echo '</td></tr>';
							$x++;
						}
					} else {
						echo '<tr><td colspan="3">No CSVs found.</td></tr>';
					}
					echo '</tbody>';
					echo '</table>';
?>

<?php } else {
					?><div id="message" class="error below-h2"><p><strong>`obits` directory not found!</strong><br />Your obituary CSV files must be found in the following directory:<br /><br /><code><?php echo $basedir ?></code><br /><br />Make sure the above directory exists and contains your CSVs for import.</p></div><?php
				}
			}
		} else {
			if ( !file_exists( $basedir ) ) {
				?><div id="message" class="error below-h2"><p><strong>`obits` directory not found!</strong><br />Your obituary data must be found in the following directory:<br /><br /><code><?php echo $basedir ?></code><br /><br />Make sure the above directory exists and contains your CSVs and files for import.</p></div><?php
			}
			if ( !file_exists( $mediadir ) ) {
				?><div id="message" class="error below-h2"><p><strong>`obits_media` directory not found!</strong><br />Please create the following directory:<br /><br /><code><?php echo $mediadir ?></code></p></div><?php
			} else if ( !is_writable( $mediadir ) ) {
					?><div id="message" class="error below-h2"><p><strong>`obits_media` not writable!</strong><br />Please make the following directory writable by the webserver (i.e. <code>chmod 777 <?php basename( $media_dir ) ?></code>):<br /><br /><code><?php echo $mediadir ?></code></p></div><?php
				}
		}
?>
</div><?php
	}

	/**
	 * Imports a legacy obituary table.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $table_suffix Table name suffix.
	 * @return bool Returns TRUE if table successfully imported.
	 */
	private function importtable( $table_suffix ) {
		$basedir = $this->getinfo( 'basedir' );
		$fp = fopen( trailingslashit( $basedir ).$table_suffix.'.csv', 'r' );

		if ( $fp !== FALSE ) {
			//echo '<h4>Importing '.$table_suffix.'.csv...</h4>';
			//echo '<table class="widefat">';
			$x = 0;
			$inserts = 0;
			$cols = array();
			while ( ( $data = fgetcsv( $fp, 0, ',' ) ) !== FALSE ) {
				$col_count = count( $data );
				if ( $x == 0 ) {
					foreach ( $data as $key => $value ) {
						$cols[] = $value;
					}
				}
				$mapped_data = array();
				//echo '<tr>';
				for ( $y = 0; $y < $col_count; $y++ ) {
					$td = trim( $data[$y] );
					$mapped_data[ $cols[$y] ] = $td;
					$td = wpautop( convert_chars( $td ) );
					$td = mb_convert_encoding( $td, 'UTF-8', 'HTML-ENTITIES' );
					//echo '<td>'.$td.'</td>';
				}
				//echo '</tr>';
				$success = $this->insert_row( $table_suffix, $data[0], $mapped_data );
				if ( $success == true ) {
					//echo '<tr><td colspan="'.$col_count.'"><strong style="color: #090">SUCCESS!:</strong> Row inserted (userid: '.$data[0].').</td></tr>';
					$inserts++;
				} else {
					//if( $x != 0 ) echo '<tr><td colspan="'.$col_count.'"><strong style="color: #900">FAILURE!:</strong> Row NOT inserted (userid: '.$data[0].').</td></tr>';
				}
				$x++;
			}
			//echo '<tfoot><tr><td colspan="'.$col_count.'"><strong>'.$table_suffix.'</strong> Inserted:  '.$inserts.'/'.($x - 1).' rows</td></tr></tfoot>';
			//echo '</table>';
			echo '<div id="message" class="updated below-h2"><p>`<code>'.$wpdb->prefix.$_GET['table'].'</code>` was imported. Inserted:  '.$inserts.'/'.( $x - 1 ).' rows.</p></div>';
			return true;
		} else {
			return false;
		}
	}

	private function insert_row( $table_suffix = null, $id, $data = array() ) {
		global $wpdb;
		$table_name = $wpdb->prefix.$table_suffix;
		if ( is_numeric( $id ) ) {
			switch ( $table_suffix ) {
			case 'obits_guestbook':
			case 'obits_photos':
			case 'obits_videos':
			case 'obits_preceded_in_death':
			case 'obits_service':
			case 'obits_survived_by':
			case 'obits_videos':
				$wpdb->insert( $table_name, $data );
				return true;
				break;
			default:
				$row = $wpdb->get_row( 'SELECT * FROM '.$table_name.' WHERE userid='.$id );
				if ( $row ) {
					return false;
				} else {
					$wpdb->insert( $table_name, $data );
					return true;
				}
				break;
			}
		} else {
			if ( $this->table_exists( $table_suffix ) == false ) {
				$this->create_table( $table_suffix, $data );
			}
		}
	}

	/**
	 * Registers import options page in the WordPress admin.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_import_option_page() {
		add_submenu_page( 'edit.php?post_type=obituary', 'Import Obituaries', 'Import Obituaries', 'activate_plugins', 'import_obits', array( $this, 'import_option_page' ) );
	}

	/**
	 * table_exists() - checks for existence of a DB table for a given table suffix
	 */
	private function table_exists( $table_suffix = null ) {
		if ( $table_suffix != null ) {
			global $wpdb;
			$sql = 'SHOW TABLES LIKE "'.$wpdb->prefix.$table_suffix.'"';
			$success = $wpdb->query( $sql );
			if ( $success == 1 ) {
				return true;
			} else {
				return false;
			}
		}
	}

	/*********************
	 * OBITUARY DATA IMPORT FUNCTIONS
	 ********************/

	/**
	 * obit_import_attachments() - note: $imgpath corresponds to $userid
	 */
	private function obit_import_attachments( $post_ID, $imgpath ) {
		$upload_dir = wp_upload_dir();
		$imgdir = $upload_dir['basedir'].'/obits/'.$imgpath.'/';
		$profile_pic = $imgpath.'_profile_pic.jpg';
		$new_imgdir = trailingslashit( obits_getinfo( 'mediadir' ) );
		if ( file_exists( $imgdir.$profile_pic ) ) {
			$wp_filetype = wp_check_filetype( $profile_pic, null );
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title' => $profile_pic,
				'post_content' => '',
				'post_status' => 'inherit'
			);
			copy( $imgdir.$profile_pic, $new_imgdir.$profile_pic );
			//*
			$attach_id = wp_insert_attachment( $attachment, $new_imgdir.$profile_pic, $post_ID );
			require_once ABSPATH . "wp-admin" . '/includes/image.php';
			$attach_data = wp_generate_attachment_metadata( $attach_id, $new_imgdir.$profile_pic );
			wp_update_attachment_metadata( $attach_id,  $attach_data );
			update_post_meta( $post_ID, '_thumbnail_id', $attach_id, true );
			/**/
		}
		$gallerydir = $upload_dir['basedir'].'/obits/'.$imgpath.'/galleries/';
		if ( file_exists( $gallerydir ) ) {
			$dh = opendir( $gallerydir );
			while ( false !== ( $resource = readdir( $dh ) ) ) {
				if ( is_dir( $gallerydir.$resource ) ) {
					$gallery_dh = opendir( $gallerydir.$resource );
					while ( false !== ( $img = readdir( $gallery_dh ) ) ) {
						if ( is_file( $gallerydir.$resource.'/'.$img ) && ( substr( $img, 0, 5 ) == 'orig_' ) && ( !stristr( $img, $imgpath ) ) ) {
							$wp_filetype = wp_check_filetype( $gallerydir.$resource.'/'.$img, null );
							$attachment = array(
								'post_mime_type' => $wp_filetype['type'],
								'post_title' => substr( $img, 5 ),
								'post_content' => '',
								'post_status' => 'inherit'
							);
							copy( $gallerydir.$resource.'/'.$img, $new_imgdir.$img );
							$attach_id = wp_insert_attachment( $attachment, $new_imgdir.$img, $post_ID );
							require_once ABSPATH . "wp-admin" . '/includes/image.php';
							$attach_data = wp_generate_attachment_metadata( $attach_id, $new_imgdir.$img );
							wp_update_attachment_metadata( $attach_id,  $attach_data );
						}
					}
				}
			}
		}
		$videosdir = $upload_dir['basedir'].'/obits/'.$imgpath. '/videos/';
		if ( file_exists( $videosdir ) ) {
			$dh = opendir( $videosdir );
			while ( false !== ( $resource = readdir( $dh ) ) ) {
				if ( is_file( $videosdir.$resource ) ) {
					$wp_filetype = wp_check_filetype( $videosdir.$resource, null );
					$attachment = array(
						'post_mime_type' => $wp_filetype['type'],
						'post_title' => $resource,
						'post_content' => '',
						'post_status' => 'inherit'
					);
					copy( $videosdir.$resource, $new_imgdir.$resource );
					$attach_id = wp_insert_attachment( $attachment, $new_imgdir.$resource, $post_ID );
				}
			}
		}

	}

	private function obit_import_info( $post_ID, $obit = array() ) {
		if ( count( $obit ) == 0 )
			return false;

		$obit_info = obit_valid_fields();
		foreach ( $obit_info as $key => $value ) {
			switch ( $key ) {
			case 'address_city':
				$obit_info[$key] = $obit['city'];
				break;
			case 'address_state':
				$obit_info[$key] = $obit['state'];
				break;
			case 'lastname':
				$obit_info[$key] = $obit['last'];
				break;
			default:
				$obit_info[$key] = $obit[$key];
				break;
			}
		}
		update_post_meta( $post_ID, '_obit_info', $obit_info );
	}

	private function obit_import_services( $post_ID, $userid ) {
		global $wpdb;
		$services = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'obits_service WHERE userid='.$userid.' ORDER BY service_date ASC', ARRAY_A );
		$service_types = array(); // use this array to prevent multiple entries of the same service type
		foreach ( $services as $service ) {
			if ( !in_array( $service['service_type'], $service_types ) ) {
				$services_meta = get_post_meta( $post_ID, '_obit_services', true ); // get _obit_services so that we can add to the existing list of services
				$service_types[] = $service['service_type'];
				$service['service_location'] = $service['location'];
				$service['service_address'] = $service['address'];
				unset( $service['userid'], $service['location'], $service['address'] );
				$services_meta[] = $service;
				update_post_meta( $post_ID, '_obit_services', $services_meta );
			}
		}

	}

	private function obit_import_relations( $post_ID, $userid ) {
		global $wpdb;
		$survivors = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'obits_survived_by WHERE userid='.$userid. ' ORDER BY relation ASC', ARRAY_A );
		$previous_survivors = array();
		foreach ( $survivors as $survivor ) {
			if ( !in_array( $survivor['name'], $previous_survivors ) ) {
				$previous_survivors[] = $survivor['name'];
				$survivors_meta = get_post_meta( $post_ID, '_obit_survivors', true );
				unset( $survivor['userid'] );
				foreach ( $survivor as $key => $value ) {
					$remapped_survivor['survivor_'.$key] = $value;
				}
				$survivors_meta[] = $remapped_survivor;
				update_post_meta( $post_ID, '_obit_survivors', $survivors_meta );
			}
		}
		$preceedors = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'obits_preceded_in_death WHERE userid='.$userid. ' ORDER BY relation ASC', ARRAY_A );
		$previous_preceedors = array();
		foreach ( $preceedors as $preceedor ) {
			if ( !in_array( $preceedor['name'], $previous_preceedors ) ) {
				$preceedors_meta = get_post_meta( $post_ID, '_obit_preceedors', true );
				unset( $preceedor['userid'] );
				foreach ( $preceedor as $key => $value ) {
					$remapped_preceedor['preceedor_'.$key] = $value;
				}
				$preceedors_meta[] = $remapped_preceedor;
				update_post_meta( $post_ID, '_obit_preceedors', $preceedors_meta );
			}
		}
	}

	private function obit_import_comments( $post_ID, $userid ) {
		global $wpdb;
		$comments = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'obits_guestbook WHERE userid='.$userid.' ORDER BY date_created ASC', ARRAY_A );
		$entries = array(); // use this array to prevent multiple entries of the same comment
		foreach ( $comments as $comment ) {
			if ( !in_array( $comment['entry'], $entries ) ) {
				$entries[] = $comment['entry'];
				( $comment['public_private'] == 'public' )? $approved = 1 : $approved = 0;
				$data = array(
					'comment_post_ID'   => $post_ID,
					'comment_author'   => $comment['name'],
					'comment_author_email' => $comment['email'],
					'comment_content'  => $comment['entry'],
					'comment_date'   => $comment['date_created'],
					'comment_approved'  => $approved
				);
				wp_insert_comment( $data );
			}
		}
	}
}

if ( class_exists( 'ObituariesImporter' ) ) {
	// instantiate the plugin class
	$ObituariesImporter = new ObituariesImporter();
}
?>
