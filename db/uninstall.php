<?php
/**
 * Created by PhpStorm.
 * User: vasileios
 * Date: 26/06/2017
 * Time: 09:36
 * QM+ Activities reporting plugin
 * Executed when the uninstall button is pressed
 * It must remove all plugin tables and plugin configuration records from Moodle
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../../config.php');


function xmldb_local_bulkactions_uninstall() {
    global $DB;

    $dbman = $DB->get_manager();
    $xmlds = $dbman->get_install_xml_schema();

    $DB->delete_records('config_plugins',array('plugin'=>'local_bulkactions'));
    return true;
}
