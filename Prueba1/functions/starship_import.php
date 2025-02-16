<?php
// Iniciar la sesión
session_start();

try {
    // Crear una conexión con la base de datos SQLite
    $pdo = new PDO('sqlite:../swapi.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("PRAGMA foreign_keys = ON;");

    // Crear las tablas si no existen
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS manufacturer (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS starship_class (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS starship (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            model TEXT NOT NULL,
            starship_class_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            created TEXT,
            edited TEXT,
            url TEXT,
            FOREIGN KEY (starship_class_id) REFERENCES starship_class(id)
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS starship_attributes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            starship_id INTEGER UNIQUE,
            cost_in_credits INTEGER,
            length FLOAT,
            max_atmosphering_speed INTEGER,
            crew INTEGER,
            passengers INTEGER,
            cargo_capacity INTEGER,
            consumables TEXT,
            hyperdrive_rating FLOAT,
            mglt INTEGER,
            FOREIGN KEY (starship_id) REFERENCES starship(id)
        );
    ");
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS starship_manufacturer (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        manufacturer_id INTEGER NOT NULL,
        starship_id INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (manufacturer_id) REFERENCES manufacturer(id),
        FOREIGN KEY (starship_id) REFERENCES starship(id)
    );
    ");
    // Verificar si el archivo CSV fue subido
    if (isset($_FILES['csvFile']) && $_FILES['csvFile']['error'] == 0) {

        // Obtener el contenido del archivo CSV
        $file = fopen($_FILES['csvFile']['tmp_name'], 'r');

        // Leer la primera línea para obtener los encabezados
        $headers = fgetcsv($file, 1000, ";");

        $headers_map = array(
            'results/name' => 'name',
            'results/model' => 'model',
            'results/manufacturer' => 'manufacturer',
            'results/cost_in_credits' => 'cost_in_credits',
            'results/length' => 'length',
            'results/max_atmosphering_speed' => 'max_atmosphering_speed',
            'results/crew' => 'crew',
            'results/passengers' => 'passengers',
            'results/cargo_capacity' => 'cargo_capacity',
            'results/consumables' => 'consumables',
            'results/hyperdrive_rating' => 'hyperdrive_rating',
            'results/MGLT' => 'mglt',
            'results/starship_class' => 'starship_class',
            'results/created' => 'created',
            'results/edited' => 'edited',
            'results/url' => 'url'
        );

        // Procesar el archivo CSV línea por línea
        while (($data = fgetcsv($file, 1000, ";")) !== FALSE) {
            // Mapear los datos de acuerdo con los encabezados
            $values = array_combine($headers, $data);

            $name = $values['results/name'];
            $model = $values['results/model'];
            $manufacturers = explode(",", $values['results/manufacturer']);
            $starship_class = $values['results/starship_class'];
            $cost_in_credits = $values['results/cost_in_credits'];
            $length = $values['results/length'];
            $max_atmosphering_speed = $values['results/max_atmosphering_speed'];
            $crew = $values['results/crew'];
            $passengers = $values['results/passengers'];
            $cargo_capacity = $values['results/cargo_capacity'];
            $consumables = $values['results/consumables'];
            $hyperdrive_rating = $values['results/hyperdrive_rating'];
            $mglt = $values['results/MGLT'];
            $created = $values['results/created'];
            $edited = $values['results/edited'];
            $url = $values['results/url'];

            if (isset($name) && !empty($name)) {
                // Extraer solo los números de max_atmosphering_speed
                $max_atmosphering_speed = isset($max_atmosphering_speed) ? preg_replace('/\D/', '', $max_atmosphering_speed) : null;

                // Si después de limpiar la variable queda vacía, establece un valor null
                $max_atmosphering_speed = $max_atmosphering_speed !== '' ? (int) $max_atmosphering_speed : null;


                // Insertar clase de nave si no existe
                $stmt = $pdo->prepare("INSERT OR IGNORE INTO starship_class (name) VALUES (?)");
                $stmt->execute([$starship_class]);

                // Insertar nave 
                $stmt = $pdo->prepare("INSERT INTO starship (name, model, starship_class_id, created, edited, url) 
                                    VALUES (?, ?, 
                                    (SELECT id FROM starship_class WHERE name = ?), ?, ?, ?)");
                $stmt->execute([$name, $model, $starship_class, $created, $edited, $url]);

                //Guardar la id
                $starship_id = $pdo->lastInsertId();

                // Insertar los fabricantes y relaciones
                foreach ($manufacturers as $manufacturer) {

                    // Verificar si el fabricante ya existe en la base de datos
                    $stmt = $pdo->prepare("SELECT id FROM manufacturer WHERE name = ?");
                    $stmt->execute([$manufacturer]);
                    $manufacturer_id = $stmt->fetchColumn();

                    // Si no existe, insertarlo
                    if (!$manufacturer_id) {
                        $stmt = $pdo->prepare("INSERT INTO manufacturer (name) VALUES (?)");
                        $stmt->execute([$manufacturer]);
                        $manufacturer_id = $pdo->lastInsertId();
                    }

                    // Insertar la relación en la tabla de relación starship_manufacturer
                    $stmt = $pdo->prepare("INSERT INTO starship_manufacturer (manufacturer_id, starship_id) 
                                   VALUES (?, ?)");
                    $stmt->execute([$manufacturer_id, $starship_id]);
                }

                // Insertar atributos de la nave
                $stmt = $pdo->prepare("INSERT INTO starship_attributes (starship_id, cost_in_credits, length, max_atmosphering_speed, crew, passengers, cargo_capacity, consumables, hyperdrive_rating, mglt) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $starship_id,
                    $cost_in_credits,
                    $length,
                    $max_atmosphering_speed,
                    $crew,
                    $passengers,
                    $cargo_capacity,
                    $consumables,
                    $hyperdrive_rating,
                    $mglt
                ]);
            }
        }

        fclose($file);

        // Establecer mensaje de éxito en la sesión
        $_SESSION['messageCSV'] = "Datos del CSV insertados correctamente!";
        header('Location: ../views/starship_import.php'); // Redirigir a la página de carga de CSV
        exit;

    } else {
        // Establecer mensaje de error en la sesión
        $_SESSION['messageCSV'] = "Error al subir el archivo CSV.";
        header('Location: ../views/starship_import.php'); // Redirigir a la página de carga de CSV
        exit;
    }
} catch (PDOException $e) {
    // En caso de error con la base de datos
    $_SESSION['messageCSV'] = "Error: " . $e->getMessage();
    header('Location: ../views/starship_import.php'); // Redirigir a la página de carga de CSV
    exit;
}
?>