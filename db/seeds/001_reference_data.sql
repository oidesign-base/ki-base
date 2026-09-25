-- =====================================================================
-- KI-BASE — reference data (platforms, metrics, carriers)
-- Safe to run once after 001_init.sql.
-- Values marked "not set" are configured later in the panel.
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_uca1400_ai_ci;
SET time_zone = '+00:00';

INSERT INTO platforms (code, name, default_language, default_currency, has_api, commission_payer, sort_order) VALUES
    ('etsy',     'Etsy',                 'en', NULL,  1, 'seller', 10),  -- currency = shop currency, not set yet
    ('vinted',   'Vinted',               'pl', 'PLN', 0, 'buyer',  20),  -- no Vinted Pro in Poland -> no API
    ('olx',      'OLX',                  'pl', 'PLN', 0, NULL,     30),  -- commission payer not set yet
    ('facebook', 'Facebook Marketplace', 'pl', 'PLN', 0, NULL,     40);

INSERT INTO platform_metrics (platform_id, code, name, metric_type, sort_order)
SELECT id, 'views',     'Перегляди', 'views',    10 FROM platforms WHERE code = 'etsy'
UNION ALL
SELECT id, 'favorites', 'Обране',    'interest', 20 FROM platforms WHERE code = 'etsy'
UNION ALL
SELECT id, 'views',     'Перегляди', 'views',    10 FROM platforms WHERE code = 'vinted'
UNION ALL
SELECT id, 'favourites','Вподобання','interest', 20 FROM platforms WHERE code = 'vinted'
UNION ALL
SELECT id, 'views',     'Перегляди', 'views',    10 FROM platforms WHERE code = 'olx'
UNION ALL
SELECT id, 'observed',  'Спостерігають', 'interest', 20 FROM platforms WHERE code = 'olx';

-- Tracking URL templates must be verified on first real use.
INSERT INTO carriers (code, name, tracking_url_template, sort_order) VALUES
    ('inpost',        'InPost',         'https://inpost.pl/sledzenie-przesylek?number={code}',                         10),
    ('orlen_paczka',  'Orlen Paczka',   'https://www.orlenpaczka.pl/sledz-paczke/?numer={code}',                       20),
    ('dpd',           'DPD',            'https://tracktrace.dpd.com.pl/parcelDetails?typ=1&p1={code}',                 30),
    ('dhl',           'DHL',            'https://www.dhl.com/pl-pl/home/tracking/tracking-parcel.html?tracking-id={code}', 40),
    ('poczta_polska', 'Poczta Polska',  'https://emonitoring.poczta-polska.pl/?numer={code}',                          50),
    ('gls',           'GLS',            'https://gls-group.com/PL/pl/sledzenie-paczek/?match={code}',                  60),
    ('ups',           'UPS',            'https://www.ups.com/track?tracknum={code}',                                   70),
    ('vinted_go',     'Vinted Go',      NULL,                                                                          80),
    ('other',         'Інший',          NULL,                                                                          99);
