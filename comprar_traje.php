<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'alumno') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $alumno_id = $_SESSION['user_id'];
    $traje = $_POST['traje'];
    $costo = $_POST['costo'];

    // Obtener los puntos totales del alumno
    $stmt = $conn->prepare("SELECT SUM(puntos) FROM puntos WHERE alumno_id = ?");
    $stmt->bind_param("i", $alumno_id);
    $stmt->execute();
    $stmt->bind_result($puntos_totales);
    $stmt->fetch();
    $stmt->close();

    if ($puntos_totales >= $costo) {
        // Insertar la compra en la base de datos
        $stmt = $conn->prepare("INSERT INTO compras (alumno_id, traje) VALUES (?, ?)");
        $stmt->bind_param("is", $alumno_id, $traje);
        if ($stmt->execute()) {
            echo "Compra exitosa.";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "No tienes suficientes puntos para comprar este traje.";
    }

    $conn->close();
    header("Location: index.php");
    exit();
}
?>
