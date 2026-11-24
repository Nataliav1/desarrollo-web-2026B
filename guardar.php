<?php
/* ===========================================================================
   Semana 9 — guardar.php · EL QUE RECIBE EL FORMULARIO
   Proyecto: ADEPALMA Casanare (palma de aceite)

   Este archivo NO se abre directamente en el navegador: se llega a él al
   enviar el formulario de panel.php. No imprime ninguna página; recibe los
   datos, decide qué hacer y devuelve al usuario al panel.
   =========================================================================== */

require_once 'datos.php';
require_once 'sesion.php';

/* EL GUARDIÁN TAMBIÉN VA AQUÍ.

   Este archivo no se ve en el navegador, pero cualquiera puede enviarle una
   petición POST con curl sin haber entrado nunca al panel. Si no se
   comprueba la sesión, esa persona agrega lotes sin tener usuario.
   exigirSesion() además abre la sesión con la configuración segura
   (httponly y samesite), que un session_start() a secas no aplica. */
exigirSesion();

/* --------------------------------------------------------------------------
   1. Aceptar solo POST

   Si alguien escribe guardar.php en la barra de direcciones, eso llega como
   GET y no debe hacer nada. Agregar datos CAMBIA el estado del sistema, y
   lo que cambia el estado va por POST. Es la distinción de la Semana 3.
   -------------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: panel.php');
    exit;   // exit es obligatorio: sin él, el programa seguiría ejecutándose
}

/* --------------------------------------------------------------------------
   2. Capturar los datos

   $_POST es un arreglo asociativo con lo que envió el formulario. Las
   claves son los atributos name de cada campo, NO los id.

   El operador ?? devuelve lo de la derecha si lo de la izquierda no existe.
   Sin él, un campo que no llegue produce una advertencia de índice no
   definido. Nunca dé por hecho que un campo llegó: el formulario pudo
   haberse enviado desde fuera de su página.
   -------------------------------------------------------------------------- */
$vereda      = trim($_POST['vereda'] ?? '');
$hectareas   = $_POST['hectareas'] ?? '';
$rendimiento = $_POST['rendimiento'] ?? '';

/* --------------------------------------------------------------------------
   3. Validar EN EL SERVIDOR

   validacion.js ya comprobó esto mismo en el navegador. Se vuelve a
   comprobar aquí porque la del navegador se puede saltar: basta desactivar
   JavaScript o enviar la petición con otra herramienta.
   -------------------------------------------------------------------------- */
$errores = validarLote($vereda, $hectareas, $rendimiento);

if (count($errores) > 0) {
    $_SESSION['errores'] = $errores;
    // Se devuelven también los valores escritos, para no obligar al usuario
    // a volver a teclear todo por un error en un solo campo.
    $_SESSION['valores'] = ['vereda' => $vereda, 'hectareas' => $hectareas, 'rendimiento' => $rendimiento];
    header('Location: panel.php');
    exit;
}

/* --------------------------------------------------------------------------
   4. Guardar
   -------------------------------------------------------------------------- */
$total = agregarLote($vereda, (float) $hectareas, (float) $rendimiento);

$_SESSION['exito'] = 'Lote agregado. La asociación tiene ahora ' . $total . ' lotes registrados.';

/* --------------------------------------------------------------------------
   5. Redirigir en vez de imprimir la página

   Esto se llama patrón POST-Redirect-GET y resuelve un problema concreto:
   si guardar.php imprimiera el panel, la última petición del navegador
   seguiría siendo un POST, y al pulsar F5 el navegador preguntaría si
   reenviar los datos. Quien diga que sí, guarda el lote dos veces.

   Al redirigir, la última petición pasa a ser un GET a panel.php y F5 ya
   no reenvía nada.
   -------------------------------------------------------------------------- */
header('Location: panel.php');
exit;
