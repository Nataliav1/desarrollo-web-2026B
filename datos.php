<?php
/* ===========================================================================
   DESARROLLO WEB (FI20064) · Grupo G3 · Periodo 2026B
   Semana 9 — datos.php · LA LÓGICA Y EL ORIGEN DE LOS DATOS
   Proyecto: ADEPALMA Casanare (palma de aceite)

   CAMBIO FRENTE A LA PRIMERA VERSIÓN
   Antes el arreglo $lotes estaba escrito aquí a mano. Ahora se lee de un
   archivo (datos/lotes.json.php) que el formulario va alimentando. Nadie
   vuelve a editar este archivo para agregar un lote.

   Este archivo sigue sin imprimir nada: solo define de dónde salen los
   datos y qué se hace con ellos.

   OJO: guardar en un archivo JSON es una solución PROVISIONAL, buena para
   esta semana y mala para producción. En la Semana 11 esta misma función
   va a leer de MariaDB, y será el ÚNICO archivo que haya que cambiar. Esa
   es la recompensa de haber separado la lógica de la presentación.
   =========================================================================== */


// Este archivo solo contiene funciones: si alguien lo abre por la URL, se
// responde 403 en vez de ejecutarlo. Solo debe cargarse con require_once.
if (basename($_SERVER['SCRIPT_NAME'] ?? '') === basename(__FILE__)) {
    http_response_code(403);
    exit;
}

/* DÓNDE SE GUARDAN LOS DATOS Y POR QUÉ NO SE PUEDEN DESCARGAR

   Los datos viven en la carpeta datos/, fuera de pagina/. Cada archivo se
   llama algo.json.php y empieza con una línea de PHP que responde 403 y
   termina. Si alguien escribe la dirección del archivo en el navegador,
   PHP lo ejecuta, ve esa primera línea y corta: nunca se entrega el JSON.
   Un .json plano, en cambio, se descargaría tal cual (con los hashes de
   las contraseñas dentro). Esto funciona en cualquier servidor, sin
   depender de la configuración de Apache.

   __DIR__ es la carpeta donde está ESTE archivo: así la ruta funciona sin
   importar desde dónde se incluya. */
const ENCABEZADO_DATOS = "<?php http_response_code(403); exit; ?>\n";
const ARCHIVO_LOTES    = __DIR__ . '/../datos/lotes.json.php';
const ARCHIVO_COMPRAS  = __DIR__ . '/../datos/compras.json.php';
const ARCHIVO_USUARIOS = __DIR__ . '/../datos/usuarios.json.php';

/**
 * Lee un archivo de datos y devuelve su contenido como arreglo, o null si
 * no existe o está dañado.
 */
function leerJson(string $ruta): ?array
{
    if (!file_exists($ruta)) {
        return null;
    }
    $contenido = file_get_contents($ruta);
    // Se quita la línea de PHP del principio para quedarse solo con el JSON.
    if (str_starts_with($contenido, ENCABEZADO_DATOS)) {
        $contenido = substr($contenido, strlen(ENCABEZADO_DATOS));
    }
    $datos = json_decode($contenido, true);
    return is_array($datos) ? $datos : null;
}

/**
 * Guarda un arreglo en un archivo de datos, con la línea que lo protege.
 *
 * LOCK_EX bloquea el archivo mientras se escribe. Sin eso, si dos personas
 * envían el formulario en el mismo instante, una escritura pisa a la otra y
 * se pierde un registro. Es el problema que las bases de datos resuelven de
 * verdad, y es una de las razones por las que existe la Semana 11.
 */
function escribirJson(string $ruta, array $datos): bool
{
    // JSON_PRETTY_PRINT para poder abrir el archivo y leerlo.
    // JSON_UNESCAPED_UNICODE para que "Tilodirán" no quede como "Tilodir\u00e1n".
    $json = json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($ruta, ENCABEZADO_DATOS . $json, LOCK_EX) !== false;
}

