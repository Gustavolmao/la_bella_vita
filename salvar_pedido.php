<?php
$db = new SQLite3("pedidos.db");

$db->exec("CREATE TABLE IF NOT EXISTS pedidos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    mesa TEXT,
    itens TEXT,
    total REAL,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$mesa = $_POST["mesa"] ?? "";
$itens = $_POST["itens"] ?? "";
$total = $_POST["total"] ?? "";

$stmt = $db->prepare("INSERT INTO pedidos (mesa, itens, total) VALUES (:mesa, :itens, :total)");
$stmt->bindValue(":mesa", $mesa, SQLITE3_TEXT);
$stmt->bindValue(":itens", $itens, SQLITE3_TEXT);
$stmt->bindValue(":total", $total, SQLITE3_FLOAT);
$stmt->execute();

header("Location: finalizar.html");
exit();
