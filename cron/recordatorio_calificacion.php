<?php
/**
 * Cron: recordatorio de calificación 30 minutos después de la entrega
 *
 * Ejecutar cada 5 minutos:
 *   php /ruta/a/api_gestion_ordenes/cron/recordatorio_calificacion.php
 *
 * Ejemplo crontab:
 *   *\/5 * * * * php /home/usuario/public_html/api_gestion_ordenes/cron/recordatorio_calificacion.php >> /home/usuario/logs/recordatorio_cron.log 2>&1
 */

// Solo permitir ejecución desde CLI
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Acceso denegado');
}

// ── Bootstrap ─────────────────────────────────────────────────────────────

$rootPath = dirname(__DIR__);

require_once $rootPath . '/config.php';
require_once $rootPath . '/conexion.php';
require_once $rootPath . '/funciones.php';
require_once $rootPath . '/helpers/notificationsToClient.php';

$timestamp = date('[Y-m-d H:i:s]');
echo "$timestamp Iniciando recordatorios de calificación\n";

// La fecha de entrega sale de tb_orden_historial (no de tb_orden_cabecera, que no tiene columna
// propia para esto) — tanto api_flotas como api_gestion_ordenes escriben ahí al pasar a
// ENTREGADA, así que cubre pedidos con o sin motorizado/flota de por medio.
$sql = "SELECT oc.cod_orden, oc.cod_usuario, oc.is_envio
        FROM tb_orden_cabecera oc
        INNER JOIN (
            SELECT cod_orden, MAX(fecha) AS fecha_entrega
            FROM tb_orden_historial
            WHERE estado = 'ENTREGADA'
            GROUP BY cod_orden
        ) h ON h.cod_orden = oc.cod_orden
        WHERE oc.estado = 'ENTREGADA'
        AND oc.recordatorio_calificacion_enviado = 0
        AND h.fecha_entrega <= NOW() - INTERVAL 30 MINUTE";

$ordenes = Conexion::buscarVariosRegistro($sql, null);

if (empty($ordenes)) {
    echo "$timestamp Sin pedidos pendientes de recordatorio. Fin.\n";
    exit(0);
}

echo "$timestamp Pedidos a notificar: " . count($ordenes) . "\n";

foreach ($ordenes as $orden) {
    try {
        notificarRecordatorioCalificacion($orden);

        Conexion::ejecutar(
            "UPDATE tb_orden_cabecera SET recordatorio_calificacion_enviado = 1 WHERE cod_orden = :cod_orden",
            [':cod_orden' => $orden['cod_orden']]
        );

        echo "$timestamp [#{$orden['cod_orden']}] Recordatorio enviado\n";
    } catch (Exception $ex) {
        echo "$timestamp [#{$orden['cod_orden']}] ERROR: " . $ex->getMessage() . "\n";
        error_log(
            date('[Y-m-d H:i:s] ') . "[#{$orden['cod_orden']}] " . $ex->getMessage() . PHP_EOL,
            3,
            $rootPath . '/errores_sql.log'
        );
    }
}

echo "$timestamp Fin de ejecución\n";
