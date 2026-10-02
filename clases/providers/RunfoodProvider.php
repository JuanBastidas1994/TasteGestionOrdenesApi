<?php
require_once __DIR__ . "/BillingProviderInterface.php";
require_once __DIR__ . "/ComandaProviderInterface.php";
require_once "clases/cl_runfood.php";
require_once "helpers/billing_helpers.php";

/**
 * Runfood es facturación Y POS/cocina, por eso el pedido vive en dos momentos:
 *  1. La orden sale de ENTRANTE → sendComanda: pedido abierto (status open) que imprime la comanda.
 *  2. La orden se ENTREGA → sendInvoice: factura la cuenta de esa comanda.
 *     Si no hubo comanda (no se pudo enviar, o nunca pasó por otro estado), se crea el pedido
 *     ya cerrado (status closed): comanda + factura en una sola llamada.
 *  3. La orden se ANULA antes de facturar → cancelComanda (DELETE). Un pedido facturado no se
 *     anula por API: requiere nota de crédito desde el POS de Runfood.
 *
 * Montos: unit_price va SIN IVA; RunFood suma el IVA según su catálogo (vat_applicable) y la tasa
 * del local, y compara su total con el nuestro (±0.01). Se envían los netos que Taste ya guardó con
 * 2 decimales (precio_no_tax, subtotal_*, adicional_no_tax_total, envio_iva) para que el cálculo de
 * RunFood reproduzca exactamente el total que pagó el cliente.
 */
class RunfoodProvider implements BillingProviderInterface, ComandaProviderInterface {

    const CODIGO_SISTEMA = 3;
    /** Runfood acepta hasta 20 modifiers por item. */
    const MAX_MODIFIERS = 20;
    /**
     * Runfood (v18.0.9) falla al facturar una cuenta ABIERTA que tiene modifiers
     * ("El producto que está disminuyendo ... debe tener un motivo"), aunque el pedido closed
     * sí los acepta. Hasta que lo corrijan, las opciones sin costo e ingredientes viajan como
     * líneas a $0. Poner en true cuando Runfood lo arregle.
     */
    const USAR_MODIFIERS = false;

    private $client;

    public function __construct() {
        $this->client = new cl_runfood();
    }

    public function getCodigoSistema(): int {
        return self::CODIGO_SISTEMA;
    }

    public function getInfoSucursal(int $cod_sucursal): ?array {
        return $this->client->getSucursal($cod_sucursal) ?: null;
    }

    public function buildSchema(int $cod_orden, array $infoFacturacion, string &$mensaje) {
        return $this->armarSchema($cod_orden, $infoFacturacion, $mensaje, 'closed');
    }

    public function sendInvoice(int $cod_orden, array $schema, array $infoFacturacion): array {
        $result = $this->enviarFactura($cod_orden, $schema, $infoFacturacion);
        if (!$result['success']) {
            $this->saveError($cod_orden, $result['mensaje']);
        }
        return $result;
    }

    private function enviarFactura(int $cod_orden, array $schema, array $infoFacturacion): array {
        mylogFile("runfood", json_encode($schema), "ORDER_REQUEST");

        $comanda = getComandaOrden($cod_orden);
        if ($comanda && $comanda['estado'] === 'ABIERTA') {
            return $this->facturarComanda($comanda, $schema, $infoFacturacion);
        }

        // Sin comanda abierta: venta directa (pedido + factura en una sola llamada).
        $pedido = $this->client->createOrder($schema);
        if (!$pedido && $this->client->lastHttpCode == 409) {
            return $this->facturarPedidoExistente($cod_orden, $schema, $infoFacturacion);
        }
        if (!$pedido) {
            return ['success' => 0, 'mensaje' => 'No se pudo facturar la orden en Runfood. ' . $this->client->msgError];
        }

        saveComandaOrden($cod_orden, self::CODIGO_SISTEMA, $infoFacturacion['cod_sucursal'], $pedido['id'], $pedido['tabs'][0]['id'] ?? null, $pedido['order_number'] ?? '', 'FACTURADA');
        return $this->resultadoFactura($pedido['id'], $pedido['invoices'][0] ?? null, $infoFacturacion, $pedido);
    }

