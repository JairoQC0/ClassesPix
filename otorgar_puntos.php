<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'profesor') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $salon_id = $_POST['salon_id'];
    $alumno_id = $_POST['alumno_id'];
    $descripcion = $_POST['descripcion'];
    $puntos = $_POST['puntos'];
    $profesor_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO puntos (salon_id, alumno_id, profesor_id, descripcion, puntos) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisi", $salon_id, $alumno_id, $profesor_id, $descripcion, $puntos);

    if ($stmt->execute()) {
        echo "Puntos otorgados correctamente.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: ver_salon.php?id=" . $salon_id);
    exit();
}
?>
