<?php
/**
 * Created by PhpStorm.
 * User: opitz
 * Date: 19/02/01
 */
global $PAGE;
$PAGE->requires->js_call_amd('local_bulkactions/bulkactions','init', array());


/*
 * Does currently nothing much...
 * @param $navigation stdClass navigation
 * @param $context stdClass context
 */
function local_bulkactions_extend_settings_navigation(settings_navigation $navigation, $context){
    global $DB,$PAGE;

    $course = $PAGE->course;

    $fo = array();
    $records = $DB->get_records('course_format_options', array('courseid' => $course->id, 'format' => $course->format));
    foreach($records as $record) {
        $fo[$record->name] = $record->value;
    }

    if($context == null) {
        return;
    }
    if (!$PAGE->user_is_editing()){
        return;
    }
    if (!has_capability('moodle/course:update', $context)){
        return;
    }
    if($context->contextlevel != CONTEXT_COURSE) {
        return;
    }

    $menutext = get_string('menu_entry', 'local_bulkactions');
    $url = '/local/bulkactions/section_list.php?courseid='.$course->id;

    $coursenode = $navigation->get('courseadmin');
    if ($coursenode) {
        $coursenode->add($menutext, $url, navigation_node::TYPE_SETTING, 'bulkactions', 'bulkactions_creator', new pix_icon('t/restore', ''));
    }
}