    public function sendComanda(int $cod_orden, array $infoFacturacion): array {
        $comanda = getComandaOrden($cod_orden);
        if ($comanda) {
            return ['success' => 1, 'mensaje' => "La comanda ya fue enviada a Runfood (estado {$comanda['estado']})", 'skipped' => true];
        }
        if (ExistFacturaToOrden($cod_orden)) {
            return ['success' => 1, 'mensaje' => 'La orden ya está facturada, no se envía comanda', 'skipped' => true];
        }

        $mensaje = "";
        $schema = $this->armarSchema($cod_orden, $infoFacturacion, $mensaje, 'open');
        if (!$schema) {
            return ['success' => 0, 'mensaje' => $mensaje];
        }
        mylogFile("runfood", json_encode($schema), "COMANDA_REQUEST");

        $pedido = $this->client->createOrder($schema);
        if (!$pedido && $this->client->lastHttpCode == 409) {
            // Ya existía en Runfood (ej. se perdió la respuesta del primer envío): se adopta si sigue abierto.
            $pedido = $this->client->getOrder($this->client->lastResponse['id'] ?? 0);
            if (!$pedido || $pedido['status'] !== 'open') {
                return ['success' => 0, 'mensaje' => 'La orden ya existe en Runfood y no está abierta (' . ($pedido['status'] ?? $this->client->msgError) . ')'];
            }
        }
        if (!$pedido) {
            return ['success' => 0, 'mensaje' => 'No se pudo enviar la comanda a Runfood. ' . $this->client->msgError];
        }

        saveComandaOrden($cod_orden, self::CODIGO_SISTEMA, $infoFacturacion['cod_sucursal'], $pedido['id'], $pedido['tabs'][0]['id'] ?? null, $pedido['order_number'] ?? '', 'ABIERTA');
        return [
            'success' => 1,
            'mensaje' => 'Comanda enviada a Runfood (pedido #' . ($pedido['order_number'] ?? $pedido['id']) . ')',
            'data'    => $pedido,
        ];
    }

    public function cancelComanda(int $cod_orden, array $comanda, array $infoFacturacion): array {
        if ($comanda['estado'] !== 'ABIERTA') {
            return ['success' => 0, 'mensaje' => "La comanda de Runfood está {$comanda['estado']}, no se puede anular por aquí"];
        }

        require_once "clases/cl_ordenes.php";
        $ClOrdenes = new cl_ordenes();
        $motivo = $ClOrdenes->getMotivoAnulacion($cod_orden) ?: "Orden anulada en Taste";
        mylogFile("runfood_anulacion", json_encode(["id" => $comanda['external_order_id'], "motivo" => $motivo]), "COMANDA_CANCEL");

        $result = $this->client->cancelOrder($comanda['external_order_id'], $motivo);
        if (!$result) {
            return ['success' => 0, 'mensaje' => 'No se pudo anular la comanda en Runfood. ' . $this->client->msgError];
        }
        setEstadoComandaOrden($comanda['cod_orden_comanda'], 'ANULADA');
        return ['success' => 1, 'mensaje' => 'Comanda anulada en Runfood correctamente', 'data' => $result];
    }

    public function voidInvoice(int $cod_orden, array $ordFactura, array $infoFacturacion): array {
        // Runfood solo permite DELETE de pedidos abiertos: una factura emitida se anula con nota de
        // crédito desde el POS. Se intenta igual por si el pedido quedó abierto en Runfood.
        $result = $this->client->cancelOrder($ordFactura['clave_acceso'], "Orden anulada en Taste");
        if (!$result) {
            return ['success' => 0, 'mensaje' => 'La orden ya está facturada en Runfood: la anulación requiere nota de crédito y debe hacerse desde el POS de Runfood. ' . $this->client->msgError];
        }

        return [
            'success' => 1,
            'mensaje' => 'Orden Anulada en Runfood correctamente',
            'data'    => $result,
        ];
    }

    public function saveError(int $cod_orden, string $motivo): void {
        saveErrorFacturacion($cod_orden, 'RUNFOOD', $motivo);
    }

    public function canVoid(int $cod_orden, string &$mensaje): bool {
        require_once "clases/cl_ordenes.php";
        $ClOrdenes = new cl_ordenes();
        if (!$ClOrdenes->getOrdenAnulada($cod_orden)) {
            $mensaje = "Una orden no puede anularse electronicamente si no se ha anulado localmente";
            return false;
        }
        return true;
    }

    public function adjustInventory(int $cod_orden, string $tipo): array {
        // El inventario lo mueve Runfood con las líneas (y modifiers) del pedido.
        return ['success' => 1, 'mensaje' => 'Runfood gestiona el inventario con el pedido', 'skipped' => true];
    }

    // ─── Privados ────────────────────────────────────────────────────────────

    /** Factura la cuenta de un pedido abierto previamente (comanda) con los datos de cobro del schema. */
    private function facturarComanda(array $comanda, array $schema, array $infoFacturacion): array {
        $tabId = $comanda['external_tab_id'];
        if ($tabId === null || $tabId === '') {
            $pedido = $this->client->getOrder($comanda['external_order_id']);
            $tabId = $pedido['tabs'][0]['id'] ?? null;
            if ($tabId === null) {
                return ['success' => 0, 'mensaje' => 'No se pudo obtener la cuenta (tab) del pedido en Runfood. ' . $this->client->msgError];
            }
        }

        $tab = $schema['tabs'][0];
        $cobro = [
            'billing'  => $tab['billing'],
            'payments' => $tab['payments'],
            'total'    => $tab['total'],
            'print'    => true,
        ];
        mylogFile("runfood", json_encode($cobro), "TAB_INVOICE_REQUEST");

        $factura = $this->client->invoiceTab($comanda['external_order_id'], $tabId, $cobro);
        if (!$factura) {
            $mensaje = 'No se pudo facturar la comanda en Runfood. ' . $this->client->msgError;
            if ($this->client->lastHttpCode == 422) {
                $mensaje .= ' (el pedido abierto en Runfood no cuadra con la orden: ¿se modificó la orden después de enviar la comanda?)';
            }
            return ['success' => 0, 'mensaje' => $mensaje];
        }

        setEstadoComandaOrden($comanda['cod_orden_comanda'], 'FACTURADA');
        return $this->resultadoFactura($comanda['external_order_id'], $factura, $infoFacturacion, $factura);
    }

