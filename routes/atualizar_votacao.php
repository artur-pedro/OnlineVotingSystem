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

// Verifica se a requisição POST contém os dados necessários
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Recupera o status atual da votação
    $stmt = $connect->prepare("SELECT status FROM votacoes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($currentStatus);
    $stmt->fetch();
    $stmt->close();

    // Inverte o status
    $newStatus = $currentStatus === 'ativo' ? 'inativo' : 'ativo';

    // Atualiza o status da votação no banco de dados
    $stmt = $connect->prepare("UPDATE votacoes SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $id);

    if ($stmt->execute()) {
        // Redireciona de volta para a página de gerenciamento de votações
        header("Location: gerenciar_votacoes.php");
        exit();
    } else {
        echo "Erro ao atualizar a votação: " . $stmt->error;
    }
} else {
    // Caso não seja um POST válido, redirecione
    header("Location: gerenciar_votacoes.php");
    exit();
}
?>
