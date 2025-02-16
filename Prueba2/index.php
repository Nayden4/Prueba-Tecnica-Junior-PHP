<?php

include "functions.php";

$total = 75;  // Número total de imágenes a obtener
$n = 1;  // Contador

// Abre el archivo CSV para agregar la información
echo "Abriendo el archivo CSV...\n";

openCsv();

while ($n <= $total) {

    echo "Obteniendo la imagen $n de $total \n";

    $imageInfo = getRandomImage();  // Obtiene la información de la imagen
    writeCsv($imageInfo);  // Escribe la información de la imagen en el CSV

    $n++;
}

echo "Cerrando el archivo CSV...\n";

// Cierra el archivo CSV
closeCsv();

echo "Proceso completado. Se han procesado $total imágenes.\n";
