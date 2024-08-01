<?php
session_start();
require_once 'db.php';
require_once 'funciones.php';  // Incluir el archivo de funciones

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'profesor') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_salon = $_POST['nombre_salon'];
    $profesor_id = $_SESSION['user_id'];
    $codigo_salon = generarCodigoSalon();

    // Verificar si el código ya existe, para evitar duplicados
    $codigo_existente = true;
    while ($codigo_existente) {
        $stmt = $conn->prepare("SELECT id FROM salones WHERE codigo = ?");
        $stmt->bind_param("s", $codigo_salon);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows == 0) {
            $codigo_existente = false;
        } else {
            $codigo_salon = generarCodigoSalon();
        }
        $stmt->close();
    }

    // Insertar el nuevo salón
    $stmt = $conn->prepare("INSERT INTO salones (nombre, profesor_id, codigo) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $nombre_salon, $profesor_id, $codigo_salon);

    if ($stmt->execute()) {
        echo "Salón creado exitosamente con código: $codigo_salon";
    } else {
        echo "Error al crear el salón: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: index.php");
    exit();
}
?>

<form method="POST" action="">
    <label>Nombre del salón:</label>
    <input type="text" name="nombre_salon" required><br>
    <button type="submit">Crear Salón</button>
</form>
<p><a href="index.php">Volver</a></p>
