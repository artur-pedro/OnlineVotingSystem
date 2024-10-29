<?php
session_start();
include('../api/connect.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['userdata'])) {
    header("Location: ../");
    exit();
}

// Obtém o ID da votação
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
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

$votacao_id = intval($_GET['id']);

// Consulta as opções de votação e seus resultados
$sql = "SELECT o.id, o.nome, COUNT(v.id) AS total_votos
        FROM opcoes o
        LEFT JOIN votos v ON o.id = v.opcao_id AND v.votacao_id = {$votacao_id}
        WHERE o.votacao_id = {$votacao_id}
        GROUP BY o.id";

$resultados = $connect->query($sql);

// Obter detalhes da votação
$votacao = $connect->query("SELECT * FROM votacoes WHERE id = {$votacao_id}")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Resultados da Votação</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/dashboard.css"> <!-- Use seu CSS para estilo -->
    <style>
        body {
            background-color: #234582; /* Azul padrão do Bootstrap */
            color: white; /* Para garantir que o texto seja legível em um fundo azul */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4 text-white">Resultados da Votação: <?php echo htmlspecialchars($votacao['titulo']); ?></h1>
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Descrição: <?php echo htmlspecialchars($votacao['descricao'] ?? 'Sem descrição disponível'); ?></h5>
                <table class="table table-bordered table-hover table-striped bg-light rounded">
                    <thead>
                        <tr>
                            <th>Opção</th>
                            <th>Total de Votos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($opcao = $resultados->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($opcao['nome']); ?></td>
                                <td><?php echo $opcao['total_votos']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-primary">Voltar para o Dashboard</a>
        </div>
    </div>
</body>
</html>
