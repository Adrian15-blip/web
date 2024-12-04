<?php
// login.php

session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Inicio de sesión exitoso
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];

            // Redirigir con mensaje dependiendo del rol
            if ($user['role'] === 'admin') {
                $_SESSION['message'] = "Bienvenido, Administrador. Estás en el panel de administración.";
                redirect('admin/dashboard.php');
            } else {
                $_SESSION['message'] = "Bienvenido, Cliente. Estás en tu panel de cliente.";
                redirect('client/dashboard.php');
            }
            var_dump($_SESSION['message']); // Verifica si el mensaje se almacena correctamente
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Correo electrónico no encontrado.";
    }


    // Mostrar el mensaje de error o éxito en la interfaz de usuario
   if (isset($error)) {
    echo "<p class='error'>$error</p>";
   } elseif (isset($_SESSION['message'])) {
    echo "<p class='success'>{$_SESSION['message']}</p>";
    // Limpiar el mensaje después de mostrarlo
    unset($_SESSION['message']);
}
}




?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Cotizaciones</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            min-height: 100vh;
            background-color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .container {
            display: flex;
            width: 100%;
            height: 100vh;
            max-width: 1200px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .image-section {
            flex: 1;
            background-image: url('/assets/img/cotiza.png');
            background-size: cover;
            background-position: center;
            height: 100vh;
            position: relative;
            animation: zoomIn 20s infinite alternate;
        }

        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .overlay-content h1 {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
            animation: fadeInUp 1.5s ease-out;
        }

        .overlay-content p {
            font-size: 1.2rem;
            animation: fadeInUp 1.5s ease-out 0.5s;
        }

        .form-section {
            flex: 1;
            background-color: aliceblue;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            animation: slideIn 1.5s ease-out;
            max-width: 400px;
            width: 100%;
            margin-left: 10px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .login-header p {
            color: #666;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4A90E2;
        }

        .form-group .input-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
            color: #4A90E2;
        }

        .error {
            background-color: #ffebee;
            color: #c62828;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #4A90E2;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            position: relative;
        }

        button:hover {
            background-color: #357ABD;
        }

        button::before {
            content: "\f00c";
            font-family: 'FontAwesome';
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }

        .register-link a {
            color: #4A90E2;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .image-section {
                height: 40vh;
                width: 100%;
            }

            .form-section {
                padding: 1rem;
                margin-top: 2rem;
            }

            .overlay-content h1 {
                font-size: 2.5rem;
            }
        }

        @keyframes zoomIn {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }

        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            0% { transform: translateX(50%); opacity: 0; }
            100% { transform: translateX(0); opacity: 1; }
        }
        .overlay-content h1 {
            color: yellowgreen;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="image-section">
            <div class="image-overlay">
                <div class="overlay-content">
                    <h1></h1>
                    <p></p>
                </div>
            </div>
        </div>
        <div class="form-section">
            <div class="login-header">
                <h2>Iniciar Sesión</h2>
                <p>Bienvenido, ingresa tus credenciales</p>
            </div>
            <?php if (isset($error)) : ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required placeholder="ejemplo@correo.com">
                    <i class="fas fa-envelope input-icon"></i>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required placeholder="Ingresa tu contraseña">
                    <i class="fas fa-lock input-icon"></i>
                </div>
                <button type="submit">Iniciar Sesión</button>
            </form>
            <p class="register-link">¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a></p>
        </div>
    </div>
</body>
</html>
