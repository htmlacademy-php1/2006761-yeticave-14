CREATE DATABASE yeticave DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;

USE yeticave;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name CHAR(64) NOT NULL,
    symbol_code CHAR(128) NOT NULL
);

CREATE TABLE lots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    winner_id INT NOT NULL,
    category_id INT NOT NULL,
    name CHAR(64) NOT NULL,
    description TEXT NOT NULL,
    img_url CHAR(255) NOT NULL,
    start_price INT NOT NULL,
    step_price INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    finished_at TIMESTAMP
);

CREATE TABLE bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lot_id INT NOT NULL,
    price INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY ,
    lot_id INT NOT NULL,
    bids_id INT NOT NULL,
    name CHAR(64) NOT NULL,
    contacts TEXT NOT NULL,
    email CHAR(64) NOT NULL,
    password CHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX name ON categories(name);
CREATE INDEX finished_at ON lots(finished_at);
CREATE INDEX created_at ON lots(created_at);
CREATE INDEX user_id ON lots(user_id);
CREATE INDEX category_id ON lots(category_id);

CREATE INDEX user_id ON bids(user_id);
CREATE INDEX lot_id ON bids(lot_id);

CREATE UNIQUE INDEX email ON users(email);

CREATE FULLTEXT INDEX search_name_dscrpt ON lots(name, description);
