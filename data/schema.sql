PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    is_admin INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS packages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    ram_mb INTEGER NOT NULL,
    description TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS servers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    package_id INTEGER NOT NULL,
    game TEXT NOT NULL,
    port INTEGER NOT NULL,
    directory TEXT NOT NULL,
    status TEXT NOT NULL,
    screen_name TEXT NOT NULL,
    pid INTEGER,
    ftp_user TEXT NOT NULL,
    ftp_password TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id),
    FOREIGN KEY (package_id) REFERENCES packages (id)
);

INSERT INTO users (email, password_hash, is_admin) VALUES
('admin@admin.cz', '$2y$12$H5RiEuGIZuesNSIS3eDaleLmWE0KMQoPcci1yP8Fro15E2w5OFozW', 1),
('test@test.cz', '$2y$12$8e3SaNRYRynD5W7F9lpPh.2TMl4C0zn021lrRy6NB6OIcy9VJmicG', 0);

INSERT INTO packages (name, ram_mb, description) VALUES
('Starter', 2048, 'Ideální start pro malé světy a komunitní server.'),
('Core', 4096, 'Vyvážený výkon pro stabilní provoz a více hráčů.'),
('Nebula', 8192, 'Výkonná volba pro náročné módy a velké komunity.');
