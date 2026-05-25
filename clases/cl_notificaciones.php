<?php

class cl_notificaciones{
    
    public function getConfigNotification($cod_empresa){
        $query = "SELECT * FROM tb_empresa_notificaciones 
                    WHERE cod_empresa = $cod_empresa 
                    AND aplicacion = 'USUARIOS'";
        return Conexion::buscarRegistro($query);
    }
}
?>