<?php
include ("restaurante.php");


$sql = "SELECT nome, data_reserva, num_pessoa FROM reservas ORDER BY id DESC";
$result = $conn->query($sql);
$reservas = [];
while ($row = $result->fetch_assoc()) {
    $reservas[] = $row;
}

echo json_encode($reservas);
?>