// Lotes con los que arranca el panel la primera vez, cuando el archivo
// todavía no existe. Son los mismos cuatro de la Semana 6.
// El rendimiento está en toneladas de fruto por hectárea al año.
const LOTES_INICIALES = [
    ['vereda' => 'Morichal',    'hectareas' => 12.5, 'toneladasPorHa' => 20],
    ['vereda' => 'El Charte',   'hectareas' =>  8.0, 'toneladasPorHa' => 17],
    ['vereda' => 'Punto Nuevo', 'hectareas' => 20.0, 'toneladasPorHa' => 14.5],
    ['vereda' => 'Tilodirán',   'hectareas' =>  5.5, 'toneladasPorHa' => 12],
];

/**
 * Devuelve todos los lotes guardados.
 * Si el archivo no existe todavía, lo crea con los lotes iniciales.
 */
function leerLotes(): array
{
    $lotes = leerJson(ARCHIVO_LOTES);

    if ($lotes === null && !file_exists(ARCHIVO_LOTES)) {
        guardarLotes(LOTES_INICIALES);
        return LOTES_INICIALES;
    }

    // Si el archivo quedó corrupto, leerJson devuelve null. Mejor arrancar
    // con los lotes iniciales que caerse: el panel sigue funcionando.
    return $lotes ?? LOTES_INICIALES;
}

/**
 * Escribe el arreglo completo de lotes en su archivo.
 */
function guardarLotes(array $lotes): bool
{
    return escribirJson(ARCHIVO_LOTES, $lotes);
}

/**
 * Agrega un lote al final y guarda. Devuelve el total de lotes resultante.
 */
function agregarLote(string $vereda, float $hectareas, float $toneladasPorHa): int
{
    $lotes = leerLotes();
    $lotes[] = [
        'vereda'         => $vereda,
        'hectareas'      => $hectareas,
        'toneladasPorHa' => $toneladasPorHa,
    ];
    guardarLotes($lotes);
    return count($lotes);
}

/* ------------------------------------------------------------------------
   LA MISMA LÓGICA DE LA SEMANA 6. No cambió al pasar al servidor.
   ------------------------------------------------------------------------ */

function clasificar(float $toneladasPorHa): array
{
    if ($toneladasPorHa >= 20) return ['texto' => 'Excelente', 'clase' => 'alto'];
    if ($toneladasPorHa >= 17) return ['texto' => 'Bueno',     'clase' => 'medio'];
    if ($toneladasPorHa >= 14) return ['texto' => 'Regular',   'clase' => 'bajo'];
    return ['texto' => 'Bajo', 'clase' => 'critico'];
}

function produccionTotal(array $lotes): float
{
    $total = 0;
    foreach ($lotes as $lote) {
        $total += $lote['hectareas'] * $lote['toneladasPorHa'];
    }
    return $total;
}

/**
 * VALIDACIÓN DEL LADO DEL SERVIDOR.
 *
 * Esta función repite las reglas que validacion.js ya comprueba en el
 * navegador. No es trabajo duplicado por descuido: es la regla más
 * importante de toda la seguridad web.
 *
 * La validación del navegador se puede saltar. Cualquiera puede desactivar
 * JavaScript, editar el HTML con F12, o enviar la petición con una
 * herramienta que no sea un navegador. Lo único que el usuario NO puede
 * tocar es lo que corre en el servidor.
 *
 * Regla: la validación del cliente es por comodidad del usuario.
 *        La validación del servidor es la que de verdad protege.
 *
 * @return array  lista de errores; vacía si todo está bien
 */
