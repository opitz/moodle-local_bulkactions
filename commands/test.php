<?php

$courseid = $_POST['courseid'];
$sections = $_POST['sections'];

$sections = json_decode($sections);

$o = '';
foreach($sections as $section){
    $o .= "Section ID $section was selected\n";
}

echo $o;
