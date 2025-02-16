<?php 
include '../partials/header.php'; 
session_start(); // Asegúrate de iniciar la sesión
?>

<div class="main container text-center mt-5 p-5">
    <h1>Sube un archivo CSV</h1>
    
    <!-- Verificar si hay un mensaje en la sesión y mostrarlo -->
    <?php if (isset($_SESSION['messageCSV'])): ?>
        <div class="alert alert-info mt-3">
            <?php 
            // Mostrar el mensaje y luego eliminarlo de la sesión
            echo $_SESSION['messageCSV']; 
            unset($_SESSION['messageCSV']); 
            ?>
        </div>
    <?php endif; ?>
    
    <div class="d-grid gap-2 d-sm-flex justify-content-center m-5">
        
        <!-- Formulario para cargar un archivo CSV -->
        <form action="../functions/starship_import.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="csvFile" class="form-label">Selecciona un archivo CSV</label>
                <input type="file" class="form-control" id="csvFile" name="csvFile" accept=".csv" required>
            </div>
            <button type="submit" class="btn btn-custom btn-lg mt-5">Subir CSV</button>
        </form>
    </div>

</div>

<?php include '../partials/footer.php'; ?>
