<?php
/*
Plugin Name: Database Table WP Plugin
Description: A plugin to dynamically display specific columns from any WordPress database table.
Version: 1.02
Author: Anupam Mondal
Author URI: https://anupammondal.in 
Text Domain: dynamic-database-table-display 
License: GPL2 or later 
Tags: database, table, display, dynamic 
Requires at least: 5.0 
Tested up to: 6.7.1 
Requires PHP: 7.0
*/

// Hook to add the admin menu
add_action('admin_menu', 'db_table_plugin_menu');

// Hook to add the shortcode
add_shortcode('display_table', 'display_database_table');

// Hook to enqueue the CSS file
add_action('wp_enqueue_scripts', 'db_table_plugin_enqueue_styles');

function db_table_plugin_enqueue_styles() {
    wp_enqueue_style('db-table-plugin-styles', plugin_dir_url(__FILE__) . 'styles.css');
}

function db_table_plugin_menu() {
    add_menu_page('Database Table Display', 'DB Table Display', 'manage_options', 'db-table-display', 'db_table_plugin_page');
}

function db_table_plugin_page() {
    global $wpdb;
    $tables = $wpdb->get_col("SHOW TABLES");

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['table_name'])) {
        $table_name = sanitize_text_field($_POST['table_name']);
        update_option('db_table_display_table_name', $table_name);
    }

    $selected_table = get_option('db_table_display_table_name');

    echo '<div class="wrap">';
    echo '<h1>Database Table Display</h1>';
    echo '<form method="post" action="">';
    echo '<label for="table_name">Select Table:</label>';
    echo '<select name="table_name" id="table_name">';
    foreach ($tables as $table) {
        $selected = ($table == $selected_table) ? 'selected' : '';
        echo "<option value='$table' $selected>$table</option>";
    }
    echo '</select>';
    echo '<input type="submit" value="Save" class="button button-primary">';
    echo '</form>';
    echo '</div>';
}

function display_database_table() {
    global $wpdb;
    $table_name = get_option('db_table_display_table_name');

    if ($table_name) {
        // Specify the columns you want to display
        // $columns = ['column1', 'column2', 'column3']; // Update with your column names
        $columns = ['meta_value']; // Update with your column names

        // Create the SQL query
        $column_string = implode(', ', $columns);
        $results = $wpdb->get_results("SELECT $column_string FROM $table_name", ARRAY_A);

        if ($results) {
            // Start table
            $output = "<div style='overflow-x:auto;'><table class='db-table'>";
            $output .= "<tr>";
            $output .= "<th>Name</th>";
            $output .= "<th>Home Address</th>";
            $output .= "<th>Shop Name</th>";
            $output .= "<th>Age</th>";
            $output .= "<th>Sex</th>";
            $output .= "<th>Aadhar Number</th>";
            $output .= "<th>WhatsApp No</th>";
//             $output .= "<th></th>";
            $output .= "</tr>";
            
			// Table rows
			$cellCount = 0; // Initialize cell counter
			$output .= "<tr>"; // Start the first row
			foreach ($results as $row) {
				foreach ($columns as $column) {
					$cellCount++;
					// $output .= "<td>{$row[$column]}</td>";
										
					if ($cellCount % 8 == 0) {						
						$output .= " ";
						// $output .= "<td style='display:none;'>{$row[$column]}</td>";
					} else {
						$output .= "<td>{$row[$column]}</td>";
					}
					
					// Check if 8 cells have been added
					if ($cellCount % 8 == 0) {
						$output .= "</tr><tr>"; // Close the current row and start a new one
					}
				}
			}
			$output .= "</tr>"; // Close the last row


			
            $output .= "</table></div>";
        } else {
            $output = "No data found.";
        }
    } else {
        $output = "Please select a table from the plugin settings.";
    }

    return $output;
}
?>
