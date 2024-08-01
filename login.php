<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Teachers:ital,wght@0,400..800;1,400..800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>ClassesPix | Login</title>
    <script src="app.js"></script>
    <link rel="icon" href="img/logofinal.png" type="image/png">
</head>
<body>
    <!--NAVBAR-->
    <nav>
        <div class="logo-container">
            <a href="./inicio.html" class="words-logo"><strong>ClassesPix</strong></a>
            <a href="./inicio.html"><img src="./img/logofinal.png" alt="logo" class="logo"></a>
        </div>
        <ul class="nav-links">
            <li class="hideOnMobile"><a href="./ayuda.html">Ayuda</a></li>
            <li class="hideOnMobile"><a href="./login.php">Login</a></li>
        </ul>
        <div class="menu-button" onclick="toggleSidebar()">
            <svg xmlns="http://www.w3.org/2000/svg" height="26" viewBox="0 96 960 960" width="26">
                <path d="M120 816v-60h720v60H120Zm0-210v-60h720v60H120Zm0-210v-60h720v60H120Z" fill="white"/>
            </svg>            
        </div>
    </nav>

    <div id="sidebar" class="sidebar">
        <ul>
            <li><a href="./ayuda.html" onclick="toggleSidebar()">Ayuda</a></li>
            <li><a href="./login.php" onclick="toggleSidebar()">Login</a></li>
        </ul>
    </div>
    <!--END NAVBAR-->

    <?php
    session_start();
    require_once 'db.php';

    $error_message = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $tipo = $_POST['tipo'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Email inválido.';
        } else {
            if ($tipo == 'profesor') {
                $stmt = $conn->prepare("SELECT id, password FROM profesores WHERE email = ?");
            } else {
                $stmt = $conn->prepare("SELECT id, password FROM alumnos WHERE email = ?");
            }

            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $hashed_password);
            $stmt->fetch();

            if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_type'] = $tipo;
                header("Location: index.php");
                exit;
            } else {
                $error_message = 'Email o contraseña incorrectos';
            }

            $stmt->close();
            $conn->close();
        }
    }
    ?>

    <div class="login-container">
        <form method="POST" action="" class="login-form">
            <h2>Iniciar Sesión</h2>
            <?php if (!empty($error_message)): ?>
                <p class="error"><?= htmlspecialchars($error_message) ?></p>
            <?php endif; ?>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="tipo">Tipo:</label>
                <select id="tipo" name="tipo">
                    <option value="profesor">Profesor</option>
                    <option value="alumno">Alumno</option>
                </select>
            </div>
            <button type="submit">Iniciar Sesión</button>
            <p>¿No tienes una cuenta? <a href="register.php">Registrarse</a></p>
        </form>
    </div>
</body>
</html>
