<?php
$db = new SQLite3('pedidos.db');

$db->exec("CREATE TABLE IF NOT EXISTS pedidos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    mesa TEXT,
    itens TEXT,
    total REAL,
    status TEXT DEFAULT 'pendente',
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    finalized_at TEXT
)");

$result = $db->query("SELECT * FROM pedidos WHERE status = 'pendente' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Pedidos Recebidos</title>

<style>
    body {
        font-family: Arial, sans-serif;
        background: #f5f5f5;
        margin: 0;
        padding: 20px;
    }

    h1 {
        text-align: center;
        margin-bottom: 25px;
    }

    .lista {
        max-width: 800px;
        margin: auto;
    }

    .pedido {
        background: white;
        padding: 20px;
        margin-bottom: 15px;
        border-radius: 10px;
        box-shadow: 0px 3px 10px rgba(0,0,0,0.1);
    }

    .pedido h3 {
        margin: 0 0 10px;
    }

    .pedido p {
        margin: 3px 0;
        color: #444;
    }

    .btn {
        background: #d32f2f;
        color: white;
        padding: 10px 18px;
        border-radius: 6px;
        text-decoration: none;
        display: inline-block;
        margin-top: 10px;
        font-weight: bold;
        transition: 0.2s;
    }

    .btn:hover {
        background: #b71c1c;
    }

    .empty {
        text-align: center;
        margin-top: 40px;
        color: #777;
        font-size: 18px;
    }
</style>
</head>
<body>

<h1> Pedidos Recebidos</h1>

<div style="text-align: center; margin-bottom: 20px;">
    <a href="administracao.php" class="btn">Administração</a>
</div>

<div class="lista">

<?php
$temPedidos = false;

if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $temPedidos = true;

        // segurança na saída
        $mesa = htmlspecialchars($row['mesa']);
        $itens = nl2br(htmlspecialchars($row['itens']));
        $total = number_format(floatval($row['total']), 2, ',', '.');
        $id = intval($row['id']);

        echo "<div class='pedido'>
                <h3>Mesa: {$mesa}</h3>
                <p><b>Itens:</b><br>{$itens}</p>
                <p><b>Total:</b> R$ {$total}</p>
                <a class='btn' href='finalizar_pedido.php?id={$id}'>Finalizar</a>
              </div>";
    }
}

if (!$temPedidos) {
    echo "<p class='empty'>Nenhum pedido no momento.</p>";
}
?>

</div>

</body>
</html>
