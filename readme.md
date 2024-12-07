# Examen Practico de Almacen de Contenedores

## Descripción

Un almacén de contenedores, quiere tener el control de sus entradas y salidas de contenedores en el que ingresan en camiones con 1 plataforma, se requiere tener vista del inventario actual y el historial de las salidas del almacén.

## Estructura del Proyecto

- **index.php**: Página principal.
- **js/structure/structure.js**: Lógica de la estructura de la aplicación.
- **js/structure/init.js**: Inicializa componentes y elementos de la interfaz.
- **controller/Conexion_bd.php**: Configuración de conexión a la base de datos.
- **controller/CapturaController.php**: Controlador para el registro de contenedores.
- **controller/InventarioController.php**: Controlador para gestionar el inventario de contenedores.
- **controller/ConsultaController.php**: Controlador para realizar consultas sobre los contenedores.
- **controller/ActualizacionController.php**: Controlador para actualizar los registros de los contenedores.

## Procedimientos en la base de datos
- **RegistrarEntrada**: Procedimiento para registrar una entrada de contenedor.
- **RegistrarSalida**: Procedimiento para registrar una salida de contenedor.
- **ActualizarEntrada**: Procedimiento para actualizar la información de un contenedor.

## Estructura de la base de datos
- **contenedores**: Tabla que almacena la información sobre los contenedores.
  - `id`: Identificador único del contenedor.
  - `numero_contenedor`: Número único de identificación del contenedor.
  - `tamano`: Tamaño del contenedor (`20HC`, `40HC`).
  - `estado`: Estado del contenedor (`Dentro`, `Fuera`).
  - `fecha_ultima_modificacion`: Fecha de la última modificación del estado.

- **camiones**: Tabla que almacena la información sobre los camiones.
  - `id`: Identificador único del camión.
  - `numero_economico`: Número económico del camión.
  - `placas`: Placas del camión.
  - `conductor`: Nombre del conductor del camión.

- **registros**: Tabla que almacena los registros de entrada y salida de los contenedores.
  - `id`: Identificador único del registro.
  - `contenedor_id`: Relacionado con la tabla `contenedores`.
  - `camion_id`: Relacionado con la tabla `camiones`.
  - `flujo`: Flujo de movimiento (`Entrada`, `Salida`).
  - `fecha_hora`: Fecha y hora de la acción.

## Interfaz de Usuario

- **Página Principal (index.php)**: La página principal muestra el estado general del almacén, incluidos los contenedores que están dentro o fuera.
- **Formulario de Registro de Entrada y Salida**: Mediante este formulario, los usuarios pueden registrar entradas y salidas de contenedores.
- **Vista de Inventario Actual**: Una sección donde se puede consultar el inventario actual, mostrando todos los contenedores con su estado.
- **Vista de Historial de Movimientos**: Una sección donde se puede consultar el historial de movimientos, mostrando el número de contenedor, el tamaño, el flujo y la fecha de la entrada o salida.

Los formularios de entrada y salida utilizan JavaScript (con jQuery) para validar los datos antes de enviarlos al servidor.

## Tecnologías Utilizadas
- **PHP 8.3.9**
- **MariaDB 10.2.7**
- **Bootstrap 5.3.0**
- **jQuery 3.6.4**
- **SweetAlert 2**
- **Flatpickr**

## Herramientas Utilizadas
- **Visual Studio Code**
- **Navicat**
