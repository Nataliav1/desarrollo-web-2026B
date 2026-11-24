// ==========================================
// ADEPALMA Casanare - app.js
// Crea las filas de la tabla de puntos de recolección (puntos.html)
// ==========================================

// Cada punto es un objeto con tres datos. "aceite" está en toneladas por mes.
// Son cifras de referencia mensual: se actualizan con el reporte de cada
// acopio (ver el aviso al final de puntos.html).
const puntos = [
    { nombre: "Acopio Palmeras del Morichal", vereda: "Morichal", aceite: 185 },
    { nombre: "Acopio El Charte", vereda: "El Charte", aceite: 142 },
    { nombre: "Acopio Punto Nuevo", vereda: "Punto Nuevo", aceite: 120 },
    { nombre: "Acopio Tilodirán", vereda: "Tilodirán", aceite: 96 },
    { nombre: "Acopio Caribayona", vereda: "Caribayona", aceite: 78 },
    { nombre: "Acopio Matepantano", vereda: "Matepantano", aceite: 64 }
];

// Da formato colombiano a un número: 1250 -> "1.250"
function formato(numero) {
    return numero.toLocaleString("es-CO");
}

// Devuelve el punto con más producción. Se parte del primero y se compara
// con el resto.
function encontrarMayor(lista) {
    let mayor = lista[0];
    for (let i = 1; i < lista.length; i++) {
        if (lista[i].aceite > mayor.aceite) {
            mayor = lista[i];
        }
    }
    return mayor;
}

// Suma el aceite de todos los puntos.
function calcularTotal(lista) {
    let total = 0;
    lista.forEach(function (punto) {
        total = total + punto.aceite;
    });
    return total;
}

// Crea una celda <td> o <th> con texto. Se usa textContent y no innerHTML:
// así un nombre con "<" o "&" se muestra como texto y nunca como código.
function crearCelda(etiqueta, texto, clase) {
    const celda = document.createElement(etiqueta);
    celda.textContent = texto;
    if (clase) {
        celda.className = clase;
    }
    if (etiqueta === "th") {
        celda.scope = "row";
    }
    return celda;
}

// Crea la fila <tr> de un punto, con la barrita que lo compara con el mayor.
function crearFila(punto, mayor) {
    const fila = document.createElement("tr");
    fila.appendChild(crearCelda("th", punto.nombre, "texto"));
    fila.appendChild(crearCelda("td", punto.vereda, "texto"));

    const celdaAceite = crearCelda("td", formato(punto.aceite) + " t");
    // <progress> dibuja la barrita sin necesitar ningún atributo style: el
    // valor del punto frente al del mayor. Es decorativa (el número ya está
    // escrito en la celda), por eso aria-hidden.
    const barra = document.createElement("progress");
    barra.className = "barra";
    barra.max = mayor.aceite;
    barra.value = punto.aceite;
    barra.setAttribute("aria-hidden", "true");
    celdaAceite.appendChild(barra);
    fila.appendChild(celdaAceite);

    return fila;
}

function mostrarPuntos() {
    const cuerpo = document.getElementById("cuerpo-puntos");
    const celdaTotal = document.getElementById("total-aceite");
    const resumen = document.getElementById("resumen");

    // Si esta página no tiene la tabla, no hacemos nada
    if (!cuerpo) {
        return;
    }

    const mayor = encontrarMayor(puntos);
    const total = calcularTotal(puntos);

    // Por cada punto se crea una fila y se agrega al <tbody>
    puntos.forEach(function (punto) {
        cuerpo.appendChild(crearFila(punto, mayor));
    });

    celdaTotal.textContent = formato(total) + " t";

    resumen.textContent =
        "Entre los " + puntos.length + " puntos suman " + formato(total) +
        " toneladas de aceite al mes. El de mayor producción es " + mayor.nombre +
        ", en la vereda " + mayor.vereda + ".";
}

mostrarPuntos();
