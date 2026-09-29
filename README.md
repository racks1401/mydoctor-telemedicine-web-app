# 🩺 My Doctor – Telemedicine Web Application

A full-stack telemedicine web application designed to provide a digital platform for doctors, patients, and administrators to manage appointments, profiles, communication, and healthcare-related activities.

## 📌 Overview

**My Doctor** is a web-based healthcare management system developed using PHP, MySQL/MariaDB, HTML, CSS, and JavaScript.

The application provides role-based functionality for administrators, doctors, and patients. It includes dashboards, appointment management, doctor and patient management, authentication, communication, and real-time audio/video functionality.

---

## ✨ Features

### 👨‍⚕️ Doctor

- Doctor registration and authentication
- Doctor profile management
- View and manage appointments
- Manage patient-related information
- Doctor dashboard
- Communication with patients
- Audio/video communication

### 👤 Patient

- Patient registration and authentication
- Patient profile management
- Browse doctors
- Book appointments
- View appointment information
- Appointment history
- Communication with doctors
- Audio/video communication

### 👨‍💼 Admin

- Admin authentication
- Dashboard with system statistics
- Doctor management
- Patient management
- Appointment management
- User verification
- Blocked-user management

### 📅 Appointment Management

- Appointment booking
- Appointment status management
- Appointment history
- Doctor and patient appointment views

### 💬 Communication

- Doctor-patient chat
- Real-time communication
- Audio/video calling using WebRTC
- Communication functionality independent of appointment management

### 📊 Dashboard & Analytics

- Dashboard statistics
- Doctor and patient data
- Appointment statistics
- Interactive charts and data visualization

---

## 🛠️ Technologies Used

### Frontend

- HTML5
- CSS3
- JavaScript
- WebRTC
- Chart.js
- Fetch API

### Backend

- PHP

### Database

- MySQL / MariaDB

### Development Tools

- XAMPP
- Apache
- phpMyAdmin
- Visual Studio Code
- Git
- GitHub

---

## 🏗️ Project Structure

```text
my_doctor/
│
├── app/
│   ├── controllers/
│   ├── helpers/
│   ├── middleware/
│   │   ├── admin-auth.php
│   │   ├── admin-profile.php
│   │   ├── doctor-auth.php
│   │   ├── doctor-profile.php
│   │   ├── patient-auth.php
│   │   ├── patient-profile.php
│   │   └── remember-me.php
│   │
│   ├── models/
│   │   ├── AppointmentModel.php
│   │   ├── DoctorModel.php
│   │   └── UserModel.php
│   │
│   ├── services/
│   │   ├── admin/
│   │   │   ├── AppointmentService.php
│   │   │   ├── BlockedUserService.php
│   │   │   ├── DashboardService.php
│   │   │   ├── DoctorsService.php
│   │   │   ├── PatientsService.php
│   │   │   └── UserVerificationService.php
│   │   │
│   │   └── patient/
│   │       ├── AppointmentService.php
│   │       ├── BlockedUserService.php
│   │       ├── DashboardService.php
│   │       ├── DoctorsService.php
│   │       ├── PatientsService.php
│   │       └── UserVerificationService.php
│   │
│   └── views/
│       ├── admin/
│       ├── doctor/
│       └── patient/
│
├── config/
│   └── database.php
│
├── database/
│   └── my_doctor_db_backup.sql
│
├── images/
│
├── public/
│   ├── admin/
│   ├── doctor/
│   ├── patient/
│   ├── assets/
│   ├── uploads/
│   ├── index.php
│   ├── login_register.php
│   └── logout.php
│
├── .gitignore
├── LICENSE
└── README.md
```

> The project structure may evolve as new features and improvements are added.

---

## ⚙️ Requirements

To run this project locally, you need:

- PHP 8.x or a compatible version
- MySQL / MariaDB
- Apache Web Server
- XAMPP
- phpMyAdmin
- Modern web browser
- Git (optional)

---

