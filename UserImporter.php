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

if(isset($options['help'])) {
    echo "--file [csv file name] – this is the name of the CSV to be parsed\n";
    echo "--create_table – this will cause the MySQL users table to be built (and no further action will be taken)\n";
    echo "--dry_run – this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered\n";
    echo "-u – MySQL username\n";
    echo "-p – MySQL password\n";
    echo "-h – MySQL host\n";
    echo "--help – which will output the above list of directives with details.\n";
}