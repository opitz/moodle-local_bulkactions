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

$PAGE->set_url('/local/bulkactions/view.php', array('id' => $courseid));
$PAGE->set_title($course->fullname.' - Bulk Actions');
$PAGE->set_heading($course->fullname.' - Bulk Actions');
$PAGE->set_pagelayout('standard');

//======================================================================================================================
$PAGE->requires->js_call_amd('local_bulkactions/execute','init', array());
echo $OUTPUT->header();

require_login();
if (has_capability('moodle/course:update', $context)) {

    $fo_records = $DB->get_records('course_format_options', array('courseid' => $courseid));
    $fo = array();
    foreach($fo_records as $for) {
        $fo[$for->name] = $for->value;
    }

    echo html_writer::tag('div',get_string('instructions', 'local_bulkactions'));
    echo html_writer::empty_tag('br');

    // build the commands array
    $commands = array();
//    $commands['Please select an action'] = '';
    $commands[] = (object)array('command' => 'check_all', 'name' => get_string('check_all', 'local_bulkactions'), 'confirm' => '', 'nosectioncheck' => true);
    $commands[] = (object)array('command' => 'uncheck_all', 'name' => get_string('uncheck_all', 'local_bulkactions'), 'confirm' => '', 'nosectioncheck' => true);
    $commands[] = (object)array('command' => 'dropdown-divider', 'name' => '', 'confirm' => '');

    if(isset($fo['tab1'])) {
        $maxtabs = ((isset($fo['maxtabs']) && (int)$fo['maxtabs'] > 0) ? (int)$fo['maxtabs'] : (isset($CFG->max_tabs) ? $CFG->max_tabs : 5));
    } else {
        $maxtabs = 0;
    }
    if($maxtabs > 0) {
// tab commands
        for($i = 0; $i <= $maxtabs; $i++) {
            $command = new stdClass();
            $command->command = 'move2tab'.$i;
//            $command->name = get_string('move_tab', 'local_bulkactions', format_string($i));
//            $command->name = get_string('move_tab', 'local_bulkactions', $fo['tab'.$i.'_title'].($fo['tab'.$i.'_title'] != "Tab $i" ? " (Tab $i)" : ''));
            $command->name = get_string('move_tab', 'local_bulkactions', ($fo['tab'.$i.'_title'] == "Tab $i" ? $i :  '"'.$fo['tab'.$i.'_title'].'"').($fo['tab'.$i.'_title'] != "Tab $i" && $i>0 ? " (Tab $i)" : ''));
            $command->confirm = get_string('move_tab_confirm', 'local_bulkactions', format_string($i));
            $commands[] = $command;
        }
    }

    $commands[] = (object)array('command' => 'dropdown-divider', 'name' => '', 'confirm' => '');
    $commands[] = (object)array('command' => 'hide_sections', 'name' => get_string('hide_sections', 'local_bulkactions'), 'confirm' => get_string('hide_sections_confirm', 'local_bulkactions'));
    $commands[] = (object)array('command' => 'hide_sections_modules', 'name' => get_string('hide_sections_modules', 'local_bulkactions'), 'confirm' => get_string('hide_sections_modules_confirm', 'local_bulkactions'));
    $commands[] = (object)array('command' => 'show_sections', 'name' => get_string('show_sections', 'local_bulkactions'), 'confirm' => get_string('show_sections_confirm', 'local_bulkactions'));
    $commands[] = (object)array('command' => 'show_sections_modules', 'name' => get_string('show_sections_modules', 'local_bulkactions'), 'confirm' => get_string('show_sections_modules_confirm', 'local_bulkactions'));
    $commands[] = (object)array('command' => 'delete_sections', 'name' => get_string('delete_sections', 'local_bulkactions'), 'confirm' => get_string('delete_sections_confirm', 'local_bulkactions'));
    $commands[] = (object)array('command' => 'dropdown-divider', 'name' => '', 'confirm' => '');
    $commands[] = (object)array(
        'command' => 'delete_empty_sections',
        'name' => get_string('delete_empty_sections', 'local_bulkactions'),
        'confirm' => get_string('delete_empty_sections_confirm', 'local_bulkactions'),
        'nosectioncheck' => true,
//        'styleclass' => 'bold'
//        'styleclass' => 'warning'
    );

    $returnurl = $_SERVER['HTTP_REFERER'];

    echo "<form method='post'>";
    if($courseid) {
        echo "<input id='courseid' type='text' value='$courseid' style='display:none;'>";
        echo "<input id='returnurl' type='text' value='$returnurl' style='display:none;'>";

        // the action menu
        echo html_writer::start_tag('button', array('type' => 'button', 'id'=>'command', 'class' => 'btn dropdown-toggle btn-primary', 'data-toggle' => 'dropdown'));
        echo get_string('select_action', 'local_bulkactions');
        echo html_writer::end_tag('button');
        echo html_writer::start_tag('div', array('class' => 'bulkactions dropdown-menu'));
        foreach($commands as $command) {
            if($command->command == 'dropdown-divider') {
                echo html_writer::tag('div', $command->name, array('class' => 'dropdown-divider'));
            } else {
                echo html_writer::tag('a', $command->name, array(
                    'class' => 'dropdown-item'.(isset($command->styleclass) ? ' '.$command->styleclass : ''),
                    'value' => $command->command,
                    'confirm_txt' => $command->confirm,
                    'no_section_check' => (isset($command->nosectioncheck) ? $command->nosectioncheck : '')
                ));
            }
        }
        echo html_writer::end_tag('div');

        echo ' ';
        echo '<input id="btn_cancel" class="btn" type="button" value="Cancel">';

        // the section list
        $sections = $DB->get_records('course_sections', array('course' => $courseid));
        $sql = "select * from {course_format_options} where courseid = $courseid and (name like 'tab_' or name like 'tab__')";
        $cfos = $DB->get_records_sql($sql);

        echo html_writer::start_tag('table',array('class' => 'table table-striped ', 'border' => '0'));
        echo html_writer::start_tag('thead');
        echo html_writer::start_tag('tr');

        echo html_writer::start_tag('th');
        echo get_string('checkbox_header', 'local_bulkactions');
        echo html_writer::end_tag('th');

        echo html_writer::start_tag('th');
        echo get_string('section_header', 'local_bulkactions');
        echo html_writer::end_tag('th');

        if(isset($fo['maxtabs'])) {
            echo html_writer::start_tag('th');
            echo get_string('tab_header', 'local_bulkactions');
            echo html_writer::end_tag('th');
        }

        echo html_writer::start_tag('th');
        echo get_string('visib_header', 'local_bulkactions');
        echo html_writer::end_tag('th');

        echo html_writer::end_tag('tr');
        echo html_writer::end_tag('thead');
        echo html_writer::start_tag('tbody');

        foreach($sections as $section){
            // excluding section 0 from bulk actions
            if($section->section == 0) continue;

            $sectionname = ($section->name == '' ? ($section->section == 0 ? get_string('section0_name', 'local_bulkactions') : get_string('section_name', 'local_bulkactions').$section->section) : $section->name);
            $hidinghint = ($section->visible ? '' : get_string('hidden_hint', 'local_bulkactions'));
            $tablocation = '';
            $i = 0;
            foreach($cfos as $cfo){
                $i = 0;
                if(in_array($section->id, explode(',',$cfo->value))){
                    $i = (int)substr($cfo->name, 3);
                    $tablocation = $fo['tab'.$i.'_title'].($fo['tab'.$i.'_title'] != "Tab $i" && $i>-1 ? " (Tab $i)" : '');
                    break;
                }
                $tablocation = $fo['tab0_title'];
            }

            echo html_writer::start_tag('tr');

            echo html_writer::start_tag('td');
            echo '<input type="checkbox" class="section" value="'.$section->id.'" name="'.$section->section.'"> ';
            echo html_writer::end_tag('td');

            echo html_writer::start_tag('td', array('class' => 'sectionname'));
            echo $sectionname;
            echo html_writer::end_tag('td');

            if(isset($fo['maxtabs'])) {
                echo html_writer::start_tag('td', array('class' => 'tablocation', 'value' => $i, 'title' => get_string('tablocation_tooltip', 'local_bulkactions')));
                echo $tablocation;
                echo html_writer::end_tag('td');
            }

            echo html_writer::start_tag('td', array('class' => 'hidinghint'.($section->visible ? '' : ' is_hiding'), 'title' => ($section->visible ? '' : get_string('hidden_tooltip', 'local_bulkactions'))));
            echo $hidinghint;
            echo html_writer::end_tag('td');

            echo html_writer::end_tag('tr');
        }

        echo html_writer::end_tag('tbody');
        echo html_writer::end_tag('table');

        echo "<br>";
    }
    echo "</form>";

} else {
    echo(get_string('login_required', 'local_bulkactions'));
}

echo $OUTPUT->footer();