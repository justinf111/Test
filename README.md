## User Upload
This application will assist in importing large sets of users into your user table. The user data will be process before going into the database to ensure it's on valid data touching the database.

### Requirements
- MySQL Database
- Access to the stty command (for password prompt)

### Usage

The application is running using the following command and directives below.
```
php user_upload.php
```

Available Directives
```
--file [csv file name] – this is the name of the CSV to be parsed. It must contain the following headers: name, surname, email
--create_table – this will cause the MySQL users table to be built or rebuilt if it already exists (and no further action will be taken). 
--dry_run – this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered
-u – MySQL username
-p – MySQL password (will prompt you to enter in password, no value needed initially)
-h – MySQL host
-d – MySQL database (defaults to user_upload)
--help – which will output the above list of directives with details.
```
   