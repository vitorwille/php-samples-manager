<?php

$pdo = new PDO(
    sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', getenv('DB_HOST'), getenv('DB_NAME')),
    getenv('DB_USER'),
    getenv('DB_PASS'),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
);

$pdo->exec('CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file VARCHAR(255) NOT NULL UNIQUE
)');

$applied = $pdo->query('SELECT file FROM migrations')->fetchAll(PDO::FETCH_COLUMN);

foreach (glob(__DIR__ . '/migrations/*.php') as $file) {
    $name = basename($file);

    if (in_array($name, $applied, true)) {
        continue;
    }

    foreach ((array) require $file as $sql) {
        $pdo->exec($sql);
    }

    $stmt = $pdo->prepare('INSERT INTO migrations (file) VALUES (?)');
    $stmt->execute([$name]);
    echo "Applied: $name\n";
}

echo "Done.\n";
