<?php
session_start(); // Iniciar sesión para manejar mensajes de éxito/error

// Verificar si la solicitud es POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['message'] = "Acceso no permitido.";
    header("Location: ../views/starship.php?id=new");
    exit;
}

// Conectar a la base de datos SQLite
try {
    $pdo = new PDO('sqlite:../swapi.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA foreign_keys = ON;");
} catch (PDOException $e) {
    $_SESSION['message'] = "Error de conexión: " . $e->getMessage();
    header("Location: ../views/starship.php?id=new");
    exit;
}

// Recibir y sanitizar datos del formulario
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
if (empty($name) || empty($model) || empty($manufacturer_ids) || !$starship_class_id) {
    $_SESSION['message'] = "Los campos obligatorios no pueden estar vacíos.";
    header("Location: ../views/starship.php?id=new");
    exit;
}

try {
    $pdo->beginTransaction(); // Iniciar transacción

    // Verificar que starship_class_id existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM starship_class WHERE id = ?");
    $stmt->execute([$starship_class_id]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception("La clase de nave seleccionada no es válida.");
    }

    // Insertar nueva nave en la tabla `starship`
    $stmt = $pdo->prepare("INSERT INTO starship (name, model, starship_class_id, created_at, updated_at,created,edited)
                           VALUES (:name, :model, :starship_class_id, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
    $stmt->execute([
        ':name' => $name,
        ':model' => $model,
        ':starship_class_id' => $starship_class_id
    ]);
    
    // Obtener el ID de la nave recién creada
    $starship_id = $pdo->lastInsertId();

    // Insertar atributos de la nave en `starship_attributes`
    $stmt = $pdo->prepare("INSERT INTO starship_attributes (starship_id, cost_in_credits, length, max_atmosphering_speed, crew, passengers, cargo_capacity, consumables, hyperdrive_rating, mglt)
                           VALUES (:starship_id, :cost_in_credits, :length, :max_speed, :crew, :passengers, :cargo_capacity, :consumables, :hyperdrive_rating, :mglt)");
    $stmt->execute([
        ':starship_id' => $starship_id,
        ':cost_in_credits' => $cost_in_credits,
        ':length' => $length,
        ':max_speed' => $max_speed,
        ':crew' => $crew,
        ':passengers' => $passengers,
        ':cargo_capacity' => $cargo_capacity,
        ':consumables' => $consumables,
        ':hyperdrive_rating' => $hyperdrive_rating,
        ':mglt' => $mglt
    ]);

    // Insertar los fabricantes seleccionados en `starship_manufacturer`
    $stmt = $pdo->prepare("INSERT INTO starship_manufacturer (starship_id, manufacturer_id) VALUES (:starship_id, :manufacturer_id)");
    foreach ($manufacturer_ids as $manufacturer_id) {
        $stmt->execute([
            ':starship_id' => $starship_id,
            ':manufacturer_id' => $manufacturer_id
        ]);
    }

    $pdo->commit(); // Confirmar cambios

    $_SESSION['messageNew'] = "Nave creada correctamente.";
    header("Location: ../views/starship.php?");
    exit;

} catch (Exception $e) {
    $pdo->rollBack(); // Revertir cambios en caso de error
    $_SESSION['message'] = "Error al crear la nave: " . $e->getMessage();
    header("Location: ../views/starship.php?id=new");
    exit;
}
?>
