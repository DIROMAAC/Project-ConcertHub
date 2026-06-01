<?php
session_start();

function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        $line = trim($line);
        if (strpos($line, '=') === false) continue;
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
    return true;
}

loadEnv(__DIR__ . '/../.env');

$clientId = getenv('SPOTIFY_CLIENT_ID') ?: ($_ENV['SPOTIFY_CLIENT_ID'] ?? null);
$clientSecret = getenv('SPOTIFY_CLIENT_SECRET') ?: ($_ENV['SPOTIFY_CLIENT_SECRET'] ?? null);

if (!$clientId || !$clientSecret) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'API credentials not found in env file']);
    exit();
}

// Verificar si existe token válido en caché en la sesión
if (isset($_SESSION['spotify_access_token']) && isset($_SESSION['spotify_token_expires_at']) && $_SESSION['spotify_token_expires_at'] > time()) {
    header('Content-Type: application/json');
    echo json_encode(['access_token' => $_SESSION['spotify_access_token']]);
    exit();
}

// Obtener un nuevo token a través de curl en backend
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://accounts.spotify.com/api/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret),
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$response) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'Failed to retrieve access token from Spotify', 'http_code' => $httpCode]);
    exit();
}

$data = json_decode($response, true);
if (isset($data['access_token'])) {
    $_SESSION['spotify_access_token'] = $data['access_token'];
    $_SESSION['spotify_token_expires_at'] = time() + intval($data['expires_in']) - 60; // Expirar un minuto antes
    header('Content-Type: application/json');
    echo json_encode(['access_token' => $data['access_token']]);
} else {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'Invalid response from Spotify token endpoint']);
}
?>
