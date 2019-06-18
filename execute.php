<?php
require_once('../../config.php');
include_once ('../../course/lib.php');

$courseid = $_POST['courseid'];
$command = $_POST['command'];
$param = $_POST['param'];
$sectionids = $_POST['sections'];

$o = '';
$sections = $DB->get_records_list('course_sections', 'id', json_decode($sectionids));
if($command) {
    $o .= "Command: $command\n";

    switch ($command) {
        case 'show_sections' :
            $o .= show_sections($sections);
            break;
        case 'hide_sections' :
            $o .= hide_sections($sections);
        case 'move2tab' :
            $o .= move2tab($param, $sections);
            break;
        case 'delete_sections' :
            $o .= delete_sections($sections);
            break;
        case 'delete_empty_sections' :
            $o .= delete_empty_sections($courseid);
            break;
        case 'test_sections' :
            $o .= test_sections($sections);
            break;
    }
    rebuild_course_cache($courseid, true); // rebuild the cache for that course so the changes become effective
}
echo $o;

//----------------------------------------------------------------------------------------------------------------------
function show_sections($sections) {
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
function hide_sections($sections) {
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

    removefromtabs($courseid, $sections);
    add2tab($courseid, $sections, $tab_nr);

    return $o;
}

//----------------------------------------------------------------------------------------------------------------------
function test_sections($sections) {
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
function add2tab($courseid, $sections, $tab_nr) {
    global $COURSE, $DB;

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

        $fo = $DB->get_record('course_format_options', array('courseid' => $courseid));

//        $fo = $DB->get_records('course_format_options', array('courseid' => $courseid));
//        $format_options = array();
//        foreach($fo as $o) {
//            $format_options[$o->name] = $o->value;
//        }

        $tabid_rec = $DB->get_record('course_format_options', array('courseid' => $courseid, 'name' => 'tab'.$tab_nr));
        $tabnum_rec = $DB->get_record('course_format_options', array('courseid' => $courseid, 'name' => 'tab'.$tab_nr.'_sectionnums'));
        if($tabid_rec) {

            if($tabid_rec->value == '') {
                $tabid_rec->value = $new_ids;
            } else {
                $tabid_rec->value .= ','.$new_ids;
            }
            $DB->update_record('course_format_options', $tabid_rec);
        } else {
            $tabid_rec = new stdClass();
            $tabid_rec->courseid = $courseid;
            $tabid_rec->format = $fo->format;
            $tabid_rec->section = 0;
            $tabid_rec->name = 'tab'.$tab_nr;
            $tabid_rec->value = $new_ids;
            $DB->insert_record('course_format_options', $tabid_rec);
        }

        if($tabnum_rec) {
            if($tabnum_rec->value == '') {
                $tabnum_rec->value = $new_nums;
            } else {
                $tabnum_rec->value .= ','.$new_nums;
            }
            $DB->update_record('course_format_options', $tabnum_rec);
        } else {
            $tabnum_rec = new stdClass();
            $tabnum_rec->courseid = $courseid;
            $tabnum_rec->format = $fo->format;
            $tabnum_rec->section = 0;
            $tabnum_rec->name = 'tab'.$tab_nr.'_sectionnums';
            $tabnum_rec->value = $new_nums;
            $DB->insert_record('course_format_options', $tabnum_rec);
        }
    }
}
function add2tab0($courseid, $sections, $tab_nr) {
    global $COURSE, $DB;

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
        if($tabid_rec) {

            if($tabid_rec->value == '') {
                $tabid_rec->value = $new_ids;
            } else {
                $tabid_rec->value .= ','.$new_ids;
            }
            $DB->update_record('course_format_options', $tabid_rec);
        } else {
            $tabid_rec = new stdClass();
            $tabid_rec->courseid = $courseid;
            $tabid_rec->name = 'tab'.$tab_nr;
        }

        if($tabnum_rec) {
            if($tabnum_rec->value == '') {
                $tabnum_rec->value = $new_nums;
            } else {
                $tabnum_rec->value .= ','.$new_nums;
            }
            $DB->update_record('course_format_options', $tabnum_rec);
        }
    }
}

///----------------------------------------------------------------------------------------------------------------------
// remove section id from all tab format settings
function removefromtabs($courseid, $sections) {
    global $DB;
    // remove sectionIDs and section numbers from any tab
    $sql = "select * from {course_format_options} where courseid = $courseid and (name like 'tab_' or name like 'tab%sectionnums')";
    $cfos = $DB->get_records_sql($sql);
    foreach($cfos as $cfo) {
        $has_changed = false;
        if($cfo->value != '') { // only check non empty tab values
            foreach($sections as $section) {
                if(strstr($cfo->name, 'sectionnums')) {
                    if(in_array($section->section, explode(',',$cfo->value))) {
                        $cfo->value = str_replace($section->section.',','', $cfo->value);
                        $cfo->value = str_replace(','.$section->section,'', $cfo->value);
                        $cfo->value = str_replace($section->section,'', $cfo->value);
                        $has_changed = true;
                    }
                } else {
                    if(in_array($section->id, explode(',',$cfo->value))) {
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
}

//----------------------------------------------------------------------------------------------------------------------
function delete_sections($sections) {
    global $DB;
    $o = '';
    $o .= "delete_sections\n";
    $courseid = $sections[key($sections)]->course; // it doesn't matter at which key the pointer is as all sections share the same course...
    removefromtabs($courseid, $sections);
    foreach($sections as $section){
        $DB->delete_records('course_sections', array('id' => $section->id));
        $o .= "Section Nr $section->section (ID = $section->id) was deleted\n";
    }

    return $o;
}

//----------------------------------------------------------------------------------------------------------------------
function delete_empty_sections($courseid) {
    global $DB;
    $o = '';
    $o .= "delete_empty_sections\n";
    $sql = "select * from {course_sections} where course = $courseid and section != 0 and (name is null or name ='') and summary = '' and sequence = ''";
    $sections = $DB->get_records_sql($sql);
    removefromtabs($courseid, $sections);
    foreach($sections as $section){
        $DB->delete_records('course_sections', array('id' => $section->id));
        $o .= "Section Nr $section->section (ID = $section->id) was deleted\n";
    }

    return $o;
}

//----------------------------------------------------------------------------------------------------------------------
// move section ID and section number to tab format settings of a given tab
function delete_section($section2del, $settings) {
    global $PAGE;
    global $DB;

    $course = $PAGE->course;

    // remove section number from all tab format settings
    $settings = $this->removefromtabs($course, $section2del, $settings);

    // add section number to new tab format settings if not tab0
    if($tabnum > 0){
        $settings['tab'.$tabnum] .= ($settings['tab'.$tabnum] === '' ? '' : ',').$section2move->id;
        $settings['tab'.$tabnum.'_sectionnums'] .= ($settings['tab'.$tabnum.'_sectionnums'] === '' ? '' : ',').$section2move->section;
        $this->update_course_format_options($settings);
    }
    return $settings;
}

