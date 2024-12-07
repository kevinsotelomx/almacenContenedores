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

    if ($_POST['action'] === 'getRegistro') {
        $id = $_POST['id'];
    
        try {
            $query = "
                SELECT 
                    c.numero_contenedor, 
                    c.tamano, 
                    r.flujo, 
                    r.fecha_hora, 
                    ca.numero_economico, 
                    ca.placas, 
                    ca.conductor
                FROM registros r
                JOIN contenedores c ON r.contenedor_id = c.id
                JOIN camiones ca ON r.camion_id = ca.id
                WHERE c.estado = 'Dentro' AND c.id = ?  -- Filtramos por el id de contenedor
                ORDER BY r.fecha_hora DESC";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param('i', $id); 
            $stmt->execute();
            
            $result = $stmt->get_result();
        
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        
            echo json_encode(["status" => "success", "data" => $data]);
        
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Excepción: " . $e->getMessage()]);
        }
    }else{
        try {
            $query = "SELECT c.id AS contenedor_id, c.numero_contenedor, c.tamano,r.flujo, r.fecha_hora FROM registros r JOIN contenedores c ON r.contenedor_id = c.id WHERE c.estado = 'Dentro' ORDER BY r.fecha_hora DESC";
            $result = $con->query($query);
    
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
    
            echo json_encode(["status" => "success", "data" => $data]);
    
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Excepción: " . $e->getMessage()]);
        }
    }

    $con->close();
?>
