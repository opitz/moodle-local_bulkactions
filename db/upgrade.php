<?php
/**
 * Created by PhpStorm.
 * User: vasileios
 * Date: 26/06/2017
 * Time: 10:37
 * QM+ Activities reporting plugin
 * executed after version.php change
 */

function xmldb_local_bulkactions_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    # if ($oldversion < 2016102600) {    }
    return true;
}