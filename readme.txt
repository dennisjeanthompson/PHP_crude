PHP + MySQL CRUD Project Setup (WAMP)

1. Extract the "dbdemo" folder into your WAMP "www" directory:

   C:\
    └── wamp64\
        └── www\
            └── dbdemo\
                ├── db.php
                ├── index.php
                ├── edit.php
                └── delete.php

2. Make sure your WAMP server is running.
   - The WAMP icon in the system tray should be green.
   - Both Apache and MySQL services must be running.

3. Open your browser and visit the project:
   http://localhost/dbdemo/

   This will automatically:
   - Create a MySQL database called "my_app"
   - Create a table called "users" (if it doesn't already exist)

4. To view and manage the database manually:
   http://localhost/phpmyadmin/

   - Login using the default credentials (usually "root" with no password)
   - Look for the "my_app" database and check the "users" table

You can now test basic CRUD operations (Create, Read, Update, Delete)
through the web interface.
