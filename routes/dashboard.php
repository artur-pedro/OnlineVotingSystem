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

// Define o tipo de votação a ser exibida com base no filtro
$tipo_votacao = 'abertas'; // Default
if (isset($_POST['filter'])) {
    $tipo_votacao = $_POST['filter'];
}

// Monta a consulta com base no filtro
$sql = "SELECT v.*, (v.votos_permitidos - IFNULL((SELECT COUNT(*) FROM votos WHERE usuario_id = '{$usuario['id']}' AND votacao_id = v.id), 0)) AS votos_restantes FROM votacoes v ";
if ($tipo_votacao == 'abertas') {
    $sql .= "WHERE v.status = 'ativo'";
} elseif ($tipo_votacao == 'finalizadas') {
    $sql .= "WHERE v.status = 'finalizado'";
} elseif ($tipo_votacao == 'minhas') {
    $sql .= "WHERE v.usuario_id = '{$usuario['id']}'";
} elseif ($tipo_votacao == 'inativas') {
    $sql .= "WHERE v.status = 'inativo'";
}

$votacoes = $connect->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Sistema de Votação</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" 
          integrity="sha512-Fo3rlrZj/kTcDe8fXg0pjJxXh3MKr5uwSFL15u3lSyfxbbn5+M1Ax5Z+GdyZxUgoxj5jR5rbc5xDTt2tnKFbw==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="full-height-container d-flex">
        <!-- Coluna de Votações -->
        <div class="col-md-8">
            <h1 class="text-center mb-4 text-white">Votações</h1>

            <div class="d-flex justify-content-end align-items-center mb-3 gap-2">
                <a href="criar_votacao.php" class="btn btn-primary">+ Criar Votação</a>
                <a href="gerenciar_votacoes.php" class="btn btn-success">Gerenciar minhas votações</a>

                <!-- Formulário de Logout -->
                <form method="POST" action="logout.php" class="d-inline">
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>

                <!-- Formulário de Filtro -->
                <form method="POST" class="d-flex">
                    <select name="filter" class="form-select" onchange="this.form.submit()">
                        <option value="abertas" <?php if ($tipo_votacao == 'abertas') echo 'selected'; ?>>Votações Ativas</option>
                        <option value="finalizadas" <?php if ($tipo_votacao == 'finalizadas') echo 'selected'; ?>>Votações Finalizadas</option>
                        <option value="minhas" <?php if ($tipo_votacao == 'minhas') echo 'selected'; ?>>Minhas Votações</option>
                        <option value="inativas" <?php if ($tipo_votacao == 'inativas') echo 'selected'; ?>>Votações Inativas</option>
                    </select>
                </form>

                <button onclick="location.reload()" class="btn btn-light d-flex align-items-center gap-2" title="Recarregar">
                    <i class="fa fa-sync-alt"></i> Recarregar
                </button>
            </div>

            <table class="table table-bordered table-hover table-striped bg-light rounded">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Descrição</th>
                        <th>Votos Restantes</th>
                        <th>Ações</th>
                        <th>Resultados</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($votacao = $votacoes->fetch_assoc()): ?>
                        <tr class="votacao-row">
                            <td><?php echo htmlspecialchars($votacao['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($votacao['descricao'] ?? 'Sem descrição disponível'); ?></td>
                            <td><?php echo $votacao['votos_restantes']; ?></td>
                            <td>
                                <?php if ($votacao['status'] == 'ativo'): ?>
                                    <a href="votacao.php?id=<?php echo $votacao['id']; ?>" class="btn btn-success me-2">Votar</a>

                                    <!-- Verificar se o usuário já votou na votação antes de exibir o botão Resetar Votos -->
                                    <?php
                                    $votou = $connect->query("SELECT COUNT(*) as total FROM votos WHERE usuario_id = '{$usuario['id']}' AND votacao_id = '{$votacao['id']}'")->fetch_assoc()['total'];
                                    if ($votou > 0): ?>
                                        <a href="resetar_votos.php?id=<?php echo $votacao['id']; ?>" 
                                           class="btn btn-danger me-2" 
                                           onclick="return confirm('Tem certeza que deseja resetar seus votos para esta votação?');">Resetar Votos</a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary me-2" disabled>Você não votou</button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="btn btn-secondary me-2" disabled>Votação Inativa</button>
                                <?php endif; ?>

                                <!-- Ações para o criador -->
                                <?php if ($votacao['usuario_id'] == $usuario['id']): ?>
                                    <?php if ($votacao['status'] != 'finalizado'): // Verificação para ocultar o botão "Terminar" ?>
                                        <a href="terminar_votacao.php?id=<?php echo $votacao['id']; ?>" class="btn btn-warning me-2">Terminar</a>
                                    <?php endif; ?>
                                    <a href="deletar_votacao.php?id=<?php echo $votacao['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Tem certeza que deseja excluir esta votação?');">Deletar</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="resultados.php?id=<?php echo $votacao['id']; ?>" class="btn btn-info">Ver Resultados</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Informações do Usuário -->
        <div class="col-md-4">
            <div class="user-info card">
                <div class="card-body text-center">
                    <img src="../uploads/<?php echo htmlspecialchars($usuario['photo']); ?>" 
                         alt="Foto de Perfil" 
                         class="img-fluid rounded-circle mb-3 user-photo">
                    <h5 class="card-title"><?php echo htmlspecialchars($usuario['nome']); ?></h5>
                    <p class="card-text">Telefone: <?php echo htmlspecialchars($usuario['mobile']); ?></p>
                    <p class="card-text">Email: <?php echo htmlspecialchars($usuario['email']); ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
