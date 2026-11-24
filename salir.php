<?php
/* ===========================================================================
   Semana 10 — salir.php · CERRAR LA SESIÓN

   No imprime ninguna página: cierra y devuelve al login.
   =========================================================================== */

require_once 'sesion.php';

cerrarSesion();   // vacía, borra la cookie y destruye el expediente

header('Location: login.php');
exit;
