-- MATKA KING DATABASE
CREATE DATABASE IF NOT EXISTS matka_king;
USE matka_king;

-- USERS TABLE
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  mobile VARCHAR(15) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  balance DECIMAL(10,2) DEFAULT 0.00,
  status ENUM('active','blocked') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ADMIN TABLE
CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL
);

-- MARKETS TABLE
CREATE TABLE markets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) UNIQUE NOT NULL,
  open_time VARCHAR(20),
  close_time VARCHAR(20),
  result VARCHAR(50) DEFAULT NULL,
  status ENUM('active','inactive') DEFAULT 'active'
);

-- BETS TABLE
CREATE TABLE bets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  market_id INT NOT NULL,
  bet_type ENUM('single','jodi','sp','dp','tp','half_sangam','full_sangam') NOT NULL,
  number VARCHAR(20) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  session ENUM('open','close') DEFAULT 'open',
  status ENUM('pending','won','lost') DEFAULT 'pending',
  win_amount DECIMAL(10,2) DEFAULT 0.00,
  bet_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (market_id) REFERENCES markets(id)
);

-- TRANSACTIONS TABLE
CREATE TABLE transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type ENUM('deposit','withdraw','bet','win') NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  note VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- WIN RATIO TABLE
CREATE TABLE win_ratios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bet_type ENUM('single','jodi','sp','dp','tp','half_sangam','full_sangam') NOT NULL,
  ratio INT NOT NULL
);

-- DEFAULT ADMIN
INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- default password: password

-- DEFAULT WIN RATIOS
-- single: 1 digit ank
-- jodi: 2 digit jodi
-- sp: single panna (3 digit, all unique e.g. 123)
-- dp: double panna (3 digit, 2 same e.g. 112)
-- tp: triple panna (3 digit, all same e.g. 111)
-- half_sangam: open panna+close ank or open ank+close panna
-- full_sangam: full result match
INSERT INTO win_ratios (bet_type, ratio) VALUES
('single', 9),
('jodi', 90),
('sp', 150),
('dp', 300),
('tp', 600),
('half_sangam', 1000),
('full_sangam', 10000);

-- DEFAULT MARKETS
INSERT INTO markets (name, slug, open_time, close_time) VALUES
('KALYAN MORNING','kalyan-morning','11:40 AM','12:40 PM'),
('MILAN MORNING','milan-morning','10:30 AM','11:30 AM'),
('SRIDEVI','sridevi','11:35 AM','12:35 PM'),
('MAIN BAZAR MORNING','main-bazar-morning','11:15 AM','12:15 PM'),
('MADHURI','madhuri','11:45 AM','12:45 PM'),
('TIME BAZAR','time-bazar','01:00 PM','02:00 PM'),
('MILAN DAY','milan-day','03:00 PM','05:00 PM'),
('KALYAN','kalyan','04:02 PM','06:02 PM'),
('SRIDEVI NIGHT','sridevi-night','07:15 PM','08:15 PM'),
('MADHURI NIGHT','madhuri-night','06:45 PM','07:45 PM'),
('MILAN NIGHT','milan-night','09:10 PM','11:10 PM'),
('RAJDHANI NIGHT','rajdhani-night','09:35 PM','11:45 PM'),
('MAIN BAZAR','main-bazar','10:00 PM','12:10 AM'),
('KALYAN NIGHT','kalyan-night','09:30 PM','11:30 PM'),
('RAJDHANI DAY','rajdhani-day','03:15 PM','05:15 PM'),
('MADHUR DAY','madhur-day','01:30 PM','02:30 PM'),
('MADHUR NIGHT','madhur-night','08:30 PM','10:30 PM');
