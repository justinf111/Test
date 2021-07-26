<?php

$shortopts  = "h:";
$shortopts .= "u:";
$shortopts .= "p:";
$shortopts .= "d:";

$longopts  = [
    "help",
    "file:",
    "create_table",
    "dry_run",
];
$options = getopt($shortopts, $longopts);

if(isset($options['help'])) {
    fwrite(STDOUT, "--file [csv file name] – this is the name of the CSV to be parsed. It must contain the following headers: name, surname, email\n");
    fwrite(STDOUT, "--create_table – this will cause the MySQL users table to be built or rebuilt if it already exists (and no further action will be taken)\n");
    fwrite(STDOUT, "--dry_run – this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered\n");
    fwrite(STDOUT, "-u – MySQL username\n");
    fwrite(STDOUT, "-p – MySQL password (will prompt you to enter in password, no value needed initially)\n");
    fwrite(STDOUT, "-h – MySQL host\n");
    fwrite(STDOUT, "-d – MySQL database (defaults to user_upload)\n");
    fwrite(STDOUT, "--help – which will output the above list of directives with details.\n");
}
$db = null;

if((isset($options['create_table']) || isset($options['file'])) && !isset($options['dry_run'])) {
    $db = new Database($options['h'], $options['u'], $options['p'] ?? '',$options['d'] ?? 'user_upload');
    $db->open();
    $db->isConnected();
}

if(isset($options['create_table']) && !isset($options['dry_run'])) {
    $db->query("DROP TABLE IF EXISTS users");
    $db->query("CREATE TABLE users (
        id int NOT NULL AUTO_INCREMENT,
        surname varchar(255),
        name varchar(255),
        email varchar(255),
        PRIMARY KEY (id)
    );");
    $db->query("CREATE UNIQUE INDEX email
                    ON Users (email);");
    fwrite(STDOUT,"Users Table has been created\n");
}

if(isset($options['file']) && !isset($options['create_table'])) {
    $extension = pathinfo($options['file'], PATHINFO_EXTENSION);
    if($extension != 'csv') {
        die('The file must be on file format CSV');
    }
    $file = fopen($options['file'], "r");
    $header = array_map(function($item) {
        return trim($item);
    }, fgetcsv($file));

    if(!array_diff($header, ['name', 'surname', 'email']) == []) {
        die('The CSV file does not contain the required fields (name, surname, email) for importing users. ');
    }
    fwrite(STDOUT,"The file is a valid CSV file.\n");
    fwrite(STDOUT, "Processing users.\n");
    $users = [];
    while ($row = fgetcsv($file)) {
        $users[] = array_combine($header, $row);
    }

    $users = array_map(function ($user) use($db, $options){
        $userFields = array_map(function($item){
            return trim($item);
        }, $user);
        $user = new User;
        $user->setName($userFields['name']);
        $user->setSurname($userFields['surname']);
        $user->setEmail($userFields['email']);
        if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            fwrite(STDOUT, $user->name. " ". $user->surname. " has an invalid email (".$user->email.") and will not be inserted into the table\n");
        } else {
            fwrite(STDOUT,$user->name. " ". $user->surname. " Email: ".$user->email. "\n");
            if(!isset($options['dry_run'])) {
                $db->query('INSERT INTO users(name, surname, email) VALUES (?, ?, ?)', 'sss', [
                        $user->name,
                        $user->surname,
                        $user->email,
                    ]
                );
            }
            return $user;
        }

    }, $users);
    fwrite(STDOUT, "Finished processing users.\n");
}

if((isset($options['create_table']) || isset($options['file'])) && !isset($options['dry_run'])) {
    $db->close();
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
    /** @var mysqli */
    private $connection;
    private $host;
    private $username;
    private $password;
    private $database;

    public function __construct($host, $username, $password, $database)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }

    public function isConnected() {
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error."\n");
        }
    }

    public function query($sql, $types = '', $data = []) {
        $statement = $this->connection->prepare($sql);
        if($types != '' && count($data) > 0) {
            $statement->bind_param($types, ...$data);
        }
        if ($statement->execute() === TRUE) {
            fwrite(STDOUT,"Query was successfully\n");
        } else {
            fwrite(STDOUT,"Error executing the query: " . $this->connection->error."\n");
        }
    }

    public function open() {
        $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database, 3306);
    }

    public function close() {
        $this->connection->close();
    }
}