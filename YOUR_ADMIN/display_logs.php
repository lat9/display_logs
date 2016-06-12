<?php
// -----
// Part of the "Display Logs" plugin for Zen Cart v1.5.0 or later
//
// Copyright (c) 2012-2016, Vinos de Frutas Tropicales (lat9)
//
define ('MAX_LOG_FILES_TO_VIEW', 20);
if (!defined('MAX_LOG_FILE_READ_SIZE')) define('MAX_LOG_FILE_READ_SIZE', 80000);

// -----
// Functions that gather the log-related files and provide the ascending/descending sort thereof.
//  
function sortLogDateAsc ($a, $b) 
{
    if ($a['mtime'] == $b['mtime']) return 0;
    return ($a['mtime'] < $b['mtime']) ? -1 : 1;
}
function sortLogDateDesc ($a, $b) 
{
    if ($a['mtime'] == $b['mtime']) return 0;
    return ($a['mtime'] > $b['mtime']) ? -1 : 1;
}
function sortLogSizeAsc ($a, $b)
{
    if ($a['filesize'] == $b['filesize']) return 0;
    return ($a['filesize'] < $b['filesize']) ? -1 : 1;
}
function sortLogSizeDesc ($a, $b)
{
    if ($a['filesize'] == $b['filesize']) return 0;
    return ($a['filesize'] > $b['filesize']) ? -1 : 1;
}

// -----
// Start main processing ...
//  
require ('includes/application_top.php');

// -----
// Determine the current sort-method chosen by the admin user.
//
$sort = 'date_d';
$sort_description = TEXT_MOST_RECENT;
$sort_function = 'sortLogDateDesc';
if (isset ($_GET['sort'])) {
    $sort = $_GET['sort'];
    switch ($sort) {
        case 'date_a':
            $sort_description = TEXT_OLDEST;
            $sort_function = 'sortLogDateAsc';
            break;
        case 'size_a':
            $sort_description = TEXT_SMALLEST;
            $sort_function = 'sortLogSizeAsc';
            break;
        case 'size_d':
            $sort_description = TEXT_LARGEST;
            $sort_function = 'sortLogSizeDesc';
            break;
        default:
            $sort = 'date_a';
            break;
    }
}

// -----
// If debug-logs-only has been selected, display only those files.
//
$files_to_match = (isset ($_GET['debug_only'])) ? 'myDEBUG-' : '(myDEBUG-|AIM_Debug_|SIM_Debug_|FirstData_Debug_|Linkpoint_Debug_|Paypal|paypal|ipn_|zcInstall|notifier|usps|SHIP_usps)';

// -----
// Gather the current log files.
//
$logFiles = array();
foreach (array (DIR_FS_LOGS, DIR_FS_SQL_CACHE, DIR_FS_CATALOG . '/includes/modules/payment/paypal/logs') as $logFolder) {
    $logFolder = rtrim ($logFolder, '/');
    $dir = @dir ($logFolder);
    if ($dir != NULL) {
        while ($file = $dir->read ()) {
            if ( ($file != '.') && ($file != '..') && substr($file, 0, 1) != '.') {
                if (preg_match('/^' . $files_to_match . '.*\.log$/', $file)) {
                    $hash = sha1 ($logFolder . '/' . $file);
                    $logFiles[$hash] = array ( 
                        'name'  => $logFolder . '/' . $file,
                        'mtime' => filemtime ($logFolder . '/' . $file),
                        'filesize' => filesize ($logFolder . '/' . $file)
                    );
                }
            }
        }
        $dir->close();
        unset ($dir);
    }
}
uasort ($logFiles, $sort_function);
reset ($logFiles);