function validarLote(string $vereda, $hectareas, $toneladasPorHa): array
{
    $errores = [];

    if ($vereda === '') {
        $errores[] = 'La vereda es obligatoria.';
    } elseif (mb_strlen($vereda) < 3) {
        $errores[] = 'El nombre de la vereda es demasiado corto.';
    } elseif (mb_strlen($vereda) > 60) {
        $errores[] = 'El nombre de la vereda es demasiado largo.';
    }

    // is_numeric rechaza el texto. Un campo vacío llega como "" y también
    // lo rechaza, así que cubre los dos casos con una sola comprobación.
    if (!is_numeric($hectareas) || (float) $hectareas <= 0) {
        $errores[] = 'Las hectáreas deben ser un número mayor que cero.';
    }

    if (!is_numeric($toneladasPorHa) || (float) $toneladasPorHa <= 0) {
        $errores[] = 'El rendimiento debe ser un número mayor que cero.';
    } elseif ((float) $toneladasPorHa > 40) {
        $errores[] = 'Un rendimiento mayor a 40 toneladas por hectárea al año no es verosímil.';
    }

    return $errores;
}


/* ------------------------------------------------------------------------
   COMPRAS (formulario.html -> registro.php)
   ------------------------------------------------------------------------ */

/**
 * VALIDACIÓN DEL LADO DEL SERVIDOR del formulario de compra.
 * Repite las reglas de los atributos HTML (required, pattern, min, max,
 * step) porque esos atributos se pueden saltar con otra herramienta.
 *
 * @return array  claves de los campos con error (por ejemplo "cedula");
 *                vacía si todo está bien. Se devuelven claves y no textos
 *                para no reflejar nunca lo que escribió el usuario.
 */
function validarCompra(array $d): array
{
    $errores = [];

    $nombre = trim($d['nombre'] ?? '');
    if (mb_strlen($nombre) < 5 || mb_strlen($nombre) > 80) {
        $errores[] = 'nombre';
    }

    if (!preg_match('/^[0-9]{6,10}$/', $d['cedula'] ?? '')) {
        $errores[] = 'cedula';
    }

    // El celular es opcional, pero si viene debe tener forma de teléfono.
    $celular = trim($d['celular'] ?? '');
    if ($celular !== '' && !preg_match('/^[0-9+\s-]{7,15}$/', $celular)) {
        $errores[] = 'celular';
    }

    if (filter_var(trim($d['correo'] ?? ''), FILTER_VALIDATE_EMAIL) === false) {
        $errores[] = 'correo';
    }

    $lugar = trim($d['lugar'] ?? '');
    if (mb_strlen($lugar) < 3 || mb_strlen($lugar) > 100) {
        $errores[] = 'lugar';
    }

    // Cantidad entre 0.5 y 500, de media en media tonelada (step="0.5").
    $cantidad = $d['cantidad'] ?? '';
    if (!is_numeric($cantidad) || (float) $cantidad < 0.5 || (float) $cantidad > 500
        || fmod((float) $cantidad * 2, 1.0) != 0.0) {
        $errores[] = 'cantidad';
    }

    // La fecha debe existir de verdad: createFromFormat acepta "2026-02-31"
    // y lo corre a marzo, por eso se compara el resultado con el original.
    $fecha = $d['fecha'] ?? '';
    $f = DateTime::createFromFormat('Y-m-d', $fecha);
    if ($f === false || $f->format('Y-m-d') !== $fecha) {
        $errores[] = 'fecha';
    }

    if (mb_strlen($d['obs'] ?? '') > 300) {
        $errores[] = 'obs';
    }

    if (!isset($d['datos'])) {
        $errores[] = 'datos';
    }

    return $errores;
}

/**
 * Agrega una compra y guarda. Devuelve el total de compras registradas.
 */
function agregarCompra(array $d): int
{
    $compras = leerJson(ARCHIVO_COMPRAS) ?? [];
    $compras[] = [
        'fecha_registro' => date('Y-m-d H:i:s'),
        'nombre'   => trim($d['nombre']),
        'cedula'   => $d['cedula'],
        'celular'  => trim($d['celular'] ?? ''),
        'correo'   => trim($d['correo']),
        'lugar'    => trim($d['lugar']),
        'cantidad' => (float) $d['cantidad'],
        'fecha'    => $d['fecha'],
        'obs'      => trim($d['obs'] ?? ''),
    ];
    escribirJson(ARCHIVO_COMPRAS, $compras);
    return count($compras);
}
