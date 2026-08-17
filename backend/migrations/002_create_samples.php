<?php

return [
    'CREATE TABLE IF NOT EXISTS samples (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sample_code VARCHAR(20) NOT NULL UNIQUE,
        sample_type VARCHAR(8) NOT NULL,
        sample_status VARCHAR(10) NOT NULL,
        sample_technician VARCHAR(40) NULL,
        sample_receival_date DATE NOT NULL,
        sample_conclusion_date DATE NULL
    )',
];
