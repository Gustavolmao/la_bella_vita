<?php


$db = new SQLite3('pedidos.db');

$db->exec("CREATE TABLE IF NOT EXISTS pedidos (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  mesa INTEGER,
  itens TEXT,
  total REAL,
  status TEXT DEFAULT 'pendente',
  created_at TEXT,
  finalized_at TEXT
)");

$editRow = null;
$message = null;

// CRIAR 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'criar') {
    $mesa = intval($_POST['mesa']);
    $itens = trim($_POST['itens'] ?? '');
    $total = floatval($_POST['total']);
    $now = date('Y-m-d H:i:s');

    $stmt = $db->prepare("INSERT INTO pedidos (mesa, itens, total, status, created_at, finalized_at) VALUES (:mesa, :itens, :total, 'finalizado', :c, :f)");
    $stmt->bindValue(':mesa', $mesa, SQLITE3_INTEGER);
    $stmt->bindValue(':itens', $itens, SQLITE3_TEXT);
    $stmt->bindValue(':total', $total, SQLITE3_FLOAT);
    $stmt->bindValue(':c', $now, SQLITE3_TEXT);
    $stmt->bindValue(':f', $now, SQLITE3_TEXT);
    $stmt->execute();

    
    $message = "Pedido criado com sucesso.";
    header("Location: administracao.php");
    exit;
}

// EDITAR 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'editar') {
    $id = intval($_POST['id']);
    $mesa = intval($_POST['mesa']);
    $itens = trim($_POST['itens'] ?? '');
    $total = floatval($_POST['total']);

    $stmt = $db->prepare("UPDATE pedidos SET mesa=:mesa, itens=:itens, total=:total WHERE id=:id");
    $stmt->bindValue(':mesa', $mesa, SQLITE3_INTEGER);
    $stmt->bindValue(':itens', $itens, SQLITE3_TEXT);
    $stmt->bindValue(':total', $total, SQLITE3_FLOAT);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();

    $message = "Pedido atualizado.";
    header("Location: administracao.php");
    exit;
}

// DELETAR
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $db->prepare("DELETE FROM pedidos WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();

    $message = "Pedido excluído.";
    header("Location: administracao.php");
    exit;
}

// BUSCAR 
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $db->prepare("SELECT * FROM pedidos WHERE id = :id LIMIT 1");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $res = $stmt->execute();
    $editRow = $res->fetchArray(SQLITE3_ASSOC) ?: null;
}

