INSERT INTO category (name, symbol_code) VALUES
('Доски и лыжи','boards'),
('Крепления ','attachment'),
('Ботинки ','boots'),
('Одежда ','clothing'),
('Инструменты ','tools'),
('Разное' ,'other');
INSERT INTO user (name, password, contacts, email) VALUES
('Григорий', 'qwerty', '123-456', 'grisha@ya.ru'),
('Владимир', 'qwerty2', '789-102', 'vova@ya.ru');
INSERT INTO lot (user_id, winner_id, category_id, name, description, img_url, start_price, step_price, created_at, finished_at) VALUES
('1', '1', '1', '2014 Rossignol District Snowboard', 'Крутая доска', 'img/lot-1.jpg', '10999', '1000', CURRENT_TIMESTAMP, '2022-06-15'),
('2', '2', '1', 'DC Ply Mens 2016/2017 Snowboard', 'Ещё круче доска', 'img/lot-2.jpg', '159999', '1000', CURRENT_TIMESTAMP, '2022-06-16'),
('2', '2', '2', 'Крепления Union Contact Pro 2015 года размер L/XL', 'Крутое крепление', 'img/lot-3.jpg', '8000', '1000', CURRENT_TIMESTAMP, '2022-06-17'),
('1', '1', '3', 'Ботинки для сноуборда DC Mutiny Charocal', 'Крутые ботинки', 'img/lot-4.jpg', '10999', '1000', CURRENT_TIMESTAMP, '2022-06-18'),
('2', '1', '4', 'Куртка для сноуборда DC Mutiny Charocal', 'Крутая куртка', 'img/lot-5.jpg', '7500', '1000', CURRENT_TIMESTAMP, '2022-06-19'),
('1', '2', '6', 'Маска Oakley Canopy', 'Крутая маска', 'img/lot-6.jpg', '5400', '1000', CURRENT_TIMESTAMP, '2022-06-21');
INSERT INTO bid (user_id, lot_id, price) VALUES
('1', '1', '11999'),
('2', '2', '160999');

-- Получить все категории;
SELECT name FROM category;

-- Получить самые новые, открытые лоты.
-- Каждый лот должен включать название, стартовую цену, ссылку на изображение, цену, название категории;
SELECT l.name, l.start_price, l.img_url, MAX(b.price), c.name
FROM lot AS l
JOIN bid AS b ON l.id = b.lot_id
JOIN category as c ON c.id = l.category_id
WHERE l.finished_at > CURDATE()
GROUP BY b.lot_id
ORDER BY l.created_at DESC;

-- Показать лот по его ID. Получите также название категории, к которой принадлежит лот;
SELECT l.id, l.name, c.name
FROM lot AS l
JOIN category AS c ON l.category_id = c.id
WHERE l.id = 2;

-- Обновить название лота по его идентификатору;
UPDATE lot SET name = 'Новое название' WHERE id = 1;

-- Получить список ставок для лота по его идентификатору с сортировкой по дате.
SELECT * FROM bid AS b JOIN lot AS l ON b.lot_id = l.id WHERE l.id = 1 ORDER BY b.created_at DESC;
