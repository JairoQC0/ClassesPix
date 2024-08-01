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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nuevo_nombre = $_POST['nombre_salon'];

    // Actualizar el nombre del salón
    $stmt = $conn->prepare("UPDATE salones SET nombre = ? WHERE id = ? AND profesor_id = ?");
    $stmt->bind_param("sii", $nuevo_nombre, $salon_id, $profesor_id);

    if ($stmt->execute()) {
        echo "Salón actualizado exitosamente.";
    } else {
        echo "Error al actualizar el salón: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: index.php");
    exit();
}

// Obtener el nombre actual del salón
$stmt = $conn->prepare("SELECT nombre FROM salones WHERE id = ? AND profesor_id = ?");
$stmt->bind_param("ii", $salon_id, $profesor_id);
$stmt->execute();
$stmt->bind_result($nombre_salon);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Salón</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-image: url(./img/fondoalumno.jpg);
            background-color: rgba(24, 24, 24, 0.507);
            background-blend-mode: soft-light;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 400px;
            width: 100%;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            font-size: 16px;
            margin-bottom: 5px;
            color: #333;
        }
        input[type="text"] {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 20px;
            width: 100%;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #45a049;
        }
        p {
            text-align: center;
            margin-top: 20px;
        }
        a {
            color: #4CAF50;
            text-decoration: none;
            transition: color 0.3s;
        }
        a:hover {
            color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Editar Salón</h1>
        <form method="POST" action="">
            <label>Nombre del salón:</label>
            <input type="text" name="nombre_salon" value="<?php echo htmlspecialchars($nombre_salon); ?>" required><br>
            <button type="submit">Actualizar Salón</button>
        </form>
        <p><a href="index.php">Volver</a></p>
    </div>
</body>
</html>