    /**
     * 409 al crear el pedido cerrado: el external_id ya existe en Runfood pero no teníamos la comanda
     * registrada (ej. se cayó la conexión tras enviarla). Si sigue abierto se factura; si ya estaba
     * facturado se registra tal cual, sin volver a cobrar.
     */
    private function facturarPedidoExistente(int $cod_orden, array $schema, array $infoFacturacion): array {
        $orderId = $this->client->lastResponse['id'] ?? null;
        $pedido = $orderId ? $this->client->getOrder($orderId) : false;
        if (!$pedido) {
            return ['success' => 0, 'mensaje' => 'La orden ya existe en Runfood pero no se pudo consultar. ' . $this->client->msgError];
        }
        $tabId = $pedido['tabs'][0]['id'] ?? null;

        if ($pedido['status'] === 'open') {
            saveComandaOrden($cod_orden, self::CODIGO_SISTEMA, $infoFacturacion['cod_sucursal'], $orderId, $tabId, $pedido['order_number'] ?? '', 'ABIERTA');
            return $this->facturarComanda(getComandaOrden($cod_orden), $schema, $infoFacturacion);
        }

        if ($pedido['status'] === 'closed') {
            $facturas = $this->client->getOrderInvoices($orderId);
            $factura = $facturas['data'][0] ?? ($facturas[0] ?? ($pedido['invoices'][0] ?? null));
            saveComandaOrden($cod_orden, self::CODIGO_SISTEMA, $infoFacturacion['cod_sucursal'], $orderId, $tabId, $pedido['order_number'] ?? '', 'FACTURADA');
            return $this->resultadoFactura($orderId, $factura, $infoFacturacion, $pedido);
        }

        return ['success' => 0, 'mensaje' => "La orden ya existe en Runfood en estado {$pedido['status']} (pedido #{$orderId}), no se puede facturar"];
    }

    /** clave_acceso guarda el id del pedido en Runfood (lo usa voidInvoice); num_factura, el número del comprobante. */
    private function resultadoFactura($orderId, ?array $factura, array $infoFacturacion, $raw): array {
        $numero = trim($factura['fiscalization']['invoiceNumber'] ?? ($factura['number'] ?? ''));
        return [
            'success'         => 1,
            'mensaje'         => 'Orden facturada en Runfood correctamente',
            'external_id'     => $orderId,
            'document_number' => $numero ?: ($factura['id'] ?? $orderId),
            'estado'          => 'CREADA',
            'cod_proveedor'   => $infoFacturacion['cod_sucursal'],
            'tipo_documento'  => $infoFacturacion['tipo_documento'],
            'data'            => $raw,
        ];
    }

