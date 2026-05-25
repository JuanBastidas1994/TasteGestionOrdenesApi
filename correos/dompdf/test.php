<?php
    //error_reporting(0);
    function getValues($usuario, $orden, $detalles, $courier, $idTracking){
        $nombre = $usuario['nombre'].' '.$usuario['apellido'];
        $direccion = $orden['direccion'].' '.$orden['referencia2'];
        $telefono = $usuario['telefono'];
        $fecha = fechaLatino($orden['fecha']);

        $subtotal = number_format($orden['subtotal'],2);
        $iva = number_format($orden['iva'],2);
        $total = number_format($orden['total'],2);

        $token = $courier['token'];

        //DETALLES
        foreach ($detalles as $detalle) {
            $lstProd = '    <tr>
                                <td class="prc-10">'.$detalle['cantidad'].' x</td>
                                <td>
                                    <p>'.$detalle['producto'].'</p>
                                    <p><b>SKU</b></p>
                                    <p>'.$detalle['sku'].'</p>
                                </td>
                                <td>
                                    <p>$'.number_format($detalle['precio'], 2).'</p>
                                </td>
                            </tr>';
        }

        return '  <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link href="https://fonts.googleapis.com/css?family=Montserrat:500,800" rel="stylesheet"> 
                    <title>Document</title>
                    <style>
                        body{
                            background-color: #F7F7F7;
                            font-family: \'Montserrat\';
                        }
                        table{
                            width: 100%;
                        }
                        table td{
                            padding: 10px 0;
                        }
                        .prc-10{
                            width: 10%;
                        }
                        .prc-25{
                            width: 25%;
                        }
                        .d-flex{
                            display: flex;
                        }
                        .container{
                            max-width: 600px;
                            padding: 0 15px;
                            margin: 0 auto;
                        }
                        .px-3{
                            padding-left: 1rem;
                            padding-right: 1rem;
                        }
                        .color-dark{
                            color: #4A4A4A;
                        }
                        .text-left{
                            text-align: left !important;
                        }
                        .text-right{
                            text-align: right !important;
                        }
                        .text-center{
                            text-align: center !important;
                        }
                        .details{
                            background-color: #FFF;
                            padding: 10px;
                            border-radius: 20px;
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1 class="color-dark text-center">Solicitud de envío</h1>
                
                        <div>
                            <div class="px-3">
                                <p>
                                    Nuevo pedido enviado el '.$fecha.'.
                                </p>
                                <p>
                                    Este es el detalle del pedido
                                </p>
                            </div>
                            <div class="details">
                                <div>
                                    <table id="tbProds">
                                        '.$lstProd.'
                                    </table>
                                </div>
                                <hr>
                
                                <div>
                                    <div>
                                        <table>
                                            <tr>
                                                <td><b>SUBTOTAL</b></td>
                                                <td class="text-right"><span>$'.$subtotal.'</span></td>
                                            </tr>
                                            <tr>
                                                <td><b>IVA</b></td>
                                                <td class="text-right"><span>$'.$iva.'</span></td>
                                            </tr>
                                            <tr>
                                                <td><b>TOTAL</b></td>
                                                <td class="text-right"><span>$'.$total.'</span></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <hr>
                
                                <div>
                                    <table>
                                        <tr>
                                            <td><b>Id para Tracking</b></td>
                                            <td class="text-right"><span>'.$idTracking.'</span></td>
                                        </tr>
                                        <tr>
                                            <td><b>Token Inlog</b></td>
                                            <td class="text-right">'.$token.'<span></span></td>
                                        </tr>
                                        <tr>
                                            <td><b>Persona</b></td>
                                            <td class="text-right"><span>'.$nombre.'</span></td>
                                        </tr>
                                        <tr>
                                            <td><b>Teléfono</b></td>
                                            <td class="text-right"><span>'.$telefono.'</span></td>
                                        </tr>
                                        <tr>
                                            <td><b>Dirección</b></td>
                                            <td class="text-right"><span>'.$direccion.'</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </body>
                </html>';
    }
?>