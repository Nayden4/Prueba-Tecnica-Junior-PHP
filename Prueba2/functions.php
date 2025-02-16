<?php

function getRandomImage()
{
    // Inicializa la solicitud cURL
    $ch = curl_init("https://picsum.photos/200/300");

    // Configura las opciones cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Sigue redirecciones 302
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36"
    ]);
    curl_setopt($ch, CURLOPT_HEADER, true); // Obtener encabezados

    // Ejecuta la solicitud
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $downloadTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME); // Tiempo total de la descarga
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);  // Obtiene la URL después de la redirección

    // Separa los encabezados del cuerpo de la respuesta
    $imageData = substr($response, $headerSize);

    // Cierra la conexión cURL
    curl_close($ch);

    // Si la solicitud fue exitosa (Código HTTP 200)
    if ($httpCode == 200) {

        // Obtener los metadatos de la imagen
        $imageInfo = @getimagesizefromstring($imageData);

        // Organiza la información en un array
        $imageDetails = [
            'imageWidth' => $imageInfo ? $imageInfo[0] : 'No disponible',
            'imageHeight' => $imageInfo ? $imageInfo[1] : 'No disponible',
            'imageType' => $imageInfo ? image_type_to_mime_type($imageInfo[2]) : 'No disponible',
            'imageSizeBytes' => strlen($imageData),
            'downloadTime' => round($downloadTime, 2),
            'finalUrl' => $finalUrl  

        ];

        // Devuelve el array con toda la información
        return $imageDetails;

    } else {
        // Si hubo un error en la solicitud, devuelve el código HTTP
        return [
            'error' => "Error: No se pudo obtener la imagen. Código HTTP: $httpCode"
        ];
    }
}


function openCsv() {
    global $csvFile;
    $csvFile = fopen('dataimages.csv', 'w');  // Abre el archivo para escribir

    // Escribir encabezados en el archivo CSV
    $headers = ['imageWidth', 'imageHeight', 'imageType', 'imageSizeBytes', 'downloadTime','finalUrl'];
    fputcsv($csvFile, $headers, ';');  // Cambia el delimitador a punto y coma (;)
}

// Función para escribir los datos en el archivo CSV
function writeCsv($imageInfo) {
    global $csvFile;

    // Escribir los datos de la imagen en una nueva línea
    fputcsv($csvFile, $imageInfo, ';');  // Cambia el delimitador a punto y coma (;)
}

// Función para cerrar el archivo CSV
function closeCsv() {
    global $csvFile;
    fclose($csvFile);  // Cierra el archivo CSV
}