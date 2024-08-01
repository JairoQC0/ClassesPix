<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'profesor') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['alumno_id']) && isset($_GET['salon_id'])) {
    $alumno_id = $_GET['alumno_id'];
    $salon_id = $_GET['salon_id'];

    // Eliminar la inscripción del alumno en el salón
    $stmt = $conn->prepare("DELETE FROM inscripciones WHERE alumno_id = ? AND salon_id = ?");
    $stmt->bind_param("ii", $alumno_id, $salon_id);

    if ($stmt->execute()) {
        echo "Alumno eliminado exitosamente del salón.";
    } else {
        echo "Error al eliminar al alumno: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: ver_salon.php?id=$salon_id");
    exit();
} else {
    echo "Datos insuficientes para eliminar al alumno.";
    header("Location: index.php");
    exit();
}
?>
