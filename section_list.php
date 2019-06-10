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

require_login();
if (is_siteadmin()) {



    $courseid = $_GET['courseid'];

    $fo_records = $DB->get_records('course_format_options', array('courseid' => $courseid));
    $fo = array();
    foreach($fo_records as $for) {
        $fo[$for->name] = $for->value;
    }
    echo "Select an action for course ID $courseid from the drop down menu, check the sections for the selected action and click on execute.<br><br>";

    $commands = array();
    $commands['Please select an action'] = 'test_sections';
    $commands['Hide sections'] = 'hide_sections';
    $commands['Show sections'] = 'show_sections';

    $maxtabs = (int)$fo['maxtabs'];
// tab commands
    for($i = 0; $i <= $maxtabs; $i++) {
        $commands['Move to Tab '.$i] = 'move2tab'.$i;
    }


    $returnurl = $_SERVER['HTTP_REFERER'];

    echo "<form method='post'>";
    if($courseid) {
        echo "<input id='courseid' type='text' value='$courseid' style='display:none;'>";
        echo "<input id='returnurl' type='text' value='$returnurl' style='display:none;'>";
        echo "<select id='command' name='command'>";
        foreach($commands as $key => $command) {
            echo "<option value='".$command."'>$key</option>";
        }
        echo "</select><hr>";


        $sections = $DB->get_records('course_sections', array('course' => $courseid));
        $sql = "select * from {course_format_options} where courseid = $courseid and name like 'tab_'";
        $cfos = $DB->get_records_sql($sql);

        foreach($sections as $section){
            $sectionname = ($section->name == '' ? 'Section '.$section->section : $section->name);
            $hidinghint = ($section->visible ? '' : ' (<i>hidden</i>)');
            $tablocation = '';
            foreach($cfos as $cfo){
                if(strstr($cfo->value, $section->id)){
                    $tablocation = " | Tab ".substr($cfo->name, -1)." ";
                    break;
                }
            }
            echo '<input type="checkbox" class="section" value="'.$section->id.'" name="'.$section->section.'"> '.$sectionname.$hidinghint.$tablocation.'</input><br>';
        }
        echo "<br>";
        echo '<input id="btn_execute" type="button" value="Execute">';
        echo ' ';
        echo '<input type="button" value="Cancel">';
    }
    echo "</form>";

} else {
    echo(get_string('login_required', 'local_pluginmeta'));
}
echo $OUTPUT->footer();