PHP + MySQL CRUD Project - Student Directory
=============================================

This project is a simple CRUD (Create, Read, Update, Delete) application for managing student records with PHP and MySQL.


## For Development (Local Setup with WAMP/XAMPP)

1. Extract the project folder into your WAMP "www" directory or XAMPP "htdocs" directory:

   C:\
    └── wamp64\
        └── www\
            └── PHP_crude\
                ├── .env.example
                ├── config.php
                ├── db.php
                ├── index.php
                ├── edit.php
                ├── delete.php
                └── setup_database.sql

2. Create a `.env` file by copying `.env.example`:
   - Copy `.env.example` to `.env`
   - Edit `.env` and set:
     ```
     APP_ENV=development
     DB_HOST=localhost
     DB_NAME=cit173n_dst_validation
     DB_USER=root
     DB_PASS=
     DISPLAY_ERRORS=1
     ```

3. Make sure your WAMP/XAMPP server is running.
   - The WAMP icon in the system tray should be green.
   - Both Apache and MySQL services must be running.

4. Open your browser and visit the project:
   http://localhost/PHP_crude/

   In development mode, this will automatically:
   - Create a MySQL database if it doesn't exist
   - Create the "users" table if it doesn't exist

5. To view and manage the database manually:
   http://localhost/phpmyadmin/


## For Production Deployment

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- A web hosting service with PHP and MySQL support

### Deployment Steps

1. **Prepare Your Database:**
   - Log into your hosting control panel (cPanel, Plesk, etc.)
   - Create a new MySQL database
   - Create a database user with full privileges on the database
   - Note down: database name, database username, and password

2. **Run the Database Setup Script:**
   - In phpMyAdmin or your database management tool, select your database
   - Import or run the `setup_database.sql` file
   - This creates the necessary tables

3. **Configure the Application:**
   - Upload all project files to your web server (via FTP, SFTP, or file manager)
   - Copy `.env.example` to `.env` on the server
   - Edit `.env` with your production settings:
     ```
     APP_ENV=production
     DB_HOST=localhost
     DB_NAME=your_production_database_name
     DB_USER=your_production_database_user
     DB_PASS=your_secure_database_password
     DISPLAY_ERRORS=0
     ```
   - **IMPORTANT:** Never commit `.env` to version control!
   - **IMPORTANT:** Set strong, unique passwords for production

4. **Security Checklist:**
   - [ ] `.env` file is configured with production credentials
   - [ ] `.env` file is NOT in version control (should be in .gitignore)
   - [ ] `DISPLAY_ERRORS=0` in production .env
   - [ ] `APP_ENV=production` in production .env
   - [ ] Database user has a strong password
   - [ ] File permissions are set correctly (files: 644, directories: 755)
   - [ ] Error logs are configured to write to a file (not displayed to users)

5. **Access Your Application:**
   - Visit your domain: https://yourdomain.com/
   - The application should now be running securely

### Environment Variables

The application uses environment variables for configuration. You can set these in:
- `.env` file (recommended for development and shared hosting)
- Server environment variables (recommended for VPS/cloud hosting)

Available configuration options:
- `APP_ENV`: Set to 'development' or 'production'
- `DB_HOST`: MySQL server hostname (usually 'localhost')
- `DB_NAME`: Your database name
- `DB_USER`: Your database username
- `DB_PASS`: Your database password
- `DISPLAY_ERRORS`: Set to '1' for development, '0' for production

### Troubleshooting

**Database Connection Failed:**
- Verify database credentials in `.env`
- Ensure the database exists and the user has proper permissions
- Check if the database server is running

**Page Shows "Database connection failed":**
- This is the production error message (hiding details for security)
- Check your server's error logs for the actual error
- Temporarily set `APP_ENV=development` to see detailed errors (then change back!)

**Tables Not Found:**
- Run `setup_database.sql` in your database
- Verify the database name matches your `.env` configuration


## Features

- Create new student records with name, email, and birthday
- View all students with calculated age
- Edit existing student information
- Delete student records
- Input validation and error handling
- Secure database configuration using environment variables
- Production-ready with proper error handling


## Security Notes

- Database credentials are stored in `.env` (not committed to git)
- Error display is disabled in production mode
- Prepared statements are used to prevent SQL injection
- Email uniqueness is enforced at the database level
- Input validation on all user-submitted data


You can now test basic CRUD operations (Create, Read, Update, Delete)
through the web interface.
