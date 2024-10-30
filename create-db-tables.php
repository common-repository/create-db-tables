<?php
/**
 * Plugin Name: Create DB Tables
 * Plugin URI:  http://jppreusdev.com/development/wordpress-plugins/create-db-tables/
 * Description: Extremely simple way for developers to create new tables inside the existing WordPress database. Forget the annoying process of opening phpMyAdmin, logging in, then typing out the full SQL command for your new table. With this plugin, everything you need to do is located on one simple to use page, and you don't have to type out any SQL queries! This plugin also keeps record of the tables you've created. It is perfect for the developer who wants to quickly and easily add new database tables in a quick and effective manner.
 * Version:     1.2.1
 * Author:      James Preus | @JPPreusDev
 * Author URI:  http://jppreusdev.com/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Current Plugin Version
 */
if ( ! defined( 'CDBT_VERSION' ) ) {
	/**
	 *
	 */
	define( 'CDBT_VERSION', '1.2.0' );
}


require 'create-new-table.php';
require 'pages/table-edit.php';
require 'pages/view-table-data.php';

function cdbt_create_db_tables_create_menu() {

	//create new top-level menu
	add_menu_page('Create DB Tables', 'Create DB Tables', 'administrator', 'create-db-tables', 'cdbt_create_db_tables_settings_page' , 'dashicons-editor-table', 81 );
	
	add_submenu_page( 'create-db-tables', 'Add New Table', 'Add New Table', 'administrator', 'add-new-table', 'cdbt_add_new_table_page' );

}
add_action('admin_menu', 'cdbt_create_db_tables_create_menu');



function cdbt_create_db_tables_settings_page() {

    $query_string = $_SERVER['QUERY_STRING'];

    // $table is equal to the table name
    parse_str($query_string);
    
    if(!empty($table)) {
    
        cdbt_edit_existing_created_table();
        
    } elseif(!empty($view_table)) {
        
        cdbt_view_table_data();
        
    } else {
    
    cdbt_plugin_main_settings_page_styles(); ?>

<div class="wrap">
	<h2>Create DB Tables 
		<a href="<?php echo admin_url('admin.php?page=add-new-table'); ?>" class="page-title-action">
			Add New Table
		</a>
	</h2>
	
	<?php
	$query = $_SERVER['QUERY_STRING'];
    // $table_name
    parse_str($query);
	?>
	
	<?php
	/**
	 * Alert: New Table Created
	 */
	if ($_SERVER['QUERY_STRING'] == 'page=create-db-tables&create_new_table_success=true&table_name=' . $table_name) { ?>
	<div class="updated">
		<p><strong>Success!</strong> The following table has been added to the database: "<span style="font-weight: 800;"><?php echo $table_name; ?></span>"</p>
	</div>
	<?php } ?>


	<?php
	/**
	 * Alert: Error Creating Table
	 */
	if ($_SERVER['QUERY_STRING'] == 'page=create-db-tables&create_new_table_success=false') { ?>
	<div class="error">
		<p><?php _e( '<strong>Error:</strong> Your table could not be created. Check your input and please try again.' ); ?></p>
	</div>
	<?php } ?>

   
    <?php
    /**
	 * Alert: Duplicate id Row
	 */
    $query = $_SERVER['QUERY_STRING'];
    // $create_new_table_success
    parse_str($query);  
    if($create_new_table_success == 'id') { ?>
        <div class="error">
            <p><?php _e( '<strong>Error:</strong> The row named "id" is created by default. Do not create one manually. Please try again without creating a row named "id".' ); ?></p>
        </div>
    <?php } ?>

	
    <?php
	/**
	 * Alert: No Data Submitted
	 */		
	if ($_SERVER['QUERY_STRING'] == 'page=create-db-tables&create_new_table_success=null') { ?>
	<div class="error">
		<p><?php _e( '<strong>Error:</strong> You did not submit any data. Fill out the form and try again.' ); ?></p>
	</div>
	<?php } ?>
	
	<section style="margin-top: 30px;">
	
        <h3>Tables Created</h3>
        
		<table id="db-tables-list">
            <colgroup>
                <col span="1" style="width: 75%;">
                <?php // <col span="1" style="width: 20%;"> ?>
                <col span="1" style="width: 25%;">
            </colgroup>
            <tbody class="db-list-body">
                <tr class="db-list-header">
                    <th><h4>Table Name</h4></th>
                    <?php // was column header for edit column <th></th> ?>
                    <th></th>
                </tr>
                <?php
                $options = get_option('create_wordpress_tables_created_tables');
                if($options == null) {
                    echo '<tr><td>You have not created any tables yet...</td></tr>';
                }
                $explode = explode(',', $options);
                foreach($explode as $table) { 
                $edit_table_url = 'admin.php?page=create-db-tables&table=' . $table;
                $view_table_url = 'admin.php?page=create-db-tables&view_table=' . $table;
                ?>
                <tr class="table-row">
                    <td>
                        <a class="table-links" href="<?php echo admin_url($view_table_url) ?>" title="<?php echo $table ?>"><?php echo $table ?></a>
                    </td>
                    <?php /**
                    <td class="edit-col">
                        <a class="table-links-edit" href="<?php echo admin_url($edit_table_url) ?>" title="<?php echo $table ?>">Edit</a>
                    </td>
                    */ ?>
                    <td class="delete-col">
                        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                            <input type="hidden" name="action" value="delete_db_table">
                            <input type="hidden" name="db_table" value="<?php echo $table ?>">
                            <button onclick="return confirm('Are you sure you want to delete this table? All the data inside the table will be permanently deleted. You will not be able to recover the deleted data.')" type="submit" class="table-links-delete">Delete</button>
                        </form>
                    </td>
                </tr>
		<?php } ?>
            </tbody>
		</table>
		
		
	</section>
	
	
</div>

<?php 
    } // END else
    
} // END cdbt_create_db_tables_settings_page

