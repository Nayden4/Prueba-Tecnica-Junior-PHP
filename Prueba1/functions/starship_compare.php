<?php
// Iniciar la sesión
session_start();

// Verificar que los datos necesarios se envíen en la solicitud
if (!isset($_GET['nave1_id']) || !isset($_GET['nave2_id'])) {
    echo json_encode(["error" => "Se requieren dos IDs de naves para la comparación."]);
    exit;
}

$nave1_id = $_GET['nave1_id'];
$nave2_id = $_GET['nave2_id'];

try {
    // Crear la conexión con la base de datos SQLite
    $pdo = new PDO('sqlite:../swapi.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener los atributos de las naves seleccionadas
    $stmt = $pdo->prepare("
    SELECT s.id, s.name, s.model, 
         GROUP_CONCAT(m.name) AS manufacturers, 
         sc.name AS starship_class, 
         sa.cost_in_credits, 
         sa.length, 
         sa.max_atmosphering_speed, 
         sa.crew, 
         sa.passengers, 
         sa.cargo_capacity, 
         sa.consumables, 
         sa.hyperdrive_rating, 
         sa.mglt
  FROM starship s
  LEFT JOIN starship_manufacturer sm ON s.id = sm.starship_id  -- Tabla pivote para relacionar naves con fabricantes
  LEFT JOIN manufacturer m ON sm.manufacturer_id = m.id
  LEFT JOIN starship_class sc ON s.starship_class_id = sc.id
  LEFT JOIN starship_attributes sa ON s.id = sa.starship_id
  WHERE s.id IN (:nave1_id, :nave2_id)
  GROUP BY s.id, s.name, s.model, sc.name, sa.cost_in_credits, sa.length, sa.max_atmosphering_speed, sa.crew, 
           sa.passengers, sa.cargo_capacity, sa.consumables, sa.hyperdrive_rating, sa.mglt
  ");

    $stmt->bindParam(':nave1_id', $nave1_id, PDO::PARAM_INT);
    $stmt->bindParam(':nave2_id', $nave2_id, PDO::PARAM_INT);

    $stmt->execute();
    $starships = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($starships) < 2) {
        echo json_encode(["error" => "No se encontraron datos completos para ambas naves."]);
        exit;
    }

    // Devolver los datos de las naves como JSON
    echo json_encode($starships);

} catch (PDOException $e) {
    echo json_encode(["error" => "Error al conectarse a la base de datos: " . $e->getMessage()]);
}
?>
