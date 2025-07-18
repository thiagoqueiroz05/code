<?php
$conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Inserção ao enviar o formulário
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $image_url = $_POST['image_url'] ?? '';

    if ($title && $slug && $image_url) {
        $stmt = $conn->prepare("INSERT INTO cities (title, slug, image_url) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $slug, $image_url);
        $stmt->execute();
        $stmt->close();
        echo "<p style='color:green;'>Cidade cadastrada com sucesso!</p>";
    } else {
        echo "<p style='color:red;'>Preencha todos os campos.</p>";
    }
}

// Listar cidades cadastradas
$result = $conn->query("SELECT * FROM cities ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Painel de Cidades</title>
  <style>
    body {
      font-family: sans-serif;
      background: #1f1f1f;
      color: #fff;
      padding: 2rem;
    }
    input[type="text"] {
      width: 100%;
      padding: 0.5rem;
      margin-bottom: 1rem;
    }
    button {
      padding: 0.5rem 1rem;
      background: #10b981;
      color: #fff;
      border: none;
      cursor: pointer;
    }
    table {
      margin-top: 2rem;
      width: 100%;
      border-collapse: collapse;
      background: #2f2f2f;
    }
    th, td {
      padding: 0.75rem;
      border: 1px solid #444;
    }
    th {
      background: #111;
    }
  </style>
</head>
<body>
  <h1>Adicionar Nova Cidade</h1>
  <form method="POST">
    <label>Título:</label>
    <input type="text" name="title" required>

    <label>Slug (ex: madrid, barcelona):</label>
    <input type="text" name="slug" required>

    <label>URL da imagem:</label>
    <input type="text" name="image_url" required>

    <button type="submit">Salvar Cidade</button>
  </form>

  <h2>Cidades Cadastradas</h2>
  <table>
    <tr><th>ID</th><th>Título</th><th>Slug</th><th>Imagem</th></tr>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['title']) ?></td>
        <td><?= htmlspecialchars($row['slug']) ?></td>
        <td><img src="<?= htmlspecialchars($row['image_url']) ?>" width="100"></td>
      </tr>
    <?php endwhile; ?>
  </table>
</body>
</html>
