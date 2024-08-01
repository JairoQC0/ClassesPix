<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/logofinal.png" type="image/png">
    <title>Editar Perfil</title>
</head>
<body>
    
</body>
</html>
<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$nombre = $email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_BCRYPT) : '';

    if ($user_type == 'profesor') {
        if ($password) {
            $stmt = $conn->prepare("UPDATE profesores SET nombre = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $nombre, $email, $password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE profesores SET nombre = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $nombre, $email, $user_id);
        }
    } else {
        if ($password) {
            $stmt = $conn->prepare("UPDATE alumnos SET nombre = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $nombre, $email, $password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE alumnos SET nombre = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $nombre, $email, $user_id);
        }
    }

    if ($stmt->execute()) {
        echo "<p class='success'>Perfil actualizado exitosamente.</p>";
    } else {
        echo "<p class='error'>Error al actualizar el perfil: " . $stmt->error . "</p>";
    }

    $stmt->close();
} else {
    if ($user_type == 'profesor') {
        $stmt = $conn->prepare("SELECT nombre, email FROM profesores WHERE id = ?");
    } else {
        $stmt = $conn->prepare("SELECT nombre, email FROM alumnos WHERE id = ?");
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($nombre, $email);
    $stmt->fetch();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url(./img/fondoalumno.jpg);
            background-color: rgba(24, 24, 24, 0.507);
            background-blend-mode: soft-light;
            color: #333;
            padding: 20px;
        }

        h2 {
            background-color: #2196F3;
            color: white;
            padding: 10px;
            border-radius: 5px;
        }

        form {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: auto;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 16px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        p {
            text-align: center;
            margin-top: 20px;
        }

        a {
            color: #2196F3;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h2>Editar Perfil</h2>
    <form method="POST" action="">
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required><br>
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required><br>
        <label>Contraseña (dejar en blanco para no cambiar):</label>
        <input type="password" name="password"><br>
        <button type="submit">Actualizar Perfil</button>
    </form>
    <p><a href="index.php">Volver</a></p>
</body>
</html>
