<?php
/**
 * Contrato que deben cumplir todos los proveedores de facturación/despacho.
 *
 * sendInvoice y voidInvoice devuelven siempre un array con:
 *   success         (bool)   — 1 ok, 0 error
 *   mensaje         (string)
 *   external_id     (string) — ID en el sistema externo  [solo en éxito]
 *   document_number (string) — Número de documento       [solo en éxito]
 *   estado          (string) — 'CREADA' | 'EMITIDA_SRI'  [solo en éxito]
 *   cod_proveedor   (int)    — ID interno del proveedor   [solo en éxito]
 *   tipo_documento  (string)                              [solo en éxito]
 *   data            (array)  — respuesta raw del proveedor
 */
interface BillingProviderInterface {

    /** Código de sistema en tb_orden_factura_electronica.cod_sistema_facturacion */
    public function getCodigoSistema(): int;

    /** Retorna la configuración de facturación de la sucursal, o null si no existe. */
    public function getInfoSucursal(int $cod_sucursal): ?array;

    /**
     * Construye el payload que el proveedor espera para crear una factura.
     * Retorna el array del schema o false en caso de error (escribe en $mensaje).
     */
    public function buildSchema(int $cod_orden, array $infoFacturacion, string &$mensaje);

    /**
     * Envía la factura al proveedor externo.
     * Retorna array estándar (ver cabecera).
     */
    public function sendInvoice(int $cod_orden, array $schema, array $infoFacturacion): array;

    /**
     * Anula/revierte la factura en el proveedor externo.
     * $ordFactura es el registro de tb_orden_factura_electronica.
     * Retorna array estándar (ver cabecera).
     */
    public function voidInvoice(int $cod_orden, array $ordFactura, array $infoFacturacion): array;

    /** Registra un error de facturación (puede ser no-op según el proveedor). */
    public function saveError(int $cod_orden, string $motivo): void;

    /**
     * Valida precondiciones propias del proveedor antes de anular (ej: Runfood exige
     * que la orden ya esté anulada localmente). Retorna true si puede continuar;
     * false y escribe $mensaje si no.
     */
    public function canVoid(int $cod_orden, string &$mensaje): bool;

    /**
     * Ajusta el inventario en el proveedor tras crear ($tipo='EGR') o anular ($tipo='ING')
     * una factura. No-op (success=1, skipped=true) para proveedores que no gestionan inventario.
     */
    public function adjustInventory(int $cod_orden, string $tipo): array;
}
