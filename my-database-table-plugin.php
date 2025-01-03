<?php
/*
Plugin Name: Database Table WP Plugin
Description: A plugin to display database table data as an HTML table.
Version: 1.0
Author: Anupam Mondal
*/

// Hook to add a shortcode
add_shortcode('display_table', 'display_database_table');

function display_database_table() {
    // Database connection details
    global $wpdb;
    $table_name = $wpdb->prefix . 'your_table_name'; // Update with your table name
    
    // Fetch data from the database
    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    if ($results) {
        // Start table
        $output = "<table border='1'>";
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

    return $output;
}
?>
