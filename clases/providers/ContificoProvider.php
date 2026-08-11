<?php
require_once __DIR__ . "/BillingProviderInterface.php";
require_once "clases/cl_contifico.php";
require_once "helpers/billing_helpers.php";

class ContificoProvider implements BillingProviderInterface {

    const CODIGO_SISTEMA = 1;

    private $client;

    public function __construct() {
        $this->client = new cl_contifico();
    }

    public function getCodigoSistema(): int {
        return self::CODIGO_SISTEMA;
    }

    public function getInfoSucursal(int $cod_sucursal): ?array {
        $info = $this->client->getInfoBySucursal($cod_sucursal);
        if ($info) {
            $this->client->API = $info['api'];
        }
        return $info ?: null;
    }

    public function buildSchema(int $cod_orden, array $infoFacturacion, string &$mensaje) {
        return $this->armarSchema($cod_orden, false, $infoFacturacion, $mensaje);
    }

    public function sendInvoice(int $cod_orden, array $schema, array $infoFacturacion): array {
        mylogFile("contifico_facturas", json_encode($schema), "TRAMA_ENVIADA cod_orden=$cod_orden");
        $respFactura = $this->client->CreateFactura($schema);
        mylogFile("contifico_facturas", json_encode($respFactura), "RESPUESTA_CONTIFICO cod_orden=$cod_orden");
        $idContifico = isset($respFactura['id']) ? $respFactura['id'] : 0;

        if ($idContifico !== 0) {
            $sentToSRI = "CREADA";
            $respSRI = $this->client->sendToSRI($idContifico);
            if (isset($respSRI["autorizacion"])) {
                $sentToSRI = "EMITIDA_SRI";
            }
            $this->client->incrementSecuencial($infoFacturacion['cod_sucursal'], $infoFacturacion["tipo_documento"]);
            return [
                'success'         => 1,
                'mensaje'         => 'Factura creada correctamente',
                'external_id'     => $respFactura['id'],
                'document_number' => $respFactura['documento'],
                'estado'          => $sentToSRI,
                'cod_proveedor'   => $infoFacturacion['cod_contifico_empresa'],
                'tipo_documento'  => $infoFacturacion['tipo_documento'],
                'respSRI'         => $respSRI,
                'data'            => $respFactura,
            ];
        }

        $msgError = isset($respFactura['mensaje']) ? $respFactura['mensaje'] : $this->client->msgError;
        $this->saveError($cod_orden, $msgError);
        return [
            'success' => 0,
            'mensaje' => "Error al crear la factura. Detalle: $msgError",
            'data'    => $respFactura,
        ];
    }

    public function voidInvoice(int $cod_orden, array $ordFactura, array $infoFacturacion): array {
        $msgError = "";
        $schema = $this->armarSchema($cod_orden, true, $infoFacturacion, $msgError);
        if (!$schema) {
            return ['success' => 0, 'mensaje' => $msgError];
        }

        mylogFile("contifico_facturas", json_encode($schema), "TRAMA_ANULACION cod_orden=$cod_orden");
        $respFactura = $this->client->EditFactura($schema);
        mylogFile("contifico_facturas", json_encode($respFactura), "RESPUESTA_CONTIFICO_ANULACION cod_orden=$cod_orden");
        if (isset($respFactura['id'])) {
            return [
                'success' => 1,
                'mensaje' => 'Factura anulada correctamente',
                'data'    => $respFactura,
            ];
        }

        $msgError = isset($respFactura['mensaje']) ? $respFactura['mensaje'] : $this->client->msgError;
        $this->saveError($cod_orden, $msgError);
        return [
            'success' => 0,
            'mensaje' => "Error al anular la factura. Detalle: $msgError",
            'data'    => $respFactura,
        ];
    }