    /**
     * Pedido para POST /orders. $status 'open' = comanda (sin cobro); 'closed' = venta directa,
     * con billing, payments y total en la cuenta.
     */
    private function armarSchema(int $cod_orden, array $infoFacturacion, string &$mensaje, string $status) {
        require_once "clases/cl_ordenes.php";
        require_once "clases/cl_usuarios.php";
        require_once "clases/cl_productos.php";

        $ClOrdenes   = new cl_ordenes();
        $Clusuarios  = new cl_usuarios();
        $Clproductos = new cl_productos();
        $codSucursal = $infoFacturacion['cod_sucursal'];

        $orden = $ClOrdenes->get_orden_array($cod_orden);
        if (!$orden) {
            $mensaje = "No se encontro informacion de la orden en el sistema";
            return false;
        }

        $items = [];
        foreach ($orden['detalle'] as $item) {
            $lineas = $this->armarLineas($item, $codSucursal, $Clproductos, $mensaje);
            if ($lineas === false) return false;
            foreach ($lineas as $linea) {
                $items[] = $linea;
            }
        }

        if ($orden['envio'] > 0) {
            $resp = getEnvioyAdicionalByAlias("ENVIO_DOMICILIO", cod_empresa, $codSucursal, self::CODIGO_SISTEMA);
            if (!$resp) {
                $mensaje = "No esta ligado el servicio a Domicilio con Runfood, por favor ir al módulo de integraciones";
                return false;
            }
            // Si la empresa no grava el envío, Taste lo suma completo a subtotal0; si lo grava, su neto es envio_iva.
            $gravaEnvio = (int)empresaGravaIva(cod_empresa) === 1;
            $items[] = [
                'sku'            => (string)$resp['id'],
                'quantity'       => 1,
                'unit_price'     => round((float)($gravaEnvio ? $orden['envio_iva'] : $orden['envio']), 2),
                'vat_applicable' => $gravaEnvio,
                'notes'          => "Envío a domicilio",
            ];
        }

        $nombreCliente = trim(($orden['nombre'] ?? '') . ' ' . ($orden['apellido'] ?? ''));
        $tab = [
            'external_id' => "tab-" . $orden['cod_orden'],
            'name'        => mb_substr($nombreCliente ?: "Orden #" . $orden['cod_orden'], 0, 120),
            'items'       => $items,
        ];

        if ($status === 'closed') {
            $total = round((float)$orden['total'], 2);
            $pagos = $this->armarPagos($orden, $codSucursal, $total, $mensaje);
            if ($pagos === false) return false;

            $tab['billing']  = $this->armarBilling($orden, $Clusuarios);
            $tab['payments'] = $pagos;
            $tab['total']    = $total;
            $this->verificarTotal($orden, $items, $total);
        }

        $esDelivery = ($orden['is_envio'] == 1);
        $pedido = [
            'external_id'  => (string)$orden['cod_orden'],
            'reference'    => "Taste #" . $orden['cod_orden'],
            'status'       => $status,
            'print'        => true,
            'service_type' => $esDelivery ? 'delivery' : 'pickup',
            'tabs'         => [$tab],
        ];
        if (!empty($orden['mesa_referencia'])) {
            $pedido['table_number'] = mb_substr($orden['mesa_referencia'], 0, 20);
        }

        if ($esDelivery) {
            $direccion = [
                'address'   => $orden['referencia'] ?? '',
                'reference' => $orden['referencia2'] ?? '',
            ];
            if (is_numeric($orden['latitud'] ?? null) && is_numeric($orden['longitud'] ?? null)) {
                $direccion['lat'] = (float)$orden['latitud'];
                $direccion['lng'] = (float)$orden['longitud'];
            }
            $pedido['delivery_address'] = $direccion;
        }

        mylogFile("logArmarFacturaRunfood", json_encode($pedido), "RUNFOOD_SCHEMA");

        return $pedido;
    }

