(function() {
    // Boton para mostrar el Modal de agregar tarea
    
        const nuevaTareaBtn = document.querySelector('#agregar-tarea');
        nuevaTareaBtn.addEventListener('click', mostrarFormulario);
    
        function mostrarFormulario () {
           const modal = document.createElement('DIV');
           modal.classList.add('modal');
           modal.innerHTML = `
                <form class="formulario nueva-tarea">
                    <legend>Añade Nueva Tarea</legend>
                    <div class="campo">
                        <label>Tarea</label>
                        <input type="text" name="tarea" placeholder="Añadir al Proyecto Actual" id="tarea" />
                    </div>
                    <div class="opciones">
                        <input type="submit" class="submi-nueva-tarea" value="Añadir Tarea" />
                        <button type="button" class="cerrar-modal">Cancelar</button>
                    </div>
                </form>
                `;

                setTimeout(() => {
                    const formulario = document.querySelector('.formulario');
                    formulario.classList.add('animar');
                }, 0);
                modal.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (e.target.classList.contains('cerrar-modal')) {
                        const formulario = document.querySelector('.formulario');
                        formulario.classList.add('cerrar');
                        setTimeout(() => {
                            modal.remove();
                        }, 500);
                    } 
                    if (e.target.classList.contains('submi-nueva-tarea')) {
                        submitFormularioNuevaTarea();
                    }

                    function submitFormularioNuevaTarea () {
                        const tarea = document.querySelector('#tarea').value.trim();

                        if(tarea === '') {
                            // Mostrar un alerta de error
                            mostrarAlerta('El nombre de la Tarea es Obligatorio', 'error', document.querySelector('.formulario legend'));
                            return;
                            } 
                            agregarTarea(tarea);
                        }
                    })
                        document.querySelector('.dashboard').appendChild(modal);
                     }
                        
                    
                    function mostrarAlerta (mensaje, tipo, referencia) {
                        const alertaPrevia = document.querySelector('.alerta');
                        if(alertaPrevia) {
                            alertaPrevia.remove();
                        }

                        const alerta = document.createElement('DIV');
                        alerta.classList.add('alerta', tipo);
                        alerta.textContent = mensaje;
                        // Inserta la alerta antes del legend
                        
                        referencia.parentElement.insertBefore(alerta, referencia.nextElementSibling);

                    }
                    
                    // Consultar al servidor para añadir una nueva tarea al Proyecto actual
                    async function agregarTarea(tarea) {
                        // Construir la petición

                        const datos = new FormData();
                        datos.append('nombre', tarea);
                        datos.append('proyectoId', obtenerProyecto());
                      
                        
                        try {
                            const url = 'http://localhost:3000/api/tarea';
                            const respuesta = await fetch(url, {
                                method: 'POST',
                                body: datos
                            });
                            const resultado = await respuesta.json();
                            console.log(respuesta);
                        } catch (error) {
                            console.log('Error');
                        }
                    }

                    function obtenerProyecto () {
                        const proyectoParams = new URLSearchParams(window.location.search);
                        const proyecto = Object.fromEntries(proyectoParams.entries());
                        return proyecto.id;
                    }
                   // for (let valor of datos.values()) {
                     //   console.log(valor);
                        
                    //}
    
    
    })();