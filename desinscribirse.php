<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'alumno') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Salón no especificado.";
    exit();
}

$salon_id = intval($_GET['id']);
$alumno_id = $_SESSION['user_id'];

// Eliminar la inscripción del alumno
$stmt = $conn->prepare("DELETE FROM inscripciones WHERE salon_id = ? AND alumno_id = ?");
$stmt->bind_param("ii", $salon_id, $alumno_id);

if ($stmt->execute()) {
    echo "Desinscripción exitosa.";
} else {
    echo "Error al desinscribirse: " . $stmt->error;
}

$stmt->close();
$conn->close();

header("Location: index.php");
exit();
?>
