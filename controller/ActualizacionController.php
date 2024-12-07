<?php
error_reporting(E_ALL);
    ini_set("display_errors", 1);

    // Se manda llamar el archivo de conexión y se crea la conexión a la base de datos
    include_once 'conexion_bd.php';
    $con = mysqli_connect($server, $user, $contra, $db);

    try {
        $id = $_POST['id']; // ID del contenedor
        $numeroContenedor = $_POST['numero_contenedor'];
        $tamanoContenedor =  $_POST['tamano'];
        $numeroEconomico = $_POST['numero_economico'];
        $numeroPlaca = $_POST['placas'];
        $fechaHora = $_POST['fecha_hora'];

        // Validación de los datos recibidos
        if (empty($id) || empty($numeroContenedor) || empty($tamanoContenedor) || empty($numeroEconomico) || empty($numeroPlaca) || empty($fechaHora)) {
            echo json_encode(["status" => "error", "message" => "Todos los campos son obligatorios."]);
            exit;
        }

        // Llamada al procedimiento almacenado ActualizarEntrada
        $query = "CALL ActualizarEntrada(?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($query);

        if (!$stmt) {
            echo json_encode(["status" => "error", "message" => "Error al preparar la consulta: " . $con->error]);
            exit;
        }

        $stmt->bind_param('isssss', $id, $numeroContenedor, $tamanoContenedor, $numeroEconomico, $numeroPlaca, $fechaHora);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Datos actualizados con éxito."]);
        } else {
            echo json_encode(["status" => "error", "message" => "No se pudo actualizar los datos."]);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Excepción: " . $e->getMessage()]);
    }

    $con->close();
?>
