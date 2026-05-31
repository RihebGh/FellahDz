CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'farmer', 'worker') NOT NULL DEFAULT 'worker',
    status ENUM('active', 'blocked') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE workers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    role ENUM('worker', 'farmer', 'admin') DEFAULT 'worker',
    status ENUM('active', 'blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- Insert default admin (Amir Zidane)
INSERT INTO users (email, password_hash, first_name, last_name, phone, role, status) 
VALUES (
    'admin@fellahdz.com', 
    '$2b$10$A2KNhf69p31gRgl8lx2Areu2IeqL3SYRqy8V.qDXj7YPxiQtN2vLy', -- bcrypt hash for 'password'
    'Amir', 
    'Zidane', 
    '0550123456', 
    'admin', 
    'active'
);

-- Insert default farmer (Saleh Zidane)
INSERT INTO users (email, password_hash, first_name, last_name, phone, role, status) 
VALUES (
    'saleh.zidane@fellahdz.com',
    '$2b$10$o4VPdFeo7LPDxKQyBsP14.CpHiZkUAO/BfHwY69moAO5JWvvwbNkW', -- bcrypt hash
    'Saleh',
    'Zidane',
    '0550123456',
    'farmer',
    'active'
);

-- Insert default farmer into workers table (NO id specified - let AUTO_INCREMENT handle it)
INSERT INTO workers (first_name, last_name, email, phone, role, status) 
VALUES (
    'Saleh',
    'Zidane',
    'saleh.zidane@fellahdz.com',
    '0550123456',
    'farmer',
    'active'
);

-- Insert default worker (Ahmed Merzoug)
INSERT INTO workers (first_name, last_name, email, phone, role, status) 
VALUES (
    'Ahmed',
    'Merzoug',
    'ahmed.merzoug@fellahdz.com',
    '0661234567',
    'worker',
    'active'
);

-- Insert default worker (Karim Benali)
INSERT INTO workers (first_name, last_name, email, phone, role, status) 
VALUES (
    'Karim',
    'Benali',
    'karim.benali@fellahdz.com',
    '0778345678',
    'worker',
    'active'
);

-- Insert default worker (Samir Boualem)
INSERT INTO workers (first_name, last_name, email, phone, role, status) 
VALUES (
    'Samir',
    'Boualem',
    'samir.boualem@fellahdz.com',
    '0555987654',
    'worker',
    'active'
);

-- Insert default worker (Farid Haddad)
INSERT INTO workers (first_name, last_name, email, phone, role, status) 
VALUES (
    'Farid',
    'Haddad',
    'farid.haddad@fellahdz.com',
    '0666111222',
    'worker',
    'active'
);

-- Insert default worker (Nadia Merabet)
INSERT INTO workers (first_name, last_name, email, phone, role, status) 
VALUES (
    'Nadia',
    'Merabet',
    'nadia.merabet@fellahdz.com',
    '0777333444',
    'worker',
    'active'
);

-- Insert default worker (Omar Amrani)
INSERT INTO workers (first_name, last_name, email, phone, role, status) 
VALUES (
    'Omar',
    'Amrani',
    'omar.amrani@fellahdz.com',
    '0788555666',
    'worker',
    'active'
);

CREATE TABLE equipment (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    status ENUM('Good', 'Broken', 'Maintenance') NOT NULL DEFAULT 'Good',
    maintenance_date DATE,
    location VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE seeds (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    quantity VARCHAR(50) NOT NULL,
    expiration_date DATE NOT NULL,
    location VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE fertilizers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    quantity VARCHAR(50) NOT NULL,
    npk_ratio VARCHAR(20) NOT NULL,
    expiration_date DATE NOT NULL,
    location VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


INSERT INTO equipment VALUES
(1,'Irrigation Pump A','Irrigation Machine','Good','2026-04-10','Field 1',NOW(),NOW()),
(2,'Drip Line B','Irrigation Machine','Good','2026-05-20','Field 2',NOW(),NOW()),
(3,'Sprinkler Set C','Irrigation Machine','Maintenance','2026-03-15','Field 3',NOW(),NOW()),
(4,'Tractor A','Tractor','Good','2026-03-26','Farm 1',NOW(),NOW()),
(5,'Tractor B','Tractor','Broken','2026-06-05','Farm 2',NOW(),NOW()),
(6,'Harvester X','Harvester','Good','2026-04-15','Farm 1',NOW(),NOW()),
(7,'Seeder Pro','Seeder','Maintenance','2026-02-20','Farm 3',NOW(),NOW()),
(8,'Pesticide Sprayer','Sprayer','Good','2026-07-01','Farm 1',NOW(),NOW());

INSERT INTO seeds VALUES
(1,'Corn','Cereal','500 kg','2026-12-10','Warehouse 1',NOW(),NOW()),
(2,'Wheat','Cereal','1200 kg','2026-11-05','Warehouse 2',NOW(),NOW()),
(3,'Barley','Cereal','300 kg','2026-10-20','Warehouse 1',NOW(),NOW()),
(4,'Tomato','Vegetable','50 kg','2026-08-15','Warehouse 1',NOW(),NOW()),
(5,'Lettuce','Vegetable','20 kg','2026-07-10','Warehouse 2',NOW(),NOW()),
(6,'Lentil','Legume','200 kg','2027-01-01','Warehouse 3',NOW(),NOW());

INSERT INTO fertilizers VALUES
(1,'NPK 20-20-20','Chemical','500 kg','20-20-20','2027-06-01','Storage A',NOW(),NOW()),
(2,'Organic Compost','Organic','2000 kg','3-2-2','2026-12-15','Storage B',NOW(),NOW()),
(3,'Urea 46-0-0','Chemical','300 kg','46-0-0','2027-03-20','Storage A',NOW(),NOW()),
(4,'Fish Emulsion','Liquid','100 L','5-1-1','2026-08-10','Storage C',NOW(),NOW()),
(5,'Bone Meal','Organic','150 kg','3-15-0','2027-01-30','Storage B',NOW(),NOW());


CREATE TABLE crops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    crop_name VARCHAR(50) NOT NULL,
    crop_type VARCHAR(50) NOT NULL,
    crop_emoji VARCHAR(10) DEFAULT '',
    crop_color VARCHAR(50) DEFAULT '', 
    planting_date DATE NOT NULL,
    harvest_date DATE NOT NULL,
    water_per_m2 VARCHAR(20) NOT NULL,
    section_number INT DEFAULT 1,
    is_mixed BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


CREATE TABLE crop_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    emoji VARCHAR(10) DEFAULT '',
    color_scheme VARCHAR(50) DEFAULT '', -- JSON or CSS class
    is_active BOOLEAN DEFAULT TRUE
);


INSERT INTO crop_types (name, emoji, color_scheme) VALUES
('Lettuce', '🥬', 'rgba(76,175,80,.12)|#2e7d32'),
('Tomato', '🍅', 'rgba(231,76,60,.1)|#c0392b'),
('Onion', '🧅', 'rgba(155,89,182,.1)|#8e44ad'),
('Apricots', '🍑', 'rgba(241,196,15,.1)|#d35400'),
('Beans', '🫘', 'rgba(46,204,113,.1)|#27ae60'),
('Carrot', '🥕', 'rgba(230,126,34,.1)|#d35400'),
('Potato', '🥔', 'rgba(189,195,199,.1)|#7f8c8d'),
('Cucumber', '🥒', 'rgba(46,204,113,.12)|#27ae60'),
('Pepper', '🫑', 'rgba(231,76,60,.12)|#c0392b'),
('Corn', '🌽', 'rgba(241,196,15,.15)|#f39c12'),
('Wheat', '🌾', 'rgba(243,156,18,.1)|#e67e22'),
('Grapes', '🍇', 'rgba(142,68,173,.1)|#8e44ad');


INSERT INTO crops (user_id, crop_name, crop_type, crop_emoji, crop_color, planting_date, harvest_date, water_per_m2, section_number, is_mixed, display_order) VALUES
(1, 'Lettuce', 'Lettuce', '🥬', 'rgba(76,175,80,.12)|#2e7d32', '2026-02-08', '2026-03-24', '3L / day', 1, FALSE, 1),
(1, 'Tomato', 'Tomato', '🍅', 'rgba(231,76,60,.1)|#c0392b', '2026-02-15', '2026-04-30', '4L / day', 2, FALSE, 2),
(1, 'Onion', 'Onion', '🧅', 'rgba(155,89,182,.1)|#8e44ad', '2026-03-01', '2026-06-15', '2.5L / day', 3, FALSE, 3),
(1, 'Apricots', 'Apricots', '🍑', 'rgba(241,196,15,.1)|#d35400', '2026-02-10', '2026-05-20', '5L / day', 4, TRUE, 4),
(1, 'Beans', 'Beans', '🫘', 'rgba(46,204,113,.1)|#27ae60', '2026-03-05', '2026-05-25', '2L / day', 4, TRUE, 4);


-- Create field_assignments table
CREATE TABLE field_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    field_number INT NOT NULL COMMENT 'Field number (1-4)',
    worker_id INT NOT NULL COMMENT 'Assigned worker ID',
    planted TINYINT(1) DEFAULT 0 COMMENT 'Planting status (0=No, 1=Yes)',
    irrigation TINYINT(1) DEFAULT 0 COMMENT 'Irrigation status (0=No, 1=Yes)',
    pesticides VARCHAR(100) DEFAULT NULL COMMENT 'Pesticides applied',
    notes TEXT DEFAULT NULL COMMENT 'Additional notes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key to workers table
    FOREIGN KEY (worker_id) REFERENCES workers(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    UNIQUE KEY unique_field_worker (field_number, worker_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes
CREATE INDEX idx_field_number ON field_assignments(field_number);
CREATE INDEX idx_worker_id ON field_assignments(worker_id);

-- Insert field assignments
INSERT INTO field_assignments (field_number, worker_id, planted, irrigation, pesticides, notes) VALUES
(1, (SELECT id FROM workers WHERE first_name='Ahmed' AND last_name='Merzoug'), 0, 0, NULL, 'Field 1'),
(2, (SELECT id FROM workers WHERE first_name='Karim' AND last_name='Benali'), 0, 0, NULL, 'Field 2'),
(3, (SELECT id FROM workers WHERE first_name='Samir' AND last_name='Boualem'), 0, 0, NULL, 'Field 3'),
(3, (SELECT id FROM workers WHERE first_name='Farid' AND last_name='Haddad'), 0, 0, NULL, 'Field 3'),
(4, (SELECT id FROM workers WHERE first_name='Nadia' AND last_name='Merabet'), 0, 0, NULL, 'Field 4'),
(4, (SELECT id FROM workers WHERE first_name='Omar' AND last_name='Amrani'), 0, 0, NULL, 'Field 4');


INSERT INTO users (email, password_hash, first_name, last_name, phone, role, status) 
VALUES (
    'ahmed.merzoug@fellahdz.com',
    '$2b$10$eCui07lG1buQLORoIPLTgO84BKP.0khzIJfXgsNOb.jOAR22hahJO',  -- Password: ahmedmerzoug
    'Ahmed',
    'Merzoug',
    '0661234567',
    'worker',
    'active'
);

INSERT INTO users (email, password_hash, first_name, last_name, phone, role, status) 
VALUES (
    'karim.benali@fellahdz.com',
    '$2b$10$EKKOJA1H7GvTmMLZvgIMjupBZhop8tBAR0QXcr7kG2u8XL0E/8EDm',  -- Password: karimbenali
    'Karim',
    'Benali',
    '0778345678',
    'worker',
    'active'
);

INSERT INTO users (email, password_hash, first_name, last_name, phone, role, status) 
VALUES (
    'samir.boualem@fellahdz.com',
    '$2b$10$DWtyh2M/88vJucKmSZjQYe3p7B7oSoIKYKVgpnS4WhYX2VhsPa4tC',  -- Password: samirboualem
    'Samir',
    'Boualem',
    '0555987654',
    'worker',
    'active'
);

INSERT INTO users (email, password_hash, first_name, last_name, phone, role, status) 
VALUES (
    'farid.haddad@fellahdz.com',
    '$2b$10$7X0gNtBnY3Md7hOe5tr9dOBHNWMAkggWNIbi51nV0i7zDdM.I/93y',  -- Password: faridhaddad
    'Farid',
    'Haddad',
    '0666111222',
    'worker',
    'active'
);

INSERT INTO users (email, password_hash, first_name, last_name, phone, role, status) 
VALUES (
    'nadia.merabet@fellahdz.com',
    '$2b$10$7mYxi7kA4PGaKeRlbVAfru53TjwFqWw6IZk7fAItTyuYv8TPBs2VG',  -- Password: nadiamerabet
    'Nadia',
    'Merabet',
    '0777333444',
    'worker',
    'active'
);

INSERT INTO users (email, password_hash, first_name, last_name, phone, role, status) 
VALUES (
    'omar.amrani@fellahdz.com',
    '$2b$10$iLFeelARMgw0gJeuzwnnDeKixjmTiKc/qNcZ4uNNyiipvs2IlEhlm',  -- Password: omaramrani
    'Omar',
    'Amrani',
    '0788555666',
    'worker',
    'active'
);


ALTER TABLE workers ADD COLUMN  password_hash VARCHAR(255) DEFAULT NULL;

-- Update existing workers with passwords
UPDATE workers SET password_hash = '$2b$10$o4VPdFeo7LPDxKQyBsP14.CpHiZkUAO/BfHwY69moAO5JWvvwbNkW' WHERE email = 'saleh.zidane@fellahdz.com';
UPDATE workers SET password_hash = '$2b$10$eCui07lG1buQLORoIPLTgO84BKP.0khzIJfXgsNOb.jOAR22hahJO' WHERE email = 'ahmed.merzoug@fellahdz.com';
UPDATE workers SET password_hash = '$2b$10$EKKOJA1H7GvTmMLZvgIMjupBZhop8tBAR0QXcr7kG2u8XL0E/8EDm' WHERE email = 'karim.benali@fellahdz.com';
UPDATE workers SET password_hash = '$2b$10$DWtyh2M/88vJucKmSZjQYe3p7B7oSoIKYKVgpnS4WhYX2VhsPa4tC' WHERE email = 'samir.boualem@fellahdz.com';
UPDATE workers SET password_hash = '$2b$10$7X0gNtBnY3Md7hOe5tr9dOBHNWMAkggWNIbi51nV0i7zDdM.I/93y' WHERE email = 'farid.haddad@fellahdz.com';
UPDATE workers SET password_hash = '$2b$10$7mYxi7kA4PGaKeRlbVAfru53TjwFqWw6IZk7fAItTyuYv8TPBs2VG' WHERE email = 'nadia.merabet@fellahdz.com';
UPDATE workers SET password_hash = '$2b$10$iLFeelARMgw0gJeuzwnnDeKixjmTiKc/qNcZ4uNNyiipvs2IlEhlm' WHERE email = 'omar.amrani@fellahdz.com';

-- Sync passwords to users table
UPDATE users u 
JOIN workers w ON u.email = w.email 
SET u.password_hash = w.password_hash;



ALTER TABLE crops 
ADD COLUMN IF NOT EXISTS crop_status VARCHAR(20) DEFAULT 'planted' 
    COMMENT 'Current growth stage: planted, germination, vegetative, flowering, fruiting, harvest',
ADD COLUMN IF NOT EXISTS health_status VARCHAR(20) DEFAULT 'good' 
    COMMENT 'Crop health condition: good, fair, poor',
ADD COLUMN IF NOT EXISTS status_changed_at TIMESTAMP NULL 
    COMMENT 'When crop status was last changed (for days-in-stage calculation)',
ADD COLUMN IF NOT EXISTS notes TEXT DEFAULT NULL 
    COMMENT 'Additional notes about the crop';


CREATE TABLE crop_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crop_id INT NOT NULL,
    previous_status VARCHAR(20) NOT NULL,
    new_status VARCHAR(20) NOT NULL,
    changed_by INT DEFAULT NULL COMMENT 'User ID who made the change',
    change_reason TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;


CREATE INDEX IF NOT EXISTS idx_crop_status ON crops(crop_status);
CREATE INDEX IF NOT EXISTS idx_crop_health ON crops(health_status);
CREATE INDEX IF NOT EXISTS idx_status_history_crop ON crop_status_history(crop_id);


UPDATE crops SET 
    crop_status = 'vegetative', 
    health_status = 'good', 
    status_changed_at = DATE_SUB(NOW(), INTERVAL 12 DAY),
    notes = 'Growing well, regular watering schedule'
WHERE crop_type = 'Lettuce';

  
UPDATE crops SET 
    crop_status = 'vegetative', 
    health_status = 'good', 
    status_changed_at = DATE_SUB(NOW(), INTERVAL 8 DAY),
    notes = 'Strong stems, leaves developing well'
WHERE crop_type = 'Tomato';


UPDATE crops SET 
    crop_status = 'planted', 
    health_status = 'good', 
    status_changed_at = DATE_SUB(NOW(), INTERVAL 5 DAY),
    notes = 'Recently planted, monitoring germination'
WHERE crop_type = 'Onion';


UPDATE crops SET 
    crop_status = 'flowering', 
    health_status = 'good', 
    status_changed_at = DATE_SUB(NOW(), INTERVAL 15 DAY),
    notes = 'Flowers appearing, pollination expected soon'
WHERE crop_type = 'Apricots';


UPDATE crops SET 
    crop_status = 'germination', 
    health_status = 'good', 
    status_changed_at = DATE_SUB(NOW(), INTERVAL 3 DAY),
    notes = 'First sprouts visible, maintaining moisture'
WHERE crop_type = 'Beans';


INSERT INTO crop_status_history (crop_id, previous_status, new_status, changed_by, change_reason) VALUES
((SELECT id FROM crops WHERE crop_type = 'Lettuce'), 'planted', 'germination', 1, 'Seeds sprouted'),
((SELECT id FROM crops WHERE crop_type = 'Lettuce'), 'germination', 'vegetative', 1, 'True leaves developed'),
((SELECT id FROM crops WHERE crop_type = 'Tomato'), 'planted', 'germination', 1, 'First sprouts visible'),
((SELECT id FROM crops WHERE crop_type = 'Tomato'), 'germination', 'vegetative', 1, 'Stem thickening, leaves growing'),
((SELECT id FROM crops WHERE crop_type = 'Onion'), 'preparation', 'planted', 1, 'Seeds sown in field'),
((SELECT id FROM crops WHERE crop_type = 'Apricots'), 'vegetative', 'flowering', 1, 'Buds opened, flowers visible'),
((SELECT id FROM crops WHERE crop_type = 'Beans'), 'planted', 'germination', 1, 'First sprouts visible above soil');

CREATE TABLE farm_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    location_name VARCHAR(255) NOT NULL DEFAULT 'Jijel, Algeria',
    lat DECIMAL(10, 6) NOT NULL DEFAULT 36.820600,
    lon DECIMAL(10, 6) NOT NULL DEFAULT 5.766700,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user (user_id)
);