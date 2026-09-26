-- =====================================================================
-- KI-BASE — migration 004: label code prefixes
--
-- A label code becomes: 2-letter prefix + 6-digit number + check digit,
-- e.g. KI9999011 (shown on the label as "KI 999 901 1").
--   * code_prefixes: catalogue of prefixes with a description; one of them
--     is the default; numbering continues separately for each prefix.
--   * code_sheets: every printed sheet belongs to one prefix.
--   * code_pool / items: codes are 9 characters.
--   * batches: the prefix of the labels used when unpacking the batch.
-- The check digit (Damm) covers the prefix too: letters are mapped to two
-- digits (A=10 ... Z=35) before the calculation; the panel computes it.
--
-- code_pool must be empty (no sheets have been generated yet); otherwise
-- the new format check stops the migration.
--
-- Apply in phpMyAdmin (database alexivan_ki_base → Import) after 003.
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_uca1400_ai_ci;

CREATE TABLE code_prefixes (
    id            SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    prefix        CHAR(2)      CHARACTER SET ascii COLLATE ascii_bin NOT NULL
                  COMMENT 'Two capital Latin letters; cannot change once codes exist',
    description   VARCHAR(200) NOT NULL,
    is_default    TINYINT(1)   NOT NULL DEFAULT 0,
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    default_flag  TINYINT(1)   AS (IF(is_default = 1, 1, NULL)) PERSISTENT
                  COMMENT 'Helper for the unique key: at most one default prefix',
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_code_prefixes_prefix (prefix),
    UNIQUE KEY uq_code_prefixes_default (default_flag),
    CONSTRAINT ck_code_prefixes_format CHECK (prefix REGEXP '^[A-Z]{2}$'),
    CONSTRAINT ck_code_prefixes_default_active CHECK (is_default = 0 OR is_active = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Prefixes of label codes (series); numbering is per prefix';

-- Codes grow from 7 to 9 characters. The foreign key items.code -> code_pool
-- is dropped for the change and restored afterwards.
ALTER TABLE items DROP FOREIGN KEY fk_items_code;

ALTER TABLE code_pool
    DROP CONSTRAINT ck_code_pool_format,
    MODIFY code CHAR(9) CHARACTER SET ascii COLLATE ascii_bin NOT NULL
         COMMENT 'Prefix (2 letters) + 6 digits + Damm check digit',
    ADD CONSTRAINT ck_code_pool_format CHECK (code REGEXP '^[A-Z]{2}[0-9]{7}$');

ALTER TABLE items
    MODIFY code CHAR(9) CHARACTER SET ascii COLLATE ascii_bin NULL
         COMMENT 'QR label code; NULL only for deleted items (code released)',
    ADD CONSTRAINT fk_items_code FOREIGN KEY (code) REFERENCES code_pool (code);

ALTER TABLE code_sheets
    ADD COLUMN code_prefix_id SMALLINT UNSIGNED NOT NULL AFTER id,
    MODIFY first_code CHAR(9) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    MODIFY last_code  CHAR(9) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    ADD KEY ix_code_sheets_prefix (code_prefix_id),
    ADD CONSTRAINT fk_code_sheets_prefix FOREIGN KEY (code_prefix_id) REFERENCES code_prefixes (id);

ALTER TABLE batches
    ADD COLUMN code_prefix_id SMALLINT UNSIGNED NULL
         COMMENT 'Prefix of the labels used for this batch; required in the form'
         AFTER supplier_id,
    ADD CONSTRAINT fk_batches_code_prefix FOREIGN KEY (code_prefix_id) REFERENCES code_prefixes (id);

INSERT INTO schema_migrations (version) VALUES ('004_code_prefixes');
