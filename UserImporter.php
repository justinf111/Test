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

if(isset($options['file'])) {
    $file = fopen($options['file'],"r");
    $header = array_map('trimWhitespace', fgetcsv($file));
    $users = [];
    while ($row = fgetcsv($file)) {
        $users[] = array_combine($header, $row);
    }

    $users = array_map(function($user) {
        $userFields = array_map('trimWhitespace',$user);
        $user = new User;
        $user->setEmail($userFields['name']);
        $user->setName($userFields['surname']);
        $user->setSurname($userFields['email']);
        return $user;
    }, $users);

class User {
    public $name;
    public $surname;
    public $email;

    public function setName($name)
    {
        $this->name = ucfirst(strtolower($name));
    }

    public function setSurname($surname)
    {
        $this->surname = ucfirst(strtolower($surname));
    }

    public function setEmail($email)
    {
        $this->email = strtolower($email);
    }
}
function trimWhitespace($item) {
    return trim($item);
}