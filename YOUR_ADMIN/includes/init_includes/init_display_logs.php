<?php
// -----
// Part of the "Display Logs" plugin for Zen Cart v1.5.0 or later
//
// Copyright (c) 2012-2016, Vinos de Frutas Tropicales (lat9)
//
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
} 

//----
// If the installation supports admin-page registration (i.e. v1.5.0 and later), then
// register the New Tools tool into the admin menu structure.
//
if (function_exists('zen_register_admin_page')) {
    if (!zen_page_key_exists('toolsDisplayLogs')) {
        zen_register_admin_page('toolsDisplayLogs', 'BOX_TOOLS_DISPLAY_LOGS', 'FILENAME_DISPLAY_LOGS', '','tools', 'Y', 20);
    }    
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
