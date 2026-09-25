-- =====================================================================
-- KI-BASE — database schema (current snapshot)
-- Target: MariaDB 11.4, InnoDB, utf8mb4 / utf8mb4_uca1400_ai_ci
--
-- Conventions
--   * English snake_case names, plural table names.
--   * Money: DECIMAL(10,2) + separate currency column (ISO 4217).
--   * Service timestamps (created_at, updated_at, deleted_at) are UTC:
--     the application runs `SET time_zone = '+00:00'` on every connection.
--   * Accounting dates (batch_date, sale_date, ...) are DATE values in
--     Polish local time (Europe/Warsaw).
--   * No physical deletes for business records: deleted_at is set instead.
--   * Schema changes go through numbered files in db/migrations/.
--     This file always reflects the result of all migrations applied
--     (currently up to 002_supplier_contacts).
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_uca1400_ai_ci;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Service tables
-- ---------------------------------------------------------------------

CREATE TABLE schema_migrations (
    version       VARCHAR(50)  NOT NULL,
    applied_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Applied migrations';

CREATE TABLE users (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username        VARCHAR(50)  NOT NULL,
    display_name    VARCHAR(100) NOT NULL,
    password_hash   VARCHAR(255) NOT NULL COMMENT 'password_hash() / PASSWORD_DEFAULT',
    is_active       TINYINT(1)   NOT NULL DEFAULT 1,
    last_login_at   DATETIME     NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME     NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Panel users (no roles, all users have equal rights)';

CREATE TABLE login_attempts (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username      VARCHAR(50)  NULL,
    ip            VARCHAR(45)  NOT NULL,
    success       TINYINT(1)   NOT NULL,
    attempted_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_login_attempts_ip_time (ip, attempted_at),
    KEY ix_login_attempts_user_time (username, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Brute-force protection for the login form';

CREATE TABLE app_settings (
    setting_key    VARCHAR(64)  NOT NULL,
    setting_value  TEXT         NULL,
    updated_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by     INT UNSIGNED NULL,
    PRIMARY KEY (setting_key),
    CONSTRAINT fk_app_settings_user FOREIGN KEY (updated_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Simple key/value settings';

CREATE TABLE tax_settings (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    valid_from        DATE         NOT NULL COMMENT 'Setting applies to sales from this date on',
    taxation_form     ENUM('scale','linear','lump_sum') NOT NULL COMMENT 'skala / liniowy / ryczalt',
    income_tax_rate   DECIMAL(5,2) NOT NULL COMMENT 'Percent, e.g. 3.00',
    vat_payer         TINYINT(1)   NOT NULL DEFAULT 0,
    note              VARCHAR(255) NULL,
    created_by        INT UNSIGNED NULL,
    created_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tax_settings_valid_from (valid_from),
    CONSTRAINT fk_tax_settings_user FOREIGN KEY (created_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Tax parameters with history; used by reports to estimate tax';

CREATE TABLE integration_tokens (
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    provider            VARCHAR(30)  NOT NULL COMMENT 'etsy, onedrive, inpost, anthropic, ...',
    account_label       VARCHAR(100) NULL,
    access_token_enc    TEXT         NULL COMMENT 'Encrypted with the key from local config',
    refresh_token_enc   TEXT         NULL COMMENT 'Encrypted with the key from local config',
    expires_at          DATETIME     NULL,
    scopes              VARCHAR(500) NULL,
    meta                JSON         NULL,
    created_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_integration_tokens_provider (provider)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='OAuth / API credentials of external services (encrypted)';

-- ---------------------------------------------------------------------
-- Dictionaries
-- ---------------------------------------------------------------------

CREATE TABLE suppliers (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name         VARCHAR(150) NOT NULL,
    tax_id       VARCHAR(20)  NULL COMMENT 'NIP',
    phone        VARCHAR(30)  NULL,
    email        VARCHAR(150) NULL,
    street       VARCHAR(200) NULL COMMENT 'Street, building and flat number',
    postal_code  VARCHAR(12)  NULL,
    city         VARCHAR(100) NULL,
    country_code CHAR(2)      CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'PL' COMMENT 'ISO 3166-1 alpha-2',
    is_active    TINYINT(1)   NOT NULL DEFAULT 1,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at   DATETIME     NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Suppliers of parcel batches';

CREATE TABLE categories (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    parent_id    INT UNSIGNED NULL COMMENT 'Optional nesting; flat list is fine',
    name         VARCHAR(100) NOT NULL COMMENT 'UI language (Ukrainian)',
    sort_order   SMALLINT     NOT NULL DEFAULT 0,
    is_active    TINYINT(1)   NOT NULL DEFAULT 1,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_parent_name (parent_id, name),
    CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Product categories, editable in the panel';

CREATE TABLE platforms (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code               VARCHAR(30)  CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    name               VARCHAR(100) NOT NULL,
    default_language   VARCHAR(5)   CHARACTER SET ascii COLLATE ascii_bin NULL COMMENT 'Language of listing texts: en, pl, ...',
    default_currency   CHAR(3)      CHARACTER SET ascii COLLATE ascii_bin NULL,
    has_api            TINYINT(1)   NOT NULL DEFAULT 0 COMMENT 'Listings can be published/synced automatically',
    commission_payer   ENUM('seller','buyer','none') NULL COMMENT 'Who pays the platform commission; NULL = not set yet',
    is_active          TINYINT(1)   NOT NULL DEFAULT 1,
    sort_order         SMALLINT     NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_platforms_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Sales platforms';

CREATE TABLE platform_metrics (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    platform_id   INT UNSIGNED NOT NULL,
    code          VARCHAR(30)  CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    name          VARCHAR(100) NOT NULL COMMENT 'Name as shown in the UI',
    metric_type   ENUM('views','interest','cart','other') NOT NULL
                  COMMENT 'Common meaning, lets different platforms be compared',
    sort_order    SMALLINT     NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_platform_metrics_code (platform_id, code),
    CONSTRAINT fk_platform_metrics_platform FOREIGN KEY (platform_id) REFERENCES platforms (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Which statistics each platform provides';

CREATE TABLE carriers (
    id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code                    VARCHAR(30)  CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    name                    VARCHAR(100) NOT NULL,
    tracking_url_template   VARCHAR(500) NULL COMMENT 'URL with {code} placeholder',
    is_active               TINYINT(1)   NOT NULL DEFAULT 1,
    sort_order              SMALLINT     NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_carriers_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Shipping carriers';

CREATE TABLE nbp_rates (
    currency     CHAR(3)       CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    rate_date    DATE          NOT NULL COMMENT 'Date the NBP table was published',
    rate         DECIMAL(10,4) NOT NULL COMMENT 'PLN per 1 unit of currency (table A mid rate)',
    table_no     VARCHAR(20)   NULL,
    fetched_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (currency, rate_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Cache of NBP average exchange rates (api.nbp.pl)';

-- ---------------------------------------------------------------------
-- Purchasing: batches and parcels
-- ---------------------------------------------------------------------

CREATE TABLE batches (
    id                          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    supplier_id                 INT UNSIGNED  NULL,
    batch_date                  DATE          NOT NULL COMMENT 'Invoice date; defaults to today in the form',
    invoice_number              VARCHAR(50)   NULL,
    currency                    CHAR(3)       CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'PLN',
    large_parcel_price          DECIMAL(10,2) NULL COMMENT 'Unit price of a large parcel in this batch',
    small_parcel_price          DECIMAL(10,2) NULL COMMENT 'Unit price of a small parcel in this batch',
    large_parcels_count         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    small_parcels_count         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    total_paid                  DECIMAL(10,2) NOT NULL COMMENT 'Everything paid for the batch (invoice total)',
    written_off_amount          DECIMAL(10,2) NOT NULL DEFAULT 0.00
                                COMMENT 'Cost of parcels NOT entered into the base (kept or unsellable)',
    written_off_parcels_count   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    status                      ENUM('open','closed') NOT NULL DEFAULT 'open',
    closed_at                   DATETIME      NULL,
    created_by                  INT UNSIGNED  NULL,
    created_at                  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at                  DATETIME      NULL,
    PRIMARY KEY (id),
    KEY ix_batches_date (batch_date),
    KEY ix_batches_status (status),
    CONSTRAINT fk_batches_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers (id),
    CONSTRAINT fk_batches_created_by FOREIGN KEY (created_by) REFERENCES users (id),
    CONSTRAINT ck_batches_amounts CHECK (total_paid >= 0 AND written_off_amount >= 0 AND written_off_amount <= total_paid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Purchased batches of undelivered parcels';

CREATE TABLE parcels (
    id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    batch_id      INT UNSIGNED  NOT NULL,
    parcel_no     SMALLINT UNSIGNED NOT NULL COMMENT 'Number of the parcel inside the batch',
    size          ENUM('large','small') NOT NULL,
    price         DECIMAL(10,2) NOT NULL COMMENT 'Copied from the batch price for this size, editable',
    unpacked_at   DATETIME      NULL,
    created_by    INT UNSIGNED  NULL,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at    DATETIME      NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_parcels_batch_no (batch_id, parcel_no),
    CONSTRAINT fk_parcels_batch FOREIGN KEY (batch_id) REFERENCES batches (id),
    CONSTRAINT fk_parcels_created_by FOREIGN KEY (created_by) REFERENCES users (id),
    CONSTRAINT ck_parcels_price CHECK (price >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Parcels entered into the base (written-off ones are only a sum on the batch)';

-- ---------------------------------------------------------------------
-- Catalogue: products (what it is) and items (physical units)
-- ---------------------------------------------------------------------

CREATE TABLE products (
    id                           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    category_id                  INT UNSIGNED  NULL COMMENT 'Required once identified; may be empty right after unpacking',
    name                         VARCHAR(255)  NULL COMMENT 'Includes size/capacity/length when relevant',
    brand                        VARCHAR(100)  NULL,
    model                        VARCHAR(150)  NULL,
    -- identification
    identification_status        ENUM('pending','draft','approved') NOT NULL DEFAULT 'pending'
                                 COMMENT 'pending = not processed, draft = filled by AI, approved = checked by a human',
    identification_confidence    TINYINT UNSIGNED NULL COMMENT '0-100, as reported by AI',
    identified_at                DATETIME      NULL,
    approved_by                  INT UNSIGNED  NULL,
    approved_at                  DATETIME      NULL,
    -- market price estimate
    market_price_min             DECIMAL(10,2) NULL,
    market_price_max             DECIMAL(10,2) NULL,
    market_price_currency        CHAR(3)       CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'PLN',
    market_price_condition       ENUM('new','used') NULL COMMENT 'Condition the estimate refers to',
    market_price_checked_at      DATE          NULL,
    market_price_source          VARCHAR(500)  NULL COMMENT 'Where the estimate came from (short note / URLs)',
    planned_price                DECIMAL(10,2) NULL COMMENT 'Our intended asking price',
    -- relations to other products
    based_on_product_id          INT UNSIGNED  NULL COMMENT 'Description was copied from this similar product',
    merged_into_product_id       INT UNSIGNED  NULL COMMENT 'Draft merged into an existing identical product',
    created_by                   INT UNSIGNED  NULL,
    created_at                   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                   DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at                   DATETIME      NULL,
    PRIMARY KEY (id),
    KEY ix_products_category (category_id),
    KEY ix_products_ident_status (identification_status),
    KEY ix_products_brand_model (brand, model),
    FULLTEXT KEY ft_products_search (name, brand, model),
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories (id),
    CONSTRAINT fk_products_approved_by FOREIGN KEY (approved_by) REFERENCES users (id),
    CONSTRAINT fk_products_based_on FOREIGN KEY (based_on_product_id) REFERENCES products (id),
    CONSTRAINT fk_products_merged_into FOREIGN KEY (merged_into_product_id) REFERENCES products (id),
    CONSTRAINT fk_products_created_by FOREIGN KEY (created_by) REFERENCES users (id),
    CONSTRAINT ck_products_confidence CHECK (identification_confidence IS NULL OR identification_confidence <= 100),
    CONSTRAINT ck_products_price_range CHECK (market_price_min IS NULL OR market_price_max IS NULL OR market_price_min <= market_price_max)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Catalogue: one record per kind of goods; shared by identical units';

CREATE TABLE product_texts (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id     INT UNSIGNED NOT NULL,
    platform_id    INT UNSIGNED NULL COMMENT 'NULL = generic text in this language',
    language       VARCHAR(5)   CHARACTER SET ascii COLLATE ascii_bin NOT NULL COMMENT 'en, pl, uk, ...',
    title          VARCHAR(255) NULL,
    body           TEXT         NULL,
    tags           VARCHAR(1000) NULL COMMENT 'Comma separated',
    source         ENUM('ai','manual') NOT NULL DEFAULT 'manual',
    approved_by    INT UNSIGNED NULL,
    approved_at    DATETIME     NULL,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at     DATETIME     NULL,
    PRIMARY KEY (id),
    KEY ix_product_texts_product (product_id, platform_id, language),
    CONSTRAINT fk_product_texts_product FOREIGN KEY (product_id) REFERENCES products (id),
    CONSTRAINT fk_product_texts_platform FOREIGN KEY (platform_id) REFERENCES platforms (id),
    CONSTRAINT fk_product_texts_approved_by FOREIGN KEY (approved_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Sales descriptions per platform / language (written once per product)';

CREATE TABLE code_sheets (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    first_code    CHAR(7)      CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    last_code     CHAR(7)      CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    codes_count   SMALLINT UNSIGNED NOT NULL,
    layout        VARCHAR(50)  NULL COMMENT 'Label sheet layout used for the PDF',
    printed_by    INT UNSIGNED NULL,
    printed_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_code_sheets_user FOREIGN KEY (printed_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Printed A4 sheets of QR labels';

CREATE TABLE code_pool (
    code          CHAR(7)      CHARACTER SET ascii COLLATE ascii_bin NOT NULL
                  COMMENT '6 digits + 1 check digit (EAN-style)',
    sheet_id      INT UNSIGNED NOT NULL,
    status        ENUM('free','assigned','spoiled') NOT NULL DEFAULT 'free',
    status_changed_at DATETIME NULL,
    PRIMARY KEY (code),
    KEY ix_code_pool_sheet (sheet_id),
    KEY ix_code_pool_status (status),
    CONSTRAINT fk_code_pool_sheet FOREIGN KEY (sheet_id) REFERENCES code_sheets (id),
    CONSTRAINT ck_code_pool_format CHECK (code REGEXP '^[0-9]{7}$')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Pre-printed label codes; an item takes a free code at unpacking';

CREATE TABLE items (
    id                 INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    product_id         INT UNSIGNED  NOT NULL,
    code               CHAR(7)       CHARACTER SET ascii COLLATE ascii_bin NULL
                       COMMENT 'QR label code; NULL only for deleted items (code released)',
    source             ENUM('parcel','own','purchase') NOT NULL DEFAULT 'parcel',
    parcel_id          INT UNSIGNED  NULL COMMENT 'Required when source = parcel',
    `condition`        ENUM('new_sealed','new_unsealed','used') NULL,
    status             ENUM('received','identified','priced','photographed','listed',
                            'sold','kept','written_off') NOT NULL DEFAULT 'received',
    status_changed_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    cost_override      DECIMAL(10,2) NULL COMMENT 'Manual cost for own/purchase items; parcel items get cost from the parcel',
    created_by         INT UNSIGNED  NULL,
    created_at         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at         DATETIME      NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_items_code (code),
    KEY ix_items_product (product_id),
    KEY ix_items_parcel (parcel_id),
    KEY ix_items_status (status, status_changed_at),
    KEY ix_items_source (source),
    CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products (id),
    CONSTRAINT fk_items_code FOREIGN KEY (code) REFERENCES code_pool (code),
    CONSTRAINT fk_items_parcel FOREIGN KEY (parcel_id) REFERENCES parcels (id),
    CONSTRAINT fk_items_created_by FOREIGN KEY (created_by) REFERENCES users (id),
    CONSTRAINT ck_items_source_parcel CHECK (
        (source = 'parcel' AND parcel_id IS NOT NULL) OR (source <> 'parcel' AND parcel_id IS NULL)
    ),
    CONSTRAINT ck_items_code_present CHECK (code IS NOT NULL OR deleted_at IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Physical units: one row = one labelled thing';

CREATE TABLE photos (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id       INT UNSIGNED NOT NULL,
    item_id          INT UNSIGNED NULL COMMENT 'Set when the photo shows a specific unit (e.g. damage)',
    kind             ENUM('identification','sale','other') NOT NULL,
    sort_order       SMALLINT     NOT NULL DEFAULT 0,
    storage          ENUM('local','cloud') NOT NULL DEFAULT 'local',
    local_path       VARCHAR(255) NULL COMMENT 'Relative to the storage root, outside the web root',
    thumb_path       VARCHAR(255) NULL COMMENT 'Small thumbnail, always kept on the hosting',
    cloud_provider   VARCHAR(20)  NULL COMMENT 'onedrive, ...',
    cloud_file_id    VARCHAR(255) NULL,
    mime             VARCHAR(50)  NULL,
    width            SMALLINT UNSIGNED NULL,
    height           SMALLINT UNSIGNED NULL,
    bytes            INT UNSIGNED NULL,
    archived_at      DATETIME     NULL COMMENT 'Moved to the cloud',
    created_by       INT UNSIGNED NULL,
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at       DATETIME     NULL,
    PRIMARY KEY (id),
    KEY ix_photos_product (product_id, kind, sort_order),
    KEY ix_photos_item (item_id),
    KEY ix_photos_storage (storage, archived_at),
    CONSTRAINT fk_photos_product FOREIGN KEY (product_id) REFERENCES products (id),
    CONSTRAINT fk_photos_item FOREIGN KEY (item_id) REFERENCES items (id),
    CONSTRAINT fk_photos_created_by FOREIGN KEY (created_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Identification and sales photos';

CREATE TABLE product_match_suggestions (
    id                     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id             INT UNSIGNED NOT NULL COMMENT 'Newly identified product',
    candidate_product_id   INT UNSIGNED NOT NULL COMMENT 'Existing product it may match',
    verdict                ENUM('same','similar','different') NOT NULL COMMENT 'AI verdict',
    confidence             TINYINT UNSIGNED NULL,
    decision               ENUM('pending','merged','used_as_base','rejected') NOT NULL DEFAULT 'pending',
    decided_by             INT UNSIGNED NULL,
    decided_at             DATETIME     NULL,
    created_at             DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_match_pair (product_id, candidate_product_id),
    KEY ix_match_decision (decision),
    CONSTRAINT fk_match_product FOREIGN KEY (product_id) REFERENCES products (id),
    CONSTRAINT fk_match_candidate FOREIGN KEY (candidate_product_id) REFERENCES products (id),
    CONSTRAINT fk_match_decided_by FOREIGN KEY (decided_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='"Looks like this product we already described" suggestions';

CREATE TABLE ai_jobs (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    job_type        ENUM('identify','match','describe') NOT NULL,
    product_id      INT UNSIGNED NULL,
    status          ENUM('queued','running','done','failed') NOT NULL DEFAULT 'queued',
    attempts        TINYINT UNSIGNED NOT NULL DEFAULT 0,
    model           VARCHAR(60)  NULL,
    input_tokens    INT UNSIGNED NULL,
    output_tokens   INT UNSIGNED NULL,
    cost_usd        DECIMAL(10,4) NULL,
    response        MEDIUMTEXT   NULL COMMENT 'Raw model response for troubleshooting',
    error           TEXT         NULL,
    created_by      INT UNSIGNED NULL,
    queued_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    started_at      DATETIME     NULL,
    finished_at     DATETIME     NULL,
    PRIMARY KEY (id),
    KEY ix_ai_jobs_status (status, queued_at),
    KEY ix_ai_jobs_product (product_id),
    CONSTRAINT fk_ai_jobs_product FOREIGN KEY (product_id) REFERENCES products (id),
    CONSTRAINT fk_ai_jobs_created_by FOREIGN KEY (created_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Background Claude API jobs with token usage';

-- ---------------------------------------------------------------------
-- Selling: listings and their statistics
-- ---------------------------------------------------------------------

CREATE TABLE listings (
    id                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    product_id        INT UNSIGNED  NOT NULL,
    platform_id       INT UNSIGNED  NOT NULL,
    status            ENUM('draft','active','inactive','sold_out','closed') NOT NULL DEFAULT 'draft',
    language          VARCHAR(5)    CHARACTER SET ascii COLLATE ascii_bin NULL,
    title             VARCHAR(255)  NULL COMMENT 'Text as published',
    body              TEXT          NULL,
    tags              VARCHAR(1000) NULL,
    currency          CHAR(3)       CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'PLN',
    initial_price     DECIMAL(10,2) NULL COMMENT 'Price when first published',
    current_price     DECIMAL(10,2) NULL,
    quantity          SMALLINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Units offered (Etsy supports > 1)',
    external_id       VARCHAR(100)  NULL COMMENT 'ID on the platform',
    external_url      VARCHAR(500)  NULL,
    published_at      DATETIME      NULL,
    closed_at         DATETIME      NULL,
    remote_updated_at DATETIME      NULL COMMENT 'Last change on the platform side (API platforms)',
    last_synced_at    DATETIME      NULL,
    created_by        INT UNSIGNED  NULL,
    created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at        DATETIME      NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_listings_external (platform_id, external_id),
    KEY ix_listings_product (product_id),
    KEY ix_listings_status (platform_id, status),
    CONSTRAINT fk_listings_product FOREIGN KEY (product_id) REFERENCES products (id),
    CONSTRAINT fk_listings_platform FOREIGN KEY (platform_id) REFERENCES platforms (id),
    CONSTRAINT fk_listings_created_by FOREIGN KEY (created_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Offers on platforms';

CREATE TABLE listing_items (
    listing_id   INT UNSIGNED NOT NULL,
    item_id      INT UNSIGNED NOT NULL,
    added_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (listing_id, item_id),
    KEY ix_listing_items_item (item_id),
    CONSTRAINT fk_listing_items_listing FOREIGN KEY (listing_id) REFERENCES listings (id),
    CONSTRAINT fk_listing_items_item FOREIGN KEY (item_id) REFERENCES items (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Which physical units a listing offers (many-to-many)';

CREATE TABLE listing_metrics (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    listing_id    INT UNSIGNED NOT NULL,
    metric_id     INT UNSIGNED NOT NULL,
    value         INT UNSIGNED NOT NULL,
    measured_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_listing_metrics_series (listing_id, metric_id, measured_at),
    CONSTRAINT fk_listing_metrics_listing FOREIGN KEY (listing_id) REFERENCES listings (id),
    CONSTRAINT fk_listing_metrics_metric FOREIGN KEY (metric_id) REFERENCES platform_metrics (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Statistic snapshots over time';

CREATE TABLE sync_queue (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    platform_id     INT UNSIGNED NOT NULL,
    listing_id      INT UNSIGNED NULL,
    action          ENUM('create','update','update_price','update_quantity','deactivate','delete') NOT NULL,
    payload         JSON         NULL,
    status          ENUM('pending','processing','done','failed','conflict') NOT NULL DEFAULT 'pending',
    attempts        TINYINT UNSIGNED NOT NULL DEFAULT 0,
    last_error      TEXT         NULL,
    available_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Retry not before this time',
    created_by      INT UNSIGNED NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at    DATETIME     NULL,
    PRIMARY KEY (id),
    KEY ix_sync_queue_pick (status, available_at),
    KEY ix_sync_queue_listing (listing_id),
    CONSTRAINT fk_sync_queue_platform FOREIGN KEY (platform_id) REFERENCES platforms (id),
    CONSTRAINT fk_sync_queue_listing FOREIGN KEY (listing_id) REFERENCES listings (id),
    CONSTRAINT fk_sync_queue_created_by FOREIGN KEY (created_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Outgoing changes for platforms with API (processed by cron)';

-- ---------------------------------------------------------------------
-- Sales and shipping
-- ---------------------------------------------------------------------

CREATE TABLE sales (
    id                  INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    item_id             INT UNSIGNED  NOT NULL,
    listing_id          INT UNSIGNED  NULL COMMENT 'NULL for a sale without a listing in the panel',
    platform_id         INT UNSIGNED  NOT NULL,
    sale_date           DATE          NOT NULL,
    currency            CHAR(3)       CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'PLN',
    sold_price          DECIMAL(10,2) NOT NULL COMMENT 'Actual gross price paid for the item (after bargaining)',
    listed_price        DECIMAL(10,2) NULL COMMENT 'Listing price at the moment of sale',
    platform_fee        DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Commission charged to us; 0 is normal (e.g. Vinted)',
    buyer_country       CHAR(2)       CHARACTER SET ascii COLLATE ascii_bin NULL COMMENT 'ISO 3166-1 alpha-2',
    external_order_id   VARCHAR(100)  NULL,
    payout_date         DATE          NULL,
    payout_amount       DECIMAL(10,2) NULL,
    nbp_rate            DECIMAL(10,4) NULL COMMENT 'NBP rate of the last business day before sale_date; NULL for PLN',
    nbp_rate_date       DATE          NULL,
    status              ENUM('completed','cancelled','returned') NOT NULL DEFAULT 'completed',
    returned_at         DATE          NULL,
    active_item_id      INT UNSIGNED  AS (IF(status = 'completed', item_id, NULL)) PERSISTENT
                        COMMENT 'Technical: guarantees one completed sale per item',
    created_by          INT UNSIGNED  NULL,
    created_at          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at          DATETIME      NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sales_active_item (active_item_id),
    KEY ix_sales_item (item_id),
    KEY ix_sales_date (sale_date),
    KEY ix_sales_platform_date (platform_id, sale_date),
    KEY ix_sales_listing (listing_id),
    CONSTRAINT fk_sales_item FOREIGN KEY (item_id) REFERENCES items (id),
    CONSTRAINT fk_sales_listing FOREIGN KEY (listing_id) REFERENCES listings (id),
    CONSTRAINT fk_sales_platform FOREIGN KEY (platform_id) REFERENCES platforms (id),
    CONSTRAINT fk_sales_created_by FOREIGN KEY (created_by) REFERENCES users (id),
    CONSTRAINT ck_sales_amounts CHECK (sold_price >= 0 AND platform_fee >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='One row = one physical item sold';

CREATE TABLE shipments (
    id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    carrier_id      INT UNSIGNED  NULL,
    service         ENUM('locker','courier','pickup_point','other') NULL,
    chosen_by       ENUM('platform','buyer','us') NULL,
    direction       ENUM('outbound','return') NOT NULL DEFAULT 'outbound',
    cost_pln        DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Shipping cost in PLN',
    paid_by         ENUM('buyer','us','platform') NOT NULL DEFAULT 'buyer',
    status          ENUM('created','sent','in_transit','delivered','returned','lost') NOT NULL DEFAULT 'created',
    sent_at         DATE          NULL,
    delivered_at    DATE          NULL,
    created_by      INT UNSIGNED  NULL,
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME      NULL,
    PRIMARY KEY (id),
    KEY ix_shipments_status (status),
    CONSTRAINT fk_shipments_carrier FOREIGN KEY (carrier_id) REFERENCES carriers (id),
    CONSTRAINT fk_shipments_created_by FOREIGN KEY (created_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Shipments (one shipment may carry several sales; returns are separate shipments)';

CREATE TABLE shipment_sales (
    shipment_id   INT UNSIGNED NOT NULL,
    sale_id       INT UNSIGNED NOT NULL,
    PRIMARY KEY (shipment_id, sale_id),
    KEY ix_shipment_sales_sale (sale_id),
    CONSTRAINT fk_shipment_sales_shipment FOREIGN KEY (shipment_id) REFERENCES shipments (id),
    CONSTRAINT fk_shipment_sales_sale FOREIGN KEY (sale_id) REFERENCES sales (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Shipment <-> sales';

CREATE TABLE shipment_codes (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    shipment_id   INT UNSIGNED NOT NULL,
    code_type     ENUM('tracking','shipment_code','qr','label_number','other') NOT NULL,
    value         VARCHAR(255) NOT NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_shipment_codes_shipment (shipment_id),
    KEY ix_shipment_codes_value (value),
    CONSTRAINT fk_shipment_codes_shipment FOREIGN KEY (shipment_id) REFERENCES shipments (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Tracking numbers and other codes of a shipment';

-- ---------------------------------------------------------------------
-- Cross-cutting: attachments, comments, activity log
-- ---------------------------------------------------------------------

CREATE TABLE attachments (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    entity_type     ENUM('batch','parcel','product','item','listing','sale','shipment','supplier') NOT NULL,
    entity_id       INT UNSIGNED NOT NULL,
    kind            ENUM('invoice','label','receipt','screenshot','other') NOT NULL DEFAULT 'other',
    file_path       VARCHAR(255) NOT NULL COMMENT 'Relative to the storage root, outside the web root',
    original_name   VARCHAR(255) NULL,
    mime            VARCHAR(100) NULL,
    bytes           INT UNSIGNED NULL,
    created_by      INT UNSIGNED NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at      DATETIME     NULL,
    PRIMARY KEY (id),
    KEY ix_attachments_entity (entity_type, entity_id),
    CONSTRAINT fk_attachments_created_by FOREIGN KEY (created_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Files: invoices, labels, receipts, screenshots';

CREATE TABLE comments (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    entity_type   ENUM('batch','parcel','product','item','listing','sale','shipment') NOT NULL,
    entity_id     INT UNSIGNED NOT NULL,
    user_id       INT UNSIGNED NULL,
    body          TEXT         NOT NULL,
    is_pinned     TINYINT(1)   NOT NULL DEFAULT 0 COMMENT 'Notes entered on creation are saved as a pinned first comment',
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at    DATETIME     NULL,
    PRIMARY KEY (id),
    KEY ix_comments_entity (entity_type, entity_id, is_pinned, created_at),
    CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Any number of comments on any record';

CREATE TABLE activity_log (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED NULL COMMENT 'NULL = system / cron',
    action        VARCHAR(30)  CHARACTER SET ascii COLLATE ascii_bin NOT NULL COMMENT 'create, update, delete, status, login, sync, ...',
    entity_type   VARCHAR(30)  CHARACTER SET ascii COLLATE ascii_bin NULL,
    entity_id     INT UNSIGNED NULL,
    changes       JSON         NULL COMMENT '{"field": [old, new], ...}',
    ip            VARCHAR(45)  NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_activity_entity (entity_type, entity_id, created_at),
    KEY ix_activity_user (user_id, created_at),
    KEY ix_activity_time (created_at),
    CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
  COMMENT='Who changed what and when';

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Views: cost and margin
-- ---------------------------------------------------------------------

-- Cost of every physical unit.
-- Parcel items: parcel price divided equally among all units entered from
-- that parcel (including ones later kept or written off). Units that were
-- never entered simply do not count, so their share moves to the rest.
-- Own / purchase items: manual cost_override (may be NULL).
CREATE OR REPLACE VIEW v_item_costs AS
SELECT
    i.id                         AS item_id,
    i.source,
    i.parcel_id,
    p.batch_id,
    CASE
        WHEN i.source = 'parcel' THEN CAST(p.price / pc.units AS DECIMAL(12,4))
        ELSE CAST(i.cost_override AS DECIMAL(12,4))
    END                          AS unit_cost
FROM items i
LEFT JOIN parcels p ON p.id = i.parcel_id
LEFT JOIN (
    SELECT parcel_id, COUNT(*) AS units
    FROM items
    WHERE deleted_at IS NULL AND parcel_id IS NOT NULL
    GROUP BY parcel_id
) pc ON pc.parcel_id = i.parcel_id
WHERE i.deleted_at IS NULL;

-- Shipping cost we paid, split equally among the sales of a shipment.
CREATE OR REPLACE VIEW v_sale_shipping AS
SELECT
    ss.sale_id,
    CAST(SUM(sh.cost_pln / sc.sales_in_shipment) AS DECIMAL(12,4)) AS shipping_pln
FROM shipment_sales ss
JOIN shipments sh ON sh.id = ss.shipment_id AND sh.deleted_at IS NULL AND sh.paid_by = 'us'
JOIN (
    SELECT shipment_id, COUNT(*) AS sales_in_shipment
    FROM shipment_sales
    GROUP BY shipment_id
) sc ON sc.shipment_id = ss.shipment_id
GROUP BY ss.sale_id;

-- Per-sale figures converted to PLN.
-- Amounts are NULL when a foreign-currency sale has no NBP rate yet;
-- rate_missing = 1 marks such rows.
CREATE OR REPLACE VIEW v_sale_financials AS
SELECT
    s.id                                   AS sale_id,
    s.item_id,
    s.platform_id,
    s.sale_date,
    s.currency,
    s.sold_price,
    s.listed_price,
    s.listed_price - s.sold_price          AS discount,
    s.platform_fee,
    CASE WHEN s.currency = 'PLN' THEN 1 ELSE s.nbp_rate END                 AS rate,
    (s.currency <> 'PLN' AND s.nbp_rate IS NULL)                            AS rate_missing,
    CAST(s.sold_price   * CASE WHEN s.currency = 'PLN' THEN 1 ELSE s.nbp_rate END AS DECIMAL(12,4)) AS revenue_pln,
    CAST(s.platform_fee * CASE WHEN s.currency = 'PLN' THEN 1 ELSE s.nbp_rate END AS DECIMAL(12,4)) AS fee_pln,
    COALESCE(sh.shipping_pln, 0)           AS shipping_pln,
    ic.unit_cost,
    i.source
FROM sales s
JOIN items i         ON i.id = s.item_id
LEFT JOIN v_item_costs ic   ON ic.item_id = s.item_id
LEFT JOIN v_sale_shipping sh ON sh.sale_id = s.id
WHERE s.deleted_at IS NULL AND s.status = 'completed';

-- Batch summary: margin is counted from the WHOLE amount paid for the batch
-- (including written-off parcels), not from the sum of unit costs.
CREATE OR REPLACE VIEW v_batch_summary AS
SELECT
    b.id                                             AS batch_id,
    b.batch_date,
    b.status,
    b.total_paid,
    b.written_off_amount,
    COALESCE(pp.entered_parcels_cost, 0)             AS entered_parcels_cost,
    b.total_paid - b.written_off_amount
        - COALESCE(pp.entered_parcels_cost, 0)       AS unallocated_amount,
    COALESCE(pp.parcels_entered, 0)                  AS parcels_entered,
    COALESCE(it.items_total, 0)                      AS items_total,
    COALESCE(it.items_sold, 0)                       AS items_sold,
    COALESCE(it.items_kept, 0)                       AS items_kept,
    COALESCE(it.items_written_off, 0)                AS items_written_off,
    COALESCE(it.items_total, 0) - COALESCE(it.items_sold, 0)
        - COALESCE(it.items_kept, 0) - COALESCE(it.items_written_off, 0) AS items_in_stock,
    COALESCE(sf.revenue_pln, 0)                      AS revenue_pln,
    COALESCE(sf.fee_pln, 0)                          AS fee_pln,
    COALESCE(shp.shipping_pln, 0)                    AS shipping_pln,
    COALESCE(sf.revenue_pln, 0) - COALESCE(sf.fee_pln, 0)
        - COALESCE(shp.shipping_pln, 0) - b.total_paid AS margin_pln,
    COALESCE(sf.sales_missing_rate, 0)               AS sales_missing_rate
FROM batches b
LEFT JOIN (
    SELECT batch_id, SUM(price) AS entered_parcels_cost, COUNT(*) AS parcels_entered
    FROM parcels
    WHERE deleted_at IS NULL
    GROUP BY batch_id
) pp ON pp.batch_id = b.id
LEFT JOIN (
    SELECT p.batch_id,
           COUNT(*)                           AS items_total,
           SUM(i.status = 'sold')             AS items_sold,
           SUM(i.status = 'kept')             AS items_kept,
           SUM(i.status = 'written_off')      AS items_written_off
    FROM items i
    JOIN parcels p ON p.id = i.parcel_id
    WHERE i.deleted_at IS NULL
    GROUP BY p.batch_id
) it ON it.batch_id = b.id
LEFT JOIN (
    SELECT p.batch_id,
           SUM(f.revenue_pln)   AS revenue_pln,
           SUM(f.fee_pln)       AS fee_pln,
           SUM(f.rate_missing)  AS sales_missing_rate
    FROM v_sale_financials f
    JOIN items i   ON i.id = f.item_id
    JOIN parcels p ON p.id = i.parcel_id
    GROUP BY p.batch_id
) sf ON sf.batch_id = b.id
LEFT JOIN (
    -- shipping we paid counts for every sale, including returned ones
    SELECT p.batch_id, SUM(ssh.shipping_pln) AS shipping_pln
    FROM v_sale_shipping ssh
    JOIN sales s   ON s.id = ssh.sale_id AND s.deleted_at IS NULL AND s.status <> 'cancelled'
    JOIN items i   ON i.id = s.item_id
    JOIN parcels p ON p.id = i.parcel_id
    GROUP BY p.batch_id
) shp ON shp.batch_id = b.id
WHERE b.deleted_at IS NULL;
