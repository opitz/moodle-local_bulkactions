<?php
/**
 * Created by PhpStorm.
 * User: opitz
 * Date: 19/02/01
 */
// File: /local/bulkactions/index.php


require_once('../../config.php');
global $PAGE;
global $DB;

$PAGE->set_url('/local/bulkactions/view.php', array('id' => $cm->id));
$PAGE->set_title('Bulk Actions');
$PAGE->set_heading('Bulk Actions');
$PAGE->set_pagelayout('standard');

//======================================================================================================================
$PAGE->requires->js_call_amd('local_bulkactions','init', array());
echo $OUTPUT->header();

echo "Please chose '".get_string('menu_entry','local_bulkactions')."' from the 'Course administrator' menu to use this feature!";

echo $OUTPUT->footer();


