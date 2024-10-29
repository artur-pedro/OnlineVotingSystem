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

// Obtém o ID da votação a ser deletada
$id = intval($_GET['id']);

// Primeiro, deletar os votos associados à votação
$deleteVotesQuery = $connect->prepare("DELETE FROM votos WHERE votacao_id = ?");
$deleteVotesQuery->bind_param("i", $id);
$deleteVotesQuery->execute();
$deleteVotesQuery->close();

// Depois, deletar as opções associadas à votação
$deleteOptionsQuery = $connect->prepare("DELETE FROM opcoes WHERE votacao_id = ?");
$deleteOptionsQuery->bind_param("i", $id);
$deleteOptionsQuery->execute();
$deleteOptionsQuery->close();

// Agora, deletar a votação do banco de dados
$query = $connect->prepare("DELETE FROM votacoes WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$query->close();

// Redireciona de volta para o dashboard
header("Location: dashboard.php?message=Votação deletada com sucesso!");
exit();
?>
