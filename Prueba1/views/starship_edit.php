<?php

// Verificar si existe el ID
if (isset($starship['id'])) {
    $starshipId = $starship['id'];
} else {
    $starshipId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

}

// Conectar a la base de datos SQLite para obtener los detalles de la nave
try {
    $pdo = new PDO('sqlite:../swapi.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener los detalles de la nave por ID junto con los atributos
    $stmt = $pdo->prepare("SELECT s.id, s.name, s.model, m.name AS manufacturer, sc.name AS starship_class, 
                                      s.created, s.updated_at, s.url, sa.cost_in_credits, sa.length, sa.max_atmosphering_speed, 
                                      sa.crew, sa.passengers, sa.cargo_capacity, sa.consumables, sa.hyperdrive_rating, sa.mglt
                               FROM starship s
                               LEFT JOIN starship_manufacturer sm ON s.id = sm.starship_id
                               LEFT JOIN manufacturer m ON sm.manufacturer_id = m.id
                               LEFT JOIN starship_class sc ON s.starship_class_id = sc.id
                               LEFT JOIN starship_attributes sa ON s.id = sa.starship_id
                               WHERE s.id = :id");
    $stmt->bindParam(':id', $starshipId, PDO::PARAM_INT);
    $stmt->execute();
    $starshipDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    // Obtener todos los fabricantes disponibles
    $stmtManufacturers = $pdo->prepare("SELECT id, name FROM manufacturer");
    $stmtManufacturers->execute();
    $manufacturers = $stmtManufacturers->fetchAll(PDO::FETCH_ASSOC);

    // Obtener todas las clases de nave disponibles
    $stmtClasses = $pdo->prepare("SELECT id, name FROM starship_class");
    $stmtClasses->execute();
    $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

    // Obtener los fabricantes asociados a esta nave
    $selectedManufacturers = [];
    $stmtManufacturersAssoc = $pdo->prepare("SELECT m.id
                                          FROM manufacturer m
                                          JOIN starship_manufacturer sm ON m.id = sm.manufacturer_id
                                          WHERE sm.starship_id = :id");
    $stmtManufacturersAssoc->bindParam(':id', $starshipId, PDO::PARAM_INT);
    $stmtManufacturersAssoc->execute();
    $manufacturersAssoc = $stmtManufacturersAssoc->fetchAll(PDO::FETCH_ASSOC);

    // Llenar el array de fabricantes seleccionados
    foreach ($manufacturersAssoc as $assoc) {
        $selectedManufacturers[] = $assoc['id'];
    }

} catch (PDOException $e) {
    // Manejo de errores si no se puede obtener la información
    echo "Error al obtener los detalles de la nave: " . $e->getMessage();
}

?>


<!-- Modal para editar la nave -->
<div class="modal fade" id="editModal<?php echo $starshipId; ?>"  role="dialog"
    aria-labelledby="editModalLabel<?php echo $starshipId; ?>" aria-hidden="true">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?php echo $starshipId; ?>">Editar Nave:
                    <?php echo htmlspecialchars($starshipDetails['name']); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($_SESSION['message'])): ?>


                    <div class="alert alert-primary">
                        <?php echo $_SESSION['message']; ?>
                    </div>


                <?php endif; ?>

                <!-- Formulario para editar los datos de la nave -->
                <form action="../functions/starship_edit.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $starshipDetails['id']; ?>">
                    <h3 class="mt-3">Datos principales</h3>
                    <hr class="hr">
                    <div class="row">
                        <!-- Nombre -->
                        <div class="col-6 mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="<?php echo htmlspecialchars($starshipDetails['name']); ?>" required>
                        </div>

                        <!-- Modelo -->
                        <div class="col-6 mb-3">
                            <label for="model" class="form-label">Modelo</label>
                            <input type="text" class="form-control" id="model" name="model"
                                value="<?php echo htmlspecialchars($starshipDetails['model']); ?>" required>
                        </div>
                    </div>

                    <h3 class="mt-3">Datos del modelo</h3>
                    <hr class="hr">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="manufacturer" class="form-label ">Fabricante</label>
                            <select class="form-control select2" id="manufacturer" name="manufacturer[]" multiple="multiple"
                                required>
                                <option value="">Seleccione un fabricante</option>
                                <?php foreach ($manufacturers as $manufacturer): ?>
                                    <option value="<?php echo $manufacturer['id']; ?>" <?php
                                       // Verificar si el fabricante está asociado con la nave actual
                                       if (in_array($manufacturer['id'], $selectedManufacturers)) {
                                           echo 'selected';
                                       }
                                       ?>>
                    <?php echo htmlspecialchars($manufacturer['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Clase de nave (select) -->
                        <div class="col-6 mb-3">
                            <label for="starship_class" class="form-label ">Clase</label>
                            <select class="form-control select2" id="starship_class" name="starship_class" required>
                                <option value="">Seleccione una clase</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>" <?php echo $starshipDetails['starship_class'] == $class['name'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($class['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <!-- Atributos adicionales de la nave -->
                    <h3 class="mt-3">Especificaciones</h3>
                    <hr class="hr">
                    <div class="row">

                        <div class="col-6 mb-3">
                            <label for="cost_in_credits" class="form-label">Costo en créditos</label>
                            <input type="text" class="form-control" id="cost_in_credits" name="cost_in_credits"
                                value="<?php echo htmlspecialchars($starshipDetails['cost_in_credits']); ?>">
                        </div>

                        <div class="col-6 mb-3">
                            <label for="length" class="form-label">Longitud</label>
                            <input type="text" class="form-control" id="length" name="length"
                                value="<?php echo htmlspecialchars($starshipDetails['length']); ?>">
                        </div>

                        <div class="col-6 mb-3">
                            <label for="max_atmosphering_speed" class="form-label">Velocidad máxima en atmósfera</label>
                            <input type="text" class="form-control" id="max_atmosphering_speed"
                                name="max_atmosphering_speed"
                                value="<?php echo htmlspecialchars($starshipDetails['max_atmosphering_speed']); ?>">
                        </div>

                        <div class="col-6 mb-3">
                            <label for="crew" class="form-label">Tripulación</label>
                            <input type="text" class="form-control" id="crew" name="crew"
                                value="<?php echo htmlspecialchars($starshipDetails['crew']); ?>">
                        </div>

                        <div class="col-6 mb-3">
                            <label for="passengers" class="form-label">Pasajeros</label>
                            <input type="text" class="form-control" id="passengers" name="passengers"
                                value="<?php echo htmlspecialchars($starshipDetails['passengers']); ?>">
                        </div>

                        <div class="col-6 mb-3">
                            <label for="cargo_capacity" class="form-label">Capacidad de carga</label>
                            <input type="text" class="form-control" id="cargo_capacity" name="cargo_capacity"
                                value="<?php echo htmlspecialchars($starshipDetails['cargo_capacity']); ?>">
                        </div>

                        <div class="col-6 mb-3">
                            <label for="consumables" class="form-label">Consumibles</label>
                            <input type="text" class="form-control" id="consumables" name="consumables"
                                value="<?php echo htmlspecialchars($starshipDetails['consumables']); ?>">
                        </div>

                        <div class="col-6 mb-3">
                            <label for="hyperdrive_rating" class="form-label">Clasificación de hipermotor</label>
                            <input type="text" class="form-control" id="hyperdrive_rating" name="hyperdrive_rating"
                                value="<?php echo htmlspecialchars($starshipDetails['hyperdrive_rating']); ?>">
                        </div>

                        <div class="col-6 mb-3">
                            <label for="mglt" class="form-label">MGTL</label>
                            <input type="text" class="form-control" id="mglt" name="mglt"
                                value="<?php echo htmlspecialchars($starshipDetails['mglt']); ?>">
                        </div>
                    </div>
                    <!-- Atributos de fecha (solo lectura) -->
                    <hr class="hr">
                    <h3 class="mt-3">Datos adicionales</h3>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="created" class="form-label">Fecha de Creación</label>
                            <input type="text" class="form-control" id="created" name="created"
                                value="<?php echo htmlspecialchars($starshipDetails['created']); ?>" disabled>
                        </div>

                        <div class="col-6 mb-3">
                            <label for="updated" class="form-label">Fecha de Modificación</label>
                            <input type="text" class="form-control" id="updated" name="updated"
                                value="<?php echo htmlspecialchars($starshipDetails['updated_at']); ?>" disabled>
                        </div>
                    </div>
                    <!-- Botón para guardar cambios -->
                    <div class="m-3">
                        <button type="submit" class="btn btn-custom w-100">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