// -----
// If any file delete requests have been made, process them first.
//
$action = (isset($_GET['action'])) ? $_GET['action'] : '';  
if (zen_not_null($action) && $action == 'delete') {
    if (isset($_POST['dList']) && sizeof($_POST['dList']) != 0) {
        $numFiles = sizeof($_POST['dList']);
        $filesDeleted = 0;
        foreach ($_POST['dList'] as $currentHash => $value) {
            if (array_key_exists ($currentHash, $logFiles)) {
                if (is_writeable ($logFiles[$currentHash]['name'])) {
                    zen_remove ($logFiles[$currentHash]['name']);
                    $filesDeleted++;
                }
            }
        }
        if ($filesDeleted == $numFiles) {
            $messageStack->add_session (sprintf (SUCCESS_FILES_DELETED, $numFiles), 'success');
        } else {
            $messageStack->add_session (sprintf (WARNING_SOME_FILES_DELETED, $filesDeleted, $numFiles), 'warning');
        }
    } else {
        $messageStack->add_session (WARNING_NO_FILES_SELECTED, 'warning');
    }
    zen_redirect (zen_href_link (FILENAME_DISPLAY_LOGS, zen_get_all_get_params (array ('action'))));
}

if (isset ($_GET['fID'])) {
    if (array_key_exists ($_GET['fID'], $logFiles)) {
        $getFile = $_GET['fID'];
    } else {
        unset ($_GET['fID']);
        $getFile = key ($logFiles);
    }
} elseif (sizeof($logFiles) != 0) {
    $getFile = key ($logFiles);
} else {
    $getFile = '';
}
$numLogFiles = count ($logFiles);

// -----
// If more files are in the log-file array than will be displayed, free up the memory associated with
// those files' entries by popping them off the end of the array.
//
if ($numLogFiles > MAX_LOG_FILES_TO_VIEW) {
    for ($i = 0, $n = $numLogFiles - MAX_LOG_FILES_TO_VIEW; $i < $n; $i++) {
        array_pop ($logFiles);
    }
}
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<style type="text/css">
<!--
#theButtons { padding-top: 10px; margin-top: 10px; border-top: 1px solid black; }
#dButtons, #dSpace { width: 50%; }
#dAll { float: right; padding-right: 20px; }
#dSel { float: right; }
#fContents { overflow: auto; max-height: <?php echo 23 * MAX_LOG_FILES_TO_VIEW; ?>px; }
#contentsOuter { vertical-align: top; }
.bigfile { font-weight: bold; color: red; }
-->
</style>
<script type="text/javascript" src="includes/menu.js"></script>
<script type="text/javascript" src="includes/general.js"></script>
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  
  function buttonCheck(whichButton) {
    var submitOK = false;
    var elements = document.getElementsByClassName('cBox');
    var n = elements.length;
    if (whichButton == 'all') {
      submitOK = confirm('<?php echo JS_MESSAGE_DELETE_ALL_CONFIRM; ?>');
      if (submitOK) {
        for (var i = 0; i < n; i++) {
          elements[i].checked = true;
        }
      }
    } else {
      var selected = 0;
      for (var i = 0; i < n; i++) {
        if (elements[i].checked) selected++;
      }
      if (selected > 0) {
        submitOK = confirm('<?php echo JS_MESSAGE_DELETE_SELECTED_CONFIRM; ?>');
      } else {
        alert('<?php echo WARNING_NO_FILES_SELECTED; ?>');
      }
    }
    return submitOK;
  }
  // -->
