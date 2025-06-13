# USSD-Appeal-Sytem
USSD-Based Student Marks Appeal System
Overview
This is a USSD (Unstructured Supplementary Service Data) application designed to facilitate student interactions with a marks appeal system. It provides a simple, interactive menu-driven interface accessible via mobile phones, allowing students to check their marks, submit appeals for specific modules, and track the status of their appeals. Additionally, it includes an administrative panel for authorized personnel to manage student registrations, module registrations, mark entries, and appeal resolutions.

The system intelligently routes users to either the student or admin interface based on their phone number, providing a seamless and secure experience.

Features
Student Panel
Check Marks: Students can view their marks for all registered modules by entering their student ID.
Appeal Marks: Students can initiate an appeal for a specific module, providing their student ID, selecting the module, and detailing the reason for the appeal.
Check Appeal Status: Students can track the current status (e.g., pending, approved, rejected) of their submitted appeals.
Session-based Navigation: Interactive menus guide the user through each process step-by-step.
Admin Panel
User Authorization: Access to the admin panel is restricted to registered administrators based on their phone number.
Register Student: Admins can register new students by providing their ID, name, and phone number.
Register Module: Admins can register new academic modules by providing a module code and name.
Insert Marks: Admins can assign marks to students for specific modules.
View Appeals: Admins can view a list of all submitted appeals, ordered by status.
Resolve Appeal: Admins can review pending appeals and decide to approve or reject them, updating the appeal's status.
Session-based Navigation: Interactive menus guide the admin through each process step-by-step.
Technologies Used
PHP: The core backend logic for handling USSD requests, processing user input, and interacting with the database.
MySQL (or compatible RDBMS): For storing system data including student information, modules, marks, appeals, and admin credentials.
USSD Gateway: (Implicit) An external USSD gateway service is required to integrate this application with mobile network operators. This application acts as the backend for such a gateway, receiving USSD requests (sessionId, serviceCode, phoneNumber, text) and returning USSD responses (CON or END).

Project Structure
.
├── admin.php      # Handles all administrative panel logic and interactions.
├── student.php    # Handles all student panel logic and interactions.
├── index.php      # Main entry point; determines user type (admin/student) and routes the request.
└── db.php         # Database connection configuration.
Note: The db.php file establishes a PDO database connection $pdo.

Database Schema
The database schema is defined in appeal_system.sql and consists of the following tables:

admins
Purpose: Stores information about authorized administrators of the system.
Columns:
id (INT, Primary Key, AUTO_INCREMENT): Unique identifier for the admin.
phone_number (VARCHAR(15), NOT NULL, UNIQUE): The admin's phone number, used for authentication.
name (VARCHAR(100), DEFAULT NULL): The admin's name.
Sample Data: An example admin is Marc with phone_number +250788658293.
students
Purpose: Stores information about registered students.
Columns:
id (INT, Primary Key, AUTO_INCREMENT): Unique internal identifier for the student.
student_id (VARCHAR(20), NOT NULL, UNIQUE): The student's unique academic ID.
name (VARCHAR(100), DEFAULT NULL): The student's full name.
phone_number (VARCHAR(15), NOT NULL, UNIQUE): The student's phone number.
Sample Data: Includes students like Niyogushimwa Vanessa (22RP01200, +250727331902).
modules
Purpose: Stores details of academic modules offered.
Columns:
id (INT, Primary Key, AUTO_INCREMENT): Unique identifier for the module.
module_code (VARCHAR(20), NOT NULL, UNIQUE): The unique code for the module (e.g., "ITLAD701").
module_name (VARCHAR(100), NOT NULL): The full name of the module (e.g., "API Dev").
Sample Data: Examples include API Dev (ITLAD701) and USSD App (ITLUA701).
marks
Purpose: Records marks obtained by students in specific modules.
Columns:
id (INT, Primary Key, AUTO_INCREMENT): Unique identifier for the mark entry.
student_id (VARCHAR(20), DEFAULT NULL): Foreign key referencing students.student_id.
module_id (INT, DEFAULT NULL): Foreign key referencing modules.id.
mark (INT, DEFAULT NULL): The numerical mark (0-100).
Relationships:
FOREIGN KEY (student_id) REFERENCES students (student_id)
FOREIGN KEY (module_id) REFERENCES modules (id)
Sample Data: Shows 22RP01200 having 80 in module 5 (API Dev).
appeals
Purpose: Stores details of student appeals regarding their marks.
Columns:
id (INT, Primary Key, AUTO_INCREMENT): Unique identifier for the appeal.
student_id (VARCHAR(20), DEFAULT NULL): Foreign key referencing students.student_id.
module_id (INT, DEFAULT NULL): Foreign key referencing modules.id.
reason (TEXT, DEFAULT NULL): The student's reason for the appeal.
status (ENUM('Pending', 'Under Review', 'Approved', 'Rejected'), DEFAULT 'Pending'): The current status of the appeal.
Relationships:
FOREIGN KEY (student_id) REFERENCES students (student_id)
FOREIGN KEY (module_id) REFERENCES modules (id)
Sample Data: An appeal from 22RP01200 for module 5 with status Rejected.

Installation and Setup
Web Server: Ensure you have a web server (e.g., Apache, Nginx) with PHP support installed.
Database: Set up a MySQL database (or a compatible RDBMS).
Database Connection (db.php):
Create a file named db.php in the root of your project.
Populate it with your database connection details using PDO. <!-- end list -->
PHP

<?php
$host = 'localhost'; // Your database host
$db   = 'appeal_system'; // Your database name
$user = 'your_db_user'; // Your database username
$pass = 'your_db_password'; // Your database password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
Crucially, create the tables (admins, students, modules, marks, appeals) in your database according to the schema described above. You'll need to write the SQL CREATE TABLE statements yourself based on the column usage in admin.php and student.php.
Place Files: Place index.php, admin.php, student.php, and db.php in your web server's document root or a sub-directory.
USSD Gateway Integration:
Configure your USSD gateway to point to the URL of your index.php file (e.g., http://yourdomain.com/path/to/index.php).
Ensure the gateway sends requests as POST with sessionId, serviceCode, phoneNumber, and text parameters.
The application responds with CON (continue session) or END (terminate session) followed by the menu/message.
Add Admin Numbers: Manually insert an admin phone number into the admins table to test the admin panel.
Usage
For Students:
Dial the USSD service code provided by your network operator.
Welcome Menu:
1: Check my marks
2: Appeal my marks
3: Check appeal status
0: Exit
Follow the on-screen prompts, entering your student ID and other requested information at each step.
For Administrators:
Dial the same USSD service code.
If your phone number is registered as an admin, you will see the Admin Panel:
1: Register student
2: Register module
3: Insert marks
4: View appeals
5: Resolve appeal
0: Exit
Follow the on-screen prompts to perform administrative tasks.
Error Handling and Caveats
Input Validation: The current system relies heavily on the text string format (* delimiter). Robust input validation (e.g., ensuring numeric input where expected, sanitizing strings) would be crucial for production.
Security: Database credentials in db.php should be secured (e.g., using environment variables) in a production environment. SQL injection prevention is handled by PDO prepared statements, but other potential vulnerabilities should be considered.
State Management: USSD is stateless by nature. This application manages "state" by appending user input to the $text variable. For complex flows, more sophisticated state management might be considered (e.g., storing user state in a database session table).
User Experience (UX): USSD limitations mean complex interactions can be cumbersome. Keep menus concise and instructions clear.
Phone Number Format: Ensure consistency in how phone numbers are stored and compared (e.g., with or without country code, leading zeros).
Atomic Operations: For critical operations (like resolving appeals), consider database transactions to ensure data consistency.
Contributing
If you wish to contribute to this project, please feel free to:

Fork the repository.
Create a new branch (git checkout -b feature/your-feature-name).
Make your changes.
Commit your changes (git commit -m 'Add new feature').
Push to the branch (git push origin feature/your-feature-name).
Open a Pull Request.
License
This project is open-source and available under the MIT License.
