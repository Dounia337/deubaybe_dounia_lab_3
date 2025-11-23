USE attendance_system;

-- Insert Sample Users
INSERT IGNORE INTO users (first_name, last_name, email, password_hash, role) VALUES 
('Hutton', 'Hutton', 'hutton@university.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'fI'),
('David', 'Sampah', 'david@university.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Deubaybe', 'Dounia', 'deubaybe@university.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Jesus', 'Matamoros', 'jesus@university.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Albert', 'Kindo', 'albert@university.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Michael', 'Johnson', 'michael@university.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Leonardo', 'Montalvo', 'leonardo@university.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

-- Insert Sample Courses
INSERT IGNORE INTO courses (course_name, semester, cohort, created_by) VALUES 
('Web Technologies', '1st Semester fall', 'Cohort A', 2),
('Data Structures', '1st Semester fall', 'Cohort A', 2),
('Intermediate Programming', '1st Semester fall', 'Cohort A', 2),
('Leadership Seminar 4', '1st Semester fall', 'Cohort A', 2),
('Algorithm Design', '1st Semester fall', 'Cohort A', 2),
('Hardware & Systems', '1st Semester fall', 'Cohort A', 2);

-- Insert Sample Enrollments
INSERT IGNORE INTO enrollments (student_id, course_id) VALUES 
(3, 1), (3, 2), (3, 3),  -- Deubaybe enrolled in 3 courses
(4, 1), (4, 2),          -- Jesus enrolled in 2 courses
(5, 1), (5, 3),          -- Albert enrolled in 2 courses
(6, 2), (6, 3),          -- Michael enrolled in 2 courses
(7, 1), (7, 2), (7, 3); -- Leonardo enrolled in 3 courses

-- Insert Sample Sessions
INSERT IGNORE INTO sessions (course_id, session_date, session_time, hall, created_by) VALUES 
(1, '2025-10-10', '09:00:00', 'Lab 221', 2),
(1, '2025-10-17', '09:00:00', 'Lab 221', 2),
(2, '2025-10-11', '10:00:00', 'Room 101', 2),
(3, '2025-10-12', '11:00:00', 'Room 102', 2);

-- Insert Sample Attendance
INSERT IGNORE INTO attendance (session_id, student_id, status, time_marked, marked_by) VALUES 
(1, 3, 'present', '2025-10-10 09:00:00', 2),
(1, 4, 'absent', NULL, 2),
(1, 5, 'present', '2025-10-10 09:05:00', 2),
(1, 6, 'present', '2025-10-10 09:02:00', 2),
(1, 7, 'absent', NULL, 2),
(2, 3, 'present', '2025-10-17 09:00:00', 2),
(2, 4, 'late', '2025-10-17 09:15:00', 2),
(2, 5, 'present', '2025-10-17 09:00:00', 2),
(2, 6, 'absent', NULL, 2),
(2, 7, 'present', '2025-10-17 09:10:00', 2);