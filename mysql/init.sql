USE helpdesk;

CREATE TABLE IF NOT EXISTS tickets (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(150) NOT NULL,
    requester VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL,
    category ENUM(
        'Hardware',
        'Software',
        'Network',
        'Access',
        'Email',
        'Other'
    ) NOT NULL,
    priority ENUM(
        'Low',
        'Medium',
        'High',
        'Critical'
    ) NOT NULL DEFAULT 'Medium',
    status ENUM(
        'Open',
        'In Progress',
        'Resolved',
        'Closed'
    ) NOT NULL DEFAULT 'Open',
    description TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_status_created (status, created_at),
    INDEX idx_category (category),
    INDEX idx_priority (priority),
    INDEX idx_requester (requester),
    INDEX idx_email (email)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;