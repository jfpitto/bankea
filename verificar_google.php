<?php
if (isset($_POST['id_token'])) {
    $id_token = $_POST['id_token'];

    // Verifica el token de ID con Google para asegurarse de que sea válido y confiable.
    // Puedes utilizar la biblioteca de Google API Client para esta verificación.
    // Asegúrate de haber instalado las dependencias requeridas.

    require_once 'vendor/autoload.php'; // Ruta al archivo autoload.php de la biblioteca de Google API Client

    $client = new Google_Client(['client_id' => 'TU_CLIENT_ID']);
    $payload = $client->verifyIdToken($id_token);

    if ($payload) {
        $user_id = $payload['sub']; // ID de usuario de Google
        // Puedes verificar y almacenar más información del usuario según tus necesidades.

        // Inicia sesión en tu aplicación y establece la sesión del usuario.
        session_start();
        $_SESSION['user_id'] = $user_id;

        echo "Éxito"; // Respuesta al cliente si la verificación es exitosa.
    } else {
        echo "Error en la verificación del token de Google";
    }
} else {
    echo "Token de ID no proporcionado";
}
?>
