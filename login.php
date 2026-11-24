<?php
/* ===========================================================================
   Semana 10 — login.php · LA PUERTA

   Este archivo hace dos cosas según cómo se llegue a él:
     - por GET  (escribiendo la dirección)  -> muestra el formulario
     - por POST (enviando el formulario)    -> comprueba las credenciales

   Es el mismo patrón de guardar.php en la Semana 9, con una diferencia:
   aquí el formulario y el que lo procesa viven en el mismo archivo, porque
   cuando las credenciales fallan hay que volver a mostrar el formulario con
   el mensaje de error.
   =========================================================================== */

require_once 'sesion.php';
require_once 'usuarios.php';

iniciarSesion();

// Si ya hay sesión abierta, no tiene sentido mostrar el login otra vez.
if (haySesion()) {
    header('Location: panel.php');
    exit;
}

$error   = '';
$usuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = trim($_POST['usuario'] ?? '');
    $clave   = $_POST['clave'] ?? '';   // la contraseña NO se recorta: un
                                        // espacio al final puede ser parte
                                        // de ella y quitarlo sería cambiarla

    if ($usuario === '' || $clave === '') {
        $error = 'Escriba el usuario y la contraseña.';
    } else {
        $u = verificarCredenciales($usuario, $clave);

        if ($u === null) {
            /* MENSAJE DELIBERADAMENTE VAGO.

               Dice "usuario o contraseña incorrectos", nunca "ese usuario no
               existe". Si distinguiera los dos casos, cualquiera podría
               probar nombres hasta descubrir cuáles son válidos, y ya
               tendría media credencial. Se llama enumeración de usuarios. */
            $error = 'Usuario o contraseña incorrectos.';

            // Una pausa corta encarece la prueba por fuerza bruta: mil
            // intentos pasan de segundos a minutos. No es la defensa
            // definitiva (eso es limitar los intentos), pero no cuesta nada.
            usleep(400000);   // 0,4 segundos
        } else {
            abrirSesionDe($u);          // aquí dentro va session_regenerate_id
            header('Location: panel.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - ADEPALMA Casanare</title>
    <link rel="icon" href="../img/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=Public+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/responsivo.css">
</head>

<body>
    <!-- Mismo encabezado y menú que el resto del sitio, para que el ingreso
         no parezca otro sitio y el usuario pueda volver a otras páginas. -->
    <header>
        <a class="marca" href="index.html">ADEPALMA Casanare</a>
        <nav aria-label="Navegación principal">
            <ul>
                <li><a href="index.html">Inicio</a></li>
                <li><a href="precios.html">Precios</a></li>
                <li><a href="contacto.html">Contacto</a></li>
                <li><a href="formulario.html">Compra</a></li>
                <li><a href="puntos.html">Puntos</a></li>
                <li><a href="panel.php">Lotes</a></li>
            </ul>
        </nav>
    </header>

    <div class="banner">
        <h1>Entrar al panel de lotes</h1>
    </div>

    <main>
        <section>
            <h2>Acceso restringido</h2>

            <?php if ($error !== ''): ?>
                <!-- role="alert" hace que el lector de pantalla lo anuncie apenas
                     aparezca, sin que el usuario tenga que ir a buscarlo. -->
                <p class="mensaje aviso-error" role="alert"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <!-- action vacío significa "este mismo archivo". method POST porque
                 una contraseña JAMÁS puede ir por GET: con GET quedaría escrita
                 en la barra de direcciones, en el historial del navegador y en
                 los registros del servidor. -->
            <form method="POST" action="">
                <fieldset>
                    <legend>Datos de ingreso</legend>

                    <div class="campo">
                        <label for="usuario">Usuario</label>
                        <!-- Se devuelve lo que el usuario escribió para que no tenga que
                             volver a teclearlo. La contraseña NO se devuelve nunca. -->
                        <input type="text" id="usuario" name="usuario" required
                               autocomplete="username"
                               value="<?= htmlspecialchars($usuario) ?>">
                    </div>

                    <div class="campo">
                        <label for="clave">Contraseña</label>
                        <!-- type="password" oculta lo que se escribe. Es solo visual: no
                             cifra nada. Lo que protege el envío es HTTPS. -->
                        <input type="password" id="clave" name="clave" required
                               autocomplete="current-password">
                    </div>

                    <button class="boton" type="submit">Entrar</button>
                </fieldset>
            </form>
        </section>
    </main>

    <footer>
        <p>ADEPALMA Casanare - Yopal, Casanare</p>
    </footer>

</body>
</html>
