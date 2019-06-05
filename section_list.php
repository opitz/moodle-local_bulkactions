<?php
/**
 * Created by PhpStorm.
 * User: opitz
 * Date: 19/02/01
 */

require_once('../../config.php');
global $PAGE;
global $DB;
global $COURSE;

$PAGE->set_url('/local/bulkactions/view.php', array('id' => $cm->id));
$PAGE->set_title('Bulk Sections Commands');
$PAGE->set_heading('Bulk Sections Commands');
$PAGE->set_pagelayout('standard');

//======================================================================================================================
$PAGE->requires->js_call_amd('local_bulkactions/execute','init', array());
echo $OUTPUT->header();
$courseid = $_GET['courseid'];
echo "Huhu! Course $courseid here!";

$commands = array();
$commands['Test'] = 'test.php';
$commands['Hide section'] = 'hide.php';
$commands['Test2'] = 'test2.php';


echo "<form action='commands/index.php' method='post'>";
if($courseid) {
    echo "<input id='courseid' type='text' value='$courseid' style='display:none;'>";
    echo "<select id='command' name='command'>";
    foreach($commands as $key => $command) {
        echo "<option value='".$command."'>$key</option>";
    }
    echo "</select><hr>";


    $sections = $DB->get_records('course_sections', array('course' => $courseid));
    foreach($sections as $section){
        $sectionname = ($section->name == '' ? 'Section '.$section->section : $section->name);
        echo '<input type="checkbox" class="section" value="'.$section->section.'" name="'.$section->section.'">'.$sectionname.'</input><br>';
    }
    echo '<input type="submit" value="Submit">';
    echo '<input type="button" value="Cancel">';
    echo '<input id="btn_execute" type="button" value="JS execute">';
}
echo "</form>";
echo $OUTPUT->footer();