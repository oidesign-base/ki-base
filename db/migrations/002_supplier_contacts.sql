-- =====================================================================
-- KI-BASE — migration 002: split supplier contact into separate fields
--
-- suppliers.contact (free text) is replaced by:
--   phone, email, street, postal_code, city, country_code
-- Existing contact values are moved to phone.
--
-- Apply in phpMyAdmin (database alexivan_ki_base → Import) BEFORE
-- deploying the code that uses the new fields.
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_uca1400_ai_ci;

ALTER TABLE suppliers
    ADD COLUMN phone        VARCHAR(30)  NULL AFTER tax_id,
    ADD COLUMN email        VARCHAR(150) NULL AFTER phone,
    ADD COLUMN street       VARCHAR(200) NULL COMMENT 'Street, building and flat number' AFTER email,
    ADD COLUMN postal_code  VARCHAR(12)  NULL AFTER street,
    ADD COLUMN city         VARCHAR(100) NULL AFTER postal_code,
    ADD COLUMN country_code CHAR(2)      CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'PL'
                            COMMENT 'ISO 3166-1 alpha-2' AFTER city;

UPDATE suppliers SET phone = LEFT(contact, 30) WHERE contact IS NOT NULL AND contact <> '';

ALTER TABLE suppliers DROP COLUMN contact;

INSERT INTO schema_migrations (version) VALUES ('002_supplier_contacts');
