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

// Verifica se o ID da votação foi passado
if (isset($_GET['id'])) {
    $votacao_id = intval($_GET['id']);

    // Remove os votos do usuário para a votação específica
    $stmt = $connect->prepare("DELETE FROM votos WHERE usuario_id = ? AND votacao_id = ?");
    $stmt->bind_param("ii", $usuario['id'], $votacao_id);
    
    if ($stmt->execute()) {
        // Sucesso na remoção dos votos

        // Obter as opções que foram votadas pelo usuário
        $stmt_opcoes = $connect->prepare("SELECT opcao_id FROM votos WHERE usuario_id = ? AND votacao_id = ?");
        $stmt_opcoes->bind_param("ii", $usuario['id'], $votacao_id);
        $stmt_opcoes->execute();
        $result = $stmt_opcoes->get_result();

        // Decrementar os votos de cada opção votada
        while ($row = $result->fetch_assoc()) {
            $opcao_id = intval($row['opcao_id']);
            $connect->query("UPDATE opcoes SET votos = votos - 1 WHERE id = $opcao_id");
        }

        // Atualizar o número de pessoas que votaram na votação
        $connect->query("UPDATE votacoes SET pessoas_que_votaram = pessoas_que_votaram - 1 WHERE id = $votacao_id");

        // Redirecionar de volta para o dashboard
        header("Location: dashboard.php?message=Votos resetados com sucesso!");
    } else {
        // Erro na remoção dos votos
        header("Location: dashboard.php?message=Erro ao resetar os votos!");
    }
    
    $stmt->close();
} else {
    header("Location: dashboard.php");
}
?>
