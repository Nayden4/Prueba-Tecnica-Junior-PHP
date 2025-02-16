<?php

include "functions.php";

$total = 75;  // Número total de imágenes a obtener
$n = 1;  // Contador

// Abre el archivo CSV para agregar la información
openCsv();

while ($n <= $total) {
    $imageInfo = getRandomImage();  // Obtiene la información de la imagen
    writeCsv($imageInfo);  // Escribe la información de la imagen en el CSV

    $n++;
}

// Cierra el archivo CSV
closeCsv();