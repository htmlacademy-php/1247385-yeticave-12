/* Заполняем таблицу Категории */
INSERT INTO categories (title, code)
VALUES ('Доски и лыжи', 'boards'),
       ('Крепления', 'attachment'),
       ('Ботинки', 'boots'),
       ('Одежда', 'clothing'),
       ('Инструменты', 'tools'),
       ('Разное', 'other');

/* Заполняем таблицу Пользователи */
INSERT INTO users (email, name, password)
VALUES ('test1@test.com', 'John Snow', '12345'),
       ('test2@test.com', 'Arya Stark', '54321');

/* Заполняем таблицу Лотов */
INSERT INTO lots (title, category_id, start_price, image, date_exp, author_id)
VALUES ('2014 Rossignol District Snowboard', 1, 10999, 'img/lot-1.jpg', TIMESTAMPADD(HOUR, 8, CURRENT_TIMESTAMP), 1),
       ('DC Ply Mens 2016/2017 Snowboard', 1, 159999, 'img/lot-2.jpg', TIMESTAMPADD(DAY, 1, CURRENT_TIMESTAMP), 2),
       ('Крепления Union Contact Pro 2015 года размер L/XL', 2, 8000, 'img/lot-3.jpg', TIMESTAMPADD(MINUTE, 45, CURRENT_TIMESTAMP), 1),
       ('Ботинки для сноуборда DC Mutiny Charocal', 3, 10999, 'img/lot-4.jpg', TIMESTAMPADD(WEEK, 1, CURRENT_TIMESTAMP), 2),
       ('Куртка для сноуборда DC Mutiny Charocal', 4, 7500, 'img/lot-5.jpg', TIMESTAMPADD(DAY, 2, CURRENT_TIMESTAMP), 1),
       ('Маска Oakley Canopy', 6, 5400, 'img/lot-6.jpg', TIMESTAMPADD(HOUR, 3, CURRENT_TIMESTAMP), 2);

/* Заполняем таблицу Ставок */
INSERT INTO bets (date_created, price, user_id, lot_id)
VALUES (TIMESTAMPADD(MINUTE, 15, CURRENT_TIMESTAMP), 11000, 1, 1),
       (TIMESTAMPADD(MINUTE, 10, CURRENT_TIMESTAMP), 99999, 2, 6);

/* Получаем список всех категорий */
SELECT * FROM categories

/* Получаем самые новые, открытые лоты. Каждый лот включает название, стартовую цену, ссылку на изображение, цену, название категории */
SELECT lots.title, start_price, image, price, date_created, categories.title as category
FROM lots
       JOIN bets ON lot_id = lots.id
       JOIN categories ON categories.id = category_id
WHERE date_exp > NOW()
ORDER BY date_created;

/* Получаем лот по его ID. Получаем также название категории, к которой принадлежит лот (для более удобного представления добавлено имя пользователя) */
SELECT lots.id, lots.title, start_price, image, date_exp, categories.title as category, users.name
FROM lots
       JOIN categories ON categories.id = category_id
       JOIN users ON users.id = author_id
WHERE lots.id = 3;

/* Обновляем название лота по его идентификатору */
UPDATE lots
SET title = 'New perfect lot with new great name'
WHERE id = 1;

/* Получаем список ставок для лота по его идентификатору с сортировкой по дате */
SELECT title, date_released, price, users.name
FROM bets
       JOIN lots ON lots.id = bets.lot_id
       JOIN users ON users.id = user_id
WHERE bets.lot_id = 1
ORDER BY date_released DESC
