<?php
/* ===========================================================================
   DESARROLLO WEB (FI20064) · Grupo G3 · Periodo 2026B
   Semana 10 — usuarios.php · QUIÉNES PUEDEN ENTRAR

   Igual que datos.php guarda los lotes, este archivo guarda los usuarios.
   No imprime nada: solo define de dónde salen y cómo se comprueban.

   LA REGLA QUE NO SE NEGOCIA
   Aquí NO hay ni una sola contraseña escrita. Lo que se guarda es el HASH,
   que es el resultado de pasar la contraseña por una función de un solo
   sentido: de la contraseña se llega al hash, pero del hash NO se puede
   volver a la contraseña.

   ¿Por qué importa tanto? Porque el día que alguien se lleve este archivo
   —y pasa más seguido de lo que uno cree— no se lleva las contraseñas. Y
   como la gente repite la misma contraseña en el correo y en el banco, una
   fuga de contraseñas en texto plano no hace daño solo aquí.
   =========================================================================== */

// Este archivo solo contiene funciones: si alguien lo abre por la URL, se
// responde 403 en vez de ejecutarlo. Solo debe cargarse con require_once.
if (basename($_SERVER['SCRIPT_NAME'] ?? '') === basename(__FILE__)) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/datos.php';   // aquí viven leerJson() y las rutas


/**
 * Lee los usuarios registrados desde datos/usuarios.json.php.
 * Si el archivo no existe devuelve una lista vacía: nadie puede entrar.
 *
 * El archivo solo trae hashes. Para agregar un usuario, genere su hash con
 * password_hash('la clave', PASSWORD_DEFAULT) desde la línea de comandos
 * (php -r "echo password_hash('clave', PASSWORD_DEFAULT);") y péguelo en el
 * campo "clave". Así ninguna contraseña queda escrita en el código.
 */
function leerUsuarios(): array
{
    return leerJson(ARCHIVO_USUARIOS) ?? [];
}

/**
 * Comprueba un usuario y una contraseña.
 *
 * @return array|null  el usuario sin su hash si coincide; null si no
 */
function verificarCredenciales(string $usuario, string $clave): ?array
{
    foreach (leerUsuarios() as $u) {
        if ($u['usuario'] === $usuario) {

            /* password_verify vuelve a calcular el hash de la contraseña que
               acaban de escribir y lo compara con el guardado.

               NUNCA se compara con ===  . Dos razones:
               1. El hash incluye una sal distinta cada vez, así que el hash
                  de la misma contraseña nunca es idéntico dos veces.
               2. password_verify compara en tiempo constante, sin salirse
                  al primer carácter distinto. Una comparación normal tarda
                  un poquito más cuando acierta más letras, y esa diferencia
                  de microsegundos se puede medir para adivinar la clave. */
            if (password_verify($clave, $u['clave'])) {
                // Se devuelve el usuario SIN el hash: lo que no se necesita,
                // no se pasea por la aplicación.
                unset($u['clave']);
                return $u;
            }
            return null;   // el usuario existe, pero la contraseña no es
        }
    }
    return null;           // no existe ese usuario
}
