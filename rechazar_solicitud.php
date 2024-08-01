<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'profesor') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['solicitud_id'])) {
    $solicitud_id = $_GET['solicitud_id'];

    // Eliminar la solicitud de inscripción
    $stmt = $conn->prepare("DELETE FROM solicitudes_inscripcion WHERE id = ?");
    $stmt->bind_param("i", $solicitud_id);

    if ($stmt->execute()) {
        echo "Solicitud rechazada exitosamente.";
    } else {
        echo "Error al rechazar la solicitud: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: ver_solicitudes.php");
    exit();
} else {
    echo "Datos insuficientes para procesar la solicitud.";
    header("Location: index.php");
    exit();
}
?>
