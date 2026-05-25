<?php

class cl_productos
{		
    public function __construct($pcod_sucursal=null)
    {
    }

    public function get($cod_producto){
        $query = "SELECT * 
                    FROM tb_productos 
                    WHERE cod_producto = $cod_producto";
        return Conexion::buscarRegistro($query);
    }

    public function getProductoOpciones($cod_producto) {
        $query = "SELECT cod_producto_opcion 
                    FROM tb_productos_opciones
                    WHERE cod_producto = $cod_producto";
        $productoOpciones = Conexion::buscarVariosRegistro($query);
        if($productoOpciones) {
            $arryDetalles = [];
            foreach ($productoOpciones as $prodOpciones) {
                $detalles = $this->getOpcionesDetalle($prodOpciones["cod_producto_opcion"]);
                if($detalles) {
                    foreach ($detalles as $det) {
                        $arryDetalles[] = $det;
                    }
                }
            }
            return $arryDetalles;
        }
        return false;
    }

    public function getOpcionesDetalle($cod_producto_opcion) {
        $query = "SELECT cod_producto_opciones_detalle 
                    FROM tb_productos_opciones_detalle 
                    WHERE cod_producto_opcion = $cod_producto_opcion";
        return Conexion::buscarVariosRegistro($query);
    }

    public function getProductoIngredientes($cod_producto) {
        $query = "SELECT pi.cod_producto_ingrediente, i.cod_ingrediente, i.ingrediente, pi.valor, i.cod_unidad_medida, i.id_contifico, i.precio
                    FROM tb_productos_ingredientes pi, tb_ingredientes i
                    WHERE pi.cod_ingrediente = i.cod_ingrediente
                    AND pi.cod_producto = $cod_producto";
        return Conexion::buscarVariosRegistro($query);
    } 

    public function getProductoOpcionesIngredientes($cod_producto_opcion, $cod_contifico_empresa) {
        $query = "SELECT pi.cod_producto_opcion_ingrediente, i.cod_ingrediente, i.ingrediente, pi.valor, i.cod_unidad_medida, ifc.id, i.precio
                    FROM tb_productos_opciones_ingredientes pi, tb_ingredientes i, tb_ingredientes_facturacion ifc
                    WHERE pi.cod_ingrediente = i.cod_ingrediente
                    AND ifc.cod_ingrediente = i.cod_ingrediente
                    AND pi.cod_producto_opcion = $cod_producto_opcion
                    AND ifc.cod_contifico_empresa = $cod_contifico_empresa";
        return Conexion::buscarVariosRegistro($query);
    }
    
    public function getProductoFromOpcionDetalleFacturacion($cod_producto_opciones_detalle, $cod_contifico_empresa) {
        $query = "SELECT * 
                FROM tb_productos_opciones_detalle_facturacion
                WHERE cod_producto_opciones_detalle = $cod_producto_opciones_detalle
                AND cod_contifico_empresa = $cod_contifico_empresa";
        return Conexion::buscarRegistro($query);
    }

    public function getProductFromOpcionDetalleIsDatabase($cod_producto_opcion_detalle, $cod_contifico_empresa){
        $query = "SELECT p.cod_producto, p.nombre, p.precio, pc.isDatabase, pd.item, pf.id, pf.name_in_contifico 
                FROM tb_productos_opciones_detalle pd
                INNER JOIN tb_productos_opciones pc ON pd.cod_producto_opcion = pc.cod_producto_opcion AND pc.isDatabase = 1
                INNER JOIN tb_productos p ON p.cod_producto = pd.item AND p.estado IN ('A', 'I')
                INNER JOIN tb_productos_facturacion pf ON p.cod_producto = pf.cod_producto AND pf.cod_contifico_empresa = $cod_contifico_empresa
                WHERE debitInventario = 1
                AND pd.cod_producto_opciones_detalle = $cod_producto_opcion_detalle";
        return Conexion::buscarRegistro($query);
    }


