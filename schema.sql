CREATE DATABASE yeticave
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

USE yeticave;

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title CHAR(200) NOT NULL UNIQUE,
  code CHAR(20) NOT NULL UNIQUE
);

CREATE INDEX idx_code ON categories(code);

CREATE TABLE lots (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  title CHAR(200) NOT NULL,
  description TEXT(500),
  image CHAR(120) NOT NULL,
  start_price DECIMAL NOT NULL,
  date_exp TIMESTAMP NOT NULL,
  step_price INT,
  author_id INT NOT NULL,
  winner_id INT,
  category_id INT NOT NULL
);

CREATE INDEX idx_date_created ON lots(date_created);
CREATE INDEX idx_title ON lots(title);
CREATE INDEX idx_date_exp ON lots(date_exp);
CREATE INDEX idx_start_price ON lots(start_price);
CREATE FULLTEXT INDEX idx_ft_lot ON lots(title, description);

CREATE TABLE bets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  price INT NOT NULL,
  user_id INT NOT NULL,
  lot_id INT NOT NULL
);

CREATE INDEX idx_date_created ON bets(date_created);

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  email CHAR(120) NOT NULL UNIQUE,
  name CHAR(128) NOT NULL,
  password CHAR(128) NOT NULL,
  contact CHAR(255),
  lot_id INT,
  bet_id INT
);

CREATE INDEX idx_date_created ON users(date_created);
CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_name ON users(name);
