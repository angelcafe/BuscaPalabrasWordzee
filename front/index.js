if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    document.documentElement.setAttribute('data-bs-theme', 'dark');
}
const activar_herramientas = Boolean(localStorage.getItem('ActivarHerramientas'));
document.getElementById('idActivarHerramientas').checked = activar_herramientas;
activarHerramientas();

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', cambiarTema);
document.getElementById('idActivarHerramientas').addEventListener('change', activarHerramientas);
document.querySelectorAll('input[readonly]').forEach(input => input.addEventListener('click', cambiarValorBotones));
document.querySelectorAll('#letras input').forEach(input => input.addEventListener('input', cambiarFoco));

function activarHerramientas() {
    const idHerramientas = document.getElementById('idHerramientas');
    const idActivarHerramientas = document.getElementById('idActivarHerramientas');
    if (idActivarHerramientas.checked) {
        localStorage.setItem('ActivarHerramientas', true);
        idHerramientas.classList.remove('d-none');
    } else {
        localStorage.removeItem('ActivarHerramientas');
        idHerramientas.classList.add('d-none');
    }
}

function cambiarFoco(ev) {
    if (ev.inputType === 'insertText') {
        const letras_permitidas = /^[a-jl-vx-zA-JL-VX-ZñÑ]$/;
        if (letras_permitidas.test(ev.target.value)) {
            if (ev.target.nextElementSibling) {
                ev.target.nextElementSibling.focus()
            }
        } else {
            ev.target.value = ''
        }
    } else if (ev.inputType === 'deleteContentBackward' && ev.target.previousElementSibling) {
        ev.target.previousElementSibling.focus()
    }
}

function cambiarTema(event) {
    const newColorScheme = event.matches ? "dark" : "light";
    document.documentElement.setAttribute('data-bs-theme', newColorScheme);
}

function cambiarValorBotones(ev) {
    const clases = { I: 'rosa', DL: 'bg-success', TL: 'info' };
    const valores = { I: 'DL', DL: 'TL', TL: '' };
    const target = ev.currentTarget;
    const currentValue = target.value || 'I';
    target.classList.remove('rosa', 'bg-success', 'info');
    target.value = valores[currentValue];
    target.classList.add(clases[currentValue]);
}

function formularioEnviar() {
    if (!verificarCamposRellenos()) {
        alert('Debe rellenar todos los campos');
        return;
    }
    const formulario = new FormData(document.getElementById('principal'));
    const cargando = document.getElementById('cargando');
    cargando.classList.remove('d-none');
    fetch('./back/buscar.php', {
        method: 'POST',
        body: formulario
    })
        .then(response => {
            if (response.ok) {
                return response.json();
            } else {
                throw new Error('Network response was not ok');
            }
        })
        .then(data => {
            mostrarPalabrasEncontradas('idPalabrasEncontradas', 'palenc noselect', data['encontradas']);
            mostrarPalabrasEncontradas('idPalabrasGanadoras', 'noselect', data['sugeridas']);
            cargando.classList.add('d-none');
            const palencs = document.querySelectorAll('td.palenc');
            palencs.forEach(function (palenc) {
                palenc.addEventListener('click', function () {
                    const palabra = palenc.textContent.split(' ')[0];
                    if (palabra.length > 0) {
                        const inputs = document.querySelectorAll('#letras>input');
                        inputs.forEach(function (input, index) {
                            input.value = palabra[index];
                        });
                        const emptyInput = document.querySelector('#letras>input[value=""]');
                        if (emptyInput) {
                            emptyInput.focus();
                        }
                        document.getElementById('letras').scrollIntoView();
                    }
                });
            });
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function mostrarPalabrasEncontradas(id, clase, datos) {
    const tabla = [[], [], [], [], []];
    for (const [palabra, puntos] of Object.entries(datos)) {
        const index = palabra.length - 3;
        tabla[index].push(`<td class="${clase}">${palabra} - ${puntos}</td>`);
    }
    const maximo = Math.max(...tabla.map(arr => arr.length));
    const filas = Array.from({ length: maximo }, (_, x) => {
        return '<tr>' + tabla.map(arr => arr[x] || '<td></td>').join('') + '</tr>';
    });
    const element = document.getElementById(id);
    element.innerHTML = filas.join('');
}

function restablecer() {
    window.location.href = window.location.href;
}

function verificarCamposRellenos() {
    const campos = document.querySelectorAll('input[name="letrasDisponibles[]"]');
    for (let campo of campos) {
        if (campo.value.trim() === '') {
            return false;
        }
    }
    return document.querySelector('input[name="ronda"]:checked') !== null;
}


/*
function inicializarEventos() {
    $('input[name="ronda"]').on('change', resetearInputLetras).first().attr('checked', true);
}

function resetearInputLetras() {
    $('#letras>input').val('').first().trigger('focus');
}

// Admin

const palabras = {
    alerta: function () {
        let myModal = new bootstrap.Modal(document.getElementById('modalAlert'));
        document.querySelector('#modalAlert .modal-title').innerText = palabras.alertaTitulo;
        document.querySelector('#modalAlert .modal-body p').innerText = palabras.alertaTexto;
        myModal.show();
        if (palabras.alertaDescargar) {
            palabras.descargar();
        }
    },
    alertaDescargar: false,
    alertaTexto: '',
    alertaTitulo: '',
    borrar: function (palabra) {
        ajax('borrar=' + palabra);
    },
    descargar: function () {
        let blob = JSON.stringify(palabras.responde);
        let fileName = 'sp.json';
        let link = document.createElement('a');
        let binaryData = [];
        binaryData.push(blob);
        link.href = window.URL.createObjectURL(new Blob(binaryData, { type: "application/json" }));
        link.download = fileName;
        link.click();
    },
    exportar: function () {
        palabras.alertaTitulo = 'Exportar JSON';
        palabras.alertaTexto = 'Exportación finalizada.';
        palabras.alertaDescargar = true;
        ajax('exportar=true', true);
    },
    guardar: function () {
        let palabra = document.getElementById('palabraGuardar').value;
        ajax('guardar=' + palabra);
    },
    importar: function () {
        const file = document.getElementById('formFile').files[0];
        const formd = new FormData();
        formd.append('archivo', file);
        palabras.alertaTitulo = 'Importar JSON';
        palabras.alertaTexto = 'Importación finalizada.';
        palabras.alertaDescargar = false;
        ajax(formd, true, false);
        return false;
    },
    responde: '',
};

$('#idBorrar').on('click', e => {
    palabras.borrar($('#palabraBorrar').val());
});
$('#idGuardar').on('click', palabras.guardar);

document.onclick = function (e) {
    document.getElementById('menu').style.display = 'none';
}

document.oncontextmenu = function (e) {
    document.getElementById('menu').style.display = 'none';
    if (e.target.className.includes("menu")) {
        e.preventDefault();
        document.getElementById('menu').style.left = e.pageX - 100 + 'px';
        document.getElementById('menu').style.top = e.pageY + 'px';
        document.getElementById('menu').style.display = 'block';
        document.getElementById('idBorrarPalabra').innerText = e.target.innerText.split(' ')[0];
        document.getElementById('idBorrarPalabra').addEventListener('click', e => {
            palabras.borrar(e.target.innerText);
        });
    }
}

Array.from(document.querySelectorAll("td.palenc")).forEach(element => {
    element.addEventListener("click", function (e) {
        let palabra = e.target.innerText.split(' ')[0];
        if (palabra.length > 0) {
            $('#palabraBorrar').val(palabra);
        }
    });
});
*/