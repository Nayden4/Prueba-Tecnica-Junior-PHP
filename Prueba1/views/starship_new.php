<?php


// Conectar a la base de datos SQLite para obtener los detalles de la nave
try {
    $pdo = new PDO('sqlite:../swapi.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    // Obtener todos los fabricantes disponibles
    $stmtManufacturers = $pdo->prepare("SELECT id, name FROM manufacturer");
    $stmtManufacturers->execute();
    $manufacturers = $stmtManufacturers->fetchAll(PDO::FETCH_ASSOC);

    // Obtener todas las clases de nave disponibles
    $stmtClasses = $pdo->prepare("SELECT id, name FROM starship_class");
    $stmtClasses->execute();

    //Lo convierte en array asociativo
    $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Manejo de errores si no se puede obtener la información
    echo "Error al obtener los detalles de la nave: " . $e->getMessage();
}

?>


<!-- Modal para Crear la nave -->
<div class="modal fade" id="newModal" tabindex="-1" aria-labelledby="newModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newModalLabel">Crear Nave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($_SESSION['message'])): ?>


                    <div class="alert alert-primary">
                        <?php echo $_SESSION['message']; ?>
                    </div>


                <?php endif; ?>

                <!-- Formulario para Crear los datos de la nave -->
                <form action="../functions/starship_new.php" method="POST">
                    <input type="hidden" name="id">
                    <h3 class="mt-3">Datos principales</h3>
                    <hr class="hr">
                    <div class="row">
                        <!-- Nombre -->
                        <div class="col-6 mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <!-- Modelo -->
                        <div class="col-6 mb-3">
                            <label for="model" class="form-label">Modelo</label>
                            <input type="text" class="form-control" id="model" name="model" required>
                        </div>
                    </div>

                    <h3 class="mt-3">Datos del modelo</h3>
                    <hr class="hr">
                    <!-- Fabricante (select) -->
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="manufacturer" class="form-label ">Fabricante</label>
                            <select class="form-control select2" id="manufacturer" name="manufacturer[]"
                                multiple="multiple" required>
                                <option value="">Seleccione un fabricante</option>
                                <?php foreach ($manufacturers as $manufacturer): ?>
                                    <option value="<?php echo $manufacturer['id']; ?>">

                                        <?php echo htmlspecialchars($manufacturer['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Clase de nave (select) -->
                        <div class="col-6 mb-3">
                            <label for="starship_class" class="form-label">Clase</label>
                            <select class="form-control" id="starship_class" name="starship_class" required>
                                <option value="">Seleccione una clase</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>">
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
                            <input type="text" class="form-control" id="cost_in_credits" name="cost_in_credits">
                        </div>

                        <div class="col-6 mb-3">
                            <label for="length" class="form-label">Longitud</label>
                            <input type="text" class="form-control" id="length" name="length">
                        </div>

                        <div class="col-6 mb-3">
                            <label for="max_atmosphering_speed" class="form-label">Velocidad máxima en atmósfera</label>
                            <input type="text" class="form-control" id="max_atmosphering_speed"
                                name="max_atmosphering_speed">
                        </div>

                        <div class="col-6 mb-3">
                            <label for="crew" class="form-label">Tripulación</label>
                            <input type="text" class="form-control" id="crew" name="crew">
                        </div>

                        <div class="col-6 mb-3">
                            <label for="passengers" class="form-label">Pasajeros</label>
                            <input type="text" class="form-control" id="passengers" name="passengers">
                        </div>

                        <div class="col-6 mb-3">
                            <label for="cargo_capacity" class="form-label">Capacidad de carga</label>
                            <input type="text" class="form-control" id="cargo_capacity" name="cargo_capacity">
                        </div>

                        <div class="col-6 mb-3">
                            <label for="consumables" class="form-label">Consumibles</label>
                            <input type="text" class="form-control" id="consumables" name="consumables">
                        </div>

                        <div class="col-6 mb-3">
                            <label for="hyperdrive_rating" class="form-label">Clasificación de hipermotor</label>
                            <input type="text" class="form-control" id="hyperdrive_rating" name="hyperdrive_rating">
                        </div>

                        <div class="col-6 mb-3">
                            <label for="mglt" class="form-label">MGTL</label>
                            <input type="text" class="form-control" id="mglt" name="mglt">
                        </div>
                    </div>

                    <!-- Botón para guardar cambios -->
                    <div class="m-3">
                        <button type="submit" class="btn btn-custom w-100">Crear nave</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>