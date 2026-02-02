function actualizarSimulacion() {
    // Obtener valores de los sliders
    let cap = document.getElementById('capital').value;
    let ann = document.getElementById('años').value;
    let int = document.getElementById('interes').value;

    // Actualizar etiquetas visuales
    document.getElementById('valCapital').innerText = new Intl.NumberFormat().format(cap);
    document.getElementById('valAños').innerText = ann;
    document.getElementById('valInteres').innerText = int;

    // Llamada al backend
    fetch(`calcularHipoteca.php?cap=${cap}&ann=${ann}&int=${int}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('cuotaFinal').innerText = data + " €";
        });
}

function runSimulacion() {
    const cap = document.getElementById('rangeCap').value;
    const int = document.getElementById('rangeInt').value;
    const ann = document.getElementById('rangeAños').value;

    // Actualizar etiquetas visuales
    document.getElementById('valCap').innerText = new Intl.NumberFormat().format(cap);
    document.getElementById('valInt').innerText = int;
    document.getElementById('valAños').innerText = ann;

    // Petición al backend que conecta con Java
    fetch(`calcularHipoteca.php?cap=${cap}&int=${int}&ann=${ann}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('resultadoCuota').innerText = data + " €";
        });
}

function guardarInversion() {
    const activo = document.getElementById('activo').value;
    const cantidad = document.getElementById('cantidad').value;

    if(activo === "" || cantidad === "") {
        alert("Por favor, rellena todos los campos");
        return;
    }

    const formData = new FormData();
    formData.append('activo', activo);
    formData.append('cantidad', cantidad);

    fetch('guardarInversion.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(res => {
        if(res.trim() === "ok") {
            alert("¡Inversión guardada con éxito!");
            location.reload(); // Recarga para ver la nueva fila en la tabla
        } else {
            alert("Error del servidor: " + res);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Ejecutar una vez al cargar para mostrar el valor inicial
window.onload = runSimulacion;