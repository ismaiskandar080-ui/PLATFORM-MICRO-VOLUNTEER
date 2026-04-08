CREATE DATABASE IF NOT EXISTS microvolunteer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE microvolunteer;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS ngo_profiles;
DROP TABLE IF EXISTS volunteer_profiles;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('volunteer','ngo','admin') NOT NULL DEFAULT 'volunteer',
    phone VARCHAR(20),
    image VARCHAR(255) DEFAULT 'default.png',
    status ENUM('active','suspended','blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS volunteer_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bio TEXT,
    skills TEXT,
    state VARCHAR(100),
    city VARCHAR(100),
    birth_date DATE,
    gender ENUM('male','female','other'),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS ngo_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    organization_name VARCHAR(200) NOT NULL,
    registration_number VARCHAR(100),
    address TEXT,
    state VARCHAR(100),
    city VARCHAR(100),
    description TEXT,
    website VARCHAR(255),
    logo VARCHAR(255) DEFAULT 'default_ngo.png',
    verified TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ngo_id INT NOT NULL,
    project_name VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('education','environment','social','health','technology','other') DEFAULT 'other',
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    state VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    lat DECIMAL(10,8),
    lng DECIMAL(11,8),
    quota INT DEFAULT 10,
    contact_phone VARCHAR(20),
    status ENUM('active','full','completed','cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ngo_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    volunteer_id INT NOT NULL,
    status ENUM('pending','accepted','rejected','attended','absent') DEFAULT 'pending',
    volunteer_comment TEXT,
    volunteer_rating TINYINT CHECK (volunteer_rating BETWEEN 1 AND 5),
    is_completed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_application (project_id, volunteer_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (volunteer_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
INSERT INTO users (name, email, password, role) VALUES
('MicroVolunteer Admin', 'admin@microvolunteer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
INSERT INTO users (name, email, password, role) VALUES
('Hope Education Foundation', 'ngo@harapan.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ngo');
INSERT INTO ngo_profiles (user_id, organization_name, registration_number, state, city, description, verified) VALUES
(2, 'Hope Education Foundation', 'PPM-001-10-22012020', 'Selangor', 'Shah Alam', 'Non-governmental organization focusing on the education of underprivileged children.', 1);
INSERT INTO users (name, email, password, role) VALUES
('Ahmad Firdaus', 'volunteer@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'volunteer');
INSERT INTO volunteer_profiles (user_id, bio, skills, state, city) VALUES
(3, 'University student who loves helping the community.', 'Teaching, Graphics, IT', 'Selangor', 'Petaling Jaya');
INSERT INTO projects (ngo_id, project_name, description, category, date, start_time, end_time, location, state, city, lat, lng, quota, contact_phone) VALUES
(2, 'B40 Student Mathematics Tutor', 'Free tutoring program for primary school students who need help in mathematics.', 'education', '2026-04-20', '09:00:00', '12:00:00', 'Taman Jaya National School, Shah Alam', 'Selangor', 'Shah Alam', 3.07387, 101.51872, 10, '0123456789'),
(2, 'Botanic Garden Cleanup', 'Park cleaning program to maintain environmental cleanliness with the community.', 'environment', '2026-04-27', '07:30:00', '11:00:00', 'Perdana Botanical Garden, KL', 'Kuala Lumpur', 'Kuala Lumpur', 3.14574, 101.68653, 20, '0112233445'),
(2, 'Donation Campaign Poster Design', 'Talented volunteers in graphic design needed to produce posters for the annual donation campaign.', 'technology', '2026-05-05', '10:00:00', '14:00:00', 'Online (Google Meet)', 'Selangor', 'Online', NULL, NULL, 5, '0167778889');