    /**
     * Agrega un cobro a una factura ya creada en Contifico.
     * Método exclusivo de Contifico, no forma parte de la interfaz.
     */
    public function addPayment(int $cod_orden, array $ordFactura): array {
        require_once "clases/cl_facturas.php";
        $ClFacturas = new cl_facturas();

        if ($ordFactura['estado'] == "ANULADA") {
            return ['success' => 0, 'mensaje' => 'No se puede realizar cobros a una factura anulada'];
        }

        $pago = $ClFacturas->lastPayment($cod_orden);
        if (!$pago) {
            return ['success' => 0, 'mensaje' => 'No se ha encontrado nuevos pagos, por favor realizar la información'];
        }

        $data = [
            "forma_cobro" => $this->getFormaPago($pago['cod_tipo_pago']),
            "monto"       => ($pago['valor_pagado'] - $pago['valor_cambio']),
            "fecha"       => date("d/m/Y", strtotime($pago['fecha'])),
            "tipo_ping"   => "D",
        ];

        $payment = $this->client->AddPayment($ordFactura['id'], $data);
        if (isset($payment['id'])) {
            return ['success' => 1, 'mensaje' => 'Cobro agregado correctamente a la factura', 'data' => $payment];
        }

        $msgError = isset($payment['mensaje']) ? $payment['mensaje'] : $this->client->msgError;
        return ['success' => 0, 'mensaje' => "Error al agregar el cobro. Detalle: $msgError", 'data' => $payment];
    }

    public function saveError(int $cod_orden, string $motivo): void {
        $this->client->saveErrorFactura($cod_orden, $motivo);
    }

    public function canVoid(int $cod_orden, string &$mensaje): bool {
        $factura = ExistFacturaToOrden($cod_orden);
        if (!$factura) {
            $mensaje = "La orden $cod_orden no tiene una factura activa para anular";
            return false;
        }

        if (empty($factura['fecha']) || date('Y-m-d', strtotime($factura['fecha'])) !== date('Y-m-d')) {
            $mensaje = "La factura solo se puede anular el mismo día en que fue enviada";
            return false;
        }

        return true;
    }

    /**
     * Port de controllers/Contifico.php::setInventario(), reutilizando $this->client
     * ya configurado por getInfoSucursal(). El controller /contifico/inventario/* se deja
     * intacto para no afectar el flujo manual existente.
     */
    public function adjustInventory(int $cod_orden, string $tipo): array {
        require_once "clases/cl_ordenes.php";
        require_once "clases/cl_productos.php";
        $ClOrdenes   = new cl_ordenes();
        $ClProductos = new cl_productos();

        $orden = $ClOrdenes->getOrden($cod_orden);
        if (!$orden) {
            return ['success' => 0, 'mensaje' => 'Orden no existe'];
        }

        $contificoSucursal = $this->client->getInfoBySucursal($orden["cod_sucursal"]);
        if (!$contificoSucursal) {
            return ['success' => 0, 'mensaje' => 'La sucursal no tiene configurado un pto de emisión'];
        }
        $this->client->API = $contificoSucursal["api"];

        if ((int)$contificoSucursal["inventario"] == 0) {
            return ['success' => 1, 'mensaje' => 'Inventario no habilitado para esta sucursal', 'skipped' => true];
        }

        $detalleOrden = $ClOrdenes->getOrdenDetalle($cod_orden);
        if (!$detalleOrden) {
            return ['success' => 0, 'mensaje' => 'Orden detalle no existe'];
        }

        $detalles = [];
        foreach ($detalleOrden as $detOrden) {
            $opciones = $detOrden["opciones"];
            if ($opciones) {
                foreach ($opciones as $opcion) {
                    foreach ($opcion["detalles"] as $detalle) {
                        // Las opciones tipo-producto (isDatabase) ya viajan como línea propia en
                        // la factura (armarSchema) — su descuento de inventario, si aplica, lo hace
                        // Contífico automáticamente según la configuración de ese producto ahí.
                        // Este egreso manual es solo para lo que NUNCA aparece como línea de
                        // factura: ingredientes de la opción y recipientes.
                        $productoOpcionesIngrendientes = $ClProductos->getProductoOpcionesIngredientes($detalle["id"], $contificoSucursal["cod_contifico_empresa"]);
                        if ($productoOpcionesIngrendientes) {
                            foreach ($productoOpcionesIngrendientes as $prodOpcIngredientes) {
                                $detalles[] = [
                                    "producto_id" => $prodOpcIngredientes["id"],
                                    "cantidad"    => number_format(($prodOpcIngredientes["valor"] * $detalle["cantidad"]) * $detOrden["cantidad"], 2),
                                    "precio"      => $prodOpcIngredientes["precio"],
                                ];
                            }
                        }
                    }
                }
            }

            $recipientes = $ClOrdenes->getRecipientesByRuc($cod_orden, cod_empresa, $contificoSucursal["cod_contifico_empresa"]);
            foreach ($recipientes as $recipiente) {
                $detalles[] = [
                    "producto_id" => $recipiente["id"],
                    "cantidad"    => number_format($recipiente["cantidad"], 2),
                    "precio"      => $recipiente["precio"],
                ];
            }
        }

        $msj = $tipo == "ING" ? "ingresó" : "descontó";

        if (count($detalles) == 0) {
            return ['success' => 0, 'mensaje' => "No se $msj inventario"];
        }

        $inventario = [
            "tipo"        => $tipo,
            "fecha"       => date_format(date_create(fecha_only()), 'd/m/Y'),
            "bodega_id"   => $contificoSucursal["id_bodega"],
            "detalles"    => $detalles,
            "descripcion" => "Compra mediante la WEB",
        ];

        $respInventario = $this->client->setInventario($inventario);
        if ($respInventario && isset($respInventario["codigo"])) {
            $ClOrdenes->saveOrdenInventario($cod_orden, $this->client->cod_contifico_empresa, $tipo, $respInventario["codigo"], $respInventario["id"]);
            return ['success' => 1, 'mensaje' => "Se $msj inventario", 'data' => $respInventario];
        }

        $msgError = $respInventario["mensaje"] ?? $this->client->msgError;
        return ['success' => 0, 'mensaje' => "No se $msj inventario. Detalle: $msgError", 'data' => $respInventario];
    }

