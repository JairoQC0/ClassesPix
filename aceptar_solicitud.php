
<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'profesor') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['solicitud_id']) && isset($_GET['alumno_id']) && isset($_GET['salon_id'])) {
    $solicitud_id = $_GET['solicitud_id'];
    $alumno_id = $_GET['alumno_id'];
    $salon_id = $_GET['salon_id'];

    // Eliminar la solicitud de inscripción
    $stmt_delete = $conn->prepare("DELETE FROM solicitudes_inscripcion WHERE id = ?");
    $stmt_delete->bind_param("i", $solicitud_id);
    $stmt_delete->execute();
    $stmt_delete->close();

    // Inscribir al alumno en el salón
    $stmt_inscribir = $conn->prepare("INSERT INTO inscripciones (alumno_id, salon_id) VALUES (?, ?)");
    $stmt_inscribir->bind_param("ii", $alumno_id, $salon_id);

    if ($stmt_inscribir->execute()) {
        echo "Alumno inscrito exitosamente.";
    } else {
        echo "Error al inscribir al alumno: " . $stmt_inscribir->error;
    }

    $stmt_inscribir->close();
    $conn->close();

    header("Location: ver_solicitudes.php");
    exit();
} else {
    echo "Datos insuficientes para procesar la solicitud.";
    header("Location: index.php");
    exit();
}
?>
