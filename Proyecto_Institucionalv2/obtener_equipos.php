<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "logistica_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

$sql = "SELECT id, codigo, nombre, categoria, estado, ubicacion FROM equipos";
$result = $conn->query($sql);

$equipos = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $equipos[] = $row;
    }
}

echo json_encode($equipos);

$conn->close();
?>