<?php
// -----
// Part of the "Display Logs" plugin for Zen Cart v1.5.0 or later
//
// Copyright (c) 2012-2016, Vinos de Frutas Tropicales (lat9)
//
define('HEADING_TITLE', 'Display Debug Log Files');

define('TABLE_HEADING_FILENAME', 'File Name');
define('TABLE_HEADING_MODIFIED', 'Date Modified');
define('TABLE_HEADING_FILESIZE', 'File Size (bytes)');
define('TABLE_HEADING_DELETE', 'Delete?');
define('TABLE_HEADING_ACTION', 'Action');

define('BUTTON_DELETE_SELECTED', 'button_delete_selected.gif');
define('DELETE_SELECTED_ALT', 'Delete all selected files');
define('BUTTON_DELETE_ALL', 'button_delete_all.gif');
define('DELETE_ALL_ALT', 'Delete all files in the current view');

define('ICON_INFO_VIEW', 'View the contents of this file');

define('DISPLAY_DEBUG_LOGS_ONLY', 'Display debug-logs only?');
define('LOG_SORT_ASC', 'Asc');
define('LOG_SORT_DESC', 'Desc');

define('TEXT_HEADING_INFO', 'File Contents');
define('TEXT_MOST_RECENT', 'most recent');
define('TEXT_OLDEST', 'oldest');
define('TEXT_SMALLEST', 'smallest');
define('TEXT_LARGEST', 'largest');

$imageName = zen_image (DIR_WS_IMAGES . 'icon_info.gif', ICON_INFO_VIEW);
define('TEXT_INSTRUCTIONS', '<br /><br />The files can be sorted in either ascending or descending order (based on either the last-modified date or the file-size) by clicking on one of the <em>Asc</em> or <em>Desc</em> links. Click on an ' . $imageName . ' icon to view the contents of the associated file.  Only the first %u bytes of the selected file will be read; if a file is &quot;over-sized&quot;, its <em>File Size</em> will be highlighted like <span class="bigfile">this</span>.<br /><br />Clicking the <strong>delete all</strong> button will delete all files currently being viewed; clicking <strong>delete selected</strong> will delete only those files with checked checkboxes.<br /><br />Currently viewing the %s %u of %u log files.<br />');

define('JS_MESSAGE_DELETE_ALL_CONFIRM', 'Are you sure you want to delete these \'+n+\' files?');
define('JS_MESSAGE_DELETE_SELECTED_CONFIRM', 'Are you sure you want to delete the \'+selected+\' selected file(s)?');

define('WARNING_NOT_SECURE','<span class="errorText">NOTE: You do not have SSL enabled. File contents you view from this page will not be encrypted and could present a security risk.</span>');
define('WARNING_NO_FILES_SELECTED', 'No files were selected for deletion!');
define('WARNING_SOME_FILES_DELETED', 'Warning: Only %u of %u log files were deleted; check permissions.');
define('SUCCESS_FILES_DELETED', '%u log files were deleted.');