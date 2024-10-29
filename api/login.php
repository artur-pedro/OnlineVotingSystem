<?php
    session_start();
    include('connect.php');
    $mobile = $_POST['mobile'];
    $password = $_POST['password'];
    $role = $_POST['role'];


    $check = mysqli_query($connect, "SELECT * FROM user WHERE mobile = '$mobile' or email = '$mobile' AND password = '$password'");

    if(mysqli_num_rows($check) > 0) {
        $userdata =  mysqli_fetch_array($check);
        $groups = mysqli_query($connect, "SELECT * FROM user");
        $groupsdata = mysqli_fetch_all($groups, MYSQLI_ASSOC);

        $_SESSION['userdata'] = $userdata;
        $_SESSION['groupsdata'] = $groupsdata;

        echo '
            <script>
                window.location = "../routes/dashboard.php";
                </script>
            ';
    }
    else {
        echo '
            <script>
                alert("Credenciais inválidas ou usuário não encontrado!");
                window.location = "../";
                </script>
            ';

    }










?>