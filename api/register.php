<?php
include("connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura os dados do formulário
    $name = $_POST['name'];
    $mobile = $_POST['mobile'];
    $password = $_POST['password'];
    $dpassword = $_POST['dpassword'];
    $email = $_POST['email'];

    // Verifica se a imagem foi enviada
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $image = $_FILES['photo']['name'];
        $tmp_name = $_FILES['photo']['tmp_name'];

        // Verificar se as senhas coincidem
        if ($password === $dpassword) {
            // Verifica se o email ou telefone já existe no banco de dados
            $checkQuery = mysqli_query($connect, "SELECT * FROM user WHERE mobile = '$mobile' OR email = '$email'");
            
            if (mysqli_num_rows($checkQuery) > 0) {
                echo '
                <script>
                    alert("O email ou número de telefone já está cadastrado.");
                    window.location = "../routes/register.html";
                </script>
                ';
            } else {
                // Move o arquivo para o diretório de uploads
                if (move_uploaded_file($tmp_name, "../uploads/$image")) {
                    // Insere os dados no banco de dados
                    $insert = mysqli_query($connect, "INSERT INTO user (nome, mobile, password, photo, status, votes, email) VALUES ('$name', '$mobile', '$password', '$image', 0, 0, '$email')");
                    
                    if ($insert) {
                        echo '
                        <script>
                            alert("Usuário registrado com sucesso!");
                            window.location = "../index.html";
                        </script>
                        ';
                    } else {
                        echo '
                        <script>
                            alert("Erro ao registrar o usuário.");
                            window.location = "../index.html";
                        </script>
                        ';
                    }
                } else {
                    echo '
                    <script>
                        alert("Erro ao enviar a imagem.");
                        window.location = "../routes/register.html";
                    </script>
                    ';
                }
            }
        } else {
            echo '
            <script>
                alert("As senhas não coincidem.");
                window.location = "../routes/register.html";
            </script>
            ';
        }
    } else {
        echo '
        <script>
            alert("Erro: Nenhuma imagem foi enviada.");
            window.location = "../routes/register.html";
        </script>
        ';
    }
}
?>
