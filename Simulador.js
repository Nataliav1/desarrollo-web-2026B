/* ===========================================================================
   DESARROLLO WEB (FI20064) - SEMANA 6
   Archivo: simulador.js
   Ejemplo dirigido: simulador de costos de producción por hectárea
   Proyecto: ADEPALMA Casanare (palma de aceite)

   QUÉ ES ESTE ARCHIVO
   Un programa en JavaScript que calcula cuánto cuesta producir una hectárea
   de palma de aceite en un año y si el cultivo deja ganancia al precio
   actual de la tonelada de fruto. Se construye en cinco pasos, en el mismo
   orden de la clase:

     PASO 1 - Variables, constantes y tipos de datos
     PASO 2 - Operadores y el cálculo del costo
     PASO 3 - Condicionales: decidir según el resultado
     PASO 4 - Arreglos y ciclos: varios lotes a la vez
     PASO 5 - Funciones: empaquetar para reutilizar

   CÓMO SE EJECUTA
   Hoy NO hay página web: JavaScript corre en la consola. Dos formas:

     a) En el navegador: abra cualquier página, oprima F12, vaya a la
        pestaña Consola y pegue el código.
     b) Con Node.js instalado: en la terminal, escriba
            node Simulador.js

   Los valores económicos son de referencia PARA EL EJERCICIO. El precio de
   la tonelada ($680.000) es un valor supuesto del ejercicio y NO es el del
   boletín de precios.html (ahí el precio mínimo de la Semana 4 es
   $572.500). Los costos también deben reemplazarse por datos reales de
   su municipio.
   =========================================================================== */


/* ===========================================================================
   PASO 1 - VARIABLES, CONSTANTES Y TIPOS DE DATOS
   =========================================================================== */

// Datos del cultivo. Son constantes: no cambian durante la ejecución.
const CULTIVO = "Palma de aceite (fruto fresco)";   // texto  -> tipo string
const MUNICIPIO = "Yopal";                          // texto  -> tipo string
const HECTAREAS = 12.5;                             // número -> tipo number
const TIENE_RIEGO = true;                           // sí/no  -> tipo boolean

// Costos de referencia por hectárea al año, en pesos colombianos.
const COSTO_SANIDAD = 750000;
const COSTO_FERTILIZANTE = 3100000;
const COSTO_MANO_OBRA = 2800000;
const COSTO_MAQUINARIA = 1300000;

// Precio de venta y rendimiento esperado.
const PRECIO_TONELADA = 680000;             // pesos por tonelada de fruto
const TONELADAS_POR_HECTAREA = 28;          // toneladas de fruto por año

// JavaScript infiere el tipo solo: no se declara. Se puede consultar con
// el operador typeof, que devuelve el nombre del tipo como texto.
console.log("=== PASO 1: tipos de datos ===");
console.log("CULTIVO es de tipo:", typeof CULTIVO);
console.log("HECTAREAS es de tipo:", typeof HECTAREAS);
console.log("TIENE_RIEGO es de tipo:", typeof TIENE_RIEGO);

// OJO: en JavaScript no existen tipos separados para entero y decimal.
// 12 y 12.5 son los dos de tipo number.
console.log("12 y 12.5 son ambos:", typeof 12, "y", typeof 12.5);
console.log("");


/* ===========================================================================
   PASO 2 - OPERADORES Y EL CÁLCULO DEL COSTO
   =========================================================================== */

console.log("=== PASO 2: cálculo del costo ===");

// Se usa let porque el valor se va acumulando: SÍ cambia.
let costoPorHectarea = 0;

costoPorHectarea += COSTO_SANIDAD;
costoPorHectarea += COSTO_FERTILIZANTE;
costoPorHectarea += COSTO_MANO_OBRA;
costoPorHectarea += COSTO_MAQUINARIA;

// El riego encarece un 15 %. El 0.15 se multiplica, no se suma como
// porcentaje: en programación los porcentajes son fracciones decimales.
if (TIENE_RIEGO) {
  costoPorHectarea = costoPorHectarea * 1.15;
}

const ingresoPorHectarea = PRECIO_TONELADA * TONELADAS_POR_HECTAREA;
const utilidadPorHectarea = ingresoPorHectarea - costoPorHectarea;

// toLocaleString da formato de miles con separadores colombianos.
// Es presentación, no cálculo: devuelve un string, no un número.
console.log("Costo por hectárea:   $", costoPorHectarea.toLocaleString("es-CO"));
console.log("Ingreso por hectárea: $", ingresoPorHectarea.toLocaleString("es-CO"));
console.log("Utilidad por hectárea:$", utilidadPorHectarea.toLocaleString("es-CO"));
console.log("");


/* ===========================================================================
   PASO 3 - CONDICIONALES: DECIDIR SEGÚN EL RESULTADO
   =========================================================================== */

console.log("=== PASO 3: clasificación del resultado ===");

// El margen es la utilidad como fracción del ingreso.
const margen = utilidadPorHectarea / ingresoPorHectarea;

// if / else if / else: se evalúan en orden y se detiene en el primero
// que sea verdadero. Por eso el orden importa.
let clasificacion;

if (margen >= 0.30) {
  clasificacion = "Rentable";
} else if (margen >= 0.15) {
  clasificacion = "Ajustado";
} else if (margen > 0) {
  clasificacion = "En riesgo";
} else {
  clasificacion = "Pérdida";
}

// toFixed(1) redondea a un decimal. Igual que toLocaleString, devuelve texto.
console.log("Margen:", (margen * 100).toFixed(1) + "%");
console.log("Clasificación:", clasificacion);

