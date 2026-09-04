-- Student Information System Database
-- American College of Higher Education

CREATE DATABASE IF NOT EXISTS sis;
USE sis;

-- Users Table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    role ENUM('admin', 'staff', 'student') DEFAULT 'staff',
    profile_pic VARCHAR(255),
    last_login DATETIME,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students Table
CREATE TABLE students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    student_number VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    ic_number VARCHAR(20) UNIQUE NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    dob DATE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    address TEXT NOT NULL,
    program VARCHAR(100) NOT NULL,
    year_of_study INT DEFAULT 1,
    photo VARCHAR(255),
    guardian_name VARCHAR(100),
    guardian_phone VARCHAR(15),
    guardian_email VARCHAR(100),
    enrollment_date DATE,
    status ENUM('active', 'graduated', 'suspended', 'withdrawn') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Courses Table
CREATE TABLE courses (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    program VARCHAR(100) NOT NULL,
    credit_hours INT NOT NULL,
    semester INT NOT NULL,
    year INT NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Enrollments Table
CREATE TABLE enrollments (
    enrollment_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    semester INT NOT NULL,
    year INT NOT NULL,
    grade VARCHAR(5),
    grade_points DECIMAL(3,2),
    status ENUM('enrolled', 'completed', 'withdrawn', 'failed') DEFAULT 'enrolled',
    enrollment_date DATE,
    completion_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
);

-- Payments Table
CREATE TABLE payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_no VARCHAR(50) UNIQUE NOT NULL,
    student_id INT NOT NULL,
    payment_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) DEFAULT 0,
    due_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'online', 'card') DEFAULT 'cash',
    status ENUM('paid', 'partial', 'due', 'overdue') DEFAULT 'due',
    description TEXT,
    receipt_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

-- Insert Admin User (password: admin123)
INSERT INTO users (username, password, full_name, email, phone, role) VALUES 
('admin', '\\\.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@college.edu.lk', '+94 77 000 0000', 'admin');

-- Insert Sample Students
INSERT INTO students (student_number, full_name, ic_number, gender, dob, email, phone, address, program, year_of_study, enrollment_date) VALUES
('STU2026001', 'Amaya Perera', '200412345678', 'Female', '2004-03-02', 'amaya@example.com', '+94 77 123 4567', '123, Galle Road, Colombo 04, Sri Lanka', 'Bachelor in Information Technology', 2, '2025-01-10'),
('STU2026002', 'Nethmi Silva', '200512345679', 'Female', '2005-06-15', 'nethmi@example.com', '+94 71 234 5678', '45, Kandy Road, Colombo 05, Sri Lanka', 'Bachelor in Information Technology', 1, '2025-08-05'),
('STU2026003', 'Kasun Fernando', '200412345680', 'Male', '2004-08-20', 'kasun@example.com', '+94 76 345 6789', '78, Negombo Road, Colombo 03, Sri Lanka', 'Bachelor in Business Administration', 2, '2025-01-12'),
('STU2026004', 'Dinithi Jayasekara', '200312345681', 'Female', '2003-09-10', 'dinithi@example.com', '+94 75 456 7890', '56, Bauddhaloka Mawatha, Colombo 07, Sri Lanka', 'Bachelor in Information Technology', 3, '2024-08-08'),
('STU2026005', 'Saman Perera', '200212345682', 'Male', '2002-11-25', 'saman@example.com', '+94 78 567 8901', '234, Havelock Road, Colombo 06, Sri Lanka', 'Bachelor in Business Administration', 3, '2026-01-12'),
('STU2026006', 'Lahiru Fonseka', '200512345683', 'Male', '2005-01-05', 'lahiru@example.com', '+94 77 678 9012', '67, Marine Drive, Colombo 03, Sri Lanka', 'Bachelor in Information Technology', 1, '2025-01-12'),
('STU2026007', 'Yashini De Silva', '200412345684', 'Female', '2004-07-18', 'yashini@example.com', '+94 72 789 0123', '89, Station Road, Colombo 08, Sri Lanka', 'Bachelor in Business Administration', 2, '2025-01-12');

-- Insert Sample Courses
INSERT INTO courses (course_code, course_name, program, credit_hours, semester, year) VALUES
('IT101', 'Introduction to Programming', 'Bachelor in Information Technology', 3, 1, 1),
('IT102', 'Data Structures and Algorithms', 'Bachelor in Information Technology', 4, 2, 1),
('IT201', 'Database Systems', 'Bachelor in Information Technology', 3, 1, 2),
('CS101', 'Discrete Mathematics', 'Bachelor in Information Technology', 3, 1, 1),
('MG101', 'Principles of Management', 'Bachelor in Business Administration', 3, 1, 1),
('MG145', 'Financial Accounting', 'Bachelor in Business Administration', 4, 2, 1),
('MG111', 'Organizational Behavior', 'Bachelor in Business Administration', 3, 2, 2);

-- Insert Sample Enrollments
INSERT INTO enrollments (student_id, course_id, semester, year, status, enrollment_date) VALUES
(1, 1, 1, 2025, 'completed', '2025-01-10'),
(1, 2, 2, 2025, 'completed', '2025-01-10'),
(2, 1, 1, 2025, 'enrolled', '2025-08-05'),
(3, 5, 1, 2025, 'completed', '2025-01-12'),
(3, 6, 2, 2025, 'enrolled', '2025-01-12'),
(4, 1, 1, 2024, 'completed', '2024-08-08'),
(4, 2, 2, 2024, 'completed', '2024-08-08'),
(4, 3, 1, 2025, 'enrolled', '2025-01-10'),
(5, 5, 1, 2025, 'completed', '2026-01-12'),
(5, 6, 2, 2025, 'enrolled', '2026-01-12');

-- Insert Sample Payments
INSERT INTO payments (invoice_no, student_id, payment_date, total_amount, paid_amount, due_amount, status) VALUES
('INV-2026-0001', 1, '2026-01-10', 50000, 50000, 0, 'paid'),
('INV-2026-0002', 2, '2026-01-10', 45000, 45000, 0, 'paid'),
('INV-2025-0003', 3, '2026-05-20', 50000, 30000, 20000, 'partial'),
('INV-2025-0004', 4, '2026-05-23', 60000, 0, 60000, 'due'),
('INV-2025-0005', 5, '2026-01-12', 150000, 150000, 0, 'paid'),
('INV-2025-0006', 6, '2026-01-12', 45000, 20000, 25000, 'partial'),
('INV-2025-0007', 7, '2026-01-12', 50000, 50000, 0, 'paid');