    /**
     * Líneas de Runfood para un item de la orden:
     *  - La línea del producto (o de la opción es_principal que lo reemplaza), con precio neto,
     *    descuento y, como modifiers, lo que va a cocina/inventario sin costo (bebida del combo,
     *    ingredientes de la opción).
     *  - Una línea propia por cada opción con costo que esté ligada a un producto de Runfood.
     *  - El costo de opciones que no tienen producto ligado va al producto genérico ADICIONALES si
     *    existe; si no, se suma al precio del producto para que el total cuadre igual.
     */
    private function armarLineas(array $item, $codSucursal, $Clproductos, string &$mensaje) {
        $cantidad = (float)$item['cantidad'];
        $precioUnitario = (float)$item['precio_no_tax'];
        // subtotal_0/12 ya es el neto de la línea con el descuento aplicado (sin opciones).
        $netoLinea = round((float)$item['subtotal_0'] + (float)$item['subtotal_12'], 2);
        $descuento = max(0, round(round($precioUnitario * $cantidad, 2) - $netoLinea, 2));

        $principal = null;
        $modifiers = [];
        $lineasOpciones = [];
        $opcionesCobradas = 0.0;

        foreach (($item['opciones'] ?? []) as $opcion) {
            foreach ($opcion['detalles'] as $detalle) {
                $qty = (float)$detalle['cantidad'];   // ya viene multiplicada por la cantidad del item
                $precioOpcion = (float)($detalle['precio_adicional_no_tax'] ?? 0);
                $skuOpcion = null;
                $nombreOpcion = $detalle['text'] ?? '';

                if ($Clproductos->esOpcionTipoProducto($detalle['id'])) {
                    // Opción que es un producto real: se usa el mapeo del propio producto.
                    $producto = $Clproductos->getFacturacionProductoOpcion($detalle['id'], $codSucursal);
                    if ($producto) $skuOpcion = (string)($producto['sku'] ?: $producto['id']);
                } else {
                    // Opción abierta: mapeo propio de la opción.
                    $mapeo = $Clproductos->getProductoFromOpcionDetalleFacturacion($detalle['id'], $codSucursal);
                    if ($mapeo) {
                        $skuOpcion = (string)($mapeo['sku'] ?: $mapeo['id_runfood']);
                        $nombreOpcion = $mapeo['nombre_runfood'] ?: $nombreOpcion;
                        if ((int)$mapeo['es_principal'] === 1) {
                            if ($principal === null) {
                                $principal = ['sku' => $skuOpcion, 'nombre' => $nombreOpcion];
                            } else {
                                mylogFile("runfood_warning", "Mas de una opcion es_principal para el mismo item (cod_producto_opciones_detalle=" . $detalle["id"] . "), se usa solo la primera.", "RUNFOOD_ES_PRINCIPAL_DUPLICADO");
                            }
                            // Su costo (si tiene) queda en el remanente y se suma al producto que reemplaza.
                            $skuOpcion = null;
                        }
                    }
                }

                if ($skuOpcion !== null) {
                    if ($precioOpcion > 0) {
                        $lineasOpciones[] = [
                            'sku'        => $skuOpcion,
                            'quantity'   => $qty,
                            'unit_price' => $precioOpcion,
                            'notes'      => mb_substr($nombreOpcion, 0, 255),
                        ];
                        $opcionesCobradas += round($precioOpcion * $qty, 2);
                    } else {
                        $modifiers[] = ['sku' => $skuOpcion, 'quantity' => round($qty / $cantidad, 4)];
                    }
                }

                $ingredientes = $Clproductos->getProductoOpcionesIngredientes($detalle['id'], $codSucursal);
                foreach (($ingredientes ?: []) as $ing) {
                    $qtyIngrediente = round(((float)$ing['valor'] * $qty) / $cantidad, 4);
                    if ($qtyIngrediente > 0) {
                        $modifiers[] = ['sku' => (string)$ing['id'], 'quantity' => $qtyIngrediente];
                    }
                }
            }
        }

        // Lo que se cobró por opciones y no viajó como línea propia.
        $remanente = round((float)$item['adicional_no_tax_total'] - $opcionesCobradas, 2);
        if ($remanente > 0) {
            $adicionales = getEnvioyAdicionalByAlias("ADICIONALES", cod_empresa, $codSucursal, self::CODIGO_SISTEMA);
            if ($adicionales) {
                $lineasOpciones[] = [
                    'sku'        => (string)$adicionales['id'],
                    'quantity'   => 1,
                    'unit_price' => $remanente,
                    'notes'      => "Adicionales",
                ];
            } else {
                $precioUnitario += $remanente / $cantidad;
            }
        } else if ($remanente < -0.02) {
            mylogFile("runfood_warning", "Opciones cobradas ($opcionesCobradas) superan adicional_no_tax_total ({$item['adicional_no_tax_total']}) en cod_orden_detalle={$item['cod_orden_detalle']}", "RUNFOOD_ADICIONALES");
        }

        if ($principal) {
            $sku = $principal['sku'];
            $notas = $item['comentarios'] ?: $principal['nombre'];
        } else {
            $resp = getProductoById($item['cod_producto'], $codSucursal, self::CODIGO_SISTEMA);
            if (!$resp) {
                $mensaje = "El producto '" . html_entity_decode($item['nombre']) . "' no está ligado a Runfood, por favor ir al módulo de integraciones";
                return false;
            }
            $sku = (string)($resp['sku'] ?: $resp['id']);
            $notas = $item['comentarios'] ?? "";
        }

        $linea = [
            'sku'        => $sku,
            'quantity'   => $cantidad,
            'unit_price' => round($precioUnitario, 6),
            // Lo que Taste cobró de verdad: con IVA si la línea cayó en subtotal_12.
            'vat_applicable' => ((float)$item['subtotal_12'] > 0) || ((float)$item['subtotal_0'] == 0 && $item['cobra_iva'] == 1),
            'metadata'   => ['cod_producto' => (string)$item['cod_producto']],
        ];
        if ($descuento > 0) $linea['discount'] = $descuento;
        if ($notas) $linea['notes'] = mb_substr($notas, 0, 255);
        if ($modifiers && !self::USAR_MODIFIERS) {
            // Mientras Runfood no facture cuentas abiertas con modifiers, van como líneas a $0:
            // igual llegan a cocina y mueven inventario. La cantidad pasa de "por unidad" a total.
            foreach ($modifiers as $modifier) {
                $lineasOpciones[] = [
                    'sku'        => $modifier['sku'],
                    'quantity'   => round($modifier['quantity'] * $cantidad, 4),
                    'unit_price' => 0,
                ];
            }
        } else if ($modifiers) {
            if (count($modifiers) > self::MAX_MODIFIERS) {
                mylogFile("runfood_warning", "Item con " . count($modifiers) . " modifiers, Runfood acepta " . self::MAX_MODIFIERS . " (cod_orden_detalle={$item['cod_orden_detalle']})", "RUNFOOD_MODIFIERS");
            }
            $linea['modifiers'] = array_slice($modifiers, 0, self::MAX_MODIFIERS);
        }

        return array_merge([$linea], $lineasOpciones);
    }

    /** Consumidor final se declara explícito (Runfood no lo asume). */
    private function armarBilling(array $orden, $Clusuarios): array {
        $consumidorFinal = ['name' => 'CONSUMIDOR FINAL', 'tax_id' => '9999999999999', 'tax_id_type' => 'ruc'];

        $usuario = $orden['datos_facturacion'] ?: $Clusuarios->get($orden['cod_usuario']);
        if (!$usuario) return $consumidorFinal;

        $documento = preg_replace('/[^A-Za-z0-9]/', '', $usuario['num_documento'] ?? '');
        if ($documento === '' || preg_match('/^9+$/', $documento)) return $consumidorFinal;

        if (ctype_digit($documento) && strlen($documento) == 13) {
            $tipo = 'ruc';
        } else if (ctype_digit($documento) && strlen($documento) == 10) {
            $tipo = 'cedula';
        } else {
            $tipo = 'pasaporte';
        }

        $billing = [
            'name'        => trim($usuario['nombre'] ?? '') ?: 'CONSUMIDOR FINAL',
            'tax_id'      => $documento,
            'tax_id_type' => $tipo,
        ];
        if (filter_var($usuario['correo'] ?? '', FILTER_VALIDATE_EMAIL)) $billing['email'] = $usuario['correo'];
        if (!empty($usuario['telefono'])) $billing['phone'] = (string)$usuario['telefono'];
        return $billing;
    }

