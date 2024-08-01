<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salon</title>
    <link rel="icon" href="img/logofinal.png" type="image/png">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Teachers:ital,wght@0,400..800;1,400..800&display=swap" rel="stylesheet">
</head>
<body>
    
</body>
</html>
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

$salon_id = $_GET['id'];

// Obtener los datos del salón
$stmt = $conn->prepare("SELECT nombre FROM salones WHERE id = ?");
$stmt->bind_param("i", $salon_id);
$stmt->execute();
$stmt->bind_result($nombre_salon);
$stmt->fetch();
$stmt->close();

echo "<h2>Salón: $nombre_salon</h2>";

// Mostrar los alumnos inscritos (solo para profesores)
if ($user_type == 'profesor') {
    $stmt = $conn->prepare("SELECT alumnos.id, alumnos.nombre FROM inscripciones INNER JOIN alumnos ON inscripciones.alumno_id = alumnos.id WHERE inscripciones.salon_id = ?");
    $stmt->bind_param("i", $salon_id);
    $stmt->execute();
    $stmt->bind_result($alumno_id, $nombre_alumno);

    echo "<ul>";
    while ($stmt->fetch()) {
        echo "<li>$nombre_alumno - ";

        // Formulario para otorgar puntos (solo para profesores)
        echo '<form method="POST" action="otorgar_puntos.php" style="display:inline;">
                <input type="hidden" name="alumno_id" value="' . $alumno_id . '">
                <input type="hidden" name="salon_id" value="' . $salon_id . '">
                <input type="text" name="descripcion" placeholder="Motivo de los puntos" required>
                <input type="number" name="puntos" placeholder="Puntos" required>
                <button type="submit" class="btn">Otorgar Puntos</button>
              </form>';

        // Botón para eliminar alumno (solo para profesores)
        echo '<form method="POST" action="eliminar_alumno.php" style="display:inline;">
                <input type="hidden" name="alumno_id" value="' . $alumno_id . '">
                <input type="hidden" name="salon_id" value="' . $salon_id . '">
                <button type="submit" class="btn red-btn">Eliminar Alumno</button>
              </form>';

        echo "</li>";
    }
    echo "</ul>";

    $stmt->close();
}

// Obtener los puntos totales del alumno (solo para alumnos)
if ($user_type == 'alumno') {
    $stmt = $conn->prepare("SELECT SUM(puntos) AS total_puntos FROM puntos WHERE alumno_id = ? AND salon_id = ?");
    $stmt->bind_param("ii", $user_id, $salon_id);
    $stmt->execute();
    $stmt->bind_result($total_puntos);
    $stmt->fetch();
    $stmt->close();

    echo "<h3>Mis Puntos Totales: $total_puntos</h3>";
}

// Mostrar los puntos otorgados (visible para todos)
echo '<h3>Puntos Otorgados</h3>';
$stmt = $conn->prepare("SELECT alumnos.nombre, puntos.descripcion, puntos.puntos, puntos.fecha FROM puntos INNER JOIN alumnos ON puntos.alumno_id = alumnos.id WHERE puntos.salon_id = ? ORDER BY puntos.fecha DESC");
$stmt->bind_param("i", $salon_id);
$stmt->execute();
$stmt->bind_result($nombre_alumno, $descripcion, $puntos, $fecha);

echo '<table class="blue-table">
        <tr>
            <th>Alumno</th>
            <th>Descripción</th>
            <th>Puntos</th>
            <th>Fecha</th>
        </tr>';
while ($stmt->fetch()) {
    echo '<tr>
            <td>' . $nombre_alumno . '</td>
            <td>' . $descripcion . '</td>
            <td>' . $puntos . '</td>
            <td>' . $fecha . '</td>
          </tr>';
}
echo '</table>';

$stmt->close();

// Mostrar la tabla de clasificación de alumnos por puntos totales (visible para todos)
echo '<h3>Clasificación de Alumnos</h3>';
$stmt = $conn->prepare("SELECT alumnos.nombre, SUM(puntos.puntos) AS total_puntos FROM puntos INNER JOIN alumnos ON puntos.alumno_id = alumnos.id WHERE puntos.salon_id = ? GROUP BY alumnos.nombre ORDER BY total_puntos DESC");
$stmt->bind_param("i", $salon_id);
$stmt->execute();
$stmt->bind_result($nombre_alumno, $total_puntos);

echo '<table class="green-table">
        <tr>
            <th>Alumno</th>
            <th>Puntos Totales</th>
        </tr>';
while ($stmt->fetch()) {
    echo '<tr>
            <td>' . $nombre_alumno . '</td>
            <td>' . $total_puntos . '</td>
          </tr>';
}
echo '</table>';

$stmt->close();

$conn->close();
?>
<p><a href="index.php">Volver</a></p>

<style>
    *{
        font-family: "Teachers", sans-serif;
    }
    body { 
        background-image: url(./img/fondoalumno.jpg);
        background-color: rgba(24, 24, 24, 0.507);
        background-blend-mode: soft-light;
        color: #333;
        padding: 20px;
    }

    h2 {
        background-color: #7400B6;
        color: white;
        padding: 10px;
        border-radius: 5px;
    }

    h3 {
        margin-top: 30px;
        color: white;
    }

    ul {
        list-style-type: none;
        padding: 0;
    }

    li {
        margin: 10px 0;
        padding: 10px;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .btn {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        margin-left: 5px;
        border-radius: 3px;
        text-decoration: none;
    }

    .red-btn {
        background-color: #f44336;
    }

    .btn:hover, .red-btn:hover {
        opacity: 0.8;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th, td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .blue-table th {
        background-color: #2196F3;
        color: white;
    }

    .blue-table tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .blue-table tr {
        background-color: #f2f2f2;
    }

    .green-table th {
        background-color: #4CAF50;
        color: white;
    }

    .green-table tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .green-table tr {
        background-color: #f2f2f2;
    }
</style>
