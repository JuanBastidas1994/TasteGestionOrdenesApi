<?php
require_once __DIR__ . "/BillingProviderInterface.php";
require_once "clases/cl_runfood.php";
require_once "helpers/billing_helpers.php";

class RunfoodProvider implements BillingProviderInterface {

    const CODIGO_SISTEMA = 3;

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
        ini_set('serialize_precision', 4);
        return $this->armarSchema($cod_orden, $infoFacturacion, $mensaje);
    }

    public function sendInvoice(int $cod_orden, array $schema, array $infoFacturacion): array {
        mylogFile("runfood", json_encode($schema, JSON_NUMERIC_CHECK), "ORDER_REQUEST");

        $invoice = $this->client->createOrder($schema);
        if (!$invoice) {
            return ['success' => 0, 'mensaje' => 'No se pudo enviar la orden a Runfood ' . $this->client->msgError];
        }
        if (!isset($invoice['id'])) {
            return ['success' => 0, 'mensaje' => 'Runfood no respondio con un ID valido de Orden'];
        }

        return [
            'success'         => 1,
            'mensaje'         => 'Orden Enviada a Runfood correctamente',
            'external_id'     => $invoice['id'],
            'document_number' => $invoice['order_number'] ?? $invoice['id'],
            'estado'          => 'CREADA',
            'cod_proveedor'   => $infoFacturacion['cod_sucursal'],
            'tipo_documento'  => $infoFacturacion['tipo_documento'],
            'data'            => $invoice,
        ];
    }

    public function voidInvoice(int $cod_orden, array $ordFactura, array $infoFacturacion): array {
        require_once "clases/cl_ordenes.php";
        $ClOrdenes = new cl_ordenes();
        $motivo = $ClOrdenes->getMotivoAnulacion($cod_orden);

        mylogFile("runfood_anulacion", json_encode(["id" => $ordFactura['clave_acceso'], "motivo" => $motivo]), "ORDER_CANCEL");

        $result = $this->client->cancelOrder($ordFactura['clave_acceso'], $motivo);
        if (!$result) {
            return ['success' => 0, 'mensaje' => 'No se pudo anular la orden en Runfood. ' . $this->client->msgError . ' (los pedidos ya cerrados en Runfood no se pueden eliminar)'];
        }

        return [
            'success' => 1,
            'mensaje' => 'Orden Anulada en Runfood correctamente',
            'data'    => $result,
        ];
    }

    public function saveError(int $cod_orden, string $motivo): void {
        // Runfood no expone endpoint de errores — se puede loggear localmente si se requiere
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
        return ['success' => 1, 'mensaje' => 'Runfood no gestiona inventario en este sistema', 'skipped' => true];
    }

    // ─── Privados ────────────────────────────────────────────────────────────

    /**
     * Nueva API de Runfood: items por SKU (ya no por id numérico interno), agrupados en tabs.
     * NOTA: para opciones "determinantes" (ej. masa verde/maduro/pintón de un Bolón) que hoy
     * resuelven vía tb_productos_opciones_detalle_facturacion, el item resultante sigue saliendo
     * con unit_price=0 igual que en armarSchemaDeprecated — ese es el "PROBLEMON" pendiente de
     * decidir (ver conversación), no se resolvió aquí todavía.
     */
    private function armarSchema(int $cod_orden, array $infoFacturacion, string &$mensaje) {
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

        $items = [];
        foreach ($orden['detalle'] as $item) {
            $resp = getProductoById($item['cod_producto'], $infoFacturacion['cod_sucursal']);

            $resultado = ['principales' => [], 'adicionales' => []];
            if (!empty($item['opciones'])) {
                $resultado = $this->armarItemsAdicionales($item['opciones'], $item['cantidad'], $infoFacturacion['cod_sucursal'], $Clproductos);
            }

            if (!empty($resultado['principales'])) {
                // La opción elegida (ej. masa) determina el producto real a facturar:
                // reemplaza al padre genérico y lleva el precio/cantidad reales del item.
                foreach ($resultado['principales'] as $principal) {
                    $items[] = [
                        'sku'        => $principal['sku'],
                        'quantity'   => intval($item['cantidad']),
                        'unit_price' => (float)$item['precio'],
                        'notes'      => $item['comentarios'] ?: $principal['notes'],
                        'metadata'   => ['cod_producto' => $item['cod_producto']],
                    ];
                }
            } else if ($resp) {
                $items[] = [
                    'sku'        => (string)($resp['sku'] ?: $resp['id']),
                    'quantity'   => intval($item['cantidad']),
                    'unit_price' => (float)$item['precio'],
                    'notes'      => $item['comentarios'] ?? "",
                    'metadata'   => ['cod_producto' => $item['cod_producto']],
                ];
            }

            foreach ($resultado['adicionales'] as $adicional) {
                $items[] = $adicional;
            }
        }

        if ($orden['envio'] > 0) {
            $resp = getEnvioyAdicionalByAlias("ENVIO_DOMICILIO", cod_empresa, $infoFacturacion['cod_sucursal'], self::CODIGO_SISTEMA);
            if (!$resp) {
                $mensaje = "No esta ligado el servicio a Domicilio con Runfood, por favor ir al módulo de integraciones";
                return false;
            }
            $items[] = [
                'sku'        => (string)$resp['id'],
                'quantity'   => 1,
                'unit_price' => (float)number_format($orden['envio'], 2),
                'notes'      => "Envío a domicilio",
                'metadata'   => [],
            ];
        }

        $usuario = $orden['datos_facturacion'] ?: $Clusuarios->get($orden['cod_usuario']);
        $customer = [
            'full_name' => $usuario['nombre'] ?? 'Consumidor Final',
            'tax_id'    => $usuario['num_documento'] ?? '9999999999',
            'email'     => $usuario['correo'] ?? '',
            'phone'     => $usuario['telefono'] ?? '',
        ];

        $serviceType = ($orden['is_envio'] == 1) ? 'delivery' : 'pickup';

        $pedido = [
            'external_id'  => (string)$orden['cod_orden'],
            'reference'    => "Orden #" . $orden['cod_orden'],
            'tabs'         => [[
                'external_id' => "tab-" . $orden['cod_orden'],
                'name'        => "Pedido",
                'reference'   => "",
                'items'       => $items,
            ]],
            'service_type' => $serviceType,
            'table_number' => null,
            'customer'     => $customer,
        ];

        if ($serviceType === 'delivery') {
            $pedido['delivery_address'] = [
                'address'   => $orden['referencia'] ?? '',
                'reference' => $orden['referencia2'] ?? '',
                'lat'       => isset($orden['latitud']) ? (float)$orden['latitud'] : null,
                'lng'       => isset($orden['longitud']) ? (float)$orden['longitud'] : null,
            ];
        }

        mylogFile("logArmarFacturaRunfood", json_encode($pedido, JSON_NUMERIC_CHECK), "RUNFOOD_SCHEMA");

        return $pedido;
    }

    private function armarItemsAdicionales(array $opciones, $cantidad, $idBussinessInvoices, $Clproductos): array {
        $principales = [];
        $adicionales = [];

        foreach ($opciones as $opcion) {
            foreach ($opcion["detalles"] as $detalle) {

                // Mapeo directo a producto Runfood (tb_productos_opciones_detalle_facturacion).
                // es_principal=1 -> la opción ES el producto a facturar (reemplaza al padre, precio real del item).
                // es_principal=0 -> línea aparte; precio = precio_adicional real de la opción (0 si es una opción
                // libre sin costo, o el cargo de la venta cruzada si lo tiene). No se asume 0 a la fuerza.
                $mappedProduct = $Clproductos->getProductoFromOpcionDetalleFacturacion($detalle["id"], $idBussinessInvoices);
                if ($mappedProduct) {
                    if ((int)$mappedProduct['es_principal'] == 1) {
                        if (!empty($principales)) {
                            mylogFile("runfood_warning", "Mas de una opcion marcada como es_principal para el mismo item (cod_producto_opciones_detalle=" . $detalle["id"] . "), se ignora y se usa solo la primera.", "RUNFOOD_ES_PRINCIPAL_DUPLICADO");
                        } else {
                            $principales[] = [
                                'sku'   => (string)($mappedProduct['sku'] ?: $mappedProduct['id_runfood']),
                                'notes' => $mappedProduct['nombre_runfood'] ?? "",
                            ];
                        }
                    } else {
                        $precioAdicional = isset($detalle['precio_adicional_no_tax']) ? $detalle['precio_adicional_no_tax'] : ($detalle['precio_adicional'] ?? 0);
                        $adicionales[] = [
                            'sku'        => (string)($mappedProduct['sku'] ?: $mappedProduct['id_runfood']),
                            'quantity'   => intval($cantidad),
                            'unit_price' => (float)$precioAdicional,
                            'notes'      => $mappedProduct['nombre_runfood'] ?? "",
                            'metadata'   => [],
                        ];
                    }
                    continue;
                }

                $productoIsDb = $Clproductos->getProductFromOpcionDetalleIsDatabase($detalle["id"], $idBussinessInvoices);
                if ($productoIsDb) {
                    $adicionales[] = [
                        'sku'        => (string)$productoIsDb['id'],
                        'quantity'   => (float)number_format($detalle["cantidad"] * $cantidad, 2),
                        'unit_price' => (float)$productoIsDb['precio'],
                        'notes'      => "",
                        'metadata'   => [],
                    ];
                }

                $ingredientes = $Clproductos->getProductoOpcionesIngredientes($detalle["id"], $idBussinessInvoices);
                if ($ingredientes) {
                    foreach ($ingredientes as $ing) {
                        $adicionales[] = [
                            'sku'        => (string)$ing['id'],
                            'quantity'   => (float)number_format(($ing["valor"] * $detalle["cantidad"]) * $cantidad, 2),
                            'unit_price' => (float)$ing['precio'],
                            'notes'      => $ing['ingrediente'],
                            'metadata'   => [],
                        ];
                    }
                }
            }
        }

        return ['principales' => $principales, 'adicionales' => $adicionales];
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
                  AND cod_contifico_empresa = $cod_proveedor";
        return Conexion::buscarRegistro($query) ?: null;
    }
}
