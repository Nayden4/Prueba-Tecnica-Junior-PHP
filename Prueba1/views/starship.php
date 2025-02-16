<?php
// Iniciar la sesión
session_start();

// Conectar a la base de datos SQLite
try {
    $pdo = new PDO('sqlite:../swapi.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener las naves y sus fabricantes desde la base de datos
    $stmt = $pdo->prepare("
        SELECT s.id, s.name, s.model, 
               GROUP_CONCAT(m.name) AS manufacturer, 
               sc.name AS starship_class
        FROM starship s
        LEFT JOIN starship_class sc ON s.starship_class_id = sc.id
        LEFT JOIN starship_manufacturer sm ON s.id = sm.starship_id
        LEFT JOIN manufacturer m ON sm.manufacturer_id = m.id
        GROUP BY s.id
    ");
    $stmt->execute();
    $starships = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // En caso de error con la base de datos
    header('Location: starship_import.php'); // Redirigir a la página de carga de CSV
    exit;
}


?>


<?php include '../partials/header.php'; ?>

<div class="container main mt-5">

    <h1 class="text-center pt-4">Lista de Naves</h1>


    <!-- Mensaje de Delete -->
    <?php if (isset($_SESSION['messageDelete'])): ?>
        <div class="alert alert-primary">
            <?php echo $_SESSION['messageDelete']; ?>
        </div>

        <?php unset($_SESSION['messageDelete']); ?>
    <?php endif; ?>

    <!-- Mensaje de New -->

    <?php if (isset($_SESSION['messageNew'])): ?>

        <div class="alert alert-primary">
            <?php echo $_SESSION['messageNew']; ?>
        </div>
        <?php unset($_SESSION['messageNew']); ?>

    <?php endif; ?>

    <!-- Tabla de naves -->
    <div class="m-4 p-4">
        <table class="table table-hover ">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Modelo</th>
                    <th>Fabricante(s)</th>
                    <th>Clase</th>
                    <th colspan="2">Acciones</th>
                </tr>
            </thead>
            <tbody class="text-white">
                <?php foreach ($starships as $starship): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($starship['id']); ?></td>
                        <td><?php echo htmlspecialchars($starship['name']); ?></td>
                        <td><?php echo htmlspecialchars($starship['model']); ?></td>
                        <td><?php echo htmlspecialchars($starship['manufacturer']); ?></td>
                        <td><?php echo htmlspecialchars($starship['starship_class']); ?></td>
                        <td>
                            <!-- Botón Editar que abre el modal -->
                            <button type="button" class="btn btn-custom btn-sm m-1" data-bs-toggle="modal"
                                data-bs-target="#editModal<?php echo $starship['id']; ?>"
                                data-id="<?php echo $starship['id']; ?>">
                                Editar
                            </button>
                        </td>
                        <td> <!-- Botón Eliminar con modal de confirmación -->
                            <button type="button" class="btn btn-danger btn-sm m-1" data-bs-toggle="modal"
                                data-bs-target="#deleteModal<?php echo $starship['id']; ?>">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                    <!-- Aquí se incluirá el modal de edición y confirmación -->
                    <?php include 'starship_edit.php'; ?>
                    <?php include 'starship_delete.php'; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Modal para crear una nueva nave  -->
    <?php include 'starship_new.php'; ?>

    <div class="d-flex justify-content-center pb-5">
        <button type="button" class="btn btn-custom btn-sm" data-bs-toggle="modal" data-bs-target="#newModal">
            Crear una nueva nave
        </button>
    </div>
</div>


<!-- Mostrar el modal despues de Editar -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalId = '<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>';

        // Verificar si modalId tiene valor
        if (modalId) {
            let modalElement;

            // Caso específico cuando modalId es "new"
            if (modalId === 'new') {
                modalElement = document.getElementById('newModal');
            } else {
                modalElement = document.getElementById('editModal' + modalId);
            }

            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        }
    });

    $(document).ready(function () {

        // Aplicar Select2 solo a los <select> con ID 'manufacturer' dentro de los modales
        $('body').on('shown.bs.modal', function (e) {
            $(e.target).find('select#manufacturer').select2({
                width: '100%',
                dropdownParent: $(e.target) // Mantiene el menú dentro del modal
            });
        });

    });
</script>

</script>

<!-- Borrar Mensaje -->
<?php unset($_SESSION['message']); ?>


<?php include '../partials/footer.php'; ?>