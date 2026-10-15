-- Az etelrendeles adatbázis sémája (etelrendeles_javitott.db.sql, 2026-10-02), adatok nélkül.
-- A Laravel ezt tölti be, ha még egy migráció sem futott (php artisan migrate, tesztek).
-- Minden utasítás IF NOT EXISTS, így a meglévő, adatokkal teli adatbázison nem csinál semmit.
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
	"id"	INTEGER NOT NULL,
	"nev"	TEXT NOT NULL COLLATE NOCASE CHECK("nev" != ''),
	"cim"	TEXT NOT NULL CHECK("cim" != ''),
	"telszam"	TEXT NOT NULL CHECK("telszam" != ''),
	"isAdmin"	INTEGER NOT NULL DEFAULT 0 CHECK("isAdmin" IN (0, 1)),
	"email"	TEXT NOT NULL UNIQUE COLLATE NOCASE,
	"password"	TEXT NOT NULL,
	PRIMARY KEY("id" AUTOINCREMENT)
);
CREATE TABLE IF NOT EXISTS "futar" (
	"id"	INTEGER NOT NULL,
	"nev"	TEXT NOT NULL COLLATE NOCASE CHECK("nev" != ''),
	"telszam"	TEXT NOT NULL UNIQUE CHECK("telszam" != ''),
	"jarmu"	TEXT NOT NULL CHECK("jarmu" IN ('auto', 'dron', 'bicikli', 'motor')),
	"ember"	INTEGER NOT NULL DEFAULT 0 CHECK("ember" IN (0, 1) AND ("jarmu" != 'dron' OR "ember" = 0)),
	"allapot"	TEXT NOT NULL DEFAULT 'elerheto' CHECK("allapot" IN ('elerheto', 'uton', 'szunet')),
	"hatosugar_km"	INTEGER NOT NULL CHECK("hatosugar_km" > 0),
	PRIMARY KEY("id" AUTOINCREMENT)
);
CREATE TABLE IF NOT EXISTS "futar_pozicio" (
	"id"	INTEGER NOT NULL,
	"futarID"	INTEGER NOT NULL,
	"gps_x"	REAL NOT NULL CHECK("gps_x" BETWEEN -90 AND 90),
	"gps_y"	REAL NOT NULL CHECK("gps_y" BETWEEN -180 AND 180),
	"cim"	TEXT NOT NULL,
	"rogzitve"	DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY("id" AUTOINCREMENT),
	CONSTRAINT "fk_futar_pozicio_futar" FOREIGN KEY("futarID") REFERENCES "futar"("id") ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS "termek" (
	"id"	INTEGER NOT NULL,
	"tipus"	TEXT NOT NULL DEFAULT 'etel' CHECK("tipus" IN ('etel', 'ital')),
	"nev"	TEXT NOT NULL COLLATE NOCASE CHECK("nev" != ''),
	"fogas"	TEXT NOT NULL CHECK("fogas" IN ('leves', 'eloetel', 'foetel', 'koret', 'salata', 'teszta', 'pizza', 'burger', 'edesseg', 'hideg_ital', 'meleg_ital')),
	"leiras"	TEXT,
	"ar"	INTEGER NOT NULL CHECK("ar" >= 0),
	"elkeszitesi_ido"	INTEGER CHECK("elkeszitesi_ido" IS NULL OR "elkeszitesi_ido" > 0),
	"prioritas"	INTEGER NOT NULL DEFAULT 0 CHECK("prioritas" >= 0),
	"aktiv"	INTEGER NOT NULL DEFAULT 1 CHECK("aktiv" IN (0, 1)),
	PRIMARY KEY("id" AUTOINCREMENT),
	CHECK(("tipus" = 'ital') = ("fogas" IN ('hideg_ital', 'meleg_ital'))),
	CHECK("tipus" = 'ital' OR "elkeszitesi_ido" IS NOT NULL)
);
CREATE TABLE IF NOT EXISTS "rendeles" (
	"id"	INTEGER NOT NULL,
	"felhasznalo_id"	INTEGER NOT NULL,
	"futar_id"	INTEGER,
	"cim"	TEXT NOT NULL CHECK("cim" != ''),
	"prioritas"	INTEGER NOT NULL DEFAULT 0 CHECK("prioritas" IN (0, 1)),
	"fizetesi_mod"	TEXT NOT NULL CHECK("fizetesi_mod" IN ('keszpenz', 'kartya')),
	"jatt"	INTEGER CHECK("jatt" IS NULL OR ("jatt" > 0 AND "fizetesi_mod" = 'kartya')),
	"szamla"	INTEGER NOT NULL DEFAULT 0 CHECK("szamla" IN (0, 1)),
	"km"	INTEGER NOT NULL CHECK("km" > 0),
	"statusz"	TEXT NOT NULL DEFAULT 'új' CHECK("statusz" IN ('új', 'felvéve', 'úton', 'kiszállítva', 'lemondva')),
	"ertekeles"	INTEGER CHECK("ertekeles" IS NULL OR ("ertekeles" BETWEEN 1 AND 5 AND "statusz" = 'kiszállítva')),
	"letrehozva"	DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY("id" AUTOINCREMENT),
	CONSTRAINT "fk_rendeles_felhasznalo" FOREIGN KEY("felhasznalo_id") REFERENCES "felhasznalo"("id") ON DELETE RESTRICT,
	CONSTRAINT "fk_rendeles_futar" FOREIGN KEY("futar_id") REFERENCES "futar"("id") ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS "rendeles_tetel" (
	"rendeles_id"	INTEGER NOT NULL,
	"termek_id"	INTEGER NOT NULL,
	"db"	INTEGER NOT NULL DEFAULT 1 CHECK("db" >= 1),
	"egysegar"	INTEGER NOT NULL CHECK("egysegar" >= 0),
	PRIMARY KEY("rendeles_id", "termek_id"),
	CONSTRAINT "fk_rendeles_tetel_rendeles" FOREIGN KEY("rendeles_id") REFERENCES "rendeles"("id") ON DELETE CASCADE,
	CONSTRAINT "fk_rendeles_tetel_termek" FOREIGN KEY("termek_id") REFERENCES "termek"("id") ON DELETE RESTRICT
);
CREATE TABLE IF NOT EXISTS "termek_allergen" (
	"termek_id"	INTEGER NOT NULL,
	"allergen_kod"	INTEGER NOT NULL,
	PRIMARY KEY("termek_id", "allergen_kod"),
	CONSTRAINT "fk_termek_allergen_allergen" FOREIGN KEY("allergen_kod") REFERENCES "allergen"("kod") ON DELETE RESTRICT,
	CONSTRAINT "fk_termek_allergen_termek" FOREIGN KEY("termek_id") REFERENCES "termek"("id") ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS id_felhasznalo_nev ON felhasznalo(nev COLLATE NOCASE);
CREATE INDEX IF NOT EXISTS id_futar_allapot_jarmu ON futar(allapot, jarmu);
CREATE INDEX IF NOT EXISTS id_futar_nev ON futar(nev COLLATE NOCASE);
CREATE INDEX IF NOT EXISTS id_futar_pozicio_futar_rogzitve ON futar_pozicio(futarID, rogzitve);
CREATE INDEX IF NOT EXISTS id_rendeles_felhasznalo_id ON rendeles(felhasznalo_id);
CREATE INDEX IF NOT EXISTS id_rendeles_futar_statusz ON rendeles(futar_id, statusz);
CREATE INDEX IF NOT EXISTS id_rendeles_statusz ON rendeles(statusz);
CREATE INDEX IF NOT EXISTS id_rendeles_tetel_termek_id ON rendeles_tetel(termek_id);
CREATE INDEX IF NOT EXISTS id_termek_allergen_allergen_kod ON termek_allergen(allergen_kod);
CREATE INDEX IF NOT EXISTS id_termek_nev ON termek(nev COLLATE NOCASE);
CREATE INDEX IF NOT EXISTS id_termek_tipus ON termek(tipus);
CREATE VIEW IF NOT EXISTS v_allergenes_etelek AS
SELECT t.id AS termek_id, t.nev AS termek, t.ar, a.kod AS allergen_kod, a.nev AS allergen
FROM termek t
JOIN termek_allergen ta ON t.id = ta.termek_id
JOIN allergen a ON ta.allergen_kod = a.kod
WHERE t.tipus = 'etel' AND t.aktiv = 1
/* v_allergenes_etelek(termek_id,termek,ar,allergen_kod,allergen) */;
CREATE VIEW IF NOT EXISTS v_futar_poziciok AS
SELECT f.id AS futar_id, f.nev AS futar, f.jarmu, f.ember, f.allapot, f.hatosugar_km,
	fp.gps_x, fp.gps_y, fp.cim AS aktualis_cim, fp.rogzitve
FROM futar f
LEFT JOIN futar_pozicio fp ON fp.id = (
	SELECT id FROM futar_pozicio WHERE futarID = f.id ORDER BY rogzitve DESC, id DESC LIMIT 1
)
/* v_futar_poziciok(futar_id,futar,jarmu,ember,allapot,hatosugar_km,gps_x,gps_y,aktualis_cim,rogzitve) */;
CREATE VIEW IF NOT EXISTS v_elerheto_futarok AS
SELECT futar_id AS id, futar AS nev, jarmu, ember, hatosugar_km, gps_x, gps_y, aktualis_cim AS cim
FROM v_futar_poziciok
WHERE allapot = 'elerheto'
/* v_elerheto_futarok(id,nev,jarmu,ember,hatosugar_km,gps_x,gps_y,cim) */;
CREATE VIEW IF NOT EXISTS v_etelek AS
SELECT id, nev, fogas, leiras, ar, elkeszitesi_ido, prioritas
FROM termek
WHERE tipus = 'etel' AND aktiv = 1
/* v_etelek(id,nev,fogas,leiras,ar,elkeszitesi_ido,prioritas) */;
CREATE VIEW IF NOT EXISTS v_italok AS
SELECT id, nev, fogas, leiras, ar, prioritas
FROM termek
WHERE tipus = 'ital' AND aktiv = 1
/* v_italok(id,nev,fogas,leiras,ar,prioritas) */;
CREATE VIEW IF NOT EXISTS v_kosar AS
SELECT r.id AS rendeles_id, r.felhasznalo_id, rt.termek_id, t.nev AS termek,
	rt.egysegar, rt.db, rt.db * rt.egysegar AS osszeg
FROM rendeles r
JOIN rendeles_tetel rt ON r.id = rt.rendeles_id
JOIN termek t ON rt.termek_id = t.id
WHERE r.statusz = 'új'
/* v_kosar(rendeles_id,felhasznalo_id,termek_id,termek,egysegar,db,osszeg) */;
CREATE VIEW IF NOT EXISTS v_prioritasos_rendelesek AS
SELECT r.id AS rendeles_id, f.nev AS felhasznalo, r.cim, fu.nev AS futar, fu.jarmu,
	r.km, r.statusz, r.fizetesi_mod, r.letrehozva
FROM rendeles r
JOIN felhasznalo f ON r.felhasznalo_id = f.id
LEFT JOIN futar fu ON r.futar_id = fu.id
WHERE r.prioritas = 1
/* v_prioritasos_rendelesek(rendeles_id,felhasznalo,cim,futar,jarmu,km,statusz,fizetesi_mod,letrehozva) */;
CREATE VIEW IF NOT EXISTS v_rendeles_osszeg AS
SELECT r.id AS rendeles_id, f.nev AS felhasznalo, r.cim AS szallitasi_cim, r.fizetesi_mod, r.jatt,
	SUM(rt.db * rt.egysegar) AS termek_osszeg,
	COALESCE(r.jatt, 0) AS jatt_osszeg,
	SUM(rt.db * rt.egysegar) + COALESCE(r.jatt, 0) AS vegosszeg,
	r.statusz, r.letrehozva
FROM rendeles r
JOIN felhasznalo f ON r.felhasznalo_id = f.id
JOIN rendeles_tetel rt ON r.id = rt.rendeles_id
GROUP BY r.id
/* v_rendeles_osszeg(rendeles_id,felhasznalo,szallitasi_cim,fizetesi_mod,jatt,termek_osszeg,jatt_osszeg,vegosszeg,statusz,letrehozva) */;
CREATE VIEW IF NOT EXISTS v_rendeles_tetelek AS
SELECT r.id AS rendeles_id, f.nev AS felhasznalo, t.id AS termek_id, t.nev AS termek, t.tipus,
	rt.db AS darab, rt.egysegar, rt.db * rt.egysegar AS tetel_ertek
FROM rendeles r
JOIN felhasznalo f ON r.felhasznalo_id = f.id
JOIN rendeles_tetel rt ON r.id = rt.rendeles_id
JOIN termek t ON rt.termek_id = t.id
/* v_rendeles_tetelek(rendeles_id,felhasznalo,termek_id,termek,tipus,darab,egysegar,tetel_ertek) */;
CREATE VIEW IF NOT EXISTS v_rendelesek AS
SELECT r.id AS rendeles_id, f.nev AS felhasznalo, f.cim AS felhasznalo_cim, f.telszam AS felhasznalo_telefon,
	fu.nev AS futar, fu.jarmu, fu.allapot AS futar_allapot, r.cim AS szallitasi_cim, r.prioritas,
	r.fizetesi_mod, r.jatt, r.szamla, r.km, r.statusz, r.ertekeles, r.letrehozva
FROM rendeles r
JOIN felhasznalo f ON r.felhasznalo_id = f.id
LEFT JOIN futar fu ON r.futar_id = fu.id
/* v_rendelesek(rendeles_id,felhasznalo,felhasznalo_cim,felhasznalo_telefon,futar,jarmu,futar_allapot,szallitasi_cim,prioritas,fizetesi_mod,jatt,szamla,km,statusz,ertekeles,letrehozva) */;
CREATE VIEW IF NOT EXISTS v_szamlas_rendelesek AS
SELECT r.id AS rendeles_id, f.nev AS felhasznalo, f.email, f.cim AS ugyfel_cim,
	r.cim AS szallitasi_cim, r.fizetesi_mod, r.statusz, r.letrehozva
FROM rendeles r
JOIN felhasznalo f ON r.felhasznalo_id = f.id
WHERE r.szamla = 1
/* v_szamlas_rendelesek(rendeles_id,felhasznalo,email,ugyfel_cim,szallitasi_cim,fizetesi_mod,statusz,letrehozva) */;
CREATE VIEW IF NOT EXISTS v_termek_allergen AS
SELECT t.id, t.nev AS termek_nev, t.tipus, t.fogas, t.ar, t.elkeszitesi_ido, t.aktiv,
	GROUP_CONCAT(a.nev, ', ') AS allergenek
FROM termek t
LEFT JOIN termek_allergen ta ON t.id = ta.termek_id
LEFT JOIN allergen a ON ta.allergen_kod = a.kod
GROUP BY t.id
/* v_termek_allergen(id,termek_nev,tipus,fogas,ar,elkeszitesi_ido,aktiv,allergenek) */;
CREATE VIEW IF NOT EXISTS v_termekek AS
SELECT id, tipus, nev, fogas, leiras, ar, elkeszitesi_ido, prioritas, aktiv
FROM termek
/* v_termekek(id,tipus,nev,fogas,leiras,ar,elkeszitesi_ido,prioritas,aktiv) */;
