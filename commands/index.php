<?php
require_once('../../../config.php');
global $PAGE;
global $DB;
global $COURSE;

$commandurl = $_POST['command'];
redirect($commandurl);
