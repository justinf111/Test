<?php

$shortopts  = "h:";
$shortopts .= "u:";
$shortopts .= "p:";

$longopts  = [
    "help",
    "file:",
    "create_table",
    "dry_run",
];
$options = getopt($shortopts, $longopts);
var_dump($options);