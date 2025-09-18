if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    document.documentElement.setAttribute('data-bs-theme', 'dark');
}

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', cambiarTema);
document.querySelectorAll('input[readonly]').forEach(input => input.addEventListener('click', cambiarValorBotones));
document.querySelectorAll('#letras input').forEach(input => input.addEventListener('input', cambiarFoco));

function buscar() {
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
                            input.value = palabra[index] || '';
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
