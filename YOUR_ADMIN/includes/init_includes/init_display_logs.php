<?php
// -----
// Part of the "Display Logs" plugin for Zen Cart v1.5.0 or later
//
// Copyright (c) 2012-2020, Vinos de Frutas Tropicales (lat9)
//
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

define('DISPLAY_LOGS_CURRENT_VERSION', '2.2.0');

//----
// If the installation supports admin-page registration (i.e. v1.5.0 and later), then
// register the New Tools tool into the admin menu structure.
//
if (function_exists('zen_register_admin_page')) {
    if (!zen_page_key_exists('toolsDisplayLogs')) {
        zen_register_admin_page('toolsDisplayLogs', 'BOX_TOOLS_DISPLAY_LOGS', 'FILENAME_DISPLAY_LOGS', '', 'tools', 'Y', 20);
    }    
}

// -----
// If the configuration values for the plugin's processing are not yet installed, set them up in the database.
//
if (!defined('DISPLAY_LOGS_VERSION')) {
    define('DISPLAY_LOGS_VERSION', DISPLAY_LOGS_CURRENT_VERSION);
    $db->Execute(
        "INSERT INTO " . TABLE_CONFIGURATION . " 
            ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function ) 
         VALUES 
            ('Display Logs: Version', 'DISPLAY_LOGS_VERSION', '" . DISPLAY_LOGS_CURRENT_VERSION . "', 'Current plugin version.', 10, 100, now(), NULL, 'trim('),
            
            ('Display Logs: Display Maximum', 'DISPLAY_LOGS_MAX_DISPLAY', '20', 'Identify the maximum number of logs to display.  (Default: <b>20</b>)', 10, 100, now(), NULL, NULL),
            
            ('Display Logs: Maximum File Size', 'DISPLAY_LOGS_MAX_FILE_SIZE', '80000', 'Identify the maximum size of any file to display.  (Default: <b>80000</b>)', 10, 101, now(), NULL, NULL),
            
            ('Display Logs: Included File Prefixes', 'DISPLAY_LOGS_INCLUDED_FILES', 'myDEBUG-|AIM_Debug_|SIM_Debug_|FirstData_Debug_|Linkpoint_Debug_|Paypal|paypal|ipn_|zcInstall|notifier|usps|SHIP_usps', 'Identify the log-file <em>prefixes</em> to include in the display, separated by the pipe character (|).  Any intervening spaces are removed by the processing code.', 10, 102, now(), NULL, NULL),
            
            ('Display Logs: Excluded File Prefixes', 'DISPLAY_LOGS_EXCLUDED_FILES', '', 'Identify the log-file prefixes to <em>exclude</em> from the display, separated by the pipe character (|). Any intervening spaces are removed by the processing code.', 10, 103, now(), NULL, NULL)"
    );
}

// -----
// If the current plugin version is different from that recorded in the database, update the database!
//
if (DISPLAY_LOGS_VERSION != DISPLAY_LOGS_CURRENT_VERSION) {
    $db->Execute(
        "UPDATE " . TABLE_CONFIGURATION . "
            SET configuration_value = '" . DISPLAY_LOGS_CURRENT_VERSION . "'
          WHERE configuration_key = 'DISPLAY_LOGS_VERSION'
          LIMIT 1"
    );
}

// -----
// Check to see if there are any debug-logs present and, if so, notify the current admin via header message ... unless the admin is already on the display logs page.
//
if ($current_page != FILENAME_DISPLAY_LOGS . '.php') {
    $path = (defined('DIR_FS_LOGS')) ? DIR_FS_LOGS : DIR_FS_SQL_CACHE;
    $log_files = glob($path . '/myDEBUG-*.log');
    $num_log_files = ($log_files === false) ? 0 : count ($log_files);
    unset ($log_files);
    if ($num_log_files > 0) {
        $messageStack->add(sprintf(DISPLAY_LOGS_MESSAGE_LOGS_PRESENT, $num_log_files, zen_href_link(FILENAME_DISPLAY_LOGS)), 'caution');
    }
}
