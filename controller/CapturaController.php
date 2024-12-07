<?php
    //error_reporting(E_ALL);
    //ini_set("display_errors", 1);

    // Verificar que la solicitud sea de tipo POST 
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $numero_contenedor = $_POST['numero_contenedor'];
        $tamano = $_POST['tamano_contenedor'];
        $flujo = $_POST['flujo'];
        $numero_economico = $_POST['numero_economico'];
        $placas = $_POST['placas_unidad'];
        $conductor = $_POST['nombre_conductor'];
    
        // Se manda llamar el archivo de conexión y se crea la conexión a la base de datos
        include_once 'conexion_bd.php';
        $con = mysqli_connect($server, $user, $contra, $db);
    
        if (!$con) {
            echo json_encode([
                "status" => "danger",
                "text" => "Error 001: Conexión fallida - " . mysqli_connect_error()
            ]);
            exit();
        } 

        try {
            if ($flujo === 'Entrada') {
                // Se llama al procedimiento RegistrarEntrada
                $stmt = $con->prepare("CALL RegistrarEntrada(?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $numero_contenedor, $tamano, $numero_economico, $placas, $conductor);
        
                if ($stmt->execute()) {
                    echo json_encode([
                        "title" => "Éxito!",
                        "status" => "success",
                        "message" => "La entrada del contenedor se registró correctamente."
                    ]);
                } else {
                    echo json_encode([
                        "title" => "Ocurrio un Error",
                        "status" => "error",
                        "message" => "Error al registrar la entrada: " . $stmt->error
                    ]);
                }
            } elseif ($flujo === 'Salida') {
                // Se llama al procedimiento RegistrarSalida
                $stmt = $con->prepare("CALL RegistrarSalida(?, ?)");
                $stmt->bind_param("ss", $numero_contenedor, $numero_economico);
        
                if ($stmt->execute()) {
                    echo json_encode([
                        "title" => "Éxito!",
                        "status" => "success",
                        "message" => "La salida del contenedor se registró correctamente."
                    ]);
                } else {
                    echo json_encode([
                        "title" => "Ocurrio un Error",
                        "status" => "error",
                        "message" => "Error al registrar la salida: " . $stmt->error
                    ]);
                }
            } else {
                echo json_encode([
                    "title" => "Ocurrio un Error",
                    "status" => "error",
                    "message" => "Flujo inválido. Debe ser 'Entrada' o 'Salida'."
                ]);
            }

            $stmt->close();
        } catch (Exception $e) {
            echo json_encode([
                "title" => "Warning",
                "status" => "error",
                "message" => "Excepción: " . $e->getMessage()
            ]);
        }
        
        $con->close();
    } else {
        echo json_encode([
            "title" => "Error",
            "status" => "error",
            "message" => "Método no permitido"
        ]);
    }