    /** Runfood exige que los pagos sumen el total exacto (±0.01) y nunca ajusta un pago. */
    private function armarPagos(array $orden, $codSucursal, float $total, string &$mensaje) {
        $pagos = [];
        foreach (($orden['pagos'] ?: []) as $pago) {
            $monto = round((float)$pago['monto'], 2);
            if ($monto <= 0) continue;

            $forma = $this->getFormaPago($pago['forma_pago'], $codSucursal);
            if (!$forma) {
                $mensaje = "La forma de pago '" . html_entity_decode($pago['descripcion']) . "' no está ligada a Runfood, por favor ir al módulo de integraciones";
                return false;
            }
            $linea = ['payment_method_id' => (int)$forma['id'], 'amount' => $monto];
            if ($pago['forma_pago'] === 'E' && $orden['is_suelto'] == 1 && (float)$orden['monto_suelto'] > $monto) {
                $linea['tendered'] = round((float)$orden['monto_suelto'], 2);
            }
            $pagos[] = $linea;
        }

        if (!$pagos) {
            $mensaje = "La orden no tiene pagos registrados para facturar en Runfood";
            return false;
        }

        $suma = round(array_sum(array_column($pagos, 'amount')), 2);
        if (abs($suma - $total) > 0.01) {
            // Un único pago con diferencia de redondeo se alinea al total; otra diferencia es un error real.
            if (count($pagos) == 1 && abs($suma - $total) <= 0.02) {
                $pagos[0]['amount'] = $total;
            } else {
                $mensaje = "Los pagos de la orden ($suma) no cuadran con su total ($total)";
                return false;
            }
        }
        return $pagos;
    }

    /**
     * Replica el cálculo de Runfood (IVA sobre la base gravada, redondeado) para dejar en el log
     * los descuadres antes de que Runfood responda 422 total_mismatch. No bloquea el envío.
     */
    private function verificarTotal(array $orden, array $items, float $total): void {
        $baseGravada = 0.0;
        $baseCero = 0.0;
        foreach ($items as $linea) {
            $neto = round($linea['unit_price'] * $linea['quantity'], 2) - ($linea['discount'] ?? 0);
            // Las líneas de opciones/adicionales no declaran vat_applicable; en Taste siguen al local.
            $gravada = $linea['vat_applicable'] ?? ((float)$orden['iva'] > 0);
            if ($gravada) $baseGravada += $neto; else $baseCero += $neto;
        }
        $tasa = (float)$orden['iva_porcentaje'];
        $esperado = round($baseGravada + $baseCero + round($baseGravada * $tasa / 100, 2), 2);
        if (abs($esperado - $total) > 0.01) {
            mylogFile("runfood_warning", "cod_orden={$orden['cod_orden']} total Taste=$total, calculo tipo Runfood=$esperado (base gravada=$baseGravada, base 0=$baseCero, iva=$tasa%)", "RUNFOOD_TOTAL_DESCUADRE");
        }
    }

