<?php
/*
Plugin Name: Dynamic Database Table Display
Description: A plugin to dynamically display data from any WordPress database table.
Version: 1.01
Author: Anupam Mondal
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
        $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

        if ($results) {
            // Start table
            $output = "<table class='db-table'>";
            $output .= "<tr>";

            // Table headers
            foreach ($results[0] as $key => $value) {
                $output .= "<th>{$key}</th>";
            }

            $output .= "</tr>";

            // Table rows
            foreach ($results as $row) {
                $output .= "<tr>";
                foreach ($row as $value) {
                    $output .= "<td>{$value}</td>";
                }
                $output .= "</tr>";
            }

            $output .= "</table>";
        } else {
            $output = "No data found.";
        }
    } else {
        $output = "Please select a table from the plugin settings.";
    }

    return $output;
}
?>
