CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    last_name VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    civil_status VARCHAR(50),
    work_status VARCHAR(50),
    position VARCHAR(100),
    dob DATE NOT NULL,
    age INT,
    place_of_birth VARCHAR(255) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    email VARCHAR(255) NOT NULL,
    social_media VARCHAR(255),
    street VARCHAR(100) NOT NULL,
    barangay VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    parent_last_name VARCHAR(100) NOT NULL,
    parent_first_name VARCHAR(100) NOT NULL,
    parent_middle_name VARCHAR(100),
    parent_relationship VARCHAR(50) NOT NULL,
    parent_contact VARCHAR(15) NOT NULL,
    school VARCHAR(255) NOT NULL,
    education_level ENUM('Elementary', 'Junior High School', 'Senior High School', 'College', 'Vocational') NOT NULL,
    year_level VARCHAR(50) NOT NULL,
    emergency_name VARCHAR(255),
    emergency_relationship VARCHAR(100),
    emergency_contact VARCHAR(15),
    id_photo VARCHAR(255) NOT NULL,
    birth_certificate VARCHAR(255) NOT NULL,
    residence_certificate VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);