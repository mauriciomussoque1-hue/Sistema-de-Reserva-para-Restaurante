<?php
include("conexao.php");

$nome = $_POST['nome'];
$email = $_POST['email'];
$senha = $_POST['senha'];

$sql = "SELECT * FROM adimin 
        WHERE nome = '$nome' 
        AND email = '$email' 
        AND senha = '$senha'";

$resultado = $conn->query($sql);

if ($resultado->num_rows > 0) {
    header("Location: gerente-dashboard.php");
    exit();
} else {
    echo "<script>
            alert('Nome, email ou senha incorretos!');
            window.location.href='gerente_login.html';
          </script>";
}
?>