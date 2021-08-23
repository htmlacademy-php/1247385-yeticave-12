CREATE DATABASE yeticave
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

USE yeticave;

CREATE TABLE category (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title CHAR(200) NOT NULL UNIQUE,
  code CHAR(20) NOT NULL UNIQUE
);

CREATE INDEX idx_code ON category(code);

CREATE TABLE lot (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  title CHAR(200) NOT NULL,
  description TEXT(500),
  image CHAR(120) NOT NULL,
  start_price DECIMAL NOT NULL,
  date_exp TIMESTAMP NOT NULL,
  step_price DECIMAL,
  author_id INT NOT NULL,
  winner_id INT,
  category_id INT NOT NULL
);

CREATE INDEX idx_date_created ON lot(date_created);
CREATE INDEX idx_title ON lot(title);
CREATE INDEX idx_date_exp ON lot(date_exp);
CREATE INDEX idx_start_price ON lot(start_price);

CREATE TABLE bet (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date_released TIMESTAMP NOT NULL,
  price DECIMAL NOT NULL,
  user_id INT NOT NULL,
  lot_id INT NOT NULL
);

CREATE INDEX idx_date_released ON bet(date_released);

CREATE TABLE user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  email CHAR(120) NOT NULL UNIQUE,
  name CHAR(128) NOT NULL,
  password CHAR(128) NOT NULL,
  contact CHAR(255),
  lot_id INT,
  bet_id INT
);

CREATE INDEX idx_date_created ON user(date_created);
CREATE INDEX idx_email ON user(email);
CREATE INDEX idx_name ON user(name);