    /** Respaldo de la versión anterior del schema (API previa de Runfood, basada en ids numéricos). No se usa actualmente. */
    private function armarSchemaDeprecated(int $cod_orden, array $infoFacturacion, string &$mensaje) {
        require_once "clases/cl_ordenes.php";
        require_once "clases/cl_usuarios.php";
        require_once "clases/cl_productos.php";

        $ClOrdenes   = new cl_ordenes();
        $Clusuarios  = new cl_usuarios();
        $Clproductos = new cl_productos();

        $orden = $ClOrdenes->get_orden_array($cod_orden);
        if (!$orden) {
            $mensaje = "No se encontro informacion de la orden en el sistema";
            return false;
        }

        $porcentaje_iva = iva;
        $cod_empresa    = cod_empresa;
        $divisorIva     = 1 + ($porcentaje_iva / 100);

        $idAdicionalesEnProducto = "";
        $adicionales = getEnvioyAdicionalByAlias("ADICIONALES", $cod_empresa, $infoFacturacion['cod_sucursal'], self::CODIGO_SISTEMA);
        if ($adicionales) {
            $idAdicionalesEnProducto = $adicionales['id'];
        }

        // Cabecera de ventas
        $ventasObj = [
            'documento' => ($infoFacturacion['tipo_documento'] === "FAC") ? 1 : 3,
            'base0'     => $orden['subtotal0'],
            'baseIva'   => $orden['subtotal12'],
            'iva'       => $orden['iva'],
            'descuento' => $orden['descuento'],
            'total'     => $orden['total'],
            'propina'   => 0,
            'servicio'  => 0,
        ];

        // Cliente
        $usuario = $orden['datos_facturacion'] ?: $Clusuarios->get($orden['cod_usuario']);
        if ($usuario) {
            $ventasObj['validarCedula']   = true;
            $ventasObj['cedula']          = $usuario['num_documento'];
            $ventasObj['direccion']       = $usuario['direccion'];
            $ventasObj['email']           = $usuario['correo'];
            $ventasObj['fechaNacimiento'] = "1994-12-07";
            $ventasObj['razonSocial']     = $usuario['nombre'];
            $ventasObj['nombreComercial'] = $usuario['nombre'];
            $ventasObj['telefono']        = $usuario['telefono'];
        }

        // Detalle de productos
        $detalle   = [];
        $x         = 0;
        $idDetalle = 0;

        foreach ($orden['detalle'] as $item) {
            $resp      = getProductoById($item['cod_producto'], $infoFacturacion['cod_sucursal']);
            $resultado = ['principales' => [], 'adicionales' => []];

            if (!empty($item['opciones'])) {
                $resultado = $this->armarAdicionalesFactura(
                    $item['opciones'], $item['cantidad'], $infoFacturacion['cod_sucursal'], $idDetalle, $Clproductos
                );
            }

            if ($resp) {
                $idDetalle++;
                $base12   = ($item['cobra_iva'] == 1) ? $item['precio'] : 0;
                $base0    = ($item['cobra_iva'] == 1) ? 0 : $item['precio'];
                $pNoTax12 = noRound(($base12 / $divisorIva) * $item['cantidad'], false);
                $pNoTax0  = noRound(($base0  / $divisorIva) * $item['cantidad'], false);

                $detalleItem = [
                    'id'              => intval($resp['id']),
                    'codigo'          => intval($resp['id']),
                    'descripcion'     => $item['nombre'],
                    'pagaIva'         => ($item['cobra_iva'] == 1),
                    '_IVA_'           => ($item['cobra_iva'] == 1) ? 12 : 0,
                    'esComponente'    => false,
                    'habilitado'      => true,
                    'pvp1'            => $item["precio"],
                    'observacion'     => "",
                    'cantidad'        => intval($item['cantidad']),
                    'pvpSeleccionado' => "pvp1",
                    'descuento'       => number_format($item['descuento'], 2),
                    'idDetalle'       => $idDetalle,
                    'dinamico'        => false,
                    'cantidadExceso'  => 0,
                ];

                if (!empty($resultado['adicionales'])) {
                    $detalleItem['dinamico']           = true;
                    $detalleItem['articulosDinamicos'] = $resultado['adicionales'];
                }

                $detalle[$x] = $detalleItem;
                $x++;

                foreach ($resultado['principales'] as $principal) {
                    $idDetalle++;
                    $principal['idDetalle'] = $idDetalle;
                    $detalle[$x] = $principal;
                    $x++;
                }
            } else {
                // Padre no ligado a Runfood — adicionales van al primer principal
                $primerPrincipal = true;
                foreach ($resultado['principales'] as $principal) {
                    $idDetalle++;
                    $principal['idDetalle'] = $idDetalle;
                    if ($primerPrincipal && !empty($resultado['adicionales'])) {
                        $principal['dinamico']           = true;
                        $principal['articulosDinamicos'] = $resultado['adicionales'];
                        $primerPrincipal = false;
                    }
                    $detalle[$x] = $principal;
                    $x++;
                }
            }
        }

        // Envío como línea de producto
        if ($orden['envio'] > 0) {
            $gravaIva = empresaGravaIva($cod_empresa);
            $resp = getEnvioyAdicionalByAlias("ENVIO_DOMICILIO", $cod_empresa, $infoFacturacion['cod_sucursal'], self::CODIGO_SISTEMA);
            if (!$resp) {
                $mensaje = "No esta ligado el servicio a Domicilio con Runfood, por favor ir al módulo de integraciones";
                return false;
            }
            $idDetalle++;
            $detalle[$x] = [
                'id'              => intval($resp['id']),
                'codigo'          => $resp['id'],
                'descripcion'     => "Envío a Domicilio",
                'pagaIva'         => ($gravaIva == 1),
                '_IVA_'           => ($gravaIva == 1) ? 12 : 0,
                'esComponente'    => false,
                'habilitado'      => true,
                'pvp1'            => (float)number_format($orden['envio'], 2),
                'observacion'     => "",
                'cantidad'        => 1,
                'pvpSeleccionado' => "pvp1",
                'descuento'       => number_format(0, 2),
                'idDetalle'       => $idDetalle,
                'dinamico'        => false,
                'cantidadExceso'  => 0,
            ];
            $x++;
        }
        $ventasObj['detalle'] = $detalle;

        // Forma de pago
        $pagos = [];
        foreach ($orden['pagos'] as $i => $item) {
            $pago = $this->getFormaPago($item['forma_pago'], $infoFacturacion['cod_sucursal']);
            $pagos[$i] = [
                'idFormaPago'       => $pago['id'] ?? null,
                'monto'             => number_format($item['monto'], 2),
                'idMarcaTarjeta'    => NULL,
                'idTipoTarjeta'     => NULL,
                'numeroTransaccion' => NULL,
                'propina'           => 0,
            ];
        }
        $ventasObj['pagos'] = $pagos;

        $pedidoObj = [
            'estado'         => "A",
            'base0'          => number_format($orden['subtotal0'], 2),
            'baseIva'        => $orden['subtotal12'],
            'iva'            => $orden['iva'],
            'descuentoTotal' => $orden['descuento'],
            'total'          => $orden['total'],
            'propina'        => 0,
            'mesa'           => null,
            'formaDespacho'  => ($orden['is_envio'] == "1") ? "DELIVERY" : "PICKUP",
            'maxIdDetalle'   => count($detalle),
            'detalle'        => $detalle,
            'ventas'         => $ventasObj,
        ];

        mylogFile("logArmarFacturaRunfood", json_encode($pedidoObj, JSON_NUMERIC_CHECK), "RUNFOOD_SCHEMA");

        return $pedidoObj;
    }