function cdbt_add_new_table_page() {
	global $wpdb;
	$prefix = $wpdb->prefix;
	cdbt_add_page_styles();
	?>
<div class="wrap">
	<h2>Add New Table
		<a href="<?php echo admin_url('admin.php?page=create-db-tables'); ?>" class="page-title-action">
			Cancel
		</a>
	</h2>
	
	<section style="margin-top: 15px;">
		
		<div style="width: 700px;margin-bottom: 30px;">

			<span><strong>Important Notes:</strong></span>
			<ul>
				<li style="padding-left:15px;">• Your table will automatically include a row named "id" with the type set to "bigint(20)" and includes the auto_increment setting as the first row in the table.</li>
				<li style="padding-left:15px;">• The table's charset is automatically set to the WordPress standard "utf8mb4_unicode_ci".</li>
			</ul>

		</div>
		
		<form id="create-table" method="post" action="<?php echo admin_url('admin-post.php'); ?>">

			<input type="hidden" name="action" value="add_table">

			<fieldset class="row-fieldset" id="table-name">
				<label id="table-name">Table Name:</label>
				<span style="position: relative; top: 2px;"><?php echo $prefix; ?></span><input type="text" class="table-name" name="table_name" size="30" id="table-name">
				<span>(Alphanumeric only, no special charaters.)</span>
			</fieldset>

			<div id="rows">
				
				<fieldset class="row-fieldset" id="1"><label id="row-label">Row:</label><input type="text" class="name-input" name="name[]" placeholder="Name" size="20"><input type="text" class="type-input" name="type[]" placeholder="Type [Ex: bigint(20)]" size="20"><span id="null-label">Null</span><input type="checkbox" class="null-input" name="null[]"><input type="text" class="default-input" name="default[]" placeholder="Default Value" size="20"><span id="unique-label">Unique</span><input type="checkbox" class="unique-input" name="unique[]"></fieldset>
				
			</div>

			<div id="add-row">
				<button type="button" class="add-row button-secondary">Add Row</button>
			</div>
            
            <div id="delete-row">
				<button type="button" class="delete-row button-secondary">Delete Row</button>
			</div>
            
            <div class="clear"></div>
			
			<fieldset>
				<input type="hidden" id="items" name="items" value="1" />
			</fieldset>

			<fieldset>
				<button type="submit" class="button button-primary button-large">Create Table</button>
			</fieldset>

		</form>

		<script>
			jQuery(function($) {
				$('.add-row').click(function () {
					$('#items').val(function(i, val) { return +val+1 });
                    var rowNumber = $('#items').val();
					var rowHTML = '<fieldset class="row-fieldset" id="' + rowNumber + '"><label id="row-label">Row:</label><input type="text" class="name-input" name="name[]" placeholder="Name" size="20"><input type="text" class="type-input" name="type[]" placeholder="Type [Ex: bigint(20)]" size="20"><span id="null-label">Null</span><input type="checkbox" class="null-input" name="null[]"><input type="text" class="default-input" name="default[]" placeholder="Default Value" size="20"><span id="unique-label">Unique</span><input type="checkbox" class="unique-input" name="unique[]"></fieldset>';
					$('#rows').append(rowHTML);
				});
                $('.delete-row').click(function () {
                    var rowNumber = $('#items').val();
					$('#items').val(function(i, val) { return +val-1 });
					var rowHTML = '<fieldset class="row-fieldset" id="' + rowNumber + '"><label id="row-label">Row:</label><input type="text" class="name-input" name="name[]" placeholder="Name" size="20"><input type="text" class="type-input" name="type[]" placeholder="Type [Ex: bigint(20)]" size="20"><span id="null-label">Null</span><input type="checkbox" class="null-input" name="null[]"><input type="text" class="default-input" name="default[]" placeholder="Default Value" size="20"><span id="unique-label">Unique</span><input type="checkbox" class="unique-input" name="unique[]"></fieldset>';
                    var rowID = '#' + rowNumber;
					$(rowID).remove();
				});
				$("input.name-input").on({
				  keydown: function(e) {
					if (e.which === 32)
					  return false;
				  },
				  change: function() {
					this.value = this.value.replace(/\s/g, "");
				  }
				});
				$("input.table-name").on({
				  keydown: function(e) {
					if (e.which === 32)
					  return false;
				  },
				  change: function() {
					this.value = this.value.replace(/\s/g, "");
				  }
				});
			});
		</script>

	</section>
	
</div>

<?php 
}

