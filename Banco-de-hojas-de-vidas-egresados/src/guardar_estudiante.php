<?php
// Configuración de conexión
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "banco_hojas_vida"; // ✅ Base de datos correcta

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    http_response_code(500);
    exit("Error de conexión a la base de datos.");
}
$conn->set_charset('utf8mb4');

// Función para sanitizar entradas
function limpiar($valor) {
    return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
}

$campos = [
    'nombre_apellido', 'anio_programa', 'cedula', 'referencias'
];
$datos = [];
foreach ($campos as $campo) {
    $datos[$campo] = isset($_POST[$campo]) ? limpiar($_POST[$campo]) : '';
}

// Validación básica de campos obligatorios
$faltantes = [];
foreach ($campos as $obligatorio) {
    if (empty($datos[$obligatorio])) {
        $faltantes[] = $obligatorio;
    }
}
if (count($faltantes) > 0) {
    http_response_code(400);
    exit("Faltan campos obligatorios: " . implode(', ', $faltantes));
}


// Procesar archivo adjunto solo si se envía
$url_archivo = null;
if (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] === UPLOAD_ERR_OK) {
    $permitidos = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    $mime = mime_content_type($_FILES['adjunto']['tmp_name']);
    if (!in_array($mime, $permitidos)) {
        http_response_code(400);
        exit('Solo se permiten archivos PDF, DOC o DOCX.');
    }
    // Subir archivo a Cloudinary
    $cloud_name = 'dmmzyjhcv';
    $api_key = '795674717346771';
    $api_secret = 'mI9iON7saEmB6tq7JmWec_VczAg';
    $upload_url = "https://api.cloudinary.com/v1_1/$cloud_name/auto/upload";
    $file_tmp = $_FILES['adjunto']['tmp_name'];
    $file_name = $_FILES['adjunto']['name'];
    $timestamp = time();
    $params = [
        'timestamp' => $timestamp,
        'upload_preset' => 'banco_de_hojas_de_vida'
    ];
    $to_sign = "timestamp=$timestamp&upload_preset=" . $params['upload_preset'] . $api_secret;
    $signature = sha1($to_sign);
    $post_fields = [
        'file' => new CURLFile($file_tmp, mime_content_type($file_tmp), $file_name),
        'api_key' => $api_key,
        'timestamp' => $timestamp,
        'upload_preset' => $params['upload_preset'],
        'signature' => $signature
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $upload_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        http_response_code(500);
        exit('Error al subir archivo a Cloudinary: ' . curl_error($ch));
    }
    curl_close($ch);
    $cloudinary_response = json_decode($result, true);
    if (!isset($cloudinary_response['secure_url'])) {
        http_response_code(500);
        exit('Error al obtener URL de Cloudinary: ' . $result);
    }
    $url_archivo = $cloudinary_response['secure_url'];
}


// Si quieres guardar la URL del archivo, primero agrega la columna 'url_archivo' en la base de datos.
$sql = "INSERT INTO estudiantes 
    (nombre_apellido, anio_programa, cedula, referencias, url_archivo, fecha_registro) 
    VALUES (?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    exit("Error interno al preparar la consulta: " . $conn->error);
}

$stmt->bind_param(
    "sssss",
    $datos['nombre_apellido'],
    $datos['anio_programa'],
    $datos['cedula'],
    $datos['referencias'],
    $url_archivo
);

if ($stmt->execute()) {
    echo "OK";
} else {
    http_response_code(500);
    echo "Error al guardar el registro: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
