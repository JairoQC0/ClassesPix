<?php
session_start();
require_once 'db.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

if ($user_type != 'alumno') {
    echo "Solo los alumnos pueden comprar productos.";
    exit();
}

$producto_id = $_POST['producto_id'];
$salon_id = $_POST['salon_id'];

// Obtener los puntos necesarios para el producto
$stmt = $conn->prepare("SELECT puntos_necesarios FROM productos WHERE id = ? AND salon_id = ?");
$stmt->bind_param("ii", $producto_id, $salon_id);
$stmt->execute();
$stmt->bind_result($puntos_necesarios);
$stmt->fetch();
$stmt->close();

// Verificar si el alumno tiene suficientes puntos
$stmt = $conn->prepare("SELECT SUM(puntos) AS total_puntos FROM puntos WHERE alumno_id = ? AND salon_id = ?");
$stmt->bind_param("ii", $user_id, $salon_id);
$stmt->execute();
$stmt->bind_result($total_puntos);
$stmt->fetch();
$stmt->close();

if ($total_puntos >= $puntos_necesarios) {
    // Registrar la compra
    $stmt = $conn->prepare("INSERT INTO compras (alumno_id, producto_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $producto_id);
    $stmt->execute();
    $stmt->close();

    // Restar los puntos al alumno
    $stmt = $conn->prepare("INSERT INTO puntos (salon_id, alumno_id, profesor_id, descripcion, puntos) VALUES (?, ?, NULL, 'Compra de producto', ?)");
    $stmt->bind_param("iii", $salon_id, $user_id, -$puntos_necesarios);
    $stmt->execute();
    $stmt->close();

    echo "Compra realizada con éxito.";
} else {
    echo "No tienes suficientes puntos para comprar este producto.";
}

header("Location: ver_salon.php?id=$salon_id");
exit();
?>
