<?php
/* ===========================================================================
   Semana 9 — panel.php · LA PÁGINA

   Dos cosas conviven aquí, y la clase es entender por qué las dos:
     - La TABLA la genera el servidor con un foreach, como vimos hoy.
     - El FORMULARIO lo valida JavaScript en el navegador, como en la
       Semana 7, y luego lo envía al servidor, que lo valida otra vez.
   =========================================================================== */

require_once 'datos.php';
require_once 'sesion.php';

/* EL GUARDIÁN, EN LA PRIMERA LÍNEA ÚTIL DEL ARCHIVO.

   Va ANTES de leer los datos y antes de imprimir una sola etiqueta. Si no
   hay sesión, exigirSesion() redirige al login y corta la ejecución con
   exit: nada de lo que sigue llega a ejecutarse.

   Ponerlo al final, o después de imprimir la cabecera, no protege nada:
   el contenido ya habría salido hacia el navegador. */
exigirSesion();

$yo = usuarioActual();

$lotes  = leerLotes();
$total  = produccionTotal($lotes);

// Se recogen los mensajes que guardar.php dejó en la sesión y se borran de
// inmediato: así se muestran una sola vez y no reaparecen al recargar.
$errores = $_SESSION['errores'] ?? [];
$exito   = $_SESSION['exito']   ?? '';
$valores = $_SESSION['valores'] ?? ['vereda' => '', 'hectareas' => '', 'rendimiento' => ''];
unset($_SESSION['errores'], $_SESSION['exito'], $_SESSION['valores']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADEPALMA - Panel de lotes</title>
    <!-- Sin esta línea el navegador pide /favicon.ico por su cuenta y
         el servidor responde 404, que aparece en la consola y distrae. -->
    <link rel="icon" href="../img/favicon.svg" type="image/svg+xml">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=Public+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/responsivo.css">
    <link rel="stylesheet" href="../css/estilos-panel.css">
</head>
<body>

    <header>
        <a class="marca" href="index.html">ADEPALMA Casanare</a>
        <nav aria-label="Navegación principal">
            <ul>
                <li><a href="index.html">Inicio</a></li>
                <li><a href="precios.html">Precios</a></li>
                <li><a href="contacto.html">Contacto</a></li>
                <li><a href="formulario.html">Compra</a></li>
                <li><a href="puntos.html">Puntos</a></li>
                <li><a href="panel.php" aria-current="page">Lotes</a></li>
            </ul>
        </nav>
            <!-- Los datos del usuario NO viajaron en la cookie: estaban guardados
         en el servidor y se recuperaron con el id de sesión. -->
    <p class="sesion">
      Sesión de <strong><?= htmlspecialchars($yo['nombre']) ?></strong>
      (<?= htmlspecialchars($yo['rol']) ?>)
      · <a href="salir.php">Cerrar sesión</a>
    </p>
    </header>

    <div class="banner">
        <h1>Panel de lotes</h1>
    </div>

    <main>

        <!-- ===================================================================
             LA TABLA · la genera el servidor
             =================================================================== -->
        <section>
            <h2>Lotes registrados</h2>

            <p class="resumen">
                <?= count($lotes) ?> lotes registrados · producción total estimada:
                <?= number_format($total, 1, ',', '.') ?> toneladas al año
            </p>

            <div class="tabla-scroll">
                <table>
                    <caption>Producción estimada de fruto de palma por vereda</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="texto">Vereda</th>
                            <th scope="col">Hectáreas</th>
                            <th scope="col">Toneladas/ha</th>
                            <th scope="col">Producción</th>
                            <th scope="col">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        /* El ciclo entra y sale del modo PHP en cada vuelta. Todo lo que
                           queda FUERA de las etiquetas se envía al navegador tal cual.

                           htmlspecialchars en la primera celda convierte < > & " en
                           entidades. Es el equivalente de textContent de la Semana 7, y
                           aquí ya NO es opcional: esa vereda la escribió un usuario. */
                        foreach ($lotes as $lote):
                            $produccion = $lote['hectareas'] * $lote['toneladasPorHa'];
                            $estado = clasificar($lote['toneladasPorHa']);
                        ?>
                        <tr>
                            <th scope="row" class="texto"><?= htmlspecialchars($lote['vereda']) ?></th>
                            <td><?= $lote['hectareas'] ?></td>
                            <td><?= $lote['toneladasPorHa'] ?></td>
                            <td><?= number_format($produccion, 1, ',', '.') ?> t</td>
                            <td class="estado <?= $estado['clase'] ?>"><?= $estado['texto'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===================================================================
             EL FORMULARIO · lo valida JavaScript y lo guarda PHP
             =================================================================== -->
        <section>
            <h2>Registrar un lote nuevo</h2>

            <?php if ($exito !== ''): ?>
                <p class="mensaje" role="status"><?= htmlspecialchars($exito) ?></p>
            <?php endif; ?>

            <?php if (count($errores) > 0): ?>
                <div class="mensaje" role="alert">
                    <strong>El servidor rechazó el envío:</strong>
                    <ul>
                        <?php foreach ($errores as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- action apunta a guardar.php y method es POST, porque este envío
                 CAMBIA el estado del sistema. novalidate apaga los mensajes
                 automáticos del navegador para que se vean los de validacion.js;
                 los atributos required y min siguen ahí y siguen sirviendo. -->
            <form id="form-lote" action="guardar.php" method="POST" novalidate>
                <fieldset>
                    <legend>Datos del lote</legend>

                    <div class="campo">
                        <label for="vereda">Vereda</label>
                        <!-- El atributo name es lo que viaja al servidor; el id es para
                             el label y para JavaScript. Sin name, el campo no llega. -->
                        <input type="text" id="vereda" name="vereda" required minlength="3" maxlength="60"
                               value="<?= htmlspecialchars($valores['vereda']) ?>">
                        <span class="error" id="error-vereda" role="alert"></span>
                    </div>

                    <div class="campo">
                        <label for="hectareas">Hectáreas</label>
                        <input type="number" id="hectareas" name="hectareas" step="0.1" min="0.1" required
                               value="<?= htmlspecialchars($valores['hectareas']) ?>">
                        <span class="error" id="error-hectareas" role="alert"></span>
                    </div>

                    <div class="campo">
                        <label for="rendimiento">Toneladas por hectárea al año</label>
                        <input type="number" id="rendimiento" name="rendimiento" step="0.1" min="0.1" max="40" required
                               value="<?= htmlspecialchars($valores['rendimiento']) ?>">
                        <span class="error" id="error-rendimiento" role="alert"></span>
                    </div>

                    <button class="boton" type="submit" id="btn-guardar">Agregar lote</button>
                    <p id="aviso" class="mensaje" role="status"></p>
                </fieldset>
            </form>
        </section>

    </main>

    <footer>
        <p>ADEPALMA Casanare - Yopal, Casanare</p>
    </footer>

    <!-- El mismo archivo de la Semana 7, con un solo cambio: ya no agrega la
         fila al DOM, porque de eso se encarga el servidor. Solo valida. -->
    <script src="../js/validacion.js"></script>

</body>
</html>
