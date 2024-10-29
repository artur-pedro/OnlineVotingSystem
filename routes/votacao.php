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

// Verifica se o ID da votação foi passado
$id = intval($_GET['id']);
$votacao = $connect->query("SELECT * FROM votacoes WHERE id = $id")->fetch_assoc();

// Verifica se a votação existe
if (!$votacao) {
    die("Votação não encontrada.");
}

// Obter as opções da votação
$opcoes_result = $connect->query("SELECT * FROM opcoes WHERE votacao_id = $id");
$opcoes = $opcoes_result->fetch_all(MYSQLI_ASSOC);

// Obter dados do usuário logado
$usuario = $_SESSION['userdata'];

// Verificar quantos votos o usuário já utilizou e quantos ele pode usar
$votos_usados = $connect->query("SELECT COUNT(*) as total FROM votos WHERE usuario_id = '{$usuario['id']}' AND votacao_id = $id")->fetch_assoc()['total'];
$votos_permitidos = $votacao['votos_permitidos'];
$votos_restantes = $votos_permitidos - $votos_usados;

// Lógica para exibir resultados caso a votação esteja finalizada
if ($votacao['status'] === 'finalizado') {
    echo "<h1>Resultado da Votação: " . htmlspecialchars($votacao['titulo']) . "</h1>";
    foreach ($opcoes as $opcao) {
        echo "<p>" . htmlspecialchars($opcao['nome']) . ": " . htmlspecialchars($opcao['votos']) . " votos</p>";
    }
    exit();
}

// Adicionando a contagem de votos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $opcoes_selecionadas = $_POST['opcao_id'] ?? [];

    // Verifica se alguma opção foi selecionada
    if (empty($opcoes_selecionadas)) {
        // Redireciona ou exibe uma mensagem de alerta se nenhuma opção foi selecionada
        echo "<script>alert('Por favor, selecione pelo menos uma opção para votar.'); window.location.href = 'dashboard.php';</script>";
        exit();
    }

    // Verifica se o usuário não excedeu o limite de votos
    if (count($opcoes_selecionadas) <= $votos_restantes) {
        // Verifica se o usuário já votou nesta votação
        $votou_na_votacao = $connect->query("SELECT COUNT(*) as total FROM votos WHERE usuario_id = '{$usuario['id']}' AND votacao_id = $id")->fetch_assoc()['total'];

        // Se o usuário não votou, incrementa a contagem de pessoas que votaram
        if ($votou_na_votacao == 0) {
            $connect->query("UPDATE votacoes SET pessoas_que_votaram = pessoas_que_votaram + 1 WHERE id = $id");
        }

        foreach ($opcoes_selecionadas as $opcao_id) {
            $opcao_id = intval($opcao_id);
            
            // Obter o nome da opção correspondente
            $opcao_nome = $connect->query("SELECT nome FROM opcoes WHERE id = $opcao_id")->fetch_assoc()['nome'];
            
            // Armazenar o voto no banco de dados
            $stmt = $connect->prepare("INSERT INTO votos (usuario_id, votacao_id, opcao_id, opcao) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $usuario['id'], $id, $opcao_id, $opcao_nome);
            $stmt->execute();
            
            // Incrementar o número de votos da opção selecionada
            $connect->query("UPDATE opcoes SET votos = votos + 1 WHERE id = $opcao_id");
        }
        
        // Reduzir a contagem de votos restantes
        $connect->query("UPDATE votacoes SET votos_usados = votos_usados + " . count($opcoes_selecionadas) . " WHERE id = $id");
        
        // Redirecionar de volta para a página do dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        // Se o limite de votos for excedido, redireciona com uma mensagem de alerta
        echo "<script>alert('Você não pode votar em mais de $votos_restantes opções.'); window.location.href = 'dashboard.php';</script>";
        exit();
    }
}

// Evitar cache da página
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Votação - <?php echo htmlspecialchars($votacao['titulo']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/dashboard.css"> <!-- Usando o mesmo CSS -->
    <style>
        body {
            background-color: #234582; /* Azul padrão do Bootstrap */
            color: white; /* Para garantir que o texto seja legível em um fundo azul */
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1><?php echo htmlspecialchars($votacao['titulo']); ?></h1>
        <div class="float-end mb-3">
            <strong>Pessoas que já votaram: <?php echo $votacao['pessoas_que_votaram']; ?></strong><br>
            <strong>Votos restantes: <?php echo $votos_restantes; ?></strong>
        </div>
        <form method="POST">
            <h3>Opções de Voto:</h3>
            <?php foreach ($opcoes as $opcao): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="opcao_id[]" id="opcao_<?php echo $opcao['id']; ?>" value="<?php echo $opcao['id']; ?>">
                    <label class="form-check-label" for="opcao_<?php echo $opcao['id']; ?>">
                        <?php echo htmlspecialchars($opcao['nome']); ?>
                    </label>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary mt-3">Votar</button>
        </form>
    </div>
</body>
</html>
