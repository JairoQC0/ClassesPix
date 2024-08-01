<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'alumno') {
    header("Location: login.php");
    exit();
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo_salon = $_POST['codigo_salon'];
    $alumno_id = $_SESSION['user_id'];

    // Buscar el salón por su código
    $stmt = $conn->prepare("SELECT id FROM salones WHERE codigo = ?");
    $stmt->bind_param("s", $codigo_salon);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($salon_id);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        // Verificar si el alumno ya está inscrito o tiene una solicitud pendiente
        $stmt_check = $conn->prepare("SELECT id FROM inscripciones WHERE alumno_id = ? AND salon_id = ?");
        $stmt_check->bind_param("ii", $alumno_id, $salon_id);
        $stmt_check->execute();
        $stmt_check->store_result();

        $stmt_pending = $conn->prepare("SELECT id FROM solicitudes_inscripcion WHERE alumno_id = ? AND salon_id = ?");
        $stmt_pending->bind_param("ii", $alumno_id, $salon_id);
        $stmt_pending->execute();
        $stmt_pending->store_result();

        if ($stmt_check->num_rows > 0) {
            // El alumno ya está inscrito en el salón
            $mensaje = "Ya estás inscrito en este salón.";
        } elseif ($stmt_pending->num_rows > 0) {
            // El alumno ya tiene una solicitud pendiente
            $mensaje = "Ya has enviado una solicitud para este salón. Espera a que sea procesada.";
        } else {
            // Almacenar la solicitud de inscripción
            $stmt_solicitud = $conn->prepare("INSERT INTO solicitudes_inscripcion (alumno_id, salon_id) VALUES (?, ?)");
            $stmt_solicitud->bind_param("ii", $alumno_id, $salon_id);

            if ($stmt_solicitud->execute()) {
                $mensaje = "Solicitud de inscripción enviada exitosamente.";
            } else {
                $mensaje = "Error al enviar la solicitud: " . $stmt_solicitud->error;
            }

            $stmt_solicitud->close();
        }

        $stmt_check->close();
        $stmt_pending->close();
    } else {
        $mensaje = "Código de salón no encontrado.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inscribirse en Salón</title>
    <style>
        /* Estilos para el modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<form method="POST" action="">
    <label>Código del Salón:</label>
    <input type="text" name="codigo_salon" required><br>
    <button type="submit">Enviar Solicitud</button>
</form>
<p><a href="index.php">Volver</a></p>

<!-- Modal -->
<div id="mensajeModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <p><?php echo $mensaje; ?></p>
  </div>
</div>

<!-- Script para mostrar el modal si hay un mensaje -->
<script>
<?php if (!empty($mensaje)) { ?>
    var modal = document.getElementById("mensajeModal");
    var span = document.getElementsByClassName("close")[0];
    modal.style.display = "block";
    span.onclick = function() {
        modal.style.display = "none";
    }
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
<?php } ?>
</script>

</body>
</html>
