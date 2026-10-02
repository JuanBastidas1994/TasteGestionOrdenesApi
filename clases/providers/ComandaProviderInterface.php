<?php
/**
 * Capacidad opcional para proveedores que además de facturar son POS/cocina (hoy Runfood):
 * permiten abrir el pedido antes de facturarlo (imprime la comanda en cocina) y facturarlo
 * después, al entregar. Contifico no la implementa: es solo facturación.
 *
 * Flujo: la orden sale de ENTRANTE → sendComanda (pedido abierto) → al ENTREGAR,
 * sendInvoice factura esa comanda. Si la orden se anula antes de facturar → cancelComanda.
 * Todos devuelven el array estándar {success, mensaje, ...}.
 */
interface ComandaProviderInterface {

    /** Abre el pedido en el sistema externo. Idempotente: si ya hay comanda, no la duplica. */
    public function sendComanda(int $cod_orden, array $infoFacturacion): array;

    /** Anula la comanda abierta (sin facturar). Un pedido ya facturado no se anula por aquí. */
    public function cancelComanda(int $cod_orden, array $comanda, array $infoFacturacion): array;
}
