<?php

if ( ! defined( 'ABSPATH' ) ) exit;

function cdbt_create_new_table() {
	
	global $wpdb;
	
	/**
	 * Sanitize User Input
	 */
	
	/** Table Name Text Input */
	$safe_table_name = sanitize_text_field($_POST['table_name']);
	
	/** Row Name Text Input */
	$row_names = $_POST['name'];
	$row_types = $_POST['type'];
	$row_defaults = $_POST['default'];
	$row_nulls = $_POST['null'];
	$row_uniques = $_POST['unique'];
	$items_amount = $_POST['items'];
	$sql = '';
	for($i = 0; $i < $items_amount; $i++) {
        
		$safe_row_name = sanitize_text_field($row_names[$i]);
		$safe_row_type = sanitize_text_field($row_types[$i]);
		$safe_row_default = sanitize_text_field($row_defaults[$i]);
		$safe_row_null = $row_nulls[$i];
		$safe_row_unique = $row_uniques[$i];
		
        /**
         * Exit & Prompt Error if a duplicate
         * id row is created.
         */
        if($safe_row_name == 'id') {
            
            $duplicate_url_redirect = admin_url( "admin.php?page=create-db-tables&create_new_table_success=id" );
			wp_redirect( $duplicate_url_redirect );
            
        }
        
		$sql .= $safe_row_name . ' ' . $safe_row_type . ' ';
		if($safe_row_null == true) { $sql = $sql . 'NULL'; } else { $sql = $sql . 'NOT NULL';}
		if($safe_row_default != false) { $sql = $sql . " DEFAULT '" . $safe_row_default . "'"; }
		if($safe_row_unique == true) { $sql = $sql . ' UNIQUE'; }
		$sql = $sql . ', ';
	}
	
	/**
	 * Prepare Table Data
	 */

	$table_name = $wpdb->prefix . $safe_table_name;
	$charset_collate = $wpdb->get_charset_collate();

	if($safe_table_name != null) {

		/**
		 * Create SQL Query From Post Values
		 */

		$completed_sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT, ";
		$completed_sql = $completed_sql . $sql;
		$completed_sql = $completed_sql . "UNIQUE KEY id (id) ) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $completed_sql );		

		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			
			$error_url_redirect = admin_url( 'admin.php?page=create-db-tables&create_new_table_success=false' );
			wp_redirect( $error_url_redirect );

		} else {

			$option = 'create_wordpress_tables_created_tables';
			$previous_option = get_option( $option );
			if($previous_option == null) {
				add_option( $option, $table_name );

			} else {

				$new_value = $previous_option . ', ' . $table_name;
				update_option( $option, $new_value );

			}
			
			$succuss_url_redirect = admin_url( "admin.php?page=create-db-tables&create_new_table_success=true&table_name=$table_name" );
			wp_redirect( $succuss_url_redirect );
			
		}

	} else {

		$null_url_redirect = admin_url( 'admin.php?page=create-db-tables&create_new_table_success=null' );
		wp_redirect( $null_url_redirect );

	}
	
}


?>