<?php
session_start();
include('../api/connect.php');

// Verifique se o usuário está logado
if (!isset($_SESSION['userdata'])) {
    header("Location: ../");
    exit();
}
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $result = mysqli_query($connect, "SELECT * FROM user WHERE id = '$user_id'");
    
    // Se o usuário não existir no banco, encerra a sessão
    if (mysqli_num_rows($result) === 0) {
        session_unset(); // Remove todas as variáveis de sessão
        session_destroy(); // Destroi a sessão
        header("Location: ../index.html"); // Redireciona para a página inicial
        exit();
    }
}

// Lógica para adicionar votação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $status = $_POST['status'];
    $usuario = $_SESSION['userdata'];
    $usuario_id = $usuario['id']; // Capturando o ID do usuário
    // Prepare a instrução SQL sem data_fim e data_inicio
    $stmt = $connect->prepare("INSERT INTO votacoes (titulo, status, usuario_id) VALUES (?, ?,?)");
    $stmt->bind_param("sss", $titulo, $status, $usuario_id);

    if ($stmt->execute()) {
        echo "<script>alert('Votação criada com sucesso!'); window.location.href = 'dashboard.php';</script>";
    } else {
        echo "<script>alert('Erro ao criar votação. Tente novamente.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Criar Votação - Sistema de Votação</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/criar_votacao.css"> <!-- CSS separado -->
</head>
<body>
    <div class="full-height-container d-flex justify-content-center align-items-center">
        <div class="form-wrapper bg-light p-5 rounded">
            <h2 class="text-center mb-4">Criar Nova Votação</h2>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="titulo" class="form-label">Título da Votação</label>
                    <input type="text" class="form-control" id="titulo" name="titulo" required>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="ativo" selected>Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Criar Votação</button>
                </div>
                <div class="text-center mt-3">
                    <a href="dashboard.php" class="text-decoration-none">Voltar para o Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