    // ─── Privados ────────────────────────────────────────────────────────────

    private function armarSchema(int $cod_orden, bool $anular, array $infoFacturacion, string &$mensaje) {
        require_once "clases/cl_ordenes.php";
        require_once "clases/cl_productos.php";
        $ClOrdenes   = new cl_ordenes();
        $ClProductos = new cl_productos();
        $orden = $ClOrdenes->get_orden_array($cod_orden);

        if (!$orden) {
            $mensaje = "No se encontro informacion de la orden en el sistema";
            return false;
        }

        $porcentaje_iva = iva;
        $cod_empresa    = cod_empresa;

        $idAdicionalesEnProducto = "";
        $adicionales = getEnvioyAdicionalByAlias("ADICIONALES", $cod_empresa, $infoFacturacion['cod_contifico_empresa'], self::CODIGO_SISTEMA);
        if ($adicionales) {
            $idAdicionalesEnProducto = $adicionales['id'];
        }

        $contifico        = [];
        $contifico['pos'] = $infoFacturacion['pos'];

        if ($anular) {
            $factElectronica = ExistFacturaToOrden($cod_orden);
            if (!$factElectronica) {
                $mensaje = "La orden $cod_orden no tiene una factura creada";
                return false;
            }
            $contifico['tipo_documento'] = $infoFacturacion['tipo_documento'];
            $contifico['anulado']        = true;
            $contifico['estado']         = 'A';
            $contifico['documento']      = $factElectronica['num_factura'];
            $contifico['id']             = $factElectronica['clave_acceso'];
        } else {
            if ($infoFacturacion['tipo_documento'] == "FAC") {
                $contifico['tipo_documento'] = "FAC";
                $contifico['documento']      = $infoFacturacion['emisor'] . "-" . $infoFacturacion['ptoemision'] . "-" . str_pad($infoFacturacion['secuencial'], 9, "0", STR_PAD_LEFT);
                $contifico['estado']         = "P";
            } else if ($infoFacturacion['tipo_documento'] == "DNA") {
                $contifico['tipo_documento'] = "DNA";
                $contifico['documento']      = str_pad($infoFacturacion['secuencial_dna'], 5, "0", STR_PAD_LEFT);
                $contifico['estado']         = "P";
            }
        }

        // El SRI ya no acepta comprobantes con fecha de emisión pasada: si la orden es de un
        // día anterior, se factura con la fecha de hoy (no se toca la fecha de la orden).
        $fechaOrden = strtotime($orden['fecha']);
        $esFechaPasada = date('Y-m-d', $fechaOrden) < date('Y-m-d');
        $contifico['fecha_emision'] = date("d/m/Y", $esFechaPasada ? time() : $fechaOrden);
        $contifico['autorizacion']  = "123456789";
        $contifico['caja_id']       = "";
        $contifico['electronico']   = true;

        // Cliente
        if ($orden['datos_facturacion']) {
            $usuario = $orden['datos_facturacion'];
        } else {
            require_once "clases/cl_usuarios.php";
            $Clusuarios = new cl_usuarios();
            $usuario = $Clusuarios->get($orden['cod_usuario']);
        }

        if ($usuario) {
            if ($usuario['num_documento'] !== "") {
                if (strlen($usuario['num_documento']) == 13) {
                    $cliente['ruc']    = $usuario['num_documento'];
                    $cliente['cedula'] = substr($usuario['num_documento'], 0, 10);
                } else {
                    $cliente['cedula'] = $usuario['num_documento'];
                    $cliente['ruc']    = $usuario['num_documento'] . "001";
                }
                $cliente['razon_social']  = $usuario['nombre'];
                $cliente['telefonos']     = $usuario['telefono'];
                $cliente['direccion']     = $usuario['direccion'];
                $cliente['tipo']          = "N";
                $cliente['email']         = $usuario['correo'];
                $cliente['es_extranjero'] = false;
            } else {
                $cliente['cedula']        = "9999999999";
                $cliente['ruc']           = "9999999999001";
                $cliente['razon_social']  = "Consumidor Final";
                $cliente['telefonos']     = "0999999999";
                $cliente['direccion']     = "Consumidor final";
                $cliente['tipo']          = "N";
                $cliente['email']         = "00000000@00.com";
                $cliente['es_extranjero'] = false;
            }
            $contifico['cliente'] = $cliente;
        }

        // Vendedor (dato fijo de la empresa)
        $contifico['vendedor'] = [
            'ruc'           => "0952423606001",
            'cedula'        => "0952423606",
            'razon_social'  => "Vendedor",
            'telefonos'     => "0999999999",
            'direccion'     => "Juan montalvo",
            'tipo'          => "N",
            'email'         => "juankbastidasjuve@gmail.com",
            'es_extranjero' => false,
        ];

        // Detalle de productos
        $detalle = [];
        foreach ($orden['detalle'] as $item) {
            $resp = getProductoById($item['cod_producto'], $infoFacturacion['cod_contifico_empresa']);
            if (!$resp) {
                $mensaje = "No existe el producto " . $item['nombre'] . " en el sistema, por favor verificar";
                return false;
            }

            $detalle[] = [
                'producto_id'          => $resp['id'],
                'cantidad'             => $item['cantidad'],
                'precio'               => $item['precio_no_tax'],
                'porcentaje_iva'       => $porcentaje_iva,
                'porcentaje_descuento' => $item['descuento_porcentaje'],
                'base_cero'            => 0,
                'base_gravable'        => $item['subtotal_12'],
                'base_no_gravable'     => $item['subtotal_0'],
                'valor_ice'            => 0,
                'porcentaje_ice'       => 0,
            ];

            if ($item["adicional_no_tax_unidad"] > 0) {
                // Cada opción con costo se factura como línea propia contra el producto real ya
                // ligado a Contífico (tb_productos_facturacion, el mismo mapeo que usa inventario).
                // El precio siempre sale de la opción (tb_productos_opciones_detalle), nunca del
                // catálogo del producto. Lo que no tenga producto ligado (opción abierta sin
                // mapear) cae al genérico "Adicionales" como remanente.
                // Se usa $item['opciones'] (ya decodificado por get_orden_array) en vez de
                // re-decodificar $item['descripcion'], que get_orden_array() ya eliminó del array.
                // La cantidad ahí ya viene multiplicada por la cantidad del item.
                $opciones = $item['opciones'] ?? [];
                $remanenteAdicionales = 0;

                foreach ($opciones as $opcion) {
                    foreach ($opcion['detalles'] as $detail) {
                        if ($detail['aumentar_precio'] != 1) continue;

                        $qty   = $detail['cantidad'];
                        $price = (float)$detail['precio_adicional_no_tax'];
                        if ($price <= 0) continue;

                        $mapeo = $ClProductos->getFacturacionProductoOpcion($detail['id'], $infoFacturacion['cod_contifico_empresa']);
                        if ($mapeo) {
                            $base0  = ($mapeo['cobra_iva'] == 0) ? $price * $qty : 0;
                            $base12 = ($mapeo['cobra_iva'] == 1) ? $price * $qty : 0;
                            $detalle[] = [
                                'producto_id'          => $mapeo['id'],
                                'cantidad'             => $qty,
                                'precio'               => number_format($price, 2),
                                'porcentaje_iva'       => $porcentaje_iva,
                                'porcentaje_descuento' => 0,
                                'base_cero'            => 0,
                                'base_gravable'        => number_format($base12, 2),
                                'base_no_gravable'     => number_format($base0, 2),
                                'valor_ice'            => 0,
                                'porcentaje_ice'       => 0,
                            ];
                            continue;
                        }

                        $remanenteAdicionales += $price * $qty;
                    }
                }

                if ($remanenteAdicionales > 0) {
                    if ($idAdicionalesEnProducto === "") {
                        $mensaje = "No esta ligado los adicionales con contífico, por favor ir al módulo de integraciones";
                        return false;
                    }
                    $detalle[] = [
                        'producto_id'          => $idAdicionalesEnProducto,
                        'cantidad'             => 1,
                        'precio'               => number_format($remanenteAdicionales, 2),
                        'porcentaje_iva'       => $porcentaje_iva,
                        'porcentaje_descuento' => 0,
                        'base_cero'            => 0,
                        'base_gravable'        => number_format($remanenteAdicionales, 2),
                        'base_no_gravable'     => 0,
                        'valor_ice'            => 0,
                        'porcentaje_ice'       => 0,
                    ];
                }
            }
        }

        // Envío como línea de producto
        if ($orden['envio'] > 0) {
            $gravaIva    = empresaGravaIva($cod_empresa);
            $ivaEnvio    = $gravaIva == 1 ? 12 : 0;
            $envioBase12 = $gravaIva == 1 ? $orden['envio'] : 0;
            $envioBase0  = $gravaIva == 1 ? 0 : $orden['envio'];

            $resp = getEnvioyAdicionalByAlias("ENVIO_DOMICILIO", $cod_empresa, $infoFacturacion['cod_contifico_empresa'], self::CODIGO_SISTEMA);
            if (!$resp) {
                $mensaje = "No esta ligado el servicio a Domicilio con contífico, por favor ir al módulo de integraciones";
                return false;
            }
            $detalle[] = [
                'producto_id'          => $resp['id'],
                'cantidad'             => 1,
                'precio'               => $orden['envio'],
                'porcentaje_iva'       => $ivaEnvio,
                'porcentaje_descuento' => 0,
                'base_cero'            => 0,
                'base_gravable'        => $envioBase12,
                'base_no_gravable'     => $envioBase0,
                'valor_ice'            => 0,
                'porcentaje_ice'       => 0,
            ];
        }

        $contifico['detalles']    = $detalle;
        $contifico['descripcion'] = "N. Orden " . $orden['cod_orden'];
        $contifico['subtotal_0']  = $orden['subtotal0'];
        $contifico['subtotal_12'] = $orden['subtotal12'];
        $contifico['iva']         = $orden['iva'];
        $contifico['servicio']    = 0;
        $contifico['total']       = $orden['total'];
        $contifico['adicional1']  = "";
        $contifico['adicional2']  = "";

        // Forma de pago
        $pagos = [];
        foreach ($orden['pagos'] as $i => $item) {
            $pagos[$i] = [
                'forma_cobro'   => $this->getFormaPago($item['forma_pago']),
                'monto'         => $item['monto'],
                'numero_cheque' => NULL,
                'tipo_ping'     => "D",
            ];
        }
        $contifico['cobros'] = $pagos;

        return $contifico;
    }

    private function getFormaPago(string $forma): string {
        $map = ['E' => 'EF', 'T' => 'TC', 'P' => 'EF', 'DB' => 'EF'];
        return $map[$forma] ?? 'EF';
    }
}