    private function armarAdicionalesFactura(array $opciones, $cantidad, $idBussinessInvoices, int &$indiceInicial, $Clproductos): array {
        $principales = [];
        $adicionales = [];

        foreach ($opciones as $opcion) {
            foreach ($opcion["detalles"] as $detalle) {

                // Mapeo directo a producto de Runfood → va como línea principal
                $mappedProduct = $Clproductos->getProductoFromOpcionDetalleFacturacion(
                    $detalle["id"], $idBussinessInvoices
                );
                if ($mappedProduct) {
                    $indiceInicial++;
                    $principales[] = [
                        'id'              => intval($mappedProduct['id_runfood']),
                        'codigo'          => intval($mappedProduct['id_runfood']),
                        'pvp1'            => 0,
                        'cantidad'        => intval($cantidad),
                        'descripcion'     => $mappedProduct['nombre_runfood'] ?? "",
                        'pagaIva'         => false,
                        '_IVA_'           => 0,
                        'esComponente'    => false,
                        'habilitado'      => true,
                        'observacion'     => "",
                        'pvpSeleccionado' => "pvp1",
                        'descuento'       => 0,
                        'idDetalle'       => $indiceInicial,
                        'dinamico'        => false,
                        'cantidadExceso'  => 0,
                    ];
                    continue;
                }

                // isDatabase → va como artículo dinámico (adicional)
                $productoIsDb = $Clproductos->getProductFromOpcionDetalleIsDatabase(
                    $detalle["id"], $idBussinessInvoices
                );
                if ($productoIsDb) {
                    $indiceInicial++;
                    $adicionales[] = [
                        'id'              => intval($productoIsDb["id"]),
                        'codigo'          => intval($productoIsDb['id']),
                        'pvp1'            => $productoIsDb["precio"],
                        'cantidad'        => number_format($detalle["cantidad"] * $cantidad, 2),
                        'descripcion'     => "",
                        'pagaIva'         => false,
                        '_IVA_'           => 0,
                        'esComponente'    => false,
                        'habilitado'      => true,
                        'observacion'     => "",
                        'pvpSeleccionado' => "pvp1",
                        'descuento'       => 0,
                        'idDetalle'       => $indiceInicial,
                        'dinamico'        => false,
                        'cantidadExceso'  => 0,
                    ];
                }

                // Ingredientes de la opción → también van como adicionales
                $ingredientes = $Clproductos->getProductoOpcionesIngredientes(
                    $detalle["id"], $idBussinessInvoices
                );
                if ($ingredientes) {
                    foreach ($ingredientes as $ing) {
                        $indiceInicial++;
                        $adicionales[] = [
                            'id'              => intval($ing["id"]),
                            'codigo'          => intval($ing['id']),
                            'pvp1'            => $ing["precio"],
                            'cantidad'        => number_format(($ing["valor"] * $detalle["cantidad"]) * $cantidad, 2),
                            'descripcion'     => $ing["ingrediente"],
                            'pagaIva'         => false,
                            '_IVA_'           => 0,
                            'esComponente'    => false,
                            'habilitado'      => true,
                            'observacion'     => "",
                            'pvpSeleccionado' => "pvp1",
                            'descuento'       => 0,
                            'idDetalle'       => $indiceInicial,
                            'dinamico'        => false,
                            'cantidadExceso'  => 0,
                        ];
                    }
                }
            }
        }

        return ['principales' => $principales, 'adicionales' => $adicionales];
    }

    private function getFormaPago(string $forma, $cod_proveedor): ?array {
        $query = "SELECT * FROM tb_formas_pago_facturacion
                  WHERE cod_forma_pago = '$forma'
                  AND cod_contifico_empresa = $cod_proveedor
                  AND cod_sistema_facturacion = " . self::CODIGO_SISTEMA;
        return Conexion::buscarRegistro($query) ?: null;
    }
}
