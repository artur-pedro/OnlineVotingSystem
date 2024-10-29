<?php
session_start();
include('../api/connect.php');

// Verifica se o usuário está logado
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

// Obter dados do usuário logado
$usuario = $_SESSION['userdata'];

// Obter apenas as votações associadas ao usuário
$sql = "SELECT * FROM votacoes WHERE usuario_id = '{$usuario['id']}'";
$votacoes = $connect->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Votações - Sistema de Votação</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/dashboard.css"> <!-- Usando o mesmo CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" 
          integrity="sha512-Fo3rlrZj/kTcDe8fXg0pjJxXh3MKr5uwSFL15u3lSyfxbbn5+M1Ax5Z+GdyZxUgoxj5jR5rbc5xDTt2tnKFbw==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="full-height-container d-flex">
        <div class="col-md-12">
            <h1 class="text-center mb-4 text-white">Gerenciar Votações</h1>

            <table class="table table-bordered table-hover table-striped bg-light rounded">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th>Votos Permitidos</th> <!-- Nova coluna para Votos Permitidos -->
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($votacao = $votacoes->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($votacao['titulo'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($votacao['descricao'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($votacao['status'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($votacao['votos_permitidos'] ?? ''); ?></td> <!-- Exibe Votos Permitidos -->
                            <td>
                                <form method="POST" action="atualizar_votacao.php" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo $votacao['id']; ?>">
                                    <button type="submit" name="status" value="<?php echo $votacao['status'] == 'ativo' ? 'inativo' : 'ativo'; ?>" 
                                            class="btn btn-<?php echo $votacao['status'] == 'ativo' ? 'warning' : 'success'; ?>">
                                        <?php echo $votacao['status'] == 'ativo' ? 'Desativar' : 'Ativar'; ?>
                                    </button>
                                </form>
                                <a href="editar_votacao.php?id=<?php echo $votacao['id']; ?>" class="btn btn-info">Editar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Botão de Voltar -->
            <div class="text-center mt-4">
                <a href="dashboard.php" class="btn btn-secondary">Voltar para o Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
