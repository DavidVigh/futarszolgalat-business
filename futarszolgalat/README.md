# Futárszolgálat – Laravel backend

A futárszolgálat backendje: felhasználók, futárok, termékek és rendelések kezelése,
a rendelési folyamattal együtt (rendelés leadása, futár felveszi, konyha készíti,
futár kiszállítja, értékelés, jatt). Az adatbázis a csapat végleges SQLite-adatbázisa
(`database/export/`), a futárpozíciókat a Python API adja
([toni-0504/futarszolgalat_db_insert](https://github.com/toni-0504/futarszolgalat_db_insert)).
A részletes szabályok a `CLAUDE.md`-ben és a csapat Business Logic dokumentumában vannak.

## Helyi indítás (bemutatóhoz)

A két alkalmazás a saját gépen fut, **egy közös adatbázisfájllal**: a Laravel
`database/database.sqlite` fájlját a Python API is ezt használja.

Feltételezett mappaszerkezet (a két repó egymás mellé klónozva):

```
valahol/
├── futarszolgalat-business/      ← ez a repó (Laravel: futarszolgalat/ mappa)
└── futarszolgalat_db_insert/     ← Python API
```

Kell hozzá: PHP 8.4+, Composer, Python 3.10+.

### 1. Laravel előkészítése (csak első alkalommal)

A `futarszolgalat-business/futarszolgalat` mappában.

macOS / Linux:

```bash
composer install
cp .env.example .env
php artisan key:generate
cp database/export/futarszolgalat_javitott.db database/database.sqlite
php artisan migrate
```

Windows (PowerShell):

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
Copy-Item database\export\futarszolgalat_javitott.db database\database.sqlite
php artisan migrate
```

A `.env`-ben a `PYTHON_API_URL` alapból `http://127.0.0.1:8000`, ez a helyben futó
Python API címe.

### 2. Python API indítása

A `futarszolgalat_db_insert` mappában. Az első két sor csak első alkalommal kell.

macOS / Linux:

```bash
python3 -m venv .venv
.venv/bin/pip install -r requirements.txt
FUTARSZOLGALAT_DB="$PWD/../futarszolgalat-business/futarszolgalat/database/database.sqlite" .venv/bin/uvicorn api:app --port 8000
```

Windows (PowerShell):

```powershell
python -m venv .venv
.venv\Scripts\pip install -r requirements.txt
$env:FUTARSZOLGALAT_DB = "$PWD\..\futarszolgalat-business\futarszolgalat\database\database.sqlite"
.venv\Scripts\uvicorn api:app --port 8000
```

A Python API dokumentációja: <http://127.0.0.1:8000/docs>

### 3. Laravel indítása

Egy másik terminálablakban, a `futarszolgalat-business/futarszolgalat` mappában (a
8000-es portot a Python API használja, ezért a Laravel a 8001-esen fut):

```bash
php artisan serve --port=8001
```

A Laravel API címe: <http://127.0.0.1:8001/api/...> (például
<http://127.0.0.1:8001/api/termekek>).

### Hasznos tudnivalók

- **Friss adatok a bemutató előtt:** a próbák alatt beírt adatok megmaradnak a
  `database/database.sqlite`-ban. Tiszta kezdéshez állítsátok le mindkét alkalmazást,
  másoljátok újra az `export` mappából (1. lépés utolsó előtti sora), és futtassátok a
  `php artisan migrate`-et.
- **API-token:** helyben nem kötelező. Ha használjátok, ugyanaz az érték kell a Python
  API-nál `API_TOKEN`, a Laravelnél (`.env`) `PYTHON_API_TOKEN` néven.
- **Bejelentkezés:** böngészős frontendből a szokásos Laravel-munkamenettel (süti +
  `X-XSRF-TOKEN` fejléc) megy; az API útvonalai a `routes/api.php`-ban vannak.
- **Tesztek:** `php artisan test` (az adatbázis egy üres, memóriában lévő másolatán
  futnak, a `database.sqlite`-ot nem érintik).
