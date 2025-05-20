CREATE DATABASE IF NOT EXISTS todo_app;
USE todo_app;

CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed BOOLEAN DEFAULT FALSE
);

-- Add test data
INSERT INTO tasks (title, description, completed) VALUES
('Buy groceries', 'Milk, eggs, bread', false),
('Finish project', 'Complete the todo app', false),
('Call mom', 'Birthday reminder', true);