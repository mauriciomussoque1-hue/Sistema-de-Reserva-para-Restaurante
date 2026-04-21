<?php
include "restaurante.php";

/* =========================
   RECEBER DADOS
========================= */

$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$data_reserva = $_POST['data_reserva'] ?? '';
$hora_reserva = $_POST['hora_reserva'] ?? '';
$num_pessoa = $_POST['num_pessoa'] ?? '';
$mesa = $_POST['mesa'] ?? '';

/* =========================
   VALIDAR CAMPOS
========================= */

if (
    empty($nome) ||
    empty($email) ||
    empty($telefone) ||
    empty($data_reserva) ||
    empty($hora_reserva) ||
    empty($num_pessoa) ||
    empty($mesa)
) {
    die("Preencha todos os campos.");
}

/* =========================
   VERIFICAR DISPONIBILIDADE
========================= */

$verificar = $conn->prepare("
    SELECT id 
    FROM reservas
    WHERE data_reserva = ?
    AND hora_reserva = ?
    AND mesa = ?
    AND estado != 'Cancelada'
");

$verificar->bind_param(
    "ssi",
    $data_reserva,
    $hora_reserva,
    $mesa
);

$verificar->execute();
$resultado = $verificar->get_result();

if ($resultado->num_rows > 0) {
    die("Esta mesa já está reservada neste horário.");
}

/* =========================
   GERAR CÓDIGO
========================= */

$codigo_reserva = "RES" . rand(10000, 99999);
$estado = "Pendente";

/* =========================
   INSERIR RESERVA
========================= */

$stmt = $conn->prepare("
    INSERT INTO reservas
    (
        codigo_reserva,
        nome,
        email,
        telefone,
        data_reserva,
        hora_reserva,
        num_pessoa,
        mesa,
        estado
    )
    VALUES
    (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "ssssssiss",
    $codigo_reserva,
    $nome,
    $email,
    $telefone,
    $data_reserva,
    $hora_reserva,
    $num_pessoa,
    $mesa,
    $estado
);


if ($stmt->execute()) {

    echo "
    <div style='
        background:#111;
        color:white;
        padding:20px;
        border-radius:15px;
        text-align:center;
        max-width:400px;
        margin:auto;
    '>

        <h2>Reserva feita com sucesso!</h2>

        <p><strong>Código da reserva:</strong></p>
        <h1 style='color:#ff2b2b;'>$codigo_reserva</h1>

        <p>Guarde este código para consultar sua reserva.</p>

        <br>

        <!-- BOTÃO DOWNLOAD -->
        <a href='gerar_codigo.php?codigo=$codigo_reserva'
           style='
           display:inline-block;
           padding:12px 18px;
           background:#ff2b2b;
           color:white;
           text-decoration:none;
           border-radius:10px;
           margin:10px;
           '>
           Baixar Código
        </a>

        <br><br>

        <a href='formulario.html'
           style='color:white; text-decoration:underline;'>
           Voltar
        </a>

    </div>
    ";
}
//if ($stmt->execute()) {
 //   echo "
   // <h2>Reserva feita com sucesso!</h2>
   // <p><strong>Código da reserva:</strong> $codigo_reserva</p>
    //<p>Guarde este código para consultar sua reserva.</p>
    //<br><br>
    //<a href='formulario.html'>Voltar</a>
    //";
//} else {
  //  echo "Erro ao reservar: " . $stmt->error;
//}
?>