    public function getOpcionesFullData($cod_product){
        $query = "SELECT * FROM tb_productos_opciones WHERE cod_producto = $cod_product ORDER BY posicion ASC";
        $opciones = Conexion::buscarVariosRegistro($query);
        if($opciones) {
            foreach($opciones as $key => $opcion){
                
            }
        }
    }

    public function getInfoBasic($cod_producto){
        $query = "SELECT cod_producto, alias, image_min, image_max, nombre, precio
                    FROM tb_productos 
                    WHERE cod_producto = $cod_producto";
        return Conexion::buscarRegistro($query);
    }

    public function opciones($cod_producto){
        $query = "SELECT cod_producto_opcion as id, titulo, isDatabase
                FROM tb_productos_opciones WHERE cod_producto = $cod_producto ORDER BY posicion ASC";
        $resp = Conexion::buscarVariosRegistro($query);
        if($resp){
            foreach ($resp as $key => $opciones) {
                $resp[$key]['titulo'] = html_entity_decode($opciones['titulo']);
                if($opciones['isDatabase'] == 1)
                    $items = $this->detalle_opciones_Productos($opciones['id']);
                else       
                    $items = $this->detalle_opciones_noProductos($opciones['id']);
 
                foreach($items as $key2 => $item){
                    $items[$key2]['ingredientes'] = $this->opcion_ingrediente($item['cod_producto_opciones_detalle']);
                    $items[$key2]['facturacion'] = $this->opcion_ligada_facturacion($item['cod_producto_opciones_detalle']);
                }
                $resp[$key]['items'] = $items;
            }       
        }
        return $resp;
    }

    public function detalle_opciones_noProductos($cod_producto_opcion){
        $query = "SELECT cod_producto_opciones_detalle,item,aumentar_precio,precio,debitInventario FROM tb_productos_opciones_detalle WHERE cod_producto_opcion = $cod_producto_opcion ORDER BY posicion ASC";
        return Conexion::buscarVariosRegistro($query);
    }

    public function detalle_opciones_Productos($cod_producto_opcion){
        $query = "SELECT po.cod_producto_opciones_detalle, p.nombre as item, po.aumentar_precio, po.precio, p.precio as precio_real, p.cod_producto, po.debitInventario
                    FROM tb_productos_opciones_detalle po, tb_productos p
                    WHERE po.item = p.cod_producto
                    AND po.cod_producto_opcion = $cod_producto_opcion
                    ORDER BY po.posicion ASC";
        return Conexion::buscarVariosRegistro($query);
    }

    public function setInventarioOpcionDetalle($cod_producto_opcion_detalle, $isInventario){
        $query = "UPDATE tb_productos_opciones_detalle SET debitInventario = $isInventario WHERE cod_producto_opciones_detalle = $cod_producto_opcion_detalle";
        return Conexion::ejecutar($query,NULL);
    }

    public function opcion_ingrediente($cod_opcion_detalle){
        $query = "SELECT pi.cod_producto_opcion_ingrediente, i.cod_ingrediente, i.ingrediente, i.cod_unidad_medida, pi.valor 
        FROM tb_productos_opciones_ingredientes pi, tb_ingredientes i
        WHERE pi.cod_ingrediente = i.cod_ingrediente
        AND pi.cod_producto_opcion = $cod_opcion_detalle";
        return Conexion::buscarVariosRegistro($query);
    }
    
    public function opcion_ligada_facturacion($cod_opcion_detalle){
        $query = "SELECT *
        FROM tb_productos_opciones_detalle_facturacion df
        WHERE df.cod_producto_opciones_detalle = $cod_opcion_detalle";
        return Conexion::buscarRegistro($query);
    }

    public function allIngredientes(){
        $query = "SELECT * FROM tb_ingredientes WHERE estado = 'A' AND cod_empresa = ".cod_empresa;
        return Conexion::buscarVariosRegistro($query);
    }
}
?>