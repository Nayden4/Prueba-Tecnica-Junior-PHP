<?php include '../partials/header.php'; ?>

<?php
// Conectar a la base de datos SQLite
try {
    $pdo = new PDO('sqlite:../swapi.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener las naves desde la base de datos
    $stmt = $pdo->prepare("SELECT s.id, s.name FROM starship s");
    $stmt->execute();
    $starships = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['message'] = "Error: " . $e->getMessage();
    header('Location: starship_import.php');
    exit;
}
?>

<div class="main container text-center mt-5 p-5">
    <h2>Comparación de Naves Espaciales</h2>

    <!-- Formulario para seleccionar naves -->
    <div class="row">
        <div class="col-md-6">
            <label for="nave1" class="form-label">Seleccione la primera nave</label>
            <select class="form-control" id="nave1" name="nave1" required onchange="compareStarships()">
                <option value="">Seleccione una nave</option>
                <?php foreach ($starships as $starship): ?>
                    <option value="<?php echo htmlspecialchars($starship['id']); ?>">
                        <?php echo htmlspecialchars($starship['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label for="nave2" class="form-label">Seleccione la segunda nave</label>
            <select class="form-control" id="nave2" name="nave2" required onchange="compareStarships()">
                <option value="">Seleccione una nave</option>
                <?php foreach ($starships as $starship): ?>
                    <option value="<?php echo htmlspecialchars($starship['id']); ?>">
                        <?php echo htmlspecialchars($starship['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Tabla de Comparación -->
    <div id="comparisonResult" class="mt-5" style="display: none;">
        <h3>Resultados de la Comparación</h3>
        <table class="table text-white">
            <thead class="table-dark">
                <tr>
                    <th id="nave1-name" class="comparation">Nave 1</th>
                    <th class="comparation">Atributo</th>
                    <th class="comparation" id="nave2-name">Nave 2</th>
                </tr>
            </thead>
            <tbody id="comparisonTableBody">
                <!-- Aquí se llenarán los datos dinámicamente -->
            </tbody>
        </table>
    </div>
</div>

<script>
function compareStarships() {
    const nave1 = document.getElementById('nave1');
    const nave2 = document.getElementById('nave2');

    // Verificar que no sean la misma nave
    if (nave1.value === nave2.value && nave1.value !== "") {
        alert("No puedes seleccionar la misma nave dos veces.");
        nave2.value = ""; // Reinicia la segunda selección
        return;
    }

   // Si estan seleccionadas las dos naves obtener los atributos
    if (nave1.value && nave2.value) {
        fetchStarshipAttributes(nave1.value, nave2.value);
    } else {
        document.getElementById('comparisonResult').style.display = 'none';
    }
}



function fetchStarshipAttributes(nave1Id, nave2Id) {
    const url = `../functions/starship_compare.php?nave1_id=${nave1Id}&nave2_id=${nave2Id}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                // Comparar atributos
                displayComparison(data[0], data[1]);
            }
        })
        .catch(error => {
            console.error('Error al obtener los datos de la nave:', error);
        });
}

function displayComparison(nave1, nave2) {
    const tableBody = document.getElementById('comparisonTableBody');
    tableBody.innerHTML = '';

    // Insertar el nombre de las naves en la tabla
    document.getElementById('nave1-name').textContent = nave1.name;
    document.getElementById('nave2-name').textContent = nave2.name;

    // Creamos un array de objetos 
    const attributes = [
        { name: 'Fabricante', value1: nave1.manufacturer, value2: nave2.manufacturer },
        { name: 'Clase', value1: nave1.starship_class, value2: nave2.starship_class },
        { name: 'Costo (créditos)', value1: nave1.cost_in_credits, value2: nave2.cost_in_credits },
        { name: 'Longitud', value1: nave1.length, value2: nave2.length },
        { name: 'Velocidad Máxima', value1: nave1.max_atmosphering_speed, value2: nave2.max_atmosphering_speed },
        { name: 'Tripulación', value1: nave1.crew, value2: nave2.crew },
        { name: 'Pasajeros', value1: nave1.passengers, value2: nave2.passengers },
        { name: 'Capacidad de Carga', value1: nave1.cargo_capacity, value2: nave2.cargo_capacity },
        { name: 'Hiperimpulsor', value1: nave1.hyperdrive_rating, value2: nave2.hyperdrive_rating },
        { name: 'MGTL', value1: nave1.mglt, value2: nave2.mglt }
    ];

    attributes.forEach(attr => {
        const row = document.createElement('tr');

        // Color por defecto
        let color1 = 'white';
        let color2 = 'white';

        // Si ambos valores son numéricos, aplicar colores de comparación
        if (!isNaN(parseFloat(attr.value1)) && !isNaN(parseFloat(attr.value2))) {
            if (parseFloat(attr.value1) > parseFloat(attr.value2)) {
                color1 = 'green';
                color2 = 'red';
            } else if (parseFloat(attr.value1) < parseFloat(attr.value2)) {
                color1 = 'red';
                color2 = 'green';
            } else if (parseFloat(attr.value1) == parseFloat(attr.value2)){
                color1 = 'orange';
                color2 = 'orange';
            }
        }

        // Definir fila
        row.innerHTML = `
            <td style="color:${color1}; font-weight:bold;">${attr.value1}</td>
            <td>${attr.name}</td>
            <td style="color:${color2}; font-weight:bold;">${attr.value2}</td>
        `;

        // Insertar fila
        tableBody.appendChild(row);
    });

    document.getElementById('comparisonResult').style.display = 'block';
}

</script>

<?php include '../partials/footer.php'; ?>
