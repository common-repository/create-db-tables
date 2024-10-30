<?php
/**
 * View Table Data Page
 * Since: 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function cdbt_view_table_data() { 

    $query_string = $_SERVER['QUERY_STRING'];

    // $table is equal to the table name
    parse_str($query_string);
    
    $safe_table_name = sanitize_text_field($view_table);

    cdbt_view_table_data_page_styles();
    
    ?>
    
    <div class="wrap">
        <h2>View Table Data
           <a href="<?php echo admin_url('admin.php?page=create-db-tables'); ?>" class="page-title-action">
			 Back
           </a>
        </h2>
        
        <?php
    
        global $wpdb;
            
        /**
         * Select Table Names
         */
        $select_columns = $wpdb->get_results("
        SELECT `COLUMN_NAME` 
        FROM `INFORMATION_SCHEMA`.`COLUMNS` 
        WHERE `TABLE_SCHEMA`='jppreus_wp' 
        AND `TABLE_NAME`='$safe_table_name';
        ");
        
        /**
         * Select Data From Table
         */
        $select_data = $wpdb->get_results("SELECT * FROM $safe_table_name");
    
        ?>
        
        <?php 

        $column_num = 0;

        /**
         * Count Number Of Columns
         */
        foreach ( $select_columns as $data => $value ) { $column_num++; }

        ?>
        
        <section style="margin-top: 30px;">
	
            <h3>Table - <?php echo $view_table ?></h3>

            <table id="db-tables-list">
                <tbody class="db-list-body">
                    <tr class="db-list-header">
                        <?php 
                        /**
                         * Generate Table Header By Columns
                         */
                        foreach ( $select_columns as $data => $value ) {

                            echo '<th><h4>' . $value->COLUMN_NAME . '</h4></th>';
                            
                            $name = $value->COLUMN_NAME;

                            /**
                             * Create An Array Of Column Names To Use As The Object Identifier
                             * When Generating The Data Results. See Below "for" Function
                             */
                            $column_names[] = $value->COLUMN_NAME;
                            
                            
                        } ?>
                    </tr>
                    <?php
                    /**
                     * Generates A Row For Each Results Set
                     */
                    foreach($select_data as $row => $values) {

                        echo '<tr class="table-row">';
                        
                        /**
                         * Loops Through The Result Set And Prints
                         * Each Value As A Table Column
                         */
                        for($i = 0; $i < $column_num; $i++) {

                            echo '<td>';
                            echo $values->$column_names[$i];
                            echo '</td>';

                        }

                        echo '</tr>';

                    }
                    ?>
                </tbody>
            </table>

        </section>


    </div>

<?php } // END cdbt_view_table_data

function cdbt_view_table_data_page_styles() {
	?>
<style>
    .table-links {
        text-decoration: none;
    }
    #db-tables-list {
        width: 85%;
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
<?php } ?>