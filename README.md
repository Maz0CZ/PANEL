# UltimatePanel (v1.0.1)

Moderní, lehký a bezpečný panel pro hosting herních serverů na Debianu. Vytvořeno pro nasazení na LAMP stacku (Apache + PHP + SQLite). Hlavní barva: **#ab47bc**. Hlavní vývojář: **Filip Piller**.

> **Důležité:** UltimatePanel nepodporuje pirátský/warez obsah. Serverové soubory si zajišťuje správce infrastruktury v souladu s licencemi herních studií.

## Hlavní funkce

- Registrace a přihlášení uživatelů (Nette + bezpečné hashování hesel).
- Automatické vytvoření jednoho serveru na uživatele.
- Podpora více her (Minecraft/Spigot + další populární tituly jako Valheim, CS2, Terraria).
- **Live konzole bez RCON** – přímé ovládání procesu přes `screen`.
- Automatické generování FTP účtu (ukládáno v DB) + vestavěný souborový manager.
- Admin dashboard s přehledem všech serverů.
- Responzivní futuristický design s Bootstrap + Font Awesome.

## Technologický stack

- PHP 8.1+
- Nette Framework (Application, DI, Forms, Security)
- SQLite
- Symfony Process (bezpečné spouštění `screen`)
- Bootstrap 5.3 + Font Awesome 6.5

## Instalace na Debianu

1. Nainstalujte závislosti:

```bash
scripts/install.sh
```

2. Nainstalujte Composer balíčky:

```bash
composer install
```

3. Inicializujte databázi:

```bash
scripts/init-db.sh /opt/ultimatepanel/panel.sqlite
```

4. Vytvořte `app/config/local.neon` z šablony `local.neon.example` (volitelné SMTP nastavení).

5. Připravte složky pro servery:

```bash
sudo mkdir -p /opt/ultimatepanel/servers
sudo mkdir -p /opt/ultimatepanel/jars
sudo chown -R www-data:www-data /opt/ultimatepanel
```

6. Umístěte `spigot.jar` do `/opt/ultimatepanel/jars/spigot.jar`.

7. Nastavte Apache dokument root na `www/` a povolte `AllowOverride All`.

## Výchozí účty

- **Admin:** `admin@admin.cz` / `pass123`
- **Test:** `test@test.cz` / `test123`

## Bezpečnost

- Ověření přístupu k serverům dle ID uživatele.
- Veškeré akce běží přes předdefinované příkazy (`screen`) bez RCON.
- Doporučeno nasadit přes HTTPS a omezit přístup k serverovým složkám.

## Poznámky k vícehrám

- Pro Minecraft je připraven přímý start přes `spigot.jar`.
- Ostatní hry vyžadují doplnění serverových souborů do adresáře serveru.

## Struktura projektu

```
app/         Nette aplikace (Presentery, šablony, modely)
www/         Veřejná část (index.php, CSS/JS)
data/        SQLite schéma a databáze
scripts/     Instalační skripty
```

## Přehled databáze

Definice tabulek je v `data/schema.sql`.

---

© 2024 UltimatePanel • Filip Piller
