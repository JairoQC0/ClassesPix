<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'profesor') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Salón no especificado.";
    exit();
}

$salon_id = intval($_GET['id']);
$profesor_id = $_SESSION['user_id'];

// Eliminar las inscripciones relacionadas con el salón
$stmt = $conn->prepare("DELETE FROM inscripciones WHERE salon_id = ?");
$stmt->bind_param("i", $salon_id);
$stmt->execute();
$stmt->close();

// Eliminar el salón
$stmt = $conn->prepare("DELETE FROM salones WHERE id = ? AND profesor_id = ?");
$stmt->bind_param("ii", $salon_id, $profesor_id);

if ($stmt->execute()) {
    echo "Salón eliminado exitosamente.";
} else {
    echo "Error al eliminar el salón: " . $stmt->error;
}

$stmt->close();
$conn->close();

header("Location: index.php");
exit();
?>
