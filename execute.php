<?php
require_once('../../config.php');
include_once ('../../course/lib.php');

$courseid = $_POST['courseid'];
$command = $_POST['command'];
$params = $_POST['params'];
$sectionids = $_POST['sections'];

$sections = $DB->get_records_list('course_sections', 'id', json_decode($sectionids));

$o = '';
$o .= "Command: $command\n";

switch ($command) {
    case 'show_sections' :
        $o .= show_sections($courseid, $sections);
        break;
    case 'hide_sections' :
        $o .= hide_sections($courseid, $sections);
        break;
    case 'move2tab' :
        $o .= move2tab($params, $sections);
        break;
    case 'test_sections' :
        $o .= test_sections($courseid, $sections);
        break;
}
rebuild_course_cache($courseid, true); // rebuild the cache for that course so the changes become effective

echo $o;

//----------------------------------------------------------------------------------------------------------------------
function show_sections($courseid, $sections) {
    global $DB;
    $o = '';
    $o .= "show_sections\n";
    foreach($sections as $section){
        $o .= "Section Nr $section->section (ID = $section->id) was selected\n";
        $section->visible = 1;
        $DB->update_record('course_sections', $section);
    }

    return $o;
}

//----------------------------------------------------------------------------------------------------------------------
function hide_sections($courseid, $sections) {
    global $DB;
    $o = '';
    $o .= "hide_sections\n";
    foreach($sections as $section){
        $o .= "Section Nr $section->section (ID = $section->id) was selected\n";
        $section->visible = 0;
        $DB->update_record('course_sections', $section);
    }

    return $o;
}

//----------------------------------------------------------------------------------------------------------------------
function move2tab($tab_nr, $sections) {
    global $DB;
    $o = '';
    $o .= "Moving to Tab $tab_nr\n";

    $courseid = $sections[key($sections)]->course; // it doesn't matter at which key the pointer is as all sections share the same course...

    // remove sectionIDs and section numbers from any tab
    $sql = "select * from {course_format_options} where courseid = $courseid and (name like 'tab_' or name like 'tab%sectionnums')";
    $cfos = $DB->get_records_sql($sql);
    foreach($cfos as $cfo) {
        $has_changed = false;
        if($cfo->value != '') { // only check non empty tab values
            foreach($sections as $section) {
                if(strstr($cfo->name, 'sectionnums')) {
                    if(strstr($cfo->value, $section->section)) {
                        $cfo->value = str_replace($section->section.',','', $cfo->value);
                        $cfo->value = str_replace(','.$section->section,'', $cfo->value);
                        $cfo->value = str_replace($section->section,'', $cfo->value);
                        $has_changed = true;
                    }
                } else {
                    if(strstr($cfo->value, $section->id)) {
                        $cfo->value = str_replace($section->id.',','', $cfo->value);
                        $cfo->value = str_replace(','.$section->id,'', $cfo->value);
                        $cfo->value = str_replace($section->id,'', $cfo->value);
                        $has_changed = true;
                    }
                }
            }
            if($has_changed) {
                $DB->update_record('course_format_options', $cfo);
            }
        }
    }

    // now add the sections to the destination tab - if it is NOT Tab 0
    if($tab_nr > 0){
        // compile strings
        $new_ids = ''; $new_nums = '';
        foreach($sections as $section) {
            if($new_ids == ''){
                $new_ids = $section->id;
                $new_nums = $section->section;
            } else {
                $new_ids .= ','.$section->id;
                $new_nums .= ','.$section->section;
            }

        }

        $tabid_rec = $DB->get_record('course_format_options', array('courseid' => $courseid, 'name' => 'tab'.$tab_nr));
        $tabnum_rec = $DB->get_record('course_format_options', array('courseid' => $courseid, 'name' => 'tab'.$tab_nr.'_sectionnums'));

        if($tabid_rec->value == '') {
            $tabid_rec->value = $new_ids;
        } else {
            $tabid_rec->value .= ','.$new_ids;
        }

        if($tabnum_rec->value == '') {
            $tabnum_rec->value = $new_nums;
        } else {
            $tabnum_rec->value .= ','.$new_nums;
        }

    $DB->update_record('course_format_options', $tabid_rec);
    $DB->update_record('course_format_options', $tabnum_rec);
    }
    return $o;
}
function move2tab0($tab_nr, $sections) {
    global $DB;
    $o = '';
    $o .= "Moving to Tab $tab_nr\n";

    $courseid = $sections[key($sections)]->course; // it doesn't matter at which key the pointer is as all sections share the same course...
    $fo_records = $DB->get_records('course_format_options', array('courseid' => $courseid));
    $fo = array();
    foreach($fo_records as $for) {
        $fo[$for->name] = $for->value;
    }

    foreach($sections as $section){
        $o .= "Section Nr $section->section (ID = $section->id) was selected\n";
        removefromtabs($courseid, $section, $fo);
    }

    return $o;
}

//----------------------------------------------------------------------------------------------------------------------
function test_sections($courseid, $sections) {
    $o = '';
    $o .= "test_sections here!\n";
    foreach($sections as $section){
        $o .= "Section Nr $section->section (ID = $section->id) was selected\n";
    }

    return $o;
}

//----------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------
// move section ID and section number to tab format settings of a given tab
function add2tab($tabnum, $section2move, $settings) {
    global $PAGE;
    global $DB;

    $course = $PAGE->course;

    // remove section number from all tab format settings
    $settings = $this->removefromtabs($course, $section2move, $settings);

    // add section number to new tab format settings if not tab0
    if($tabnum > 0){
        $settings['tab'.$tabnum] .= ($settings['tab'.$tabnum] === '' ? '' : ',').$section2move->id;
        $settings['tab'.$tabnum.'_sectionnums'] .= ($settings['tab'.$tabnum.'_sectionnums'] === '' ? '' : ',').$section2move->section;
        $this->update_course_format_options($settings);
    }
    return $settings;
}

///----------------------------------------------------------------------------------------------------------------------
// remove section id from all tab format settings
function removefromtabs($courseid, $section2remove, $settings) {
    global $DB;
    $max_tabs = 9;

    for($i = 0; $i <= $max_tabs; $i++) {
        if(strstr($settings['tab'.$i], $section2remove->id) > -1) {
            $section_ids = explode(',', $settings['tab'.$i]);
            $section_nums = explode(',', $settings['tab'.$i.'_sectionnums']);
            $new_section_ids = array();
            $new_section_nums = array();
            foreach($section_ids as $section_id) {
                if($section_id != $section2remove->id) {
                    $new_section_ids[] = $section_id;
                }
            }
            foreach($section_nums as $section_num) {
                if((int)$section_num != (int)$section2remove->section) {
                    $new_section_nums[] = $section_num;
                }
            }
//            $settings['tab'.$i.'_sectionnums'] = implode(',', $new_section_nums);

            // save back the new setting
            $sectionids = $DB->get_record('course_format_options', array('courseid' => $courseid, 'name' => 'tab'.$i));
            $sectionnums = $DB->get_record('course_format_options', array('courseid' => $courseid, 'name' => 'tab'.$i.'_sectionnums'));
            $sectionids->value = implode(',', $new_section_ids);
            $sectionnums->value = implode(',', $new_section_nums);
            $DB->update_record('course_format_options', $sectionids);
            $DB->update_record('course_format_options', $sectionnums);
//$halt=true;
//            $this->update_course_format_options($settings);
        }
    }
    return $settings;
}

