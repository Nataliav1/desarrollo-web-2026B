/* ===========================================================================
   DESARROLLO WEB (FI20064) · Grupo G3 · Periodo 2026B
   Semana 9 — validacion.js
   Proyecto: ADEPALMA Casanare (palma de aceite)

   ES EL MISMO ARCHIVO DE LA SEMANA 7, CON UN SOLO CAMBIO.

   En la Semana 7 este código hacía dos cosas: validaba el formulario y
   además agregaba la fila a la tabla con createElement y appendChild.
   Ahora solo valida. La fila la pinta el servidor.

   ¿Por qué se quitó esa parte? Porque si JavaScript agregara la fila Y
   además el servidor la guardara, la fila aparecería dos veces: una pintada
   por el navegador y otra al recargar la página desde el servidor.

   Lo que SÍ conserva es el valor que tenía desde el principio: avisarle al
   usuario del error al instante, sin esperar el viaje al servidor.
   =========================================================================== */

// Los mismos selectores de la Semana 7.
const formulario = document.getElementById("form-lote");
const aviso      = document.getElementById("aviso");

/**
 * Muestra u oculta el mensaje de error de un campo.
 * Devuelve 1 si hay error y 0 si no, para poder contarlos.
 */
function mostrarError(idCampo, mensaje) {
  const campo = document.getElementById(idCampo);
  const span  = document.getElementById("error-" + idCampo);
  span.textContent = mensaje;              // "" borra el mensaje anterior
  // toggle con segundo argumento: agrega la clase si la condición es
  // verdadera y la quita si es falsa.
  campo.classList.toggle("invalido", mensaje !== "");
  return mensaje === "" ? 0 : 1;
}

/* Cada función valida UN campo y devuelve 1 si hay error y 0 si no.
   Son las MISMAS reglas que validarLote() comprueba en datos.php.
   Que estén en los dos lados no es un descuido: ver el comentario del final. */

function validarVereda() {
  const vereda = document.getElementById("vereda").value.trim();
  if (vereda === "") {
    return mostrarError("vereda", "Escriba el nombre de la vereda.");
  }
  if (vereda.length < 3) {
    return mostrarError("vereda", "El nombre es demasiado corto.");
  }
  if (vereda.length > 60) {
    return mostrarError("vereda", "El nombre es demasiado largo (máximo 60 letras).");
  }
  return mostrarError("vereda", "");
}

function validarHectareas() {
  const hectareas = Number(document.getElementById("hectareas").value);
  // Number("") devuelve 0, así que el campo vacío cae en este mismo caso.
  // Se niega la condición positiva para cubrir también el caso NaN.
  if (!(hectareas > 0)) {
    return mostrarError("hectareas", "Las hectáreas deben ser mayores que cero.");
  }
  return mostrarError("hectareas", "");
}

function validarRendimiento() {
  const rendimiento = Number(document.getElementById("rendimiento").value);
  if (!(rendimiento > 0)) {
    return mostrarError("rendimiento", "Escriba el rendimiento en toneladas por hectárea al año.");
  }
  // 40 exacto SÍ es válido: el límite es "mayor que 40", igual que en PHP.
  if (rendimiento > 40) {
    return mostrarError("rendimiento", "Un rendimiento mayor a 40 toneladas por hectárea no es verosímil; verifique el dato.");
  }
  return mostrarError("rendimiento", "");
}

/** Valida los tres campos y devuelve cuántos tienen error. */
function contarErrores() {
  return validarVereda() + validarHectareas() + validarRendimiento();
}

/* ---------------------------------------------------------------------------
   AQUÍ ESTÁ LA DIFERENCIA CON LA SEMANA 7

   Si hay errores:  preventDefault cancela el envío. El formulario no sale
                    del navegador y el usuario corrige sin perder lo escrito.

   Si NO hay errores: NO se llama a preventDefault, y por eso el navegador
                    hace lo que hace por omisión: enviar el formulario a
                    guardar.php por POST. Eso es justamente lo que queremos.

   En la Semana 7 preventDefault se llamaba SIEMPRE, porque no había
   servidor a donde enviar nada.
   --------------------------------------------------------------------------- */
formulario.addEventListener("submit", function (evento) {
  const errores = contarErrores();

  if (errores > 0) {
    evento.preventDefault();
    aviso.textContent = "Revise los campos marcados: " + errores +
      (errores === 1 ? " error" : " errores");
    aviso.classList.add("aviso-error");
    return;
  }

  // Sin errores: no se cancela nada. El formulario viaja al servidor.
  aviso.classList.remove("aviso-error");
  aviso.textContent = "Enviando…";
});

// El evento input se dispara con cada tecla: el error desaparece apenas el
// usuario corrige, en vez de esperar a que vuelva a pulsar el botón. Cada
// campo revisa solo lo suyo para no marcar error en los que aún no tocó.
document.getElementById("vereda").addEventListener("input", validarVereda);
document.getElementById("hectareas").addEventListener("input", validarHectareas);
document.getElementById("rendimiento").addEventListener("input", validarRendimiento);

/* ===========================================================================
   LA PREGUNTA QUE SIEMPRE SALE:
   ¿Para qué validar aquí si el servidor valida otra vez?

   Porque las dos validaciones sirven para cosas distintas.

   La de aquí es por COMODIDAD: avisa al instante, sin esperar el viaje de
   ida y vuelta al servidor, y no hace perder lo escrito.

   La del servidor es por SEGURIDAD: es la única que no se puede saltar.
   Cualquiera puede desactivar JavaScript, borrar este archivo con F12 o
   enviar la petición con una herramienta que no sea un navegador. Lo único
   que el usuario NO controla es lo que corre en el servidor.

   Si tuviera que quedarse con una sola, quédese con la del servidor.
   La de aquí mejora la experiencia; la de allá es la que protege.
   =========================================================================== */
