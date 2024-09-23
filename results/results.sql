CREATE TABLE results (
id INTEGER PRIMARY KEY AUTOINCREMENT,
run_id INTEGER,
test text,
runtime text,
comment text,
result text,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
flags text
);
CREATE TABLE run (
id INTEGER PRIMARY KEY AUTOINCREMENT,
run_id text,
comment text,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
hidden INTEGER DEFAULT 0
);