</script>
</head>
<body onLoad="init();">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?><span style="font-size: smaller;">&nbsp;&nbsp;(v2.0.0)</span></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>

          <tr>
            <td class="main"><?php echo ((substr(HTTP_SERVER, 0, 5) != 'https') ? WARNING_NOT_SECURE : '') . sprintf (TEXT_INSTRUCTIONS, MAX_LOG_FILE_READ_SIZE, $sort_description, (($numLogFiles > MAX_LOG_FILES_TO_VIEW) ? MAX_LOG_FILES_TO_VIEW : $numLogFiles), $numLogFiles); ?></td>
            <td class="main" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
          
          <tr colspan="2">
            <td><?php echo zen_draw_form ('logs_form', FILENAME_DISPLAY_LOGS, '', 'get') . '<b>' . DISPLAY_DEBUG_LOGS_ONLY . '</b>&nbsp;&nbsp;' . zen_draw_checkbox_field ('debug_only', 'on', (isset ($_GET['debug_only'])) ? true : false, '', 'onclick="this.form.submit();"') . zen_draw_hidden_field ('sort', $sort) . '</form>'; ?></td>
          </tr>

        </table></td>
      </tr>
    </table></td>
  </tr>
  
  <tr>    
    <td>
      <form id="dlFormID" name="dlForm" action="<?php echo zen_href_link (FILENAME_DISPLAY_LOGS, zen_get_all_get_params (array ('action')) . 'action=delete', 'NONSSL'); ?>" method="post"><?php echo zen_draw_hidden_field ('securityToken', $_SESSION['securityToken']) . "\n"; ?>
      <table border="0" width="100%" cellspacing="0" cellpadding="0">
      
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top" width="50%"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_FILENAME; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_MODIFIED; ?><br /><a href="<?php echo zen_href_link (FILENAME_DISPLAY_LOGS, zen_get_all_get_params (array ('sort')) . 'sort=date_a', 'NONSSL'); ?>"><?php echo LOG_SORT_ASC; ?></a>&nbsp;&nbsp;<a href="<?php echo zen_href_link (FILENAME_DISPLAY_LOGS, zen_get_all_get_params (array ('sort')) . 'sort=date_d', 'NONSSL'); ?>"><?php echo LOG_SORT_DESC; ?></a></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_FILESIZE; ?><br /><a href="<?php echo zen_href_link (FILENAME_DISPLAY_LOGS, zen_get_all_get_params (array ('sort')) . 'sort=size_a', 'NONSSL'); ?>"><?php echo LOG_SORT_ASC; ?></a>&nbsp;&nbsp;<a href="<?php echo zen_href_link (FILENAME_DISPLAY_LOGS, zen_get_all_get_params (array ('sort')) . 'sort=size_d', 'NONSSL'); ?>"><?php echo LOG_SORT_DESC; ?></a></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DELETE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
reset ($logFiles);
$fileData = '';
$heading = array();
$contents = array();
foreach ($logFiles as $curHash => $curFile) {
?>
              <tr>
                <td class="dataTableContent" align="left"><?php echo $curFile['name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo date (PHP_DATE_TIME_FORMAT, $curFile['mtime']); ?></td>
                <td class="dataTableContent<?php echo ($curFile['filesize'] > MAX_LOG_FILE_READ_SIZE) ? ' bigfile' : ''; ?>" align="center"><?php echo $curFile['filesize']; ?></td>
                <td class="dataTableContent" align="center"><?php echo zen_draw_checkbox_field ('dList[' . $curHash . ']', false, false, '', 'class="cBox"'); ?></td>
                <td class="dataTableContent" align="right"><?php if ($getFile == $curHash) { echo zen_image (DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link(FILENAME_DISPLAY_LOGS, 'fID=' . $curHash . '&amp;' . zen_get_all_get_params (array ('fID'))) . '">' . zen_image (DIR_WS_IMAGES . 'icon_info.gif', ICON_INFO_VIEW) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    if ($getFile == $curHash) {
      $heading[] = array('text' => '<strong>' . TEXT_HEADING_INFO . '( ' . $curFile['name'] . ')</strong>');
      $contents[] = array('align' => 'left', 'text' => '<div id="fContents">' . nl2br (htmlentities (trim (@file_get_contents($curFile['name'], NULL, NULL, -1, MAX_LOG_FILE_READ_SIZE)), ENT_COMPAT+ENT_IGNORE, CHARSET, false)) . '</div>');
    }
}
?>
            </table></td>
<?php
if ( zen_not_null ($heading) && zen_not_null ($contents) ) {
?>
            <td id="contentsOuter" width="50%">
<?php
    $box = new box;
    echo $box->infoBox ($heading, $contents);
?>
            </td>
<?php
}
?>           
          </tr>
        </table></td>
      </tr>
<?php
if ($numLogFiles > 0) {
?>
      <tr>
        <td id="theButtons">
          <div id="dButtons">
            <div id="dSel"><?php echo zen_image_submit(BUTTON_DELETE_SELECTED, DELETE_SELECTED_ALT, 'name="dButton" value="delete" onclick="return buttonCheck(\'delete\');"'); /*v1.0.6c*/ ?></div>
            <div id="dAll"><?php echo zen_image_submit(BUTTON_DELETE_ALL, DELETE_ALL_ALT, 'name="sButton" value="all" onclick="return buttonCheck(\'all\');"'); /*v1.0.6c*/ ?></div>
          </div>
          <div id="dSpace">&nbsp;</div>
        </td>
      </tr>
<?php
}
?>
    </table></form></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>