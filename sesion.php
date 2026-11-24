<?php
/* ===========================================================================
   DESARROLLO WEB (FI20064) · Grupo G3 · Periodo 2026B
   Semana 10 — sesion.php · LA MEMORIA DEL SERVIDOR

   EL PROBLEMA QUE RESUELVE
   HTTP no recuerda. Cada petición llega como si fuera la primera: el
   servidor atiende, responde y olvida. Si alguien escribe su usuario y su
   contraseña en una página, en la SIGUIENTE el servidor ya no sabe quién es.

   La sesión es el remedio. Funciona así:
     1. El servidor crea un expediente con un número único (el id de sesión).
     2. Le manda al navegador SOLO ese número, en una cookie.
     3. En cada petición el navegador devuelve el número, y el servidor
        busca el expediente y recupera lo que había guardado.

   LO IMPORTANTE: por la red viaja el NÚMERO, no los datos. El nombre, el
   rol y todo lo demás se quedan en un archivo del servidor. Por eso una
   sesión es más segura que guardar los datos en la propia cookie.
   =========================================================================== */

// Este archivo solo contiene funciones: si alguien lo abre por la URL, se
// responde 403 en vez de ejecutarlo. Solo debe cargarse con require_once.
if (basename($_SERVER['SCRIPT_NAME'] ?? '') === basename(__FILE__)) {
    http_response_code(403);
    exit;
}


/**
 * Arranca la sesión con la configuración segura.
 * Debe llamarse ANTES de imprimir nada: ni una etiqueta, ni un espacio.
 * La cookie viaja en los encabezados, y los encabezados van antes del
 * contenido. Si algo se imprimió primero, PHP avisa con
 * "headers already sent" y la sesión no se abre.
 */
function iniciarSesion(): void
{
    // session_status() evita el aviso de "sesión ya iniciada" cuando dos
    // archivos incluyen este mismo archivo.
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        // httponly: JavaScript NO puede leer esta cookie con
        // document.cookie. Si alguien logra meter un script en la página,
        // no puede robarse el id de sesión y hacerse pasar por el usuario.
        'httponly' => true,

        // samesite 'Lax': el navegador no manda la cookie cuando la
        // petición nace en otro sitio. Es la defensa contra CSRF, que es
        // el ataque de hacer que el usuario, sin darse cuenta, envíe una
        // petición a un sitio donde tiene la sesión abierta.
        'samesite' => 'Lax',

        // secure: la cookie solo viaja por HTTPS. En XAMPP se trabaja por
        // http://localhost y por eso va en false; en producción va SIEMPRE
        // en true, o el id de sesión viaja en claro por la red.
        'secure'   => false,

        'path'     => '/',
    ]);

    // Modo estricto: PHP rechaza un id de sesión que el servidor no creó,
    // lo que refuerza la defensa contra la fijación de sesión.
    ini_set('session.use_strict_mode', '1');

    session_start();
}

/**
 * Registra en la sesión que este usuario entró.
 */
function abrirSesionDe(array $usuario): void
{
    iniciarSesion();

    /* ESTA LÍNEA ES LA DEFENSA CONTRA LA FIJACIÓN DE SESIÓN.

       El ataque funciona así: alguien le pasa a la víctima un enlace que ya
       trae un id de sesión escogido por el atacante. La víctima entra con
       ese id y se autentica. Como el id no cambió al iniciar sesión, el
       atacante ya conoce el id de una sesión autenticada y entra con ella.

       session_regenerate_id(true) cambia el número en el momento justo de
       autenticarse y borra el expediente viejo. El id que el atacante
       conocía deja de servir. Es una línea, y sin ella todo lo demás sobra. */
    session_regenerate_id(true);

    $_SESSION['usuario'] = $usuario['usuario'];
    $_SESSION['nombre']  = $usuario['nombre'];
    $_SESSION['rol']     = $usuario['rol'];
    $_SESSION['desde']   = time();
}

/**
 * ¿Hay alguien autenticado en esta sesión?
 */
function haySesion(): bool
{
    iniciarSesion();
    // isset() comprueba que la clave exista y no sea null. Preguntar
    // directamente por $_SESSION['usuario'] daría aviso si no existe.
    return isset($_SESSION['usuario']);
}

/**
 * Devuelve los datos del usuario autenticado, o null si no hay nadie.
 */
function usuarioActual(): ?array
{
    if (!haySesion()) {
        return null;
    }
    return [
        'usuario' => $_SESSION['usuario'],
        'nombre'  => $_SESSION['nombre'],
        'rol'     => $_SESSION['rol'],
    ];
}

/**
 * EL GUARDIÁN.
 *
 * Se llama en la PRIMERA línea de toda página protegida. Si no hay sesión,
 * manda al login y corta la ejecución.
 *
 * El exit es obligatorio y es el error más común de la semana: header()
 * solo AGREGA un encabezado a la respuesta, no detiene el programa. Sin
 * exit, PHP sigue ejecutando e imprime la página entera debajo de la
 * redirección. El navegador normalmente obedece y redirige, pero el
 * contenido YA SALIÓ del servidor y cualquiera lo ve con una herramienta
 * que no siga redirecciones. La página quedaría protegida solo en
 * apariencia.
 */
function exigirSesion(): void
{
    if (!haySesion()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Cierra la sesión por completo. Son tres pasos y hacen falta los tres.
 */
function cerrarSesion(): void
{
    iniciarSesion();

    // 1. Vaciar el arreglo, por si algo más lo consulta antes de terminar.
    $_SESSION = [];

    // 2. Pedirle al navegador que borre la cookie, poniéndole una fecha de
    //    vencimiento en el pasado. Sin esto el navegador seguiría mandando
    //    un id que ya no sirve.
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }

    // 3. Destruir el expediente en el servidor. Si solo se borrara la
    //    cookie, el archivo seguiría ahí y el id seguiría siendo válido
    //    para quien lo conociera.
    session_destroy();
}
