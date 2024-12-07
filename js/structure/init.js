$(function($){    

    // Maneja el evento cuando se hace clic en el botón de guardar contenedor
    $(document).on('click', '#guardar_contenedor', function(event) {
        event.preventDefault();

        const campos = [
            { id: '#numero_contenedor', mensaje: 'El campo Número de Contenedor es obligatorio' },
            { id: '#tamano_contenedor', mensaje: 'El campo Tamaño del Contenedor es obligatorio' },
            { id: '#numero_economico', mensaje: 'El campo Número Económico es obligatorio' },
            { id: '#placas_unidad', mensaje: 'El campo Placas de la Unidad es obligatorio' },
            { id: '#nombre_conductor', mensaje: 'El campo Nombre del Conductor es obligatorio' },
            { id: '#flujo', mensaje: 'El campo Entrada / Salida es obligatorio' }
        ];
    
        for (const campo of campos) {
            if ($(campo.id).val().trim() === '') {
                mostrarSwalError(campo.id, campo.mensaje);
                return false;
            }
        }
    
        guardarContenedor();
    });
    
    // Función para mostrar un mensaje de error con SweetAlert
    function mostrarSwalError(campoId, mensaje) {
        $(campoId).focus();
        Swal.fire({
            title: "Espera un Momento!",
            text: mensaje,
            icon: "warning"
        });
    }
    
    // Función para guardar un contenedor en la base de datos mediante AJAX
    function guardarContenedor() {
        const data = {
            numero_contenedor: $('#numero_contenedor').val(),
            tamano_contenedor: $('#tamano_contenedor').val(),
            numero_economico: $('#numero_economico').val(),
            placas_unidad: $('#placas_unidad').val(),
            nombre_conductor: $('#nombre_conductor').val(),
            flujo: $('#flujo').val()
        };

        $('#guardar_contenedor').attr('disabled', true);

        $.ajax({
            url: 'controller/CapturaController.php',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                Swal.fire({
                    title: response.title,
                    text: response.message,
                    icon: response.status === 'success' ? "success" : "error"
                }).then(() => {
                    location.reload(); 
                });
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    title: "Error",
                    text: "No se pudo conectar con el servidor. Intenta de nuevo más tarde.",
                    icon: "error"
                });
            }
        });
    }

    // Función para inicializar la tabla de inventario actual
    $(document).ready(function () {
        flatpickr("#editarFechaHora", {
            enableTime: true,
            dateFormat: "Y-m-d H:i", 
            locale: "es",
            minuteIncrement: 1 
        });

        // Función para cargar el historial de movimientos
        function verHistorial() {
            $.ajax({
                url: 'controller/ConsultaController.php', 
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        const datos = response.data;
    
                        const tbody = $('#tabla_inventario tbody');
                        tbody.empty();
    
                        datos.forEach(dato => {
                            const fila = `
                                <tr>
                                    <td>${dato.numero_contenedor}</td>
                                    <td>${dato.tamano}</td>
                                    <td>${dato.flujo}</td>
                                    <td>${dato.fecha_hora}</td>
                                </tr>
                            `;
                            tbody.append(fila);
                        });
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: response.message,
                            icon: "error"
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        title: "Error",
                        text: "No se pudo obtener el inventario.",
                        icon: "error"
                    });
                }
            });
        }
        
        // Función para cargar el inventario actual
        function verInventarioActual() {
            $.ajax({
                url: 'controller/InventarioController.php',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        const datos = response.data;
    
                        const tbody = $('#tabla_inventario_actual tbody');
                        tbody.empty();
    
                        datos.forEach(dato => {
                            const fila = `
                                <tr>
                                    <td>${dato.numero_contenedor}</td>
                                    <td>${dato.tamano}</td>
                                    <td>${dato.fecha_hora}</td>
                                    <td>
                                        <div class="d-flex justify-content-center">
                                            <button class="btn btn-warning btn-sm editar-contenedor" data-id="${dato.contenedor_id}">
                                                <i class="fa fa-edit"></i> Editar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                `;
                            tbody.append(fila);
                        });
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: response.message,
                            icon: "error"
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        title: "Error",
                        text: "No se pudo obtener el inventario.",
                        icon: "error"
                    });
                }
            });
        }

        verInventarioActual();
        verHistorial();
    });

    // Maneja el evento cuando cambia el selector de tabla
    $('input[name="tabla_selector"]').on('change', function () {
        const selectedTable = $(this).val();

        if (selectedTable === 'inventario') {
            $('#tabla_inventario_container').removeClass('d-none');
            $('#tabla_historial_container').addClass('d-none');
        } else if (selectedTable === 'historial') {
            $('#tabla_inventario_container').addClass('d-none');
            $('#tabla_historial_container').removeClass('d-none');
        }
    });

    // Maneja el evento cuando se hace clic en el botón de editar un contenedor
    $(document).on('click', '.editar-contenedor', function () {
        const id = $(this).data('id'); 

        $.ajax({
            url: 'controller/InventarioController.php',
            type: 'POST',
            dataType: 'json',
            data: { id: id, action: 'getRegistro' },
            success: function (response) {
                if (response.status === 'success') {
                    const registro = response.data[0];
    
                    $('#guardarCambios').data('id', id);
                    $('#editarNumeroContenedor').val(registro.numero_contenedor);
                    $('#editarNumeroEconomico').val(registro.numero_economico);
                    $('#editarPlacasUnidad').val(registro.placas);
                    $('#editarTamanoContenedor').val(registro.tamano);
                    $('#editarFechaHora').val(registro.fecha_hora);
                    
                    $('#modalEditar').modal('show');
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error"
                    });
                }
            },
            error: function () {
                Swal.fire({
                    title: "Error",
                    text: "No se pudo obtener la información del registro.",
                    icon: "error"
                });
            }
        });
    });

    // Maneja el evento cuando se hace clic en el botón de guardar los cambios
    $(document).on('click', '#guardarCambios', function (e) {
        e.preventDefault();
        
        var id = $(this).data('id'); 
        console.log('ID del contenedor:', id); 
        var numeroContenedor = $('#editarNumeroContenedor').val();
        var numeroPlaca = $('#editarPlacasUnidad').val();
        var fechaHora = $('#editarFechaHora').val();
        var numeroEconomico = $('#editarNumeroEconomico').val();
        var tamanoContenedor = $('#editarTamanoContenedor').val();

        if (!numeroContenedor || !numeroPlaca || !fechaHora || !numeroEconomico) {
            Swal.fire({
                title: "Error",
                text: "Todos los campos son obligatorios.",
                icon: "error"
            });
            return;
        }

        $.ajax({
            url: 'controller/ActualizacionController.php',
            type: 'POST',
            data: {
                id: id,
                numero_contenedor: numeroContenedor,
                tamano: tamanoContenedor,
                numero_economico: numeroEconomico,
                placas: numeroPlaca,
                fecha_hora: fechaHora
            },

            dataType: 'json',
            success: function (response) {
                console.log(response);
                if (response.status === 'success') {
                    Swal.fire({
                        title: "Éxito",
                        text: response.message,
                        icon: "success"
                    }).then(() => {
                        location.reload(); 
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error"
                    });
                }
            },
            error: function (xhr, status, error) {
                Swal.fire("Error", "Error en la solicitud AJAX:", error);
                console.error("Error en la solicitud AJAX:", error);
                console.log(xhr.responseText);
            }
        });
    });
});