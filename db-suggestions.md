# Adatbázis struktúra módosítás javaslatok

## Hibás adatok

### 1. Két `Szombathely` szerepel a `cities` táblában

A `002_import_city_data.sql` a 10-es azonosítóhoz is a `Szombathely` nevet
rendeli (`Észak és Alföld` régióval), a `003_import_weather_data.py` viszont
ehhez az azonosítóhoz a `Turkeve` CSV fájlokat tölti be. A 10-es város tehát
valójában **Túrkeve**. A hiba a felületen két azonos nevű várost eredményez, és
egy dunántúli városnév kerül az alföldi régióba.

Ehhez nem nyúltam, de feltételezem, hogy véletlen került így bele.

### 2. Hiányzó mérések `-999` értékkel

A forrás CSV-k a hiányzó méréseket `-999`-cel jelölik, és ez változtatás nélkül
került az adatbázisba. Ezek valódi számként viselkednek, ezért torzítják az aggregált értékeket.

Helyette `NULL`-ként tárolnám már adatbázis szinten is, hogy a lekérdezések ne kelljen külön kezelniük a hiányzó értékeket.


### 3. Nincs kulcs és index a mérés táblákon

A `maximum_temperatures`, `minimum_temperatures` és `precipitation` táblákon
nincs elsődleges kulcs, egyedi megkötés, index és idegen kulcs sem. Ennek
következményei:

- minden lekérdezés teljes táblát olvas
- ugyanarra a városra és napra két mérés is kerülhet
- létező város nélkül is be lehet szúrni mérést

### 4. Három tábla helyett egy

A három mérés tábla ugyanazt írja le: egy város egy napját. A külön táblák miatt
minden lekérdezés háromszoros join, és könnyen előfordulhat, hogy egy napra
csak az egyik mérés létezik. Egyetlen tábla egyszerűbb és gyorsabb:

```sql
CREATE TABLE daily_weather (
    city_id       INT          NOT NULL,
    date          DATE         NOT NULL,
    min_temp      DECIMAL(4,1) NULL COMMENT 'Minimum hőmérséklet, Celsius fokban',
    max_temp      DECIMAL(4,1) NULL COMMENT 'Maximum hőmérséklet, Celsius fokban',
    precipitation DECIMAL(5,1) NULL COMMENT 'Csapadék, milliméterben',
    PRIMARY KEY (city_id, date),
    CONSTRAINT fk_daily_weather_city FOREIGN KEY (city_id) REFERENCES cities (id),
    CONSTRAINT chk_temp_order CHECK (min_temp IS NULL OR max_temp IS NULL OR min_temp <= max_temp),
    CONSTRAINT chk_precipitation CHECK (precipitation IS NULL OR precipitation >= 0)
);
```

### 5. Dátum `bigint`-ként tárolva

A `date` oszlop `YYYYMMDD` alakú `bigint`. Így az adatbázis érvénytelen
dátumot is elfogad (pl. `20230230`), dátumfüggvények (`YEAR()`, `DATE_ADD()`,
`BETWEEN` két dátum között) csak konverzióval használhatók, és minden
alkalmazásnak tudnia kell erről a formátumról. Helyette: `DATE` típust használnék.
