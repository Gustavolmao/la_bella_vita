<?php
$db = new SQLite3('pedidos.db');

if (!isset($_GET['id'])) {
    die("ID inválido.");
}

$id = intval($_GET['id']);
$agora = date('Y-m-d H:i:s');

$stmt = $db->prepare("
    UPDATE pedidos 
    SET status = 'finalizado', finalized_at = :f 
    WHERE id = :id
");
$stmt->bindValue(':f', $agora, SQLITE3_TEXT);
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$stmt->execute();

header("Location: pedidos.php");
exit;
?>
