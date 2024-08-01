<?php
session_start();
require_once 'db.php';
require_once 'funciones.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$nombre_usuario = '';
$total_puntos = 0;

// Obtener el nombre del usuario
if ($user_type == 'profesor') {
    $stmt = $conn->prepare("SELECT nombre FROM profesores WHERE id = ?");
} else {
    $stmt = $conn->prepare("SELECT nombre FROM alumnos WHERE id = ?");
    // Obtener la suma de puntos para el alumno
    $stmt_puntos = $conn->prepare("SELECT SUM(puntos) FROM puntos WHERE alumno_id = ?");
    $stmt_puntos->bind_param("i", $user_id);
    $stmt_puntos->execute();
    $stmt_puntos->bind_result($total_puntos);
    $stmt_puntos->fetch();
    $stmt_puntos->close();
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($nombre_usuario);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario</title>
    <link rel="icon" href="img/logofinal.png" type="image/png">
    <link rel="stylesheet" href="styles_index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Teachers:ital,wght@0,400..800;1,400..800&display=swap" rel="stylesheet">
    <style>
        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            color: #fff;
            border: none;
            border-radius: 5px;
            margin: 5px;
            transition: background-color 0.3s;
        }
        .btn-edit {
            background-color: #4CAF50;
        }
        .btn-edit:hover {
            background-color: #45a049;
        }
        .btn-logout {
            background-color: #f44336;
        }
        .btn-logout:hover {
            background-color: #da190b;
        }
        .centered-image {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            margin: 10px;
        }
        .card img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
        }
        .cards-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }
        .card h3 {
            font-size: 18px;
            margin: 10px 0;
        }
        .card p {
            font-size: 16px;
            color: #333;
        }
        .card .btn-buy {
            background-color: #008CBA;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 10px 2px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
        .card .btn-buy:hover {
            background-color: #007B9E;
        }
        .fondo-personaje {
            background-image: url(./img/fondo_pixel.avif);
            background-color: rgba(24, 24, 24, 0.507);
            background-blend-mode: soft-light;
        }
    </style>
</head>
<body>
    <div class="container">
        <header style="background-color: <?php echo ($user_type == 'profesor' ? 'blue' : 'green'); ?>;">
            <h1>Bienvenido <u><?php echo ($user_type == 'profesor' ? "Profesor " : "Alumno ") . $nombre_usuario; ?></u></h1>
        </header>

        <main>
            <?php if ($user_type == 'profesor'): ?>
                <section>
                    <h2>Crear Salón</h2>
                    <form method="POST" action="crear_salon.php">
                        <label>Nombre del salón:</label>
                        <input type="text" name="nombre_salon" required>
                        <button type="submit">Crear Salón</button>
                    </form>
                    <p><a href="ver_solicitudes.php">Ver Solicitudes de Inscripción</a></p>
                </section>
                <section>
                    <h2>Mis Salones de Clases</h2>
                    <ul>
                        <?php
                        $stmt = $conn->prepare("SELECT id, nombre, codigo FROM salones WHERE profesor_id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $stmt->bind_result($salon_id, $nombre_salon, $codigo_salon);
                        while ($stmt->fetch()): ?>
                            <li>
                                <strong> Código: <?php echo $codigo_salon; ?></strong> - 
                                <a href="ver_salon.php?id=<?php echo $salon_id; ?>" style="color: green"><?php echo $nombre_salon; ?></a> - 
                                <a href="editar_salon.php?id=<?php echo $salon_id; ?>" style="color: blue">Editar</a> - 
                                <a href="eliminar_salon.php?id=<?php echo $salon_id; ?>" onclick="return confirm('¿Estás seguro que deseas eliminar este curso?');" style="color: red">Eliminar</a>
                            </li>
                        <?php endwhile; 
                        $stmt->close();
                        ?>
                    </ul>
                </section>
            <?php elseif ($user_type == 'alumno'): ?>
                <section>
                    <h2>Inscribirse en un Salón</h2>
                    <form method="POST" action="inscribirse.php">
                        <label>Código del Salón:</label>
                        <input type="text" name="codigo_salon" required>
                        <button type="submit">Inscribirse</button>
                    </form>
                </section>
                <section>
                    <h2>Salones en los que estás inscrito</h2>
                    <ul>
                        <?php
                        $stmt = $conn->prepare("SELECT salones.id, salones.nombre, salones.codigo, profesores.nombre FROM inscripciones INNER JOIN salones ON inscripciones.salon_id = salones.id INNER JOIN profesores ON salones.profesor_id = profesores.id WHERE inscripciones.alumno_id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $stmt->bind_result($salon_id, $nombre_salon, $codigo_salon, $nombre_profesor);
                        while ($stmt->fetch()): ?>
                            <li>
                                <strong>Código: <?php echo $codigo_salon; ?></strong> - 
                                <?php echo $nombre_salon; ?> (Profesor: <?php echo $nombre_profesor; ?>) - 
                                <a href="ver_salon.php?id=<?php echo $salon_id; ?>" style="color: blue">Ver Salón</a> - 
                                <a href="desinscribirse.php?id=<?php echo $salon_id; ?>" onclick="return confirm('¿Estás seguro que deseas desinscribirte de este curso?');" style="color:red">Desinscribirse</a>
                            </li>
                        <?php endwhile; 
                        $stmt->close();
                        ?>
                    </ul>
                </section>
                <hr><br>
                <section class="fondo-personaje">
                    <h2 style="color: white; text-align:center;" >Tu Personaje</h2>
                    <div class="centered-image">
                        <img src="img/base.png" alt="Tu personaje" style="width: 300px;">
                    </div>
                </section>
                <hr>
                <br>
                <section>
                    <h2>Total de Puntos: <?php echo $total_puntos; ?></h2>
                </section>
                <section class="container-tienda">
                    <h2 style="text-align:center;">Tienda</h2>
                    <div class="cards-container">
                        <div class="card">
                            <img src="img/trajes/luchador.png" alt="Item 1">
                            <h3>Luchador</h3>
                            <p>100 puntos</p>
                            <button class="btn-buy">Comprar</button>
                        </div>
                        <div class="card">
                            <img src="img/trajes/rosa.png" alt="Item 2">
                            <h3>Rosa</h3>
                            <p>200 puntos</p>
                            <button class="btn-buy">Comprar</button>
                        </div>
                        <div class="card">
                            <img src="img/trajes/mario.png" alt="Item 3">
                            <h3>Mario</h3>
                            <p>300 puntos</p>
                            <button class="btn-buy">Comprar</button>
                        </div>
                        <div class="card">
                            <img src="img/trajes/zombie.png" alt="Item 4">
                            <h3>Zombie</h3>
                            <p>400 puntos</p>
                            <button class="btn-buy">Comprar</button>
                        </div>
                        <div class="card">
                            <img src="img/trajes/joker.png" alt="Item 5">
                            <h3>Joker</h3>
                            <p>500 puntos</p>
                            <button class="btn-buy">Comprar</button>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        </main>
        <footer>
            <button class="btn btn-edit" onclick="location.href='editar_perfil.php'">Editar Perfil</button>
            <button class="btn btn-logout" onclick="location.href='logout.php'">Cerrar sesión</button>
        </footer>
    </div>
</body>
</html>

<?php $conn->close(); ?>
