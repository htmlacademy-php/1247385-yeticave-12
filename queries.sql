/* заполняем таблицу Категории */
INSERT INTO categories (title, code)
VALUES ('Доски и лыжи', 'boards'),
       ('Крепления', 'attachment'),
       ('Ботинки', 'boots'),
       ('Одежда', 'clothing'),
       ('Инструменты', 'tools'),
       ('Разное', 'other');

/* заполняем таблицу Пользователи */
INSERT INTO users (email, name, password)
VALUES ('test1@test.com', 'John Snow', '12345'),
       ('test2@test.com', 'Arya Stark', '54321');

/* заполняем таблицу Лотов */
INSERT INTO lots (title, category_id, start_price, image, date_exp, author_id)
VALUES ('2014 Rossignol District Snowboard', 1, 10999, 'img/lot-1.jpg', TIMESTAMPADD(HOUR, 8, CURRENT_TIMESTAMP), 1),
       ('DC Ply Mens 2016/2017 Snowboard', 1, 159999, 'img/lot-2.jpg', TIMESTAMPADD(DAY, 1, CURRENT_TIMESTAMP), 2),
       ('Крепления Union Contact Pro 2015 года размер L/XL', 2, 8000, 'img/lot-3.jpg', TIMESTAMPADD(MINUTE, 45, CURRENT_TIMESTAMP), 1),
       ('Ботинки для сноуборда DC Mutiny Charocal', 3, 10999, 'img/lot-4.jpg', TIMESTAMPADD(WEEK, 1, CURRENT_TIMESTAMP), 2),
       ('Куртка для сноуборда DC Mutiny Charocal', 4, 7500, 'img/lot-5.jpg', TIMESTAMPADD(DAY, 2, CURRENT_TIMESTAMP), 1),
       ('Маска Oakley Canopy', 6, 5400, 'img/lot-6.jpg', TIMESTAMPADD(HOUR, 3, CURRENT_TIMESTAMP), 2);

/* заполняем таблицу Ставок */
INSERT INTO bets (date_released, price, user_id, lot_id)
VALUES (TIMESTAMPADD(MINUTE, 15, CURRENT_TIMESTAMP), 11000, 1, 1),
       (TIMESTAMPADD(MINUTE, 10, CURRENT_TIMESTAMP), 11500, 2, 1);
