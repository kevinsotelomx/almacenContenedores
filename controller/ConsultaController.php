<?php
    //error_reporting(E_ALL);
    //ini_set("display_errors", 1);

    // Se manda llamar el archivo de conexión y se crea la conexión a la base de datos
    include_once 'conexion_bd.php';
    $con = mysqli_connect($server, $user, $contra, $db);

    if (!$con) {
        echo json_encode([
            "status" => "error", 
            "message" => "Error de conexión: " . mysqli_connect_error()
        ]);
        exit;
    }

    try {
        $query = "SELECT c.numero_contenedor, c.tamano, r.flujo, r.fecha_hora FROM registros r JOIN contenedores c ON r.contenedor_id = c.id ORDER BY r.fecha_hora DESC";
        $result = $con->query($query);

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode(["status" => "success", "data" => $data]);

    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Excepción: " . $e->getMessage()]);
    }

    $con->close();
?>