// Operador lógico &&: ambas condiciones deben cumplirse.
if (TIENE_RIEGO && margen >= 0.15) {
  console.log("Recomendación: el riego se justifica con este margen.");
} else if (TIENE_RIEGO && margen < 0.15) {
  console.log("Recomendación: revisar si el riego se está pagando solo.");
}
console.log("");


/* ===========================================================================
   PASO 4 - ARREGLOS Y CICLOS: VARIOS LOTES A LA VEZ
   =========================================================================== */

console.log("=== PASO 4: los lotes de la asociación ===");

// Cada elemento es un objeto: un conjunto de pares nombre-valor.
const lotes = [
  // Los rendimientos están escogidos a propósito para que el programa
  // recorra las CUATRO clasificaciones posibles del paso 3.
  { vereda: "Morichal",    hectareas: 12.5, toneladasPorHa: 20 },
  { vereda: "El Charte",   hectareas: 8.0,  toneladasPorHa: 17 },
  { vereda: "Punto Nuevo", hectareas: 20.0, toneladasPorHa: 14.5 },
  { vereda: "Tilodirán",   hectareas: 5.5,  toneladasPorHa: 12 }
];

console.log("Lotes registrados:", lotes.length);
console.log("El primero es:", lotes[0].vereda);

// Ciclo for clásico: inicio, condición, incremento.
// i++ es lo mismo que i = i + 1.
let hectareasTotales = 0;

for (let i = 0; i < lotes.length; i++) {
  hectareasTotales += lotes[i].hectareas;
}

console.log("Hectáreas totales:", hectareasTotales);

// Ciclo for...of: más legible cuando no se necesita el índice.
// Se lee: "para cada lote dentro de lotes".
console.log("Producción estimada por lote:");

for (const lote of lotes) {
  const toneladas = lote.hectareas * lote.toneladasPorHa;
  console.log("  " + lote.vereda + ": " + toneladas.toFixed(1) + " toneladas");
}
console.log("");


/* ===========================================================================
   PASO 5 - FUNCIONES: EMPAQUETAR PARA REUTILIZAR
   =========================================================================== */

console.log("=== PASO 5: el cálculo como función ===");

/**
 * Calcula el resultado económico de un lote.
 * @param {number} hectareas        Superficie sembrada.
 * @param {number} toneladasPorHa   Rendimiento esperado por hectárea al año.
 * @param {boolean} conRiego        Si el lote tiene riego.
 * @returns {object} Costo, ingreso, utilidad, margen y clasificación.
 */
function calcularLote(hectareas, toneladasPorHa, conRiego) {

  // Las variables declaradas DENTRO de la función solo existen aquí.
  // Eso se llama alcance local, y es lo que evita que una función dañe
  // por accidente los valores de otra parte del programa.
  let costoHa = COSTO_SANIDAD + COSTO_FERTILIZANTE
              + COSTO_MANO_OBRA + COSTO_MAQUINARIA;

  if (conRiego) {
    costoHa = costoHa * 1.15;
  }

  const costoTotal = costoHa * hectareas;
  const ingresoTotal = PRECIO_TONELADA * toneladasPorHa * hectareas;
  const utilidad = ingresoTotal - costoTotal;
  const margenLote = utilidad / ingresoTotal;

  // return entrega el resultado y TERMINA la función: nada de lo que
  // se escriba después de esta línea se ejecuta.
  return {
    costoTotal: costoTotal,
    ingresoTotal: ingresoTotal,
    utilidad: utilidad,
    margen: margenLote,
    clasificacion: clasificar(margenLote)
  };
}

/**
 * Traduce un margen a una etiqueta. Es una función aparte porque hace
 * una sola cosa: así se puede probar y cambiar sin tocar el cálculo.
 * @param {number} m Margen como fracción (0.25 es 25 %).
 * @returns {string} La clasificación.
 */
function clasificar(m) {
  if (m >= 0.30) return "Rentable";
  if (m >= 0.15) return "Ajustado";
  if (m > 0) return "En riesgo";
  return "Pérdida";
}

// Ahora el cálculo se aplica a los cuatro lotes sin repetir una sola línea.
let utilidadAsociacion = 0;

for (const lote of lotes) {
  const r = calcularLote(lote.hectareas, lote.toneladasPorHa, true);
  utilidadAsociacion += r.utilidad;

  console.log(
    lote.vereda.padEnd(12) +
    " margen " + (r.margen * 100).toFixed(1).padStart(5) + "%" +
    "  ->  " + r.clasificacion
  );
}

console.log("");
console.log("Utilidad total de la asociación: $",
            utilidadAsociacion.toLocaleString("es-CO"));
console.log("");


/* ===========================================================================
   SOLUCIÓN ALTERNA
   =========================================================================== */

console.log("=== Solución alterna con métodos de arreglo ===");

const resultados = lotes.map(lote =>
  calcularLote(lote.hectareas, lote.toneladasPorHa, true)
);

const totalAlterno = resultados.reduce((suma, r) => suma + r.utilidad, 0);

const enRiesgo = lotes.filter((lote, i) => resultados[i].margen < 0.15);

console.log("Utilidad total (misma cuenta):  $",
            totalAlterno.toLocaleString("es-CO"));
console.log("Lotes con margen bajo:", enRiesgo.length);

// Comprobación: las dos formas deben dar exactamente lo mismo.
console.log("¿Los dos métodos coinciden?",
            Math.abs(totalAlterno - utilidadAsociacion) < 0.01);
