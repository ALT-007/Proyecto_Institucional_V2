<?php
header('Content-Type: application/json');

// 1. VALIDAR MÉTODO HTTP - Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(["message" => "Método no permitido. Solo POST."]));
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "logistica_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500); 
    die(json_encode(["message" => "Conexión fallida: " . $conn->connect_error]));
}

// 2. VALIDAR QUE EXISTAN DATOS JSON
$input = file_get_contents('php://input');
if (empty($input)) {
    http_response_code(400);
    die(json_encode(["message" => "No se recibieron datos."]));
}

$data = json_decode($input, true);

// 3. VALIDAR QUE EL JSON SEA VÁLIDO
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode(["message" => "Datos JSON inválidos."]));
}

$id = $data['id'] ?? null;
$nombre = $data['nombre'] ?? '';
$categoria = $data['categoria'] ?? '';
$estado = $data['estado'] ?? '';
$ubicacion = $data['ubicacion'] ?? '';

// 4. VALIDAR CAMPOS OBLIGATORIOS (ajusta según tus necesidades)
if (empty($nombre) || empty($categoria)) {
    http_response_code(400);
    die(json_encode(["message" => "Nombre y categoría son obligatorios."]));
}

try {
    // Iniciar transacción para evitar problemas de concurrencia
    $conn->begin_transaction();
    
    if ($id) {
        // ACTUALIZACIÓN - verificar que el registro existe
        $check_sql = "SELECT id FROM equipos WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("El registro con ID $id no existe.");
        }
        
        $codigo = $data['codigo'];
        $sql = "UPDATE equipos SET codigo=?, nombre=?, categoria=?, estado=?, ubicacion=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $codigo, $nombre, $categoria, $estado, $ubicacion, $id);
        
    } else {
        // INSERCIÓN - Generar código usando AUTO_INCREMENT de forma segura
        $sql = "INSERT INTO equipos (nombre, categoria, estado, ubicacion) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nombre, $categoria, $estado, $ubicacion);
        
        if ($stmt->execute()) {
            // Obtener el ID real generado
            $newId = $conn->insert_id;
            $codigo = "FIERRE " . str_pad($newId, 3, '0', STR_PAD_LEFT);
            
            // Actualizar con el código generado
            $update_sql = "UPDATE equipos SET codigo = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $codigo, $newId);
            
            if (!$update_stmt->execute()) {
                throw new Exception("Error al actualizar el código: " . $update_stmt->error);
            }
            $update_stmt->close();
        } else {
            throw new Exception("Error al insertar: " . $stmt->error);
        }
    }
    
    if ($id && !$stmt->execute()) {
        throw new Exception("Error al actualizar: " . $stmt->error);
    }
    
    // Confirmar transacción
    $conn->commit();
    echo json_encode(["message" => "Operación completada correctamente."]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["message" => $e->getMessage()]);
    
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($check_stmt)) $check_stmt->close();
    $conn->close();
}
?>