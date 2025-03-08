<?php
/*
Plugin Name: Database Table Display
Plugin URI: https://github.com/anupammo/database-table-display
Description: A tool to dynamically display specific columns from any WordPress database table.
Version: 1.03
Stable Tag: 1.03
Requires at least: 5.0 
Tested up to: 6.7.1 
Requires PHP: 7.0
Author: Anupam Mondal
Author URI: https://anupammondal.in 
License: GPL3
License URI: https://github.com/anupammo/database-table-display/blob/main/LICENSE 
Text Domain: database-table-display-main
Tags: database, table, display, dynamic 
*/

// Hook to add the admin menu
add_action('admin_menu', 'db_table_display_menu');

// Hook to add the shortcode
add_shortcode('display_table', 'display_database_table');

// Hook to enqueue the CSS file
add_action('wp_enqueue_scripts', 'db_table_display_enqueue_styles');

function db_table_display_enqueue_styles() {
    wp_enqueue_style('db-table-display-styles', plugin_dir_url(__FILE__) . 'styles.css', array(), '1.03'); // Version parameter added
}

function db_table_display_menu() {
    add_menu_page('Database Table Display', 'DB Table Display', 'manage_options', 'db-table-display', 'db_table_display_page');
}

function db_table_display_page() {
    global $wpdb;
    $tables = wp_cache_get('db_tables');

    if (false === $tables) {
        $tables = $wpdb->get_col("SHOW TABLES");
        wp_cache_set('db_tables', $tables);
    }

    // Handle form submission
    if (isset($_SERVER['REQUEST_METHOD']) && 'POST' === $_SERVER['REQUEST_METHOD']) {
        check_admin_referer('db_table_display_nonce_action', 'db_table_display_nonce_name');

        if (!empty($_POST['table_name'])) {
            $table_name = sanitize_text_field(wp_unslash($_POST['table_name']));
            update_option('db_table_display_table_name', $table_name);
        }
    }

    $selected_table = get_option('db_table_display_table_name');

    echo '<div class="wrap">';
    echo '<h1>Database Table Display</h1>';
    echo '<form method="post" action="">';
    wp_nonce_field('db_table_display_nonce_action', 'db_table_display_nonce_name');
    echo '<label for="table_name">Select Table:</label>';
    echo '<select name="table_name" id="table_name">';
    foreach ($tables as $table) {
        $selected = ($table == $selected_table) ? 'selected' : '';
        echo '<option value="' . esc_attr($table) . '" ' . selected($selected, true, false) . '>' . esc_html($table) . '</option>';
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
        $cached_results = wp_cache_get('db_table_results_' . $table_name);

        if (false === $cached_results) {
            // Specify the columns you want to display
            $columns = ['meta_value']; // Update with your column names

            // Create the SQL query
            $column_string = implode(', ', $columns);
            $results = $wpdb->get_results($wpdb->prepare("SELECT $column_string FROM %s", $table_name), ARRAY_A);

            wp_cache_set('db_table_results_' . $table_name, $results);
        } else {
            $results = $cached_results;
        }

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
            $output .= "</tr>";

            // Table rows
            $cellCount = 0; // Initialize cell counter
            $output .= "<tr>"; // Start the first row
            foreach ($results as $row) {
                foreach ($columns as $column) {
                    $cellCount++;
                    if ($cellCount % 8 == 0) {
                        $output .= " ";
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
