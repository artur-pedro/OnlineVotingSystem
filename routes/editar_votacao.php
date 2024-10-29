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

// Obter a votação que está sendo editada
if (!isset($_GET['id'])) {
    header("Location: gerenciar_votacoes.php?error=no_id");
    exit();
}

$votacao_id = intval($_GET['id']);
$votacao_result = $connect->query("SELECT * FROM votacoes WHERE id = $votacao_id");
$votacao = $votacao_result->fetch_assoc();

if (!$votacao) {
    header("Location: gerenciar_votacoes.php?error=votacao_not_found");
    exit();
}

// Verifica se a requisição contém dados para atualizar a descrição ou adicionar uma nova opção
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['salvar'])) {
        // Atualiza a descrição e a quantidade de votos permitidos
        if (isset($_POST['descricao']) && isset($_POST['votos_permitidos'])) {
            $descricao = htmlspecialchars($_POST['descricao']);
            $votos_permitidos = intval($_POST['votos_permitidos']); // Captura o valor dos votos permitidos
            $stmt = $connect->prepare("UPDATE votacoes SET descricao = ?, votos_permitidos = ? WHERE id = ?");
            $stmt->bind_param("sii", $descricao, $votos_permitidos, $votacao_id);
            $stmt->execute();
        }
    } elseif (isset($_POST['adicionar_opcao'])) {
        // Verifica se a requisição contém dados para adicionar uma nova opção
        if (isset($_POST['opcao'])) {
            $opcao = htmlspecialchars($_POST['opcao']);
            
            // Insere a nova opção na tabela de opções
            $stmt = $connect->prepare("INSERT INTO opcoes (votacao_id, nome) VALUES (?, ?)");
            $stmt->bind_param("is", $votacao_id, $opcao);
            $stmt->execute();
        }
    }

    // Redireciona de volta para a página de edição
    header("Location: editar_votacao.php?id=$votacao_id");
    exit();
}

// Obter as opções da votação
$opcoes_result = $connect->query("SELECT * FROM opcoes WHERE votacao_id = $votacao_id");
$opcoes = $opcoes_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Votação - Sistema de Votação</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/editar_votacao.css"> <!-- Usando o mesmo CSS -->
</head>
<body>
    <div class="container">
        <h1 class="mt-4">Editar Votação: <?php echo htmlspecialchars($votacao['titulo']); ?></h1>
        
        <!-- Formulário para atualizar a descrição e a quantidade de votos permitidos -->
        <form method="POST" class="mb-4">
            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="3" required placeholder="Digite a descrição"><?php echo htmlspecialchars($votacao['descricao'] ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="votos_permitidos" class="form-label">Votos Permitidos</label>
                <input type="number" id="votos_permitidos" name="votos_permitidos" class="form-control" required value="<?php echo htmlspecialchars($votacao['votos_permitidos']); ?>"> <!-- Mostra o valor atual -->
            </div>
            <button type="submit" name="salvar" class="btn btn-success">Salvar</button> <!-- Botão Salvar -->
        </form>

        <!-- Formulário para adicionar uma nova opção -->
        <form method="POST" class="mb-4">
            <div class="mb-3">
                <label for="opcao" class="form-label">Adicionar Opção</label>
                <input type="text" id="opcao" name="opcao" class="form-control" required>
            </div>
            <button type="submit" name="adicionar_opcao" class="btn btn-primary">Adicionar Opção</button> <!-- Botão Adicionar Opção -->
        </form>

        <h2>Opções da Votação</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nome da Opção</th>
                    <th>Votos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($opcoes as $opcao): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($opcao['nome']); ?></td>
                        <td><?php echo htmlspecialchars($opcao['votos']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="gerenciar_votacoes.php" class="btn btn-secondary">Voltar</a>
    </div>
</body>
</html>
