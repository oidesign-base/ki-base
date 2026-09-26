-- =====================================================================
-- KI-BASE — migration 005: code generations
--
-- One generation = the label sheets created together in one go
-- (48 ... 240 codes of one prefix). The panel lists generations and shows
-- all sheets of a generation on one page.
--   * code_generations: prefix, code range, number of codes, who and when.
--   * code_sheets.generation_id: the generation of each sheet.
-- Sheets that already exist are grouped automatically: consecutive sheets
-- of the same prefix, created by the same user within 5 seconds, form one
-- generation.
--
-- Apply in phpMyAdmin (database alexivan_ki_base → Import) after 004.
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_uca1400_ai_ci;

CREATE TABLE code_generations (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code_prefix_id  SMALLINT UNSIGNED NOT NULL,
    first_code      CHAR(9)      CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    last_code       CHAR(9)      CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    codes_count     SMALLINT UNSIGNED NOT NULL,
    created_by      INT UNSIGNED NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_code_generations_prefix (code_prefix_id),
    CONSTRAINT fk_code_generations_prefix FOREIGN KEY (code_prefix_id) REFERENCES code_prefixes (id),
    CONSTRAINT fk_code_generations_user FOREIGN KEY (created_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Label sheets generated together (one or more A4 sheets of one prefix)';

ALTER TABLE code_sheets
    ADD COLUMN generation_id INT UNSIGNED NULL AFTER id;

-- Group the existing sheets into generations.
INSERT INTO code_generations (code_prefix_id, first_code, last_code, codes_count, created_by, created_at)
SELECT code_prefix_id, MIN(first_code), MAX(last_code), SUM(codes_count), MIN(printed_by), MIN(printed_at)
  FROM (
        SELECT id, code_prefix_id, first_code, last_code, codes_count, printed_by, printed_at,
               SUM(starts) OVER (ORDER BY id) AS grp
          FROM (
                SELECT s.*,
                       CASE WHEN LAG(code_prefix_id) OVER w IS NULL
                              OR LAG(code_prefix_id) OVER w <> code_prefix_id
                              OR NOT (LAG(printed_by) OVER w <=> printed_by)
                              OR TIMESTAMPDIFF(SECOND, LAG(printed_at) OVER w, printed_at) > 5
                            THEN 1 ELSE 0 END AS starts
                  FROM code_sheets s
                WINDOW w AS (ORDER BY id)
               ) flagged
       ) grouped
 GROUP BY grp
 ORDER BY grp;

-- Codes of one prefix are fixed-width, so a sheet belongs to the generation
-- whose code range contains it.
UPDATE code_sheets s
  JOIN code_generations g
    ON g.code_prefix_id = s.code_prefix_id
   AND s.first_code BETWEEN g.first_code AND g.last_code
   SET s.generation_id = g.id;

ALTER TABLE code_sheets
    MODIFY generation_id INT UNSIGNED NOT NULL,
    ADD KEY ix_code_sheets_generation (generation_id),
    ADD CONSTRAINT fk_code_sheets_generation FOREIGN KEY (generation_id) REFERENCES code_generations (id);

INSERT INTO schema_migrations (version) VALUES ('005_code_generations');
