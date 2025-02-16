<?php
session_start(); // Iniciar sesión para manejar mensajes de éxito/message

// Verificar si la solicitud es POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['message'] = "Acceso no permitido.";
    header("Location: ../views/starship.php");
    exit;
}

// Conectar a la base de datos SQLite
try {
    $pdo = new PDO('sqlite:../swapi.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA foreign_keys = ON;");
} catch (PDOException $e) {
    $_SESSION['message'] = "message de conexión: " . $e->getMessage();
    // Mantener el modal abierto
    header("Location: ../views/starship.php");
    exit;
}

// Recibir y sanitizar datos del formulario
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$name = trim($_POST['name'] ?? '');
$model = trim($_POST['model'] ?? '');
$manufacturer_ids = $_POST['manufacturer'] ?? []; 
$starship_class_id = filter_input(INPUT_POST, 'starship_class', FILTER_VALIDATE_INT);
$cost_in_credits = filter_input(INPUT_POST, 'cost_in_credits', FILTER_VALIDATE_INT);
$length = filter_input(INPUT_POST, 'length', FILTER_VALIDATE_FLOAT);
$max_speed = filter_input(INPUT_POST, 'max_atmosphering_speed', FILTER_VALIDATE_INT);
$crew = filter_input(INPUT_POST, 'crew', FILTER_VALIDATE_INT);
$passengers = filter_input(INPUT_POST, 'passengers', FILTER_VALIDATE_INT);
$cargo_capacity = filter_input(INPUT_POST, 'cargo_capacity', FILTER_VALIDATE_INT);
$consumables = trim($_POST['consumables'] ?? '');
$hyperdrive_rating = filter_input(INPUT_POST, 'hyperdrive_rating', FILTER_VALIDATE_FLOAT);
$mglt = filter_input(INPUT_POST, 'mglt', FILTER_VALIDATE_INT);

// Validar datos requeridos
// if (!$id || empty($name) || empty($model) || empty($manufacturer_ids) || !$starship_class_id) {
//     $_SESSION['message'] = "Los campos obligatorios no pueden estar vacíos.";
//     header("Location: ../views/starship.php?id=$id");
//     exit;
// }

try {
    $pdo->beginTransaction(); // Iniciar transacción

    // Verificar que starship_class_id existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM starship_class WHERE id = ?");

        $stmt->execute([$starship_class_id]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception("La clase de nave seleccionada no es válida.");
    }

    // Actualizar `starship`
    $stmt = $pdo->prepare("UPDATE starship 
                           SET name = :name, model = :model, starship_class_id = :starship_class, updated_at = CURRENT_TIMESTAMP
                           WHERE id = :id");
    $stmt->execute([
        ':id' => $id,
        ':name' => $name,
        ':model' => $model,
        ':starship_class' => $starship_class_id
    ]);

    // Actualizar `starship_attributes`
    $stmt = $pdo->prepare("UPDATE starship_attributes 
                           SET cost_in_credits = :cost, length = :length, 
                               max_atmosphering_speed = :max_speed, crew = :crew, 
                               passengers = :passengers, cargo_capacity = :cargo_capacity, 
                               consumables = :consumables, hyperdrive_rating = :hyperdrive, mglt = :mglt
                           WHERE starship_id = :id");
    $stmt->execute([
        ':id' => $id,
        ':cost' => $cost_in_credits,
        ':length' => $length,
        ':max_speed' => $max_speed,
        ':crew' => $crew,
        ':passengers' => $passengers,
        ':cargo_capacity' => $cargo_capacity,
        ':consumables' => $consumables,
        ':hyperdrive' => $hyperdrive_rating,
        ':mglt' => $mglt
    ]);

    // Eliminar fabricantes anteriores de `starship_manufacturer`
    $stmt = $pdo->prepare("DELETE FROM starship_manufacturer WHERE starship_id = :id");
    $stmt->execute([':id' => $id]);

    // Insertar los nuevos fabricantes seleccionados
    $stmt = $pdo->prepare("INSERT INTO starship_manufacturer (starship_id, manufacturer_id) VALUES (:starship_id, :manufacturer_id)");
    foreach ($manufacturer_ids as $manufacturer_id) {
        $stmt->execute([
            ':starship_id' => $id,
            ':manufacturer_id' => $manufacturer_id
        ]);
    }

    $pdo->commit(); // Confirmar cambios

    $_SESSION['message'] = "Nave actualizada correctamente.";
    header("Location: ../views/starship.php?id=$id");
     exit;
} catch (Exception $e) {
    $pdo->rollBack(); // Revertir cambios en caso de message
    $_SESSION['message'] = "message al actualizar la nave: " . $e->getMessage();
    // Mostrar modal
    header("Location: ../views/starship.php?id=$id");
    exit;
}
?>
