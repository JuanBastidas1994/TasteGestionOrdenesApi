<?php
require_once __DIR__ . "/BillingProviderInterface.php";
require_once __DIR__ . "/ContificoProvider.php";
require_once __DIR__ . "/RunfoodProvider.php";
require_once "helpers/billing_helpers.php";

class BillingProviderFactory {

    public static function make(int $cod_sistema_facturacion): ?BillingProviderInterface {
        switch ($cod_sistema_facturacion) {
            case ContificoProvider::CODIGO_SISTEMA:
                return new ContificoProvider();
            case RunfoodProvider::CODIGO_SISTEMA:
                return new RunfoodProvider();
            default:
                return null;
        }
    }

    /** Resuelve el proveedor de facturación activo de mayor prioridad para la empresa. */
    public static function makeForEmpresa(int $cod_empresa): ?BillingProviderInterface {
        $config = getFacturacionElectronica($cod_empresa);
        if (!$config || count($config) == 0) {
            return null;
        }
        return self::make((int)$config[0]['cod_sistema_facturacion']);
    }
}
