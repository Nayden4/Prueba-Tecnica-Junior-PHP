<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteModal<?php echo $starship['id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar la nave
                <strong><?php echo htmlspecialchars($starship['name']); ?></strong>?
          
                <form action="../functions/starship_delete.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $starship['id']; ?>">
                    <button type="submit" class="btn btn-danger mt-3">Eliminar</button>
                </form>
                <button type="button" class="btn btn-secondary mt-3" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>