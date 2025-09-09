<?php
// Configuración de la base de datos
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "logistica_db";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Recibir los datos en formato JSON
$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'] ?? null;

if ($id) {
    // Si el ID existe, es una eliminación
    $sql = "DELETE FROM equipos WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Equipo eliminado correctamente."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error al eliminar: " . $stmt->error]);
    }
    
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(["message" => "ID no proporcionado."]);
}

$conn->close();
?>