## 🚀 Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/racks1401/mydoctor-telemedicine-web-app.git
```

Navigate into the project directory:

```bash
cd mydoctor-telemedicine-web-app
```

### 2. Start XAMPP

Open the **XAMPP Control Panel** and start:

- Apache
- MySQL

Make sure both services are running successfully.

### 3. Create the Database

Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Create the required database.

Then import the SQL backup located at:

```text
database/my_doctor_db_backup.sql
```

The SQL file contains the database structure required by the application.

### 4. Configure the Database Connection

Open:

```text
config/database.php
```

Update the database configuration according to your local environment.

Example:

```php
<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "my_doctor";
```

> The values above are examples for a local XAMPP environment. Use the appropriate credentials for your own setup.

### 5. Place the Project in XAMPP

Copy the project into the XAMPP `htdocs` directory.

Example:

```text
C:\xampp\htdocs\my_doctor
```

### 6. Run the Application

Open your browser and navigate to:

```text
http://localhost/my_doctor/public/
```

The exact URL may vary depending on your XAMPP and project directory configuration.

---

## 🗄️ Database

The application uses **MySQL/MariaDB** as its database system.

The database backup is included in:

```text
database/my_doctor_db_backup.sql
```

The database contains tables required for managing:

- Users
- Doctors
- Patients
- Appointments
- Authentication-related data
- Other application data

### Importing the Database

1. Open phpMyAdmin.
2. Create the application database.
3. Select the database.
4. Click **Import**.
5. Select:

```text
database/my_doctor_db_backup.sql
```

6. Click **Go**.

> For a public repository, use only test or sample data. Do not commit real patient, doctor, or user information.

---

## 📸 Screenshots

Screenshots of the application's major interfaces can be added here.

### Admin Dashboard

_Add screenshot here._

### Doctor Dashboard

_Add screenshot here._

### Patient Dashboard

_Add screenshot here._

### Appointment Management

_Add screenshot here._

### Doctor Management

_Add screenshot here._

---

## 🔄 Application Flow

```text
                    ┌─────────────────┐
                    │     Visitor     │
                    └────────┬────────┘
                             │
                             ▼
                    ┌─────────────────┐
                    │ Login / Register│
                    └────────┬────────┘
                             │
              ┌──────────────┼──────────────┐
              │              │              │
              ▼              ▼              ▼
       ┌────────────┐ ┌────────────┐ ┌────────────┐
       │    Admin   │ │   Doctor   │ │   Patient  │
       └─────┬──────┘ └─────┬──────┘ └─────┬──────┘
             │              │              │
             ▼              ▼              ▼
       ┌────────────┐ ┌────────────┐ ┌────────────┐
       │ Dashboard  │ │ Dashboard  │ │ Dashboard  │
       └────────────┘ └─────┬──────┘ └─────┬──────┘
                            │              │
                            └──────┬───────┘
                                   ▼
                         ┌──────────────────┐
                         │ Appointments     │
                         │ Chat / Calling   │
                         └────────┬─────────┘
                                  │
                                  ▼
                         ┌──────────────────┐
                         │ MySQL / MariaDB  │
                         └──────────────────┘
```

---

## 📂 Main Application Modules

| Module | Description |
|---|---|
| Authentication | Login, registration, and session management |
| Admin | System and user management |
| Doctors | Doctor profiles and management |
| Patients | Patient profiles and management |
| Appointments | Booking and appointment management |
| Dashboard | Statistics and application overview |
| Chat | Doctor-patient communication |
| Calling | Real-time audio/video communication |
| Database | MySQL/MariaDB data storage |

---

## 🔐 Security

The application includes authentication and role-based access control for different types of users.

Security-related functionality includes:

- Authentication
- Role-based authorization
- Protected application areas
- Session management
- User verification
- Centralized database configuration
- Remember-me functionality

For production deployment, additional security measures should be implemented, including:

- HTTPS
- Secure cookies
- CSRF protection
- Strong password policies
- Input validation and sanitization
- Rate limiting
- Environment-based configuration
- Secure API credentials
- Production database security

---

## 🔮 Future Improvements

Possible future enhancements include:

- Online payment integration
- Prescription management
- Medical report/document management
- Push notifications
- Improved video consultation
- Advanced analytics
- Cloud deployment
- Mobile application integration
- Enhanced security
- Automated email/SMS notifications

---

## 📌 Project Status

🚧 **Active Development**

The project is being continuously improved with new features, bug fixes, security improvements, and performance optimizations.

---

## 📄 License

This project is developed for educational and portfolio purposes.

See the [LICENSE](LICENSE) file for more information.

---

## 👨‍💻 Author

**Ravi Kumar Prabudh**

- GitHub: [racks1401](https://github.com/racks1401)
- Portfolio: [portfolio-site-c4ceb.web.app](https://portfolio-site-c4ceb.web.app/)

---

## ⭐ Acknowledgements

This project was developed as a full-stack telemedicine web application to explore healthcare management systems, role-based authentication, database-driven applications, appointment management, and real-time communication technologies.
