-- Az adatbázis sémája: a DB-s csapat végleges exportja (futarszolgalat.db.sql, 2026-10-15)
-- a futarszolgalat_javitas.sql javításaival (futarszolgalat_javitott.db), adatok nélkül.
-- Kivétel a fogas törzsadat-tábla: a termek.fogas idegen kulcs miatt a sorai is kellenek.
-- A Laravel ezt tölti be, ha még egy migráció sem futott (php artisan migrate, tesztek).
CREATE TABLE IF NOT EXISTS "migrations" (
	"id"	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	"migration"	VARCHAR NOT NULL,
	"batch"	INTEGER NOT NULL
);
CREATE TABLE IF NOT EXISTS "allergen" (
	"kod"	INTEGER NOT NULL,
	"nev"	TEXT NOT NULL UNIQUE COLLATE NOCASE,
	PRIMARY KEY("kod" AUTOINCREMENT)
);
CREATE TABLE IF NOT EXISTS "felhasznalo" (
    id        INTEGER NOT NULL,
    nev       TEXT    NOT NULL COLLATE NOCASE,
    cim       TEXT    NOT NULL,
    telszam   TEXT    NOT NULL
              CHECK (length(telszam) BETWEEN 9 AND 16 AND telszam NOT GLOB '*[^0-9+]*'),
    isAdmin   INTEGER NOT NULL CHECK (isAdmin IN (0, 1)),
    email     TEXT    NOT NULL COLLATE NOCASE UNIQUE
              CHECK (email LIKE '%_@_%._%' AND email = trim(email)),
    password  TEXT    NOT NULL,
    aktiv     INTEGER NOT NULL DEFAULT 1 CHECK (aktiv IN (0, 1)),
    PRIMARY KEY (id AUTOINCREMENT)
);
CREATE TABLE IF NOT EXISTS fogas (
    kod   TEXT NOT NULL PRIMARY KEY,
    tipus TEXT NOT NULL CHECK (tipus IN ('etel', 'ital')),
    UNIQUE (kod, tipus)
);
CREATE TABLE IF NOT EXISTS "futar" (
    id            INTEGER NOT NULL,
    nev           TEXT    NOT NULL COLLATE NOCASE,
    telszam       TEXT    NOT NULL UNIQUE
                  CHECK (length(telszam) BETWEEN 9 AND 16 AND telszam NOT GLOB '*[^0-9+]*'),
    jarmu         TEXT    NOT NULL CHECK (jarmu IN ('auto', 'bicikli', 'motor')),
    allapot       TEXT    NOT NULL DEFAULT 'elerheto' CHECK (allapot IN ('elerheto', 'uton', 'szunet')),
    hatosugar_km  INTEGER NOT NULL CHECK (hatosugar_km > 0),
    aktiv         INTEGER NOT NULL DEFAULT 1 CHECK (aktiv IN (0, 1)),
    PRIMARY KEY (id AUTOINCREMENT)
);
CREATE TABLE IF NOT EXISTS "rendeles" (
    id               INTEGER NOT NULL,
    felhasznalo_id   INTEGER NOT NULL,
    futar_id         INTEGER,
    cim              TEXT    NOT NULL,
    prioritas        INTEGER NOT NULL DEFAULT 0 CHECK (prioritas IN (0, 1)),
    fizetesi_mod     TEXT    NOT NULL CHECK (fizetesi_mod IN ('keszpenz', 'kartya')),
    jatt             INTEGER,
    szamla           INTEGER NOT NULL CHECK (szamla IN (0, 1)),
    km               INTEGER CHECK (km IS NULL OR km > 0),
    szallitasi_dij   INTEGER NOT NULL DEFAULT 0 CHECK (szallitasi_dij >= 0),
    statusz          TEXT    NOT NULL DEFAULT 'új'
                     CHECK (statusz IN ('kosar', 'új', 'elfogadva', 'készül', 'úton', 'kiszállítva', 'lemondva')),
    ertekeles        INTEGER CHECK (ertekeles IN (1, 2, 3, 4, 5)),
    letrehozva       TEXT DEFAULT (strftime('%Y-%m-%d %H:%M:%S', 'now', 'localtime')),
    kiszallitva_ido  TEXT,
    PRIMARY KEY (id AUTOINCREMENT),
    CONSTRAINT fk_rendeles_felhasznalo FOREIGN KEY (felhasznalo_id)
        REFERENCES felhasznalo (id) ON DELETE RESTRICT,
    CONSTRAINT fk_rendeles_futar FOREIGN KEY (futar_id)
        REFERENCES futar (id) ON DELETE RESTRICT,
    CONSTRAINT ck_rendeles_futar_kotelezo
        CHECK (futar_id IS NOT NULL OR statusz IN ('kosar', 'új', 'lemondva')),
    CONSTRAINT ck_rendeles_km_kotelezo
        CHECK (km IS NOT NULL OR statusz = 'kosar'),
    CONSTRAINT ck_rendeles_jatt
        CHECK (jatt IS NULL OR (jatt > 0 AND fizetesi_mod = 'kartya' AND statusz = 'kiszállítva')),
    CONSTRAINT ck_rendeles_ertekeles
        CHECK (ertekeles IS NULL OR statusz = 'kiszállítva')
);
CREATE TABLE IF NOT EXISTS "rendeles_tetel" (
    rendeles_id INTEGER NOT NULL,
    termek_id   INTEGER NOT NULL,
    db          INTEGER NOT NULL DEFAULT 1 CHECK (db >= 1),
    egysegar    INTEGER CHECK (egysegar IS NULL OR egysegar >= 0),
    PRIMARY KEY (rendeles_id, termek_id),
    CONSTRAINT fk_rendeles_tetel_rendeles FOREIGN KEY (rendeles_id)
        REFERENCES rendeles (id) ON DELETE CASCADE,
    CONSTRAINT fk_rendeles_tetel_termek FOREIGN KEY (termek_id)
        REFERENCES termek (id) ON DELETE RESTRICT
) WITHOUT ROWID;
CREATE TABLE IF NOT EXISTS szamlazasi_adat (
    rendeles_id INTEGER NOT NULL PRIMARY KEY,
    nev         TEXT    NOT NULL,
    cim         TEXT    NOT NULL,
    adoszam     TEXT    CHECK (adoszam IS NULL
                          OR adoszam GLOB '[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]-[0-9]-[0-9][0-9]'),
    CONSTRAINT fk_szamlazasi_adat_rendeles FOREIGN KEY (rendeles_id)
        REFERENCES rendeles (id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS "termek" (
    id                    INTEGER NOT NULL,
    tipus                 TEXT    NOT NULL DEFAULT 'etel' CHECK (tipus IN ('etel', 'ital')),
    nev                   TEXT    NOT NULL COLLATE NOCASE,
    fogas                 TEXT    NOT NULL,
    leiras                TEXT,
    ar                    INTEGER NOT NULL CHECK (ar >= 0),
    elkeszitesi_ido       INTEGER CHECK (elkeszitesi_ido IS NULL OR elkeszitesi_ido >= 0),
    prioritas             INTEGER NOT NULL DEFAULT 0 CHECK (prioritas >= 0),
    aktiv                 INTEGER NOT NULL DEFAULT 1 CHECK (aktiv IN (0, 1)),
    allergen_ellenorizve  INTEGER NOT NULL DEFAULT 0 CHECK (allergen_ellenorizve IN (0, 1)),
    PRIMARY KEY (id AUTOINCREMENT),
    CONSTRAINT fk_termek_fogas FOREIGN KEY (fogas, tipus)
        REFERENCES fogas (kod, tipus) ON UPDATE CASCADE ON DELETE RESTRICT
);
CREATE TABLE IF NOT EXISTS "termek_allergen" (
    termek_id    INTEGER NOT NULL,
    allergen_kod INTEGER NOT NULL,
    PRIMARY KEY (termek_id, allergen_kod),
    CONSTRAINT fk_termek_allergen_allergen FOREIGN KEY (allergen_kod)
        REFERENCES allergen (kod) ON DELETE RESTRICT,
    CONSTRAINT fk_termek_allergen_termek FOREIGN KEY (termek_id)
        REFERENCES termek (id) ON DELETE CASCADE
) WITHOUT ROWID;
INSERT OR IGNORE INTO fogas (kod, tipus) VALUES ('burger', 'etel');
INSERT OR IGNORE INTO fogas (kod, tipus) VALUES ('edesseg', 'etel');
INSERT OR IGNORE INTO fogas (kod, tipus) VALUES ('eloetel', 'etel');
INSERT OR IGNORE INTO fogas (kod, tipus) VALUES ('foetel', 'etel');
INSERT OR IGNORE INTO fogas (kod, tipus) VALUES ('koret', 'etel');
INSERT OR IGNORE INTO fogas (kod, tipus) VALUES ('leves', 'etel');
INSERT OR IGNORE INTO fogas (kod, tipus) VALUES ('pizza', 'etel');
INSERT OR IGNORE INTO fogas (kod, tipus) VALUES ('salata', 'etel');
INSERT OR IGNORE INTO fogas (kod, tipus) VALUES ('teszta', 'etel');
INSERT OR IGNORE INTO fogas (kod, tipus) VALUES ('hideg_ital', 'ital');
INSERT OR IGNORE INTO fogas (kod, tipus) VALUES ('meleg_ital', 'ital');
CREATE VIEW IF NOT EXISTS v_allergen_ellenorizetlen AS
SELECT t.id, t.nev AS termek_nev, t.tipus, t.fogas,
       COALESCE(GROUP_CONCAT(a.nev, ', '), '(nincs megadva)') AS allergenek
FROM termek t
LEFT JOIN termek_allergen ta ON t.id = ta.termek_id
LEFT JOIN allergen a         ON ta.allergen_kod = a.kod
WHERE t.allergen_ellenorizve = 0 AND t.aktiv = 1
GROUP BY t.id;
CREATE VIEW IF NOT EXISTS v_allergenes_etelek AS
SELECT t.id AS termek_id, t.nev AS termek, t.ar,
       a.kod AS allergen_kod, a.nev AS allergen
FROM termek t
JOIN termek_allergen ta ON t.id = ta.termek_id
JOIN allergen a         ON ta.allergen_kod = a.kod
WHERE t.tipus = 'etel';
CREATE VIEW IF NOT EXISTS v_elerheto_futarok AS
SELECT f.id, f.nev, f.telszam, f.jarmu, f.hatosugar_km
FROM futar f
WHERE f.allapot = 'elerheto' AND f.aktiv = 1;
CREATE VIEW IF NOT EXISTS v_etelek AS
SELECT id, nev, fogas, leiras, ar, elkeszitesi_ido, prioritas
FROM termek
WHERE tipus = 'etel' AND aktiv = 1;
CREATE VIEW IF NOT EXISTS v_italok AS
SELECT id, nev, fogas, leiras, ar, prioritas
FROM termek
WHERE tipus = 'ital' AND aktiv = 1;
CREATE VIEW IF NOT EXISTS v_kosar AS
SELECT r.id AS rendeles_id, r.felhasznalo_id, rt.termek_id,
       t.nev AS termek,
       COALESCE(rt.egysegar, t.ar) AS egysegar,
       rt.db,
       rt.db * COALESCE(rt.egysegar, t.ar) AS osszeg
FROM rendeles r
JOIN rendeles_tetel rt ON r.id = rt.rendeles_id
JOIN termek t          ON rt.termek_id = t.id
WHERE r.statusz = 'kosar';
CREATE VIEW IF NOT EXISTS v_prioritasos_rendelesek AS
SELECT r.id AS rendeles_id, f.nev AS felhasznalo, r.cim,
       fu.nev AS futar, fu.jarmu, r.km, r.statusz, r.fizetesi_mod
FROM rendeles r
JOIN felhasznalo f   ON r.felhasznalo_id = f.id
LEFT JOIN futar fu   ON r.futar_id = fu.id
WHERE r.prioritas = 1;
CREATE VIEW IF NOT EXISTS v_rendeles_osszeg AS
SELECT r.id AS rendeles_id,
       f.nev AS felhasznalo,
       r.cim AS szallitasi_cim,
       r.fizetesi_mod,
       r.jatt,
       COALESCE(SUM(rt.db * COALESCE(rt.egysegar, t.ar)), 0) AS termek_osszeg,
       r.szallitasi_dij,
       COALESCE(r.jatt, 0) AS jatt_osszeg,
       COALESCE(SUM(rt.db * COALESCE(rt.egysegar, t.ar)), 0)
           + r.szallitasi_dij + COALESCE(r.jatt, 0) AS vegosszeg,
       r.statusz
FROM rendeles r
JOIN felhasznalo f          ON r.felhasznalo_id = f.id
LEFT JOIN rendeles_tetel rt ON r.id = rt.rendeles_id
LEFT JOIN termek t          ON rt.termek_id = t.id
GROUP BY r.id;
CREATE VIEW IF NOT EXISTS v_rendeles_tetelek AS
SELECT r.id AS rendeles_id, f.nev AS felhasznalo,
       t.id AS termek_id, t.nev AS termek, t.tipus,
       rt.db AS darab,
       COALESCE(rt.egysegar, t.ar) AS egysegar,
       rt.db * COALESCE(rt.egysegar, t.ar) AS tetel_ertek
FROM rendeles r
JOIN felhasznalo f     ON r.felhasznalo_id = f.id
JOIN rendeles_tetel rt ON r.id = rt.rendeles_id
JOIN termek t          ON rt.termek_id = t.id;
CREATE VIEW IF NOT EXISTS v_rendelesek AS
SELECT r.id AS rendeles_id,
       f.nev AS felhasznalo, f.cim AS felhasznalo_cim, f.telszam AS felhasznalo_telefon,
       fu.nev AS futar, fu.jarmu, fu.allapot AS futar_allapot,
       r.cim AS szallitasi_cim, r.prioritas, r.fizetesi_mod, r.jatt, r.szamla,
       r.km, r.szallitasi_dij, r.statusz, r.ertekeles,
       r.letrehozva, r.kiszallitva_ido
FROM rendeles r
JOIN felhasznalo f ON r.felhasznalo_id = f.id
LEFT JOIN futar fu ON r.futar_id = fu.id;
CREATE VIEW IF NOT EXISTS v_szamlas_rendelesek AS
SELECT r.id AS rendeles_id,
       f.nev AS felhasznalo, f.email,
       f.cim AS ugyfel_cim,
       sz.nev AS szamlazasi_nev, sz.cim AS szamlazasi_cim, sz.adoszam,
       r.cim AS szallitasi_cim, r.fizetesi_mod, r.statusz
FROM rendeles r
JOIN felhasznalo f            ON r.felhasznalo_id = f.id
LEFT JOIN szamlazasi_adat sz  ON sz.rendeles_id = r.id
WHERE r.szamla = 1;
CREATE VIEW IF NOT EXISTS v_termek_allergen AS
SELECT t.id, t.nev AS termek_nev, t.tipus, t.fogas, t.ar, t.elkeszitesi_ido,
       GROUP_CONCAT(a.nev, ', ') AS allergenek,
       t.allergen_ellenorizve
FROM termek t
LEFT JOIN termek_allergen ta ON t.id = ta.termek_id
LEFT JOIN allergen a         ON ta.allergen_kod = a.kod
GROUP BY t.id;
CREATE VIEW IF NOT EXISTS v_termekek AS
SELECT id, tipus, nev, fogas, leiras, ar, elkeszitesi_ido, prioritas
FROM termek
WHERE aktiv = 1;
CREATE INDEX IF NOT EXISTS id_felhasznalo_nev                  ON felhasznalo(nev COLLATE NOCASE);
CREATE INDEX IF NOT EXISTS id_futar_allapot_jarmu              ON futar(allapot, jarmu);
CREATE INDEX IF NOT EXISTS id_futar_nev                        ON futar(nev COLLATE NOCASE);
CREATE INDEX IF NOT EXISTS id_rendeles_felhasznalo_id          ON rendeles(felhasznalo_id);
CREATE INDEX IF NOT EXISTS id_rendeles_futar_id                ON rendeles(futar_id);
CREATE INDEX IF NOT EXISTS id_rendeles_futar_statusz           ON rendeles(futar_id, statusz);
CREATE INDEX IF NOT EXISTS id_rendeles_letrehozva              ON rendeles(letrehozva);
CREATE INDEX IF NOT EXISTS id_rendeles_statusz                 ON rendeles(statusz);
CREATE INDEX IF NOT EXISTS id_rendeles_tetel_termek_id         ON rendeles_tetel(termek_id);
CREATE INDEX IF NOT EXISTS id_termek_allergen_allergen_kod     ON termek_allergen(allergen_kod);
CREATE INDEX IF NOT EXISTS id_termek_nev                       ON termek(nev COLLATE NOCASE);
CREATE INDEX IF NOT EXISTS id_termek_tipus                     ON termek(tipus);
CREATE TRIGGER IF NOT EXISTS trg_rendeles_futar_ellenorzes_ins
BEFORE INSERT ON rendeles
WHEN NEW.futar_id IS NOT NULL
BEGIN
    SELECT RAISE(ABORT, 'A futar nem aktiv')
    WHERE (SELECT aktiv FROM futar WHERE id = NEW.futar_id) = 0;

    SELECT RAISE(ABORT, 'A futar szunetel, nem kaphat aktiv rendelest')
    WHERE NEW.statusz IN ('elfogadva', 'készül', 'úton')
      AND (SELECT allapot FROM futar WHERE id = NEW.futar_id) = 'szunet';

    SELECT RAISE(ABORT, 'A rendeles tavolsaga meghaladja a futar hatosugarat')
    WHERE NEW.km > (SELECT hatosugar_km FROM futar WHERE id = NEW.futar_id);
END;
CREATE TRIGGER IF NOT EXISTS trg_rendeles_futar_ellenorzes_upd
BEFORE UPDATE OF futar_id, km ON rendeles
WHEN NEW.futar_id IS NOT NULL
BEGIN
    SELECT RAISE(ABORT, 'A futar nem aktiv')
    WHERE (SELECT aktiv FROM futar WHERE id = NEW.futar_id) = 0;

    SELECT RAISE(ABORT, 'A futar szunetel, nem kaphat aktiv rendelest')
    WHERE NEW.statusz IN ('elfogadva', 'készül', 'úton')
      AND (SELECT allapot FROM futar WHERE id = NEW.futar_id) = 'szunet';

    SELECT RAISE(ABORT, 'A rendeles tavolsaga meghaladja a futar hatosugarat')
    WHERE NEW.km > (SELECT hatosugar_km FROM futar WHERE id = NEW.futar_id);
END;
CREATE TRIGGER IF NOT EXISTS trg_rendeles_kiszallitva
AFTER UPDATE OF statusz ON rendeles
WHEN NEW.statusz = 'kiszállítva' AND NEW.kiszallitva_ido IS NULL
BEGIN
    UPDATE rendeles
    SET kiszallitva_ido = strftime('%Y-%m-%d %H:%M:%S', 'now', 'localtime')
    WHERE id = NEW.id;
END;
CREATE TRIGGER IF NOT EXISTS trg_rendeles_tetel_egysegar
AFTER INSERT ON rendeles_tetel
WHEN NEW.egysegar IS NULL
BEGIN
    UPDATE rendeles_tetel
    SET egysegar = (SELECT ar FROM termek WHERE id = NEW.termek_id)
    WHERE rendeles_id = NEW.rendeles_id AND termek_id = NEW.termek_id;
END;
