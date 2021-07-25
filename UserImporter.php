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
if(isset($options['create_table']) && !isset($options['dry_run'])) {
    $db = new Database($options['h'], $options['u'], $options['p'] ?? '',$options['d'] ?? 'user_upload');
    $db->open();
    $db->query("DROP TABLE IF EXISTS users");
    $db->query("CREATE TABLE Users (
        id int NOT NULL AUTO_INCREMENT,
        surname varchar(255),
        name varchar(255),
        email varchar(255),
        PRIMARY KEY (id)
    );");
    $db->query("CREATE UNIQUE INDEX index_name
                    ON Users (email);");
    $db->close();
}

if(isset($options['file']) && !isset($options['create_table'])) {
    $extension = pathinfo($options['file'], PATHINFO_EXTENSION);
    if($extension != 'csv') {
        die('The file must be on file format CSV');
    }
    $file = fopen($options['file'], "r");
    $header = array_map('trimWhitespace', fgetcsv($file));
    if(!array_diff($header, ['name', 'surname', 'email']) == []) {
        die('The CSV file does not contain the required fields (name, surname, email) for importing users. ');
    }
    $users = [];
    while ($row = fgetcsv($file)) {
        $users[] = array_combine($header, $row);
    }

    $users = array_map(function ($user) {
        $userFields = array_map('trimWhitespace', $user);
        $user = new User;
        $user->setEmail($userFields['name']);
        $user->setName($userFields['surname']);
        $user->setSurname($userFields['email']);
        return $user;
    }, $users);
}


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

class Database {
    private $connection;
    private $host;
    private $username;
    private $password;

    public function __construct($host, $username, $password)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
    }

    public function isConnected() {
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    public function query($sql) {
        $this->connection = new mysqli($this->host, $this->username, $this->password);
        $this->isConnected();
        if ($this->connection->query($sql) === TRUE) {
            echo "Query was successfully";
        } else {
            echo "Error executing the query: " . $this->connection->error;
        }

        $this->connection->close();
    }
}

function trimWhitespace($item) {
    return trim($item);
}