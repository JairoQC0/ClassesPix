<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassesPix | Solicitudes</title>
    <link rel="icon" href="img/logofinal.png" type="image/png">
</head>
<body>
    
</body>
</html>
<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'profesor') {
    header("Location: login.php");
    exit();
}

$profesor_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT solicitudes_inscripcion.id, alumnos.nombre, salones.nombre, solicitudes_inscripcion.salon_id, alumnos.id 
    FROM solicitudes_inscripcion
    INNER JOIN alumnos ON solicitudes_inscripcion.alumno_id = alumnos.id
    INNER JOIN salones ON solicitudes_inscripcion.salon_id = salones.id
    WHERE salones.profesor_id = ?
");
$stmt->bind_param("i", $profesor_id);
$stmt->execute();
$stmt->bind_result($solicitud_id, $nombre_alumno, $nombre_salon, $salon_id, $alumno_id);

echo "<h2>Solicitudes de Inscripción</h2>";
echo "<ul class='solicitudes-list'>";
while ($stmt->fetch()) {
    echo "<li>
            <div class='solicitud-info'>
                <span class='alumno-nombre'>Alumno: $nombre_alumno</span> - 
                <span class='salon-nombre'>Salón: $nombre_salon</span> 
            </div>
            <div class='solicitud-acciones'>
                <a class='btn aceptar' href='aceptar_solicitud.php?solicitud_id=$solicitud_id&alumno_id=$alumno_id&salon_id=$salon_id'>Aceptar</a> 
                <a class='btn rechazar' href='rechazar_solicitud.php?solicitud_id=$solicitud_id' onclick=\"return confirm('¿Estás seguro de que deseas rechazar esta solicitud?');\">Rechazar</a>
            </div>
          </li>";
}
echo "</ul>";

$stmt->close();
$conn->close();
?>
<p><a href="index.php" class="btn volver">Volver</a></p>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #333333;
        color: #333;
        padding: 20px;
    }

    h2 {
        background-color: #2196F3;
        color: white;
        padding: 10px;
        border-radius: 5px;
        text-align: center;
    }

    .solicitudes-list {
        list-style-type: none;
        padding: 0;
    }

    .solicitudes-list li {
        background-color: #fff3e0;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin: 10px 0;
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .solicitud-info {
        flex-grow: 1;
    }

    .solicitud-acciones {
        display: flex;
        gap: 10px;
    }

    .alumno-nombre {
        font-weight: bold;
        color: #555;
    }

    .salon-nombre {
        font-style: italic;
        color: #888;
    }

    .btn {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 8px 15px;
        cursor: pointer;
        border-radius: 5px;
        text-decoration: none;
    }

    .btn:hover {
        background-color: #45a049;
    }

    .btn.rechazar {
        background-color: #f44336;
    }

    .btn.rechazar:hover {
        background-color: #e41e1e;
    }

    .btn.volver {
        display: block;
        width: 100px;
        text-align: center;
        margin: 20px auto;
        background-color: #2196F3;
    }

    .btn.volver:hover {
        background-color: #1976D2;
    }
</style>
