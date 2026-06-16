# Taste — Guía de proyecto para Claude

## Stack
- PHP 7/8 + MySQL (PDO), Bootstrap 4, jQuery, ApexCharts, Handlebars.js
- Sin framework PHP. Todo MVC manual.
- Servidor: Laragon (localhost). DB: `jc_taste`.

## Estructura de carpetas
```
/clases/            Clases de negocio (cl_empresas, cl_sucursales, cl_ordenes, etc.)
/controllers/       Controladores AJAX (devuelven JSON). Usan controller_create() de funciones.php
/content_index/     Fragmentos PHP incluidos por index.php según rol
/assets/js/pages/   JS por página
/assets/js/dashboard/ JS del dashboard (charts.js, metrics.js, shared.js)
/plugins/           Librerías JS externas (apex/, momentjs/, datatables/, etc.)
/excel_export/      Exportación a XLSX con SimpleXLSXGen
```

## Patrones clave

### Controladores AJAX
Todos los controladores siguen este patrón:
```php
require_once "../funciones.php";
controller_create(); // lee $_GET['metodo'] y llama la función PHP del mismo nombre

function miFuncion() {
    $input = json_decode(file_get_contents("php://input"), true);
    // lógica...
    return ['success' => 1, 'data' => $resultado];
}
```

### Autenticación
```php
if (!isLogin()) { header("location:login.php"); }
$session = getSession(); // tiene: cod_usuario, cod_rol, cod_empresa, alias, nombre
```

### Roles
- `1` = Super Admin (Taste)
- `2` = Admin Empresa
- `3` = Admin Sucursal
- `19` = Delivery

### Conexión a base de datos
```php
Conexion::buscarRegistro($sql, $params)      // 1 fila
Conexion::buscarVariosRegistro($sql, $params) // N filas
Conexion::ejecutar($sql, $params)             // INSERT/UPDATE/DELETE
Conexion::getSingleValue($sql, $params)       // 1 valor escalar
```
Params siempre como array `[':campo' => $valor]`.

### Layout de página
```php
<?php css_mandatory(); ?>    <!-- en <head> -->
<?php echo top(); ?>
<?php echo navbar(); ?>
<?php echo sidebar(); ?>
<!-- contenido -->
<?php footer(); ?>
<?php js_mandatory(); ?>     <!-- antes de </body> -->
```

### Gráficas (dashboard)
- `charts.js` expone: `addWidgetText()`, `generateChart()`, `createBarChartData()`, `createPieChartData()`, `createLineChartData()`
- `template` = variable global Handlebars compilada desde `#widget-template`
- Los widgets se insertan en `#widgetsInfo`

## Tablas principales
- `tb_empresas` — cod_empresa, nombre, alias, estado (A/I/D)
- `tb_sucursales` — cod_sucursal, cod_empresa, nombre, estado
- `tb_orden_cabecera` — cod_orden, cod_empresa, cod_sucursal, cod_usuario, total, subtotal, descuento, is_envio (0=pickup/1=delivery/2=mesa), medio_compra, estado, fecha
- `tb_estado_ordenes` — cod_estado, nombre, icono, posicion
- `tb_usuarios` — cod_usuario, cod_empresa, cod_rol, nombre, alias
- `tb_productos` — cod_producto, cod_empresa, nombre, precio, image_min

## Estados de órdenes (texto)
ENTRANTE → ACEPTADA → PREPARANDO → ASIGNADA → ENVIANDO → ENTREGADA / CANCELADA / NO_ENTREGADA / ANULADA

## Páginas de reportes existentes
`reporte_ventas.php`, `reporte_delivery.php`, `reporte_diario.php`, `reporte_mapa_calor.php`,
`reporte_courier.php`, `reporte_estado_empresa.php` (super admin, rango de fechas libre + PDF)

## Notas importantes
- `url_sistema` = constante con la URL base (definida en config.php)
- `fechaLatinoShort($fecha)` = helper de fecha en formato latino
- `editor_decode($texto)` = decodifica contenido de editor rich text
- No hay composer ni autoload. Todos los `require_once` son manuales.
- PDF del reporte_estado_empresa usa ApexCharts dataURI + jsPDF (NO html2canvas).
