-- ============================================
-- BACKFILL: FOTOT E PRODUKTEVE
-- ============================================
-- init.sql ekzekutohet nga Docker vetëm hera e parë që ngrihet kontejneri
-- i MySQL-it (mbi një volume bosh). Nëse databaza jote ekziston tashmë,
-- ky skedar plotëson kolonën `image` te produktet shembull që janë
-- krijuar më parë pa foto.
--
-- SI TË EKZEKUTOHET (Docker):
--   docker compose exec -T db mysql -u root -p<MYSQL_ROOT_PASSWORD> web_platform < docker/mysql/seed_product_images.sql
-- ose kopjo-ngjite te phpMyAdmin (http://localhost:8081) në tab "SQL".

UPDATE products SET image = 'smartphone-xyz-pro.jpg'         WHERE slug = 'smartphone-xyz-pro';
UPDATE products SET image = 'laptop-pro-15.jpg'              WHERE slug = 'laptop-pro-15';
UPDATE products SET image = 'kufje-wireless.jpg'             WHERE slug = 'kufje-wireless';
UPDATE products SET image = 'xhakete-dimri-premium.jpg'      WHERE slug = 'xhakete-dimri-premium';
UPDATE products SET image = 'bluza-sportive.jpg'             WHERE slug = 'bluza-sportive';
UPDATE products SET image = 'tavoline-kafeje-moderne.jpg'    WHERE slug = 'tavoline-kafeje-moderne';
UPDATE products SET image = 'llambe-led-smart.jpg'           WHERE slug = 'llambe-led-smart';
UPDATE products SET image = 'top-futbolli-pro.jpg'           WHERE slug = 'top-futbolli-pro';
UPDATE products SET image = 'pesha-fitness-set.jpg'          WHERE slug = 'pesha-fitness-set';
UPDATE products SET image = 'koleksion-libra-programimi.jpg' WHERE slug = 'koleksion-libra-programimi';

SELECT 'Fotot e produkteve u vendosën!' AS message;
