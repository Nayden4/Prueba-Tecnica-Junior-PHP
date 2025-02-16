<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    try {
        // Conectar a la base de datos SQLite
        $pdo = new PDO('sqlite:../swapi.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("PRAGMA foreign_keys = ON;");

        // Verificar si la nave existe antes de eliminarla
        $stmt = $pdo->prepare("SELECT * FROM starship WHERE id = :id");
        $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
        $stmt->execute();
        $starship = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$starship) {
            $_SESSION['messageDelete'] = "Error: La nave no existe.";
            header("Location: ../views/starship.php");
            exit;
        }

        // Eliminar primero los registros dependientes en starship_attributes
        $stmt = $pdo->prepare("DELETE FROM starship_attributes WHERE starship_id = :id");
        $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
        $stmt->execute();

        // Ahora eliminar la nave en starship
        $stmt = $pdo->prepare("DELETE FROM starship WHERE id = :id");
        $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['messageDelete'] = "Nave eliminada correctamente.";
    } catch (PDOException $e) {
        $_SESSION['messageDelete'] = "Error al eliminar: " . $e->getMessage();
    }
}

// Redirigir de vuelta a la lista de naves
header("Location: ../views/starship.php");
exit;
?>
