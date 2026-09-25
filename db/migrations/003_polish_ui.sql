-- =====================================================================
-- KI-BASE — migration 003: Polish interface, Etsy currency
--
-- The panel interface switches from Ukrainian to Polish:
--   * reference names shown in the UI (platform metrics, "other" carrier)
--     are renamed to Polish;
--   * the categories.name column comment now says the names are Polish.
-- Etsy listings are priced in EUR (texts stay English).
--
-- Names entered by users (categories, suppliers, users) are not touched.
--
-- Apply in phpMyAdmin (database alexivan_ki_base → Import) after 002,
-- before deploying the Polish interface.
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_uca1400_ai_ci;

ALTER TABLE categories
    MODIFY name VARCHAR(100) NOT NULL COMMENT 'UI language (Polish)';

UPDATE platforms SET default_currency = 'EUR' WHERE code = 'etsy';

UPDATE platform_metrics pm
  JOIN platforms p ON p.id = pm.platform_id
   SET pm.name = CASE pm.code
                     WHEN 'views'      THEN 'Wyświetlenia'
                     WHEN 'favorites'  THEN 'Ulubione'
                     WHEN 'favourites' THEN 'Ulubione'
                     WHEN 'observed'   THEN 'Obserwujący'
                     ELSE pm.name
                 END
 WHERE p.code IN ('etsy', 'vinted', 'olx');

UPDATE carriers SET name = 'Inny' WHERE code = 'other';

INSERT INTO schema_migrations (version) VALUES ('003_polish_ui');