function cdbt_add_page_styles() {
	?>
<style>
	#table-name,
	#row-label {
		padding-right:25px;
		font-weight: 600;
	}
	input[type="text"], input[type="checkbox"] {
		margin-right: 10px!important;
	}
	#null-label,
	#unique-label {
		padding-right: 5px;
	}
	.row-fieldset {
		margin-bottom: 15px;
		display: inline-table;
	}
	#rows {
		margin-bottom: 15px;
	}
	#add-row {
		margin-bottom: 20px;
        float: left;
        margin-right: 20px;
	}
</style>
<?php
}


function cdbt_plugin_main_settings_page_styles() {
	?>
<style>
    .table-links {
        text-decoration: none;
    }
    #db-tables-list {
        width: 45%;
        min-width: 300px;
        border: 1px solid #e1e1e1;
        box-shadow: 0 1px 2px #cecece;
    }
    .db-list-header {
        background: #F9F9F9;
        text-align: left;
    }
    .db-list-header th {
        border: 1px solid #FDFDFD;
        padding-left: 15px;
    }
    .db-list-header th h4 {
        margin: 10px 0;
    }
    .table-row td {
        padding: 10px 0 10px 15px;
    }
    .edit-col, .delete-col {
        text-align: center;
        padding-left: 0px!important;
    }
    .table-links-edit, .table-links-delete {
        text-decoration: none;
        letter-spacing: -0.5px;
        transition: 0.4s;
        font-weight: 600;
    }
    .table-links-delete {
        padding: 0px;
        background: none;
        border: 0px;
        color: #0073aa;
    }
    .table-links-delete:hover {
        color: #dd1010;
    }
    .table-links-edit:hover {
        color: #147d00;
    }
    .db-list-body {
        background-color: #fff;
    }
</style>
<?php
}

function cdbt_delete_db_table() {

    $db_table = sanitize_text_field($_POST['db_table']);

	if($db_table != null) {
    
        global $wpdb;
        
        $delete_table_statement = $wpdb->query("DROP TABLE IF EXISTS $db_table");
        			
        $option = 'create_wordpress_tables_created_tables';
        $existing_option = get_option( $option );           // Grabs the exisiting option string
            
        $remove_table_option = str_replace($db_table, '', $existing_option);
        $trim_whitespace = trim($remove_table_option);                    // Removes the trailing whitespace if it exists
        $new_option = rtrim($trim_whitespace, ',');     // Removes the trailing comma if it exists
            
        update_option( $option, $new_option );              // Updates option with selected table removed

        $succuss_url_redirect = admin_url( "admin.php?page=create-db-tables&delete_table_success=true" );
        wp_redirect( $succuss_url_redirect );
        
    } else {

		$null_url_redirect = admin_url( 'admin.php?page=create-db-tables&delete_table_success=null' );
		wp_redirect( $null_url_redirect );

	}
    
}

function cdbt_edit_db_table() {
    echo 'Table Edited...';
    
}

// Create Database Table
add_action( 'admin_post_add_table', 'cdbt_create_new_table' );

// Delete Database Table
add_action( 'admin_post_delete_db_table', 'cdbt_delete_db_table' );

// Edit Database Table
add_action( 'admin_post_edit_db_table', 'cdbt_edit_db_table' );

?>