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
        $respFactura = $this->client->CreateFactura($schema);
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

        $respFactura = $this->client->EditFactura($schema);
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

    // ─── Privados ────────────────────────────────────────────────────────────

    private function armarSchema(int $cod_orden, bool $anular, array $infoFacturacion, string &$mensaje) {
        require_once "clases/cl_ordenes.php";
        $ClOrdenes = new cl_ordenes();
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

        $contifico['fecha_emision'] = date("d/m/Y", strtotime($orden['fecha']));
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
        $x = 0;
        foreach ($orden['detalle'] as $item) {
            $resp = getProductoById($item['cod_producto'], $infoFacturacion['cod_contifico_empresa']);
            if (!$resp) {
                $mensaje = "No existe el producto " . $item['nombre'] . " en el sistema, por favor verificar";
                return false;
            }

            $detalle[$x] = [
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
                if ($cod_empresa == 24) { // Lógica especial empresa Danilo
                    $opciones = $ClOrdenes->decodificarOpcionesByDetalle($item['descripcion']);
                    foreach ($opciones as $opcion) {
                        if ($this->getOpcionIsDatabase($opcion['id'])) {
                            foreach ($opcion['detalles'] as $detail) {
                                if ($detail['aumentar_precio'] != 1) continue;
                                $optionitem = $this->getOptionDetalle($detail['id']);
                                if (!$optionitem) {
                                    $mensaje = "Ocurrió un problema al encontrar la opción " . $detail['nombre'];
                                    return false;
                                }
                                $optionContifico = getProductoById($optionitem['cod_producto'], $infoFacturacion['cod_contifico_empresa']);
                                if (!$optionContifico) {
                                    $mensaje = "No existe el producto de la opción en el sistema, por favor verificar";
                                    return false;
                                }
                                $x++;
                                $qty    = $detail['cantidad'] * $item['cantidad'];
                                $price  = number_format($optionitem['precio_no_tax'], 2);
                                $base0  = ($optionitem['cobra_iva'] == 0) ? $price * $qty : 0;
                                $base12 = ($optionitem['cobra_iva'] == 1) ? $price * $qty : 0;
                                $detalle[$x] = [
                                    'producto_id'          => $optionContifico['id'],
                                    'cantidad'             => $qty,
                                    'precio'               => $price,
                                    'porcentaje_iva'       => $porcentaje_iva,
                                    'porcentaje_descuento' => 0,
                                    'base_cero'            => 0,
                                    'base_gravable'        => number_format($base12, 2),
                                    'base_no_gravable'     => number_format($base0, 2),
                                    'valor_ice'            => 0,
                                    'porcentaje_ice'       => 0,
                                ];
                            }
                        }
                    }
                } else {
                    if ($idAdicionalesEnProducto === "") {
                        $mensaje = "No esta ligado los adicionales con contífico, por favor ir al módulo de integraciones";
                        return false;
                    }
                    $x++;
                    $detalle[$x] = [
                        'producto_id'          => $idAdicionalesEnProducto,
                        'cantidad'             => $item['cantidad'],
                        'precio'               => $item["adicional_no_tax_unidad"],
                        'porcentaje_iva'       => $porcentaje_iva,
                        'porcentaje_descuento' => 0,
                        'base_cero'            => 0,
                        'base_gravable'        => $item["adicional_no_tax_total"],
                        'base_no_gravable'     => 0,
                        'valor_ice'            => 0,
                        'porcentaje_ice'       => 0,
                    ];
                }
            }
            $x++;
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
            $detalle[$x] = [
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

        mylogFile("logArmarFactura", json_encode($contifico), "CONTIFICO_SCHEMA");

        return $contifico;
    }

    private function getFormaPago(string $forma): string {
        $map = ['E' => 'EF', 'T' => 'TC', 'P' => 'EF', 'DB' => 'EF'];
        return $map[$forma] ?? 'EF';
    }

    private function getOpcionIsDatabase(int $cod_opcion): bool {
        $query = "SELECT * FROM tb_productos_opciones WHERE cod_producto_opcion = $cod_opcion";
        $resp = Conexion::buscarRegistro($query);
        return $resp && $resp['isDatabase'] == 1;
    }

    private function getOptionDetalle(int $cod_opcion_detalle): ?array {
        $query = "SELECT p.cod_producto, p.nombre, p.precio, p.precio_no_tax, p.cobra_iva
                  FROM tb_productos_opciones_detalle od
                  INNER JOIN tb_productos p ON p.cod_producto = od.item
                  WHERE od.cod_producto_opciones_detalle = $cod_opcion_detalle";
        return Conexion::buscarRegistro($query) ?: null;
    }
}
