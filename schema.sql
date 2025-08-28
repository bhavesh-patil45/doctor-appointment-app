-- Disable foreign key checks so we can drop without errors
SET FOREIGN_KEY_CHECKS = 0;

-- Drop dependent tables first
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS doctor_availability;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  profile_image VARCHAR(255) DEFAULT 'default.png',
  role ENUM('doctor','patient') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create doctor availability table
CREATE TABLE doctor_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    available_date DATE NOT NULL,
    available_time TIME NOT NULL,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create appointments table
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATETIME NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Insert sample data
INSERT INTO users (name, email, password, role)
VALUES
('Dr. Smith', 'drsmith@example.com', '$2y$10$8BHv8ZyV9X1YkWJ6lHje9uqCy6r3ClO07tJhTtQpR6xPCthAoTqP2', 'doctor'),
('John Doe', 'johndoe@example.com', '$2y$10$8BHv8ZyV9X1YkWJ6lHje9uqCy6r3ClO07tJhTtQpR6xPCthAoTqP2', 'patient');

INSERT INTO doctor_availability (doctor_id, available_date, available_time)
VALUES
(1, '2025-08-15', '10:00:00'),
(1, '2025-08-16', '14:00:00');