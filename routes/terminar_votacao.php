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

// Obtém o ID da votação a ser finalizada
$id = intval($_GET['id']);

// Define constantes para os status

// Inicializa a consulta
$query = $connect->prepare("UPDATE votacoes SET status = ? WHERE id = ?");
if ($query) {
    // Utiliza uma variável temporária para o ID
    $status = 'finalizado';
    $query->bind_param("si", $status, $id);
    
    // Executa a consulta
    $query->execute();

    // Verifica se a atualização foi bem-sucedida
    if ($query->affected_rows > 0) {
        // Redireciona para o dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        // Lidar com erro ao atualizar, se necessário
        echo "Erro ao finalizar a votação. Tente novamente.";
    }
} else {
    // Lidar com erro ao preparar a consulta
    echo "Erro ao preparar a consulta.";
}

// Fecha a conexão
$query->close();
$connect->close();
?>
