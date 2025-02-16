<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    try {
        // Conectar a la base de datos SQLite
        $pdo = new PDO('sqlite:../swapi.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("PRAGMA foreign_keys = ON;");

        // Validar la entrada
        $starship_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$starship_id) {
            $_SESSION['messageDelete'] = "Error: ID inválido.";
            header("Location: ../views/starship.php");
            exit;
        }

        // Iniciar transacción
        $pdo->beginTransaction();

        // Verificar si la nave existe
        $stmt = $pdo->prepare("SELECT id FROM starship WHERE id = :id");
        $stmt->execute([':id' => $starship_id]);
        if (!$stmt->fetch()) {
            throw new Exception("La nave no existe.");
        }

        // Eliminar relaciones en starship_manufacturer
        $stmt = $pdo->prepare("DELETE FROM starship_manufacturer WHERE starship_id = :id");
        $stmt->execute([':id' => $starship_id]);

        // Eliminar en starship_attributes
        $stmt = $pdo->prepare("DELETE FROM starship_attributes WHERE starship_id = :id");
        $stmt->execute([':id' => $starship_id]);

        // Finalmente, eliminar la nave en starship
        $stmt = $pdo->prepare("DELETE FROM starship WHERE id = :id");
        $stmt->execute([':id' => $starship_id]);

        // Confirmar eliminación
        $pdo->commit();
        $_SESSION['messageDelete'] = "Nave eliminada correctamente.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['messageDelete'] = "Error al eliminar: " . $e->getMessage();
    }
}

// Redirigir de vuelta a la lista de naves
header("Location: ../views/starship.php");
exit;
?>