// LISTAR 
$result = $db->query("SELECT * FROM pedidos WHERE status = 'finalizado' ORDER BY finalized_at DESC, id DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Administração — Pedidos Finalizados</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* ====== Layout ====== */
    body {
      font-family: Arial, Helvetica, sans-serif;
      background: #f3f3f3;
      margin: 0;
      padding: 20px;
      color: #222;
    }

    .container {
      max-width: 1100px;
      margin: 30px auto;
    }

    .top-row {
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px;
      margin-bottom: 18px;
    }

    h1 {
      color: #701010;
      margin: 0;
      font-size: 1.6rem;
    }

    /* ====== Form ====== */
    .form-box {
      background: #fff7f7;
      border: 1px solid #efdddd;
      padding: 16px;
      border-radius: 10px;
      margin-bottom: 22px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    }

    .form-box h2 { margin-top:0; color:#8b1c1c; }

    label { display:block; font-weight:600; margin-top:10px; color:#701010; }
    input[type="text"], input[type="number"], select {
      width:100%;
      padding:10px;
      margin-top:6px;
      border-radius:8px;
      border:1px solid #d6bebe;
      font-size:1rem;
      box-sizing:border-box;
    }

    .form-actions { margin-top:12px; display:flex; gap:8px; align-items:center; }

    /* ====== Tabela ====== */
    .card {
      background:#fff;
      padding:14px;
      border-radius:12px;
      box-shadow:0 2px 8px rgba(0,0,0,0.06);
    }

    table {
      width:100%;
      border-collapse:collapse;
      margin-top:12px;
      font-size:0.95rem;
    }
    thead th {
      text-align:left;
      background:#701010;
      color:#fff;
      padding:12px;
      font-weight:700;
    }
    tbody td {
      padding:12px;
      border-bottom:1px solid #eee;
      vertical-align:top;
    }
    tbody tr:nth-child(even) td { background:#faf3f3; }

    .small-muted { font-size:0.85rem; color:#666; }

    /* ====== Botões ====== */
    .btn { display:inline-block; border:none; padding:10px 18px; border-radius:30px; font-weight:700; text-decoration:none; cursor:pointer; }
    .btn-primary { background:#701010; color:#fff; }
    .btn-primary:hover { background:#a52828; transform:scale(1.02); }
    .btn-edit { background:#2d89ef; color:#fff; }
    .btn-delete { background:#e53935; color:#fff; }

    .actions a { margin-right:6px; }

    @media (max-width:800px){
      .top-row { flex-direction:column; align-items:stretch; gap:8px; }
      thead { display:none; }
      table, tbody, tr, td { display:block; width:100%; }
      td { box-sizing:border-box; padding:10px; border-bottom:1px solid #eee; }
      td:before { content: attr(data-label); font-weight:600; display:block; margin-bottom:6px; color:#701010; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="top-row">
      <h1>Administração — Pedidos Finalizados</h1>
      <div>
        <a href="pedidos.php" class="btn btn-primary">← Pedidos Ativos</a>
        <a href="index.html" class="btn" style="background:#777;color:#fff;margin-left:8px">Início</a>
      </div>
    </div>

    <?php if (!empty($message)): ?>
      <div style="padding:10px;background:#e7f7e7;border:1px solid #cfead1;border-radius:8px;margin-bottom:12px;color:#1a7a2b">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <div class="form-box">
      <h2><?= $editRow ? "Editar Pedido #".intval($editRow['id']) : "Criar Pedido Manualmente" ?></h2>

      <form method="POST" action="administracao.php">
        <input type="hidden" name="acao" value="<?= $editRow ? 'editar' : 'criar' ?>">
        <?php if ($editRow): ?>
          <input type="hidden" name="id" value="<?= intval($editRow['id']) ?>">
        <?php endif; ?>

        <label for="mesa">Mesa</label>
        <select id="mesa" name="mesa" required>
          <?php for ($i=1; $i<=8; $i++): ?>
            <option value="<?= $i ?>" <?= ($editRow && intval($editRow['mesa']) === $i) ? 'selected' : '' ?>>Mesa <?= $i ?></option>
          <?php endfor; ?>
        </select>

        <label for="itens">Itens (texto)</label>
        <input id="itens" type="text" name="itens" required value="<?= $editRow ? htmlspecialchars($editRow['itens']) : '' ?>">

        <label for="total">Total (ex: 45.50)</label>
        <input id="total" type="number" name="total" required step="0.01" value="<?= $editRow ? number_format($editRow['total'],2,'.','') : '' ?>">

        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><?= $editRow ? 'Salvar Alterações' : 'Criar Pedido' ?></button>
          <?php if ($editRow): ?>
            <a class="btn" href="administracao.php" style="background:#777;color:#fff">Cancelar</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div class="card">
      <h2 style="margin-top:0">Histórico (finalizados)</h2>

      <table>
        <thead>
          <tr>
            <th style="width:60px">ID</th>
            <th style="width:100px">Mesa</th>
            <th>Itens</th>
            <th style="width:110px">Total</th>
            <th style="width:160px">Criado em</th>
            <th style="width:160px">Finalizado em</th>
            <th style="width:170px">Ações</th>
          </tr>
        </thead>

        <tbody>
          <?php if ($result !== false): ?>
            <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
              <tr>
                <td data-label="ID"><?= intval($row['id']) ?></td>
                <td data-label="Mesa">Mesa <?= intval($row['mesa']) ?></td>
                <td data-label="Itens" style="white-space:pre-wrap"><?= nl2br(htmlspecialchars($row['itens'])) ?></td>
                <td data-label="Total"><strong style="color:#701010">R$ <?= number_format($row['total'],2,',','.') ?></strong></td>
                <td data-label="Criado"><?= isset($row['created_at']) ? htmlspecialchars($row['created_at']) : '-' ?></td>
                <td data-label="Finalizado"><?= isset($row['finalized_at']) ? htmlspecialchars($row['finalized_at']) : '-' ?></td>
                <td data-label="Ações" class="actions">
                  <a class="btn btn-edit" href="?edit=<?= intval($row['id']) ?>">Editar</a>
                  <a class="btn btn-delete" href="?delete=<?= intval($row['id']) ?>" onclick="return confirm('Excluir pedido #<?= intval($row['id']) ?>?')">Excluir</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="7">Nenhum pedido encontrado.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</body>
</html>

