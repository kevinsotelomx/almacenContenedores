<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Contenedores</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <!-- Estilos CSS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Flatpickr CSS -->
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    <!-- Script de Flatpickr para usar el calendario -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Estilos personalizados para la tabla y otros elementos -->
    <style>
        table {
            border-radius: 10px;
            overflow: hidden;
        }
        thead th {
            background-color: #007bff;
            color: #fff;
        }
        tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        tbody tr:hover {
            background-color: #f1f1f1;
        }

        .form-label{
            font-size: 1rem;
            font-weight: bold;
        }

        .d-none {
            display: none;
        }
    </style>

</head>
    <body>
        <div class="container mt-4">
            <h1 class="text-center mb-4">Almacen de Contenedores</h1>
            
            <form id="form_registro" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="numeroContenedor" class="form-label">Número de Contenedor</label>
                        <input type="text" class="form-control" id="numero_contenedor" placeholder="Ej. ABCD1234567" maxlength="15" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label for="tamanoContenedor" class="form-label">Tamaño del Contenedor</label>
                        <select class="form-select" id="tamano_contenedor">
                            <option value="" selected>Seleccione...</option>
                            <option value="20HC">20HC</option>
                            <option value="40HC">40HC</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="numeroEconomico" class="form-label">Número Económico</label>
                        <input type="text" class="form-control" id="numero_economico" placeholder="Ej. 12345" maxlength="10" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label for="placasUnidad" class="form-label">Placas de la Unidad</label>
                        <input type="text" class="form-control" id="placas_unidad" placeholder="Ej. ABC-1234" maxlength="15" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label for="nombreConductor" class="form-label">Nombre del Conductor</label>
                        <input type="text" class="form-control" id="nombre_conductor" placeholder="Ej. Juan Pérez" maxlength="100" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label for="flujo" class="form-label">Entrada / Salida</label>
                        <select class="form-select" id="flujo">
                            <option value="" selected>Seleccione...</option>
                            <option value="Entrada">Entrada</option>
                            <option value="Salida">Salida</option>
                        </select>
                    </div>
                </div>

                <span class="btn btn-primary mt-3" id="guardar_contenedor" name="guardar_contenedor">
                    <i class="fa fa-save"></i> Guardar
                </span>
            </form>

            <div class="text-center mb-4">
                <label class="form-check-label me-3">
                    <input type="radio" name="tabla_selector" value="inventario" checked>
                    Inventario Actual
                </label>
                <label class="form-check-label">
                    <input type="radio" name="tabla_selector" value="historial">
                    Historial de Movimientos
                </label>
            </div>

            <!-- Tabla para inventario -->
            <div id="tabla_inventario_container">
                <h2 class="text-center mb-4 inventarioActual">Inventario Actual de Contenedores</h2>
                <table class="table table-bordered table-striped inventarioActual" id="tabla_inventario_actual">
                    <thead class="table-dark">
                        <tr>
                            <th>Número de Contenedor</th>
                            <th>Tamaño</th>
                            <th>Fecha y Hora</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>

            <!-- Tabla para historial de movimientos -->
            <div id="tabla_historial_container" class="d-none">
                <h2 class="text-center mb-4 historialMovimientos">Historial de Movimientos</h2>
                <table class="table table-bordered table-striped historialMovimientos" id="tabla_inventario">
                    <thead class="table-dark">
                        <tr>
                            <th>Número de Contenedor</th>
                            <th>Tamaño</th>
                            <th>Flujo</th>
                            <th>Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

        <!-- jQuery JS -->
        <script src="js/structure/structure.js?v=1"></script>
        <script src="js/structure/init.js?v=1"></script>
    </body>

    <!-- Modal para editar un contenedor -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarLabel">Editar Registro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditar">
                        <div class="mb-3">
                            <label for="editarNumeroContenedor" class="form-label">Número de Contenedor</label>
                            <input type="text" class="form-control" id="editarNumeroContenedor" maxlength="15">
                        </div>
                        <div class="mb-3">
                            <label for="editarNumeroEconomico" class="form-label">Número Económico</label>
                            <input type="text" class="form-control" id="editarNumeroEconomico" maxlength="10">                            
                        </div>
                        <div class="mb-3">
                            <label for="editarPlacasUnidad" class="form-label">Número de Placas</label>
                            <input type="text" class="form-control" id="editarPlacasUnidad" maxlength="15">
                        </div>
                        <div class="mb-3">
                            <label for="editarTamanoContenedor" class="form-label">Tamaño</label>
                            <input type="text" class="form-control" id="editarTamanoContenedor" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="editarFechaHora" class="form-label">Fecha y Hora de Entrada</label>
                            <input type="text" class="form-control" id="editarFechaHora">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="guardarCambios" data-id="">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>
</html>
