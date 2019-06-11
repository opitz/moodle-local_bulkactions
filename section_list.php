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

$courseid = $_GET['courseid'];
$context = context_course::instance($courseid);
$course = $DB->get_record('course', array('id' => $courseid));

$PAGE->set_url('/local/bulkactions/view.php', array('id' => $cm->id));
$PAGE->set_title($course->fullname.' - Bulk Actions');
$PAGE->set_heading($course->fullname.' - Bulk Actions');
$PAGE->set_pagelayout('standard');

//======================================================================================================================
$PAGE->requires->js_call_amd('local_bulkactions/execute','init', array());
echo $OUTPUT->header();

require_login();
if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {
//if (is_courseadmin()) {



    $fo_records = $DB->get_records('course_format_options', array('courseid' => $courseid));
    $fo = array();
    foreach($fo_records as $for) {
        $fo[$for->name] = $for->value;
    }

    echo html_writer::tag('div','Check the topics you want the activity to apply to. Then select an action from the drop down menu.');
    echo html_writer::empty_tag('br');

    // build the commands array
    $commands = array();
//    $commands['Please select an action'] = '';

    $maxtabs = (int)$fo['maxtabs'];
// tab commands
    for($i = 0; $i <= $maxtabs; $i++) {
        $commands['Move to Tab '.$i] = 'move2tab'.$i;
    }

    $commands['Hide sections'] = 'hide_sections';
    $commands['Show sections'] = 'show_sections';

    $returnurl = $_SERVER['HTTP_REFERER'];

    echo "<form method='post'>";
    if($courseid) {
        echo "<input id='courseid' type='text' value='$courseid' style='display:none;'>";
        echo "<input id='returnurl' type='text' value='$returnurl' style='display:none;'>";

        echo html_writer::start_tag('button', array('type' => 'button', 'id'=>'command', 'class' => 'btn dropdown-toggle', 'data-toggle' => 'dropdown'));
        echo "Select Action";
        echo html_writer::end_tag('button');

        echo html_writer::start_tag('div', array('class' => 'dropdown-menu'));
        foreach($commands as $key => $command) {
            echo html_writer::tag('a', $key, array('class' => 'dropdown-item','value' => $command));
//            "<option value='".$command."'>$key</option>";
        }
        echo html_writer::end_tag('div');

        echo ' ';
        echo '<input id="btn_cancel" class="btn" type="button" value="Cancel">';

        echo ' ';
        echo '<input id="btn_test" class="btn" type="button" value="Test">';

        /*
                echo "<select id='command' class='form-control0' type='button' name='command'>";
                foreach($commands as $key => $command) {
                    echo "<option value='".$command."'>$key</option>";
                }
                echo "</select>";
                echo "<div id='message'></div><hr>";
        */

        $sections = $DB->get_records('course_sections', array('course' => $courseid));
        $sql = "select * from {course_format_options} where courseid = $courseid and name like 'tab_'";
        $cfos = $DB->get_records_sql($sql);

//        echo html_writer::start_tag('div', array('class' => 'container-fluid'));

        echo html_writer::start_tag('table',array('class' => 'table table-striped ', 'border' => '0'));
        echo html_writer::start_tag('thead');
        echo html_writer::start_tag('tr');

        echo html_writer::start_tag('th');
        echo "Apply";
        echo html_writer::end_tag('th');
        echo html_writer::start_tag('th');
        echo "Topic";
        echo html_writer::end_tag('th');
        echo html_writer::start_tag('th');
        echo "Tab Position";
        echo html_writer::end_tag('th');
        echo html_writer::start_tag('th');
        echo "Visibility";
        echo html_writer::end_tag('th');

        echo html_writer::end_tag('tr');
        echo html_writer::end_tag('thead');
        echo html_writer::start_tag('tbody');

        foreach($sections as $section){
            $sectionname = ($section->name == '' ? 'Section '.$section->section : $section->name);
            $hidinghint = ($section->visible ? '' : ' (<i>hidden</i>)');
            $tablocation = '';
            foreach($cfos as $cfo){
                if(strstr($cfo->value, $section->id)){
                    $tablocation = "Tab ".substr($cfo->name, -1)." ";
                    break;
                }
            }

            echo html_writer::start_tag('tr');
            echo html_writer::start_tag('td');
//            echo html_writer::tag('input', array('type' => 'checkbox', 'class' => 'section', 'value' => $section->id, 'name' => $section->section));
            echo '<input type="checkbox" class="section" value="'.$section->id.'" name="'.$section->section.'"> ';
            echo html_writer::end_tag('td');
            echo html_writer::start_tag('td');
            echo $sectionname;
            echo html_writer::end_tag('td');
            echo html_writer::start_tag('td');
            echo $tablocation;
            echo html_writer::end_tag('td');
            echo html_writer::start_tag('td');
            echo $hidinghint;
            echo html_writer::end_tag('td');
            echo html_writer::end_tag('tr');

//            echo '<input type="checkbox" class="section" value="'.$section->id.'" name="'.$section->section.'"> '.$sectionname.$hidinghint.$tablocation.'</input><br>';
        }

        echo html_writer::end_tag('tbody');
        echo html_writer::end_tag('table');

//        echo html_writer::end_tag('div');

        echo "<br>";
//        echo html_writer::start_tag('button', array('id' => 'btn_execute', 'class' => 'btn'));
//        echo "Execute";
//        echo html_writer::end_tag('button');
//        echo '<input id="btn_execute" class="btn" type="button" value="Execute">';
//        echo ' ';
//        echo html_writer::start_tag('button', array('id' => 'btn_cancel', 'class' => 'btn'));
//        echo "Cancel";
//        echo html_writer::end_tag('button');
//        echo '<input id="btn_cancel" class="btn" type="button" value="Cancel">';
    }
    echo "</form>";

} else {
    echo(get_string('login_required', 'local_pluginmeta'));
}

echo $OUTPUT->footer();