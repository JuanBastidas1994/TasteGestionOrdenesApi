# Base de datos — digitalm_mi_ecommerce

Documentación automática para Claude Code.


# Convenciones

- Todas las PK usan prefijo `cod_`
- Estados usan:
  - A = Activo
  - I = Inactivo
  - D = Eliminado
- Relaciones NO usan foreign keys físicas

---

# auth_tokens

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_token | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| token | varchar(300) | YES |  |  |
| navigator | varchar(25) | NO |  |  |
| operative_system | varchar(25) | NO |  |  |
| is_mobile | int(11) | NO |  |  |
| fecha_creacion | date | YES |  |  |
| fecha_expiracion | date | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `auth_tokens` (
  `cod_token` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `token` varchar(300) DEFAULT NULL,
  `navigator` varchar(25) NOT NULL,
  `operative_system` varchar(25) NOT NULL,
  `is_mobile` int(11) NOT NULL DEFAULT '0',
  `fecha_creacion` date DEFAULT NULL,
  `fecha_expiracion` date DEFAULT NULL,
  `estado` enum('A','I','D') DEFAULT NULL,
  PRIMARY KEY (`cod_token`)
) ENGINE=InnoDB AUTO_INCREMENT=35338 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_token
- cod_usuario

---

# currency

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| iso | char(3) | NO | PRI |  |
| name | varchar(200) | NO | UNI |  |

## SQL Structure

```sql
CREATE TABLE `currency` (
  `iso` char(3) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`iso`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

---

# datos_compra

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_dato_compra | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| nombre | varchar(50) | YES |  |  |
| apellido | varchar(50) | YES |  |  |
| empresa | varchar(50) | YES |  |  |
| ciudad | varchar(50) | YES |  |  |
| correo | varchar(50) | YES |  |  |
| telefono | varchar(10) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `datos_compra` (
  `cod_dato_compra` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `nombre` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `apellido` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `empresa` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ciudad` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correo` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefono` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_dato_compra`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_dato_compra
- cod_usuario

---

# log_error_app

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa | int(11) | YES |  |  |
| SO | varchar(30) | YES |  |  |
| version_code | varchar(8) | YES |  |  |
| usuario_logeado | int(11) | YES |  |  |
| obs1 | varchar(200) | YES |  |  |
| obs2 | varchar(50) | YES |  |  |
| fecha | datetime | YES |  |  |
| error | text | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `log_error_app` (
  `cod_empresa` int(11) DEFAULT NULL,
  `SO` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version_code` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,
  `usuario_logeado` int(11) DEFAULT NULL,
  `obs1` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `obs2` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `error` text COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa

---

# log_pagos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_log_pago | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| tipo | varchar(10) | YES |  |  |
| detalle | text | YES |  |  |
| mensaje_resp | text | YES |  |  |
| estado_resp | text | YES |  |  |
| fecha | datetime | YES |  |  |
| cod_transaccion | varchar(25) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `log_pagos` (
  `cod_log_pago` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `tipo` varchar(10) DEFAULT NULL,
  `detalle` text,
  `mensaje_resp` text,
  `estado_resp` text,
  `fecha` datetime DEFAULT NULL,
  `cod_transaccion` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`cod_log_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=228 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_log_pago
- cod_usuario
- cod_transaccion

---

# mie_auth_intent_login

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_intent_login | int(11) | NO | PRI | auto_increment |
| usuario | varchar(100) | YES |  |  |
| password | varchar(100) | NO |  |  |
| token | varchar(100) | YES |  |  |
| fecha | datetime | YES |  |  |
| ip | varchar(20) | YES |  |  |
| success | int(11) | YES |  |  |
| cod_usuario | int(11) | YES |  |  |
| estado | enum('A','I') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `mie_auth_intent_login` (
  `cod_intent_login` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `ip` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `success` int(11) DEFAULT NULL,
  `cod_usuario` int(11) DEFAULT NULL,
  `estado` enum('A','I') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_intent_login`)
) ENGINE=InnoDB AUTO_INCREMENT=27021 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_intent_login
- cod_usuario

---

# mie_log_pago

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_mie_log_pago | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| fecha | datetime | YES |  |  |
| monto | float | YES |  |  |
| card_type | varchar(5) | YES |  |  |
| card_number | varchar(5) | YES |  |  |
| card_token | varchar(20) | YES |  |  |
| estado | varchar(10) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `mie_log_pago` (
  `cod_mie_log_pago` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `monto` float DEFAULT NULL,
  `card_type` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `card_number` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `card_token` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_mie_log_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa

---

# mie_log_pago_error

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_mie_log_pago_error | int(11) | NO | PRI | auto_increment |
| cod_mie_log_pago | int(11) | YES |  |  |
| descripcion | varchar(150) | YES |  |  |
| json | text | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `mie_log_pago_error` (
  `cod_mie_log_pago_error` int(11) NOT NULL AUTO_INCREMENT,
  `cod_mie_log_pago` int(11) DEFAULT NULL,
  `descripcion` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `json` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`cod_mie_log_pago_error`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_mie_log_pago

---

# mie_log_pago_success

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_mie_log_pago_success | int(11) | NO | PRI | auto_increment |
| cod_mie_log_pago | int(11) | YES |  |  |
| transaction_id | varchar(10) | YES |  |  |
| transaction_status | varchar(10) | YES |  |  |
| transaction_reference | varchar(100) | YES |  |  |
| transaction_autorizacion | varchar(10) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `mie_log_pago_success` (
  `cod_mie_log_pago_success` int(11) NOT NULL AUTO_INCREMENT,
  `cod_mie_log_pago` int(11) DEFAULT NULL,
  `transaction_id` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `transaction_status` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `transaction_reference` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `transaction_autorizacion` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_mie_log_pago_success`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_mie_log_pago

---

# promocion_producto

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_promocion | int(11) | YES |  |  |
| cod_producto | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `promocion_producto` (
  `cod_promocion` int(11) DEFAULT NULL,
  `cod_producto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_promocion
- cod_producto

---

# promocion_recompensa

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_recompensa | int(11) | NO | PRI | auto_increment |
| cod_promocion | int(11) | NO | MUL |  |
| cod_producto_regalo | int(11) | NO |  |  |
| cantidad_regalo | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `promocion_recompensa` (
  `cod_recompensa` int(11) NOT NULL AUTO_INCREMENT,
  `cod_promocion` int(11) NOT NULL,
  `cod_producto_regalo` int(11) NOT NULL,
  `cantidad_regalo` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cod_recompensa`),
  KEY `cod_promocion` (`cod_promocion`),
  CONSTRAINT `promocion_recompensa_ibfk_1` FOREIGN KEY (`cod_promocion`) REFERENCES `promociones` (`cod_promocion`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_recompensa
- cod_promocion
- cod_producto_regalo

---

# promocion_recurrente

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_promocion | int(11) | YES |  |  |
| dia_semana | enum('lunes','martes','miércoles','jueves','viernes','sábado','domingo') | YES |  |  |
| hora_inicio | time | YES |  |  |
| hora_fin | time | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `promocion_recurrente` (
  `cod_promocion` int(11) DEFAULT NULL,
  `dia_semana` enum('lunes','martes','miércoles','jueves','viernes','sábado','domingo') COLLATE utf8_unicode_ci DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_promocion

---

# promocion_sucursal

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_promocion | int(11) | YES |  |  |
| cod_sucursal | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `promocion_sucursal` (
  `cod_promocion` int(11) DEFAULT NULL,
  `cod_sucursal` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_promocion
- cod_sucursal

---

# promocion_tipo_entrega

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_promocion | int(11) | NO | PRI |  |
| tipo_entrega | enum('DELIVERY','PICKUP','EN_MESA') | NO | PRI |  |

## SQL Structure

```sql
CREATE TABLE `promocion_tipo_entrega` (
  `cod_promocion` int(11) NOT NULL,
  `tipo_entrega` enum('DELIVERY','PICKUP','EN_MESA') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_promocion`,`tipo_entrega`),
  CONSTRAINT `promocion_tipo_entrega_ibfk_1` FOREIGN KEY (`cod_promocion`) REFERENCES `promociones` (`cod_promocion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_promocion

---

# promociones

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_promocion | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| descripcion | varchar(255) | YES |  |  |
| is_porcentaje | int(11) | YES |  |  |
| cantidad | int(11) | NO |  |  |
| valor | decimal(10,2) | YES |  |  |
| texto | varchar(255) | YES |  |  |
| fecha_inicio | datetime | YES |  |  |
| fecha_fin | datetime | YES |  |  |
| is_recurrente | tinyint(1) | YES |  |  |
| estado | enum('A','I','D') | NO |  |  |
| imagen | varchar(255) | NO |  |  |
| tipo_promocion | varchar(50) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `promociones` (
  `cod_promocion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_porcentaje` int(11) DEFAULT '0',
  `cantidad` int(11) NOT NULL DEFAULT '0',
  `valor` decimal(10,2) DEFAULT NULL,
  `texto` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `is_recurrente` tinyint(1) DEFAULT '0',
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'A',
  `imagen` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tipo_promocion` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'descuento',
  PRIMARY KEY (`cod_promocion`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_promocion
- cod_empresa

---

# tb_agenda

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_agenda | int(11) | NO | PRI | auto_increment |
| cod_categoria | int(11) | NO |  |  |
| titulo | varchar(100) | YES |  |  |
| imagen | varchar(150) | YES |  |  |
| archivo | varchar(100) | YES |  |  |
| fecha | date | YES |  |  |
| hora_inicio | time | YES |  |  |
| hora_fin | time | YES |  |  |
| user_create | int(11) | YES |  |  |
| color | int(11) | YES |  |  |
| descripcion | text | YES |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_agenda` (
  `cod_agenda` int(11) NOT NULL AUTO_INCREMENT,
  `cod_categoria` int(11) NOT NULL,
  `titulo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imagen` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `archivo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `user_create` int(11) DEFAULT NULL,
  `color` int(11) DEFAULT NULL,
  `descripcion` text COLLATE utf8_unicode_ci,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_agenda`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_categoria

---

# tb_agenda_categorias

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_agenda_categoria | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| nombre | varchar(100) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_agenda_categorias` (
  `cod_agenda_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_agenda_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_agenda_categoria
- cod_empresa

---

# tb_anuncio_cabecera

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_anuncio_cabecera | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| nombre | varchar(150) | YES |  |  |
| descripcion | varchar(500) | YES |  |  |
| width | int(11) | NO |  |  |
| height | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_anuncio_cabecera` (
  `cod_anuncio_cabecera` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `nombre` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `descripcion` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  PRIMARY KEY (`cod_anuncio_cabecera`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa

---

# tb_anuncio_detalle

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_anuncio_detalle | int(11) | NO | PRI | auto_increment |
| cod_anuncio_cabecera | int(11) | YES |  |  |
| cod_empresa | int(11) | YES |  |  |
| titulo | varchar(150) | YES |  |  |
| subtitulo | varchar(150) | YES |  |  |
| descripcion | varchar(600) | YES |  |  |
| imagen | varchar(50) | YES |  |  |
| text_boton | varchar(50) | YES |  |  |
| url_boton | varchar(300) | YES |  |  |
| accion_id | enum('FILTER','PRODUCTO','NOTICIA','URL','ZOOM','INFO') | NO |  |  |
| categorias | text | NO |  |  |
| posicion | int(11) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |
| fecha_modificacion | timestamp | NO |  | on update CURRENT_TIMESTAMP |

## SQL Structure

```sql
CREATE TABLE `tb_anuncio_detalle` (
  `cod_anuncio_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `cod_anuncio_cabecera` int(11) DEFAULT NULL,
  `cod_empresa` int(11) DEFAULT NULL,
  `titulo` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subtitulo` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `descripcion` varchar(600) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imagen` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text_boton` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_boton` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `accion_id` enum('FILTER','PRODUCTO','NOTICIA','URL','ZOOM','INFO') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'URL',
  `categorias` text COLLATE utf8_unicode_ci NOT NULL,
  `posicion` int(11) DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_modificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cod_anuncio_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=596 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_anuncio_cabecera
- cod_empresa

---

# tb_app_registro_reglas

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_app_registro_regla | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| origen | varchar(30) | YES |  |  |
| version_code | int(11) | YES |  |  |
| campo | varchar(10) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_app_registro_reglas` (
  `cod_app_registro_regla` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `origen` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version_code` int(11) DEFAULT NULL,
  `campo` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_app_registro_regla`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_app_registro_regla
- cod_empresa

---

# tb_banner

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_banner | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| titulo | varchar(300) | YES |  |  |
| subtitulo | varchar(300) | YES |  |  |
| descuento | varchar(150) | NO |  |  |
| text_boton | varchar(50) | NO |  |  |
| url_boton | varchar(300) | NO |  |  |
| tipo | enum('APP','WEB') | YES |  |  |
| image_min | varchar(50) | YES |  |  |
| posicion | int(11) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |
| ubicacion | enum('top_left','top_center','top_right','center_left','center','center_right','bottom_left','bottom_center','bottom_right') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_banner` (
  `cod_banner` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `titulo` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subtitulo` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `descuento` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `text_boton` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `url_boton` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `tipo` enum('APP','WEB') COLLATE utf8_unicode_ci DEFAULT NULL,
  `image_min` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT 'A',
  `ubicacion` enum('top_left','top_center','top_right','center_left','center','center_right','bottom_left','bottom_center','bottom_right') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'bottom_center',
  PRIMARY KEY (`cod_banner`)
) ENGINE=MyISAM AUTO_INCREMENT=589 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa

---

# tb_cajero_gestionordenes_config

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_cajero_go | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| recordatorio | text | YES |  |  |
| printer | text | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_cajero_gestionordenes_config` (
  `cod_cajero_go` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `recordatorio` text COLLATE utf8_unicode_ci,
  `printer` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`cod_cajero_go`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_cajero_go
- cod_usuario

---

# tb_card_fidelizacion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_card_fidelizacion | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| cod_cliente | int(11) | YES |  |  |
| codigo | varchar(30) | YES |  |  |
| estado | enum('A','I') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_card_fidelizacion` (
  `cod_card_fidelizacion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `cod_cliente` int(11) DEFAULT '0',
  `codigo` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I') COLLATE utf8_unicode_ci DEFAULT 'I',
  PRIMARY KEY (`cod_card_fidelizacion`)
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa
- cod_cliente

---

# tb_catalogo_items

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_catalogo_item | int(11) | NO | PRI | auto_increment |
| cod_catalogo | int(11) | YES |  |  |
| nombre | varchar(150) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_catalogo_items` (
  `cod_catalogo_item` int(11) NOT NULL AUTO_INCREMENT,
  `cod_catalogo` int(11) DEFAULT NULL,
  `nombre` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_catalogo_item`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_catalogo_item
- cod_catalogo

---

# tb_catalogos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_catalogo | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| nombre | varchar(150) | YES |  |  |
| alias | varchar(150) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_catalogos` (
  `cod_catalogo` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `nombre` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `alias` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_catalogo`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_catalogo
- cod_empresa

---

# tb_categorias

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_categoria | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| cod_categoria_padre | int(11) | YES |  |  |
| alias | varchar(250) | YES |  |  |
| categoria | varchar(250) | YES |  |  |
| desc_corta | varchar(250) | YES |  |  |
| desc_larga | text | YES |  |  |
| image_min | varchar(150) | YES |  |  |
| image_max | varchar(150) | YES |  |  |
| posicion | int(11) | YES |  |  |
| fecha_modificacion | timestamp | NO |  | on update CURRENT_TIMESTAMP |
| estado | enum('A','I','D') | YES |  |  |
| image_path | text | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_categorias` (
  `cod_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `cod_categoria_padre` int(11) DEFAULT '0',
  `alias` varchar(250) DEFAULT NULL,
  `categoria` varchar(250) DEFAULT NULL,
  `desc_corta` varchar(250) DEFAULT NULL,
  `desc_larga` text,
  `image_min` varchar(150) DEFAULT NULL,
  `image_max` varchar(150) DEFAULT NULL,
  `posicion` int(11) DEFAULT '0',
  `fecha_modificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `estado` enum('A','I','D') DEFAULT NULL,
  `image_path` text,
  PRIMARY KEY (`cod_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=1666 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_categoria
- cod_empresa
- cod_categoria_padre

---

# tb_categorias_dependientes

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_categoria_dependiente | int(11) | NO | PRI | auto_increment |
| cod_categoria | int(11) | YES |  |  |
| cod_categoria_padre | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_categorias_dependientes` (
  `cod_categoria_dependiente` int(11) NOT NULL AUTO_INCREMENT,
  `cod_categoria` int(11) DEFAULT NULL,
  `cod_categoria_padre` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_categoria_dependiente`)
) ENGINE=InnoDB AUTO_INCREMENT=1903 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_categoria_dependiente
- cod_categoria
- cod_categoria_padre

---

# tb_categorias_noticias

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_categorias_noticias | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | NO |  |  |
| nombre | varchar(50) | YES |  |  |
| cod_categoria_padre | int(11) | YES |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_categorias_noticias` (
  `cod_categorias_noticias` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `cod_categoria_padre` int(11) DEFAULT NULL,
  `estado` enum('A','I','D') NOT NULL,
  PRIMARY KEY (`cod_categorias_noticias`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4
```

## Posibles relaciones

- cod_empresa
- cod_categoria_padre

---

# tb_cierre_caja

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_cierre | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | NO |  |  |
| cod_usuario | int(11) | NO |  |  |
| valor_inicial | float | NO |  |  |
| hora_inicial | datetime | NO |  |  |
| efectivo_cierre | float | YES |  |  |
| otros_cierre | float | YES |  |  |
| hora_cierre | datetime | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_cierre_caja` (
  `cod_cierre` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) NOT NULL,
  `cod_usuario` int(11) NOT NULL,
  `valor_inicial` float NOT NULL,
  `hora_inicial` datetime NOT NULL,
  `efectivo_cierre` float DEFAULT NULL,
  `otros_cierre` float DEFAULT NULL,
  `hora_cierre` datetime DEFAULT NULL,
  PRIMARY KEY (`cod_cierre`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_cierre
- cod_sucursal
- cod_usuario

---

# tb_ciudades

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_ciudad | int(11) | NO | PRI | auto_increment |
| cod_courier | int(11) | NO |  |  |
| nombre | char(35) | NO |  |  |
| codigo | varchar(20) | NO |  |  |
| provincia | varchar(100) | NO |  |  |
| trayecto | varchar(5) | NO |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_ciudades` (
  `cod_ciudad` int(11) NOT NULL AUTO_INCREMENT,
  `cod_courier` int(11) NOT NULL DEFAULT '2',
  `nombre` char(35) NOT NULL DEFAULT '',
  `codigo` varchar(20) NOT NULL,
  `provincia` varchar(100) NOT NULL,
  `trayecto` varchar(5) NOT NULL,
  `estado` enum('A','I','D') NOT NULL DEFAULT 'A',
  PRIMARY KEY (`cod_ciudad`)
) ENGINE=InnoDB AUTO_INCREMENT=361 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_ciudad
- cod_courier

---

# tb_cliente_dinero

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_cliente_dinero | int(11) | NO | PRI | auto_increment |
| cod_cliente | int(11) | YES |  |  |
| cod_orden | varchar(60) | YES |  |  |
| cod_tipo_pago | int(11) | NO |  |  |
| dinero | float | YES |  |  |
| saldo | float | NO |  |  |
| fecha | date | YES |  |  |
| fecha_caducidad | date | NO |  |  |
| estado | enum('A','I','D') | NO |  |  |
| user_create | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_cliente_dinero` (
  `cod_cliente_dinero` int(11) NOT NULL AUTO_INCREMENT,
  `cod_cliente` int(11) DEFAULT NULL,
  `cod_orden` varchar(60) COLLATE utf8_unicode_ci DEFAULT '',
  `cod_tipo_pago` int(11) NOT NULL,
  `dinero` float DEFAULT NULL,
  `saldo` float NOT NULL,
  `fecha` date DEFAULT NULL,
  `fecha_caducidad` date NOT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL,
  `user_create` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cod_cliente_dinero`)
) ENGINE=InnoDB AUTO_INCREMENT=117914 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_cliente
- cod_orden
- cod_tipo_pago

---

# tb_cliente_facturacion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_cliente_facturacion | int(11) | NO | PRI | auto_increment |
| id | varchar(50) | YES |  |  |
| cod_usuario | int(11) | YES |  |  |
| cod_sistema_facturacion | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_cliente_facturacion` (
  `cod_cliente_facturacion` int(11) NOT NULL AUTO_INCREMENT,
  `id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cod_usuario` int(11) DEFAULT NULL,
  `cod_sistema_facturacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_cliente_facturacion`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_usuario
- cod_sistema_facturacion

---

# tb_clientes

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_cliente | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | NO |  |  |
| cod_usuario | int(11) | NO |  |  |
| cod_nivel | int(11) | NO |  |  |
| nombre | varchar(300) | NO |  |  |
| fecha_nac | date | YES |  |  |
| tipo_documento | int(11) | NO |  |  |
| num_documento | varchar(15) | NO |  |  |
| correo | varchar(100) | NO |  |  |
| correo2 | varchar(100) | NO |  |  |
| telefono | varchar(20) | NO |  |  |
| telefono_2 | varchar(20) | NO |  |  |
| direccion | varchar(300) | NO |  |  |
| tipo_persona | int(11) | NO |  |  |
| es_extranjero | int(11) | NO |  |  |
| cod_prioridad | int(11) | NO |  |  |
| observacion | text | NO |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_clientes` (
  `cod_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) NOT NULL,
  `cod_usuario` int(11) NOT NULL,
  `cod_nivel` int(11) NOT NULL DEFAULT '1',
  `nombre` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `fecha_nac` date DEFAULT NULL,
  `tipo_documento` int(11) NOT NULL,
  `num_documento` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `correo` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `correo2` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `telefono_2` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `direccion` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `tipo_persona` int(11) NOT NULL,
  `es_extranjero` int(11) NOT NULL,
  `cod_prioridad` int(11) NOT NULL,
  `observacion` text COLLATE utf8_unicode_ci NOT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_cliente`)
) ENGINE=MyISAM AUTO_INCREMENT=110786 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_cliente
- cod_empresa
- cod_usuario
- cod_nivel
- cod_prioridad

---

# tb_clientes_puntos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_cliente_punto | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | YES |  |  |
| cod_cliente | int(11) | YES |  |  |
| cod_nivel | int(11) | YES |  |  |
| puntos | int(11) | YES |  |  |
| dinero | float | YES |  |  |
| fecha_create | date | YES |  |  |
| fecha_caducidad | date | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_clientes_puntos` (
  `cod_cliente_punto` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) DEFAULT NULL,
  `cod_cliente` int(11) DEFAULT NULL,
  `cod_nivel` int(11) DEFAULT NULL,
  `puntos` int(11) DEFAULT NULL,
  `dinero` float DEFAULT NULL,
  `fecha_create` date DEFAULT NULL,
  `fecha_caducidad` date DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_cliente_punto`)
) ENGINE=InnoDB AUTO_INCREMENT=85307 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_cliente_punto
- cod_orden
- cod_cliente
- cod_nivel

---

# tb_clientes_saldos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_cliente_saldo | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | YES |  |  |
| cod_cliente | int(11) | NO |  |  |
| dinero | float | YES |  |  |
| saldo_anterior | float | NO |  |  |
| fecha_create | date | YES |  |  |
| fecha_caducidad | date | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_clientes_saldos` (
  `cod_cliente_saldo` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) DEFAULT NULL,
  `cod_cliente` int(11) NOT NULL,
  `dinero` float DEFAULT NULL,
  `saldo_anterior` float NOT NULL,
  `fecha_create` date DEFAULT NULL,
  `fecha_caducidad` date DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_cliente_saldo`)
) ENGINE=InnoDB AUTO_INCREMENT=127710 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_cliente_saldo
- cod_orden
- cod_cliente

---

# tb_codigo_promocional

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_codigo_promocional | int(11) | NO | PRI | auto_increment |
| codigo | varchar(50) | YES |  |  |
| tipo | enum('descuento','giftcard') | YES |  |  |
| por_o_din | int(11) | NO |  |  |
| monto | float | YES |  |  |
| cantidad | int(11) | YES |  |  |
| usos_restantes | int(11) | YES |  |  |
| restriccion | float | NO |  |  |
| fecha_create | date | YES |  |  |
| fecha_expiracion | date | YES |  |  |
| cod_empresa | int(11) | NO |  |  |
| ilimitado | int(11) | NO |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_codigo_promocional` (
  `cod_codigo_promocional` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tipo` enum('descuento','giftcard') COLLATE utf8_unicode_ci DEFAULT NULL,
  `por_o_din` int(11) NOT NULL,
  `monto` float DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `usos_restantes` int(11) DEFAULT NULL,
  `restriccion` float NOT NULL,
  `fecha_create` date DEFAULT NULL,
  `fecha_expiracion` date DEFAULT NULL,
  `cod_empresa` int(11) NOT NULL,
  `ilimitado` int(11) NOT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_codigo_promocional`)
) ENGINE=InnoDB AUTO_INCREMENT=351 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa

---

# tb_config_api

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| clave | varchar(50) | NO | PRI |  |
| valor | text | NO |  |  |
| fecha_expiracion | datetime | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_config_api` (
  `clave` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `valor` text COLLATE utf8_unicode_ci NOT NULL,
  `fecha_expiracion` datetime NOT NULL,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

---

# tb_contifico_empresa

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_contifico_empresa | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| ambiente | enum('development','production') | YES |  |  |
| razon_social | varchar(50) | NO |  |  |
| ruc | varchar(20) | NO |  |  |
| api | varchar(150) | YES |  |  |
| categoria | varchar(20) | NO |  |  |
| cuenta_id_products | varchar(60) | NO |  |  |
| estado | enum('A','I') | NO |  |  |
| facturar | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_contifico_empresa` (
  `cod_contifico_empresa` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `ambiente` enum('development','production') DEFAULT NULL,
  `razon_social` varchar(50) NOT NULL,
  `ruc` varchar(20) NOT NULL,
  `api` varchar(150) DEFAULT NULL,
  `categoria` varchar(20) NOT NULL,
  `cuenta_id_products` varchar(60) NOT NULL,
  `estado` enum('A','I') NOT NULL,
  `facturar` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cod_contifico_empresa`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_empresa

---

# tb_contifico_empresa_postokens

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_postoken | int(11) | NO | PRI | auto_increment |
| cod_contifico_empresa | int(11) | NO |  |  |
| cod_empresa | int(11) | YES |  |  |
| ambiente | enum('development','production') | YES |  |  |
| pos | varchar(120) | YES |  |  |
| emisor | varchar(3) | YES |  |  |
| ptoemision | varchar(3) | YES |  |  |
| secuencial | int(11) | YES |  |  |
| secuencial_dna | int(11) | NO |  |  |
| tipo_documento | enum('FAC','DNA') | NO |  |  |
| facturar | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_contifico_empresa_postokens` (
  `cod_postoken` int(11) NOT NULL AUTO_INCREMENT,
  `cod_contifico_empresa` int(11) NOT NULL,
  `cod_empresa` int(11) DEFAULT NULL,
  `ambiente` enum('development','production') COLLATE utf8_unicode_ci DEFAULT NULL,
  `pos` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL,
  `emisor` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ptoemision` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `secuencial` int(11) DEFAULT NULL,
  `secuencial_dna` int(11) NOT NULL DEFAULT '1',
  `tipo_documento` enum('FAC','DNA') COLLATE utf8_unicode_ci NOT NULL,
  `facturar` int(11) NOT NULL,
  PRIMARY KEY (`cod_postoken`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_postoken
- cod_contifico_empresa
- cod_empresa

---

# tb_contifico_sucursal

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_contifico_sucursal | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | YES |  |  |
| cod_contifico_empresa | int(11) | NO |  |  |
| cod_postoken | int(11) | YES |  |  |
| id_bodega | varchar(100) | NO |  |  |
| name_bodega | varchar(100) | NO |  |  |
| inventario | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_contifico_sucursal` (
  `cod_contifico_sucursal` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) DEFAULT NULL,
  `cod_contifico_empresa` int(11) NOT NULL,
  `cod_postoken` int(11) DEFAULT NULL,
  `id_bodega` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `name_bodega` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `inventario` int(11) NOT NULL,
  PRIMARY KEY (`cod_contifico_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal
- cod_contifico_empresa
- cod_postoken

---

# tb_cotizaciones_json

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_cotizacion_json | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | NO |  |  |
| nombre | varchar(60) | YES |  |  |
| correo | varchar(30) | YES |  |  |
| telefono | varchar(10) | YES |  |  |
| fecha | date | YES |  |  |
| json | longtext | YES |  |  |
| fecha_create | date | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_cotizaciones_json` (
  `cod_cotizacion_json` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) NOT NULL,
  `nombre` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correo` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefono` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `json` longtext COLLATE utf8_unicode_ci,
  `fecha_create` date DEFAULT NULL,
  PRIMARY KEY (`cod_cotizacion_json`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_cotizacion_json
- cod_empresa

---

# tb_courier

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_courier | int(11) | NO | PRI | auto_increment |
| nombre | varchar(100) | NO |  |  |
| tipo | enum('MOTO','CAMION') | NO |  |  |
| imagen | varchar(150) | NO |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_courier` (
  `cod_courier` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `tipo` enum('MOTO','CAMION') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'MOTO',
  `imagen` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_courier`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

---

# tb_cupones

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_cupon | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| titulo | varchar(100) | YES |  |  |
| imagen | varchar(100) | YES |  |  |
| descripcion | text | YES |  |  |
| cantidad_dias_disponibles | int(11) | YES |  |  |
| tipo | enum('CUMPLEANIOS','REGISTRO','NIVEL2','NIVEL3') | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_cupones` (
  `cod_cupon` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `titulo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imagen` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8_unicode_ci,
  `cantidad_dias_disponibles` int(11) DEFAULT NULL,
  `tipo` enum('CUMPLEANIOS','REGISTRO','NIVEL2','NIVEL3') COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_cupon`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_cupon
- cod_empresa

---

# tb_cupones_usuarios

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_cupon_usuario | int(11) | NO | PRI | auto_increment |
| cod_cupon | int(11) | YES |  |  |
| cod_usuario | int(11) | YES |  |  |
| fecha_creacion | date | YES |  |  |
| fecha_caducidad | date | YES |  |  |
| estado | enum('ACTIVO','USADO') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_cupones_usuarios` (
  `cod_cupon_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `cod_cupon` int(11) DEFAULT NULL,
  `cod_usuario` int(11) DEFAULT NULL,
  `fecha_creacion` date DEFAULT NULL,
  `fecha_caducidad` date DEFAULT NULL,
  `estado` enum('ACTIVO','USADO') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_cupon_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=16794 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_cupon_usuario
- cod_cupon
- cod_usuario

---

# tb_dashboard_helpdesk

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_helpdesk | int(11) | NO | PRI | auto_increment |
| alias | varchar(300) | YES |  |  |
| titulo | varchar(300) | YES |  |  |
| tags | varchar(300) | YES |  |  |
| video | varchar(150) | YES |  |  |
| desc_corta | varchar(500) | YES |  |  |
| desc_larga | text | YES |  |  |
| user_create | int(11) | YES |  |  |
| fecha_create | datetime | YES |  |  |
| posicion | int(11) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_dashboard_helpdesk` (
  `cod_helpdesk` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `titulo` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tags` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `video` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `desc_corta` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `desc_larga` text COLLATE utf8_unicode_ci,
  `user_create` int(11) DEFAULT NULL,
  `fecha_create` datetime DEFAULT NULL,
  `posicion` int(11) DEFAULT '99',
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_helpdesk`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_helpdesk

---

# tb_demos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_demo | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| nombre | varchar(150) | YES |  |  |
| alias | varchar(150) | YES |  |  |
| direccion | varchar(300) | YES |  |  |
| telefono | varchar(30) | YES |  |  |
| correo | varchar(100) | YES |  |  |
| logo | varchar(150) | YES |  |  |
| color | varchar(10) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_demos` (
  `cod_demo` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `nombre` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `alias` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `direccion` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logo` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `color` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_demo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_demo
- cod_empresa

---

# tb_descuentos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_descuento | int(11) | NO | PRI | auto_increment |
| nombre | varchar(100) | NO |  |  |
| porcentaje | int(11) | YES |  |  |
| posicion | int(11) | YES |  |  |
| cod_empresa | int(11) | NO |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_descuentos` (
  `cod_descuento` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `porcentaje` int(11) DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  `cod_empresa` int(11) NOT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_descuento`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_descuento
- cod_empresa

---

# tb_disponibilidad

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_disponibilidad | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | NO |  |  |
| cod_usuario | int(11) | YES |  |  |
| dia | int(11) | NO |  |  |
| hora_inicio | varchar(11) | NO |  |  |
| hora_final | varchar(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_disponibilidad` (
  `cod_disponibilidad` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) NOT NULL,
  `cod_usuario` int(11) DEFAULT NULL,
  `dia` int(11) NOT NULL,
  `hora_inicio` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  `hora_final` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_disponibilidad`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal
- cod_usuario

---

# tb_empresa_botonpagos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_botonpagos | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| cod_proveedor_botonpagos | int(11) | YES |  |  |
| fecha_create | datetime | YES |  |  |
| user_create | int(11) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_botonpagos` (
  `cod_empresa_botonpagos` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `cod_proveedor_botonpagos` int(11) DEFAULT NULL,
  `fecha_create` datetime DEFAULT NULL,
  `user_create` int(11) DEFAULT '0',
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_empresa_botonpagos`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa
- cod_proveedor_botonpagos

---

# tb_empresa_clickup

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_clickup | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| id_lista | varchar(30) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_clickup` (
  `cod_empresa_clickup` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `id_lista` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_empresa_clickup`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa

---

# tb_empresa_configuraciones

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_configuracion | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| encender_tienda | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_configuraciones` (
  `cod_empresa_configuracion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `encender_tienda` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_empresa_configuracion`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa_configuracion
- cod_empresa

---

# tb_empresa_costo_envio

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_costo_envio | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| base_dinero | float | YES |  |  |
| base_km | float | YES |  |  |
| adicional_km | float | YES |  |  |
| tipo | enum('carro','moto','camion') | NO |  |  |
| peso_maximo | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_costo_envio` (
  `cod_empresa_costo_envio` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `base_dinero` float DEFAULT NULL,
  `base_km` float DEFAULT NULL,
  `adicional_km` float DEFAULT NULL,
  `tipo` enum('carro','moto','camion') NOT NULL DEFAULT 'moto',
  `peso_maximo` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_empresa_costo_envio`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_empresa

---

# tb_empresa_courier

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_courier | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | NO |  |  |
| cod_courier | int(11) | NO |  |  |
| estado | enum('A','I','D') | NO |  |  |
| fecha_create | datetime | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_courier` (
  `cod_empresa_courier` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) NOT NULL,
  `cod_courier` int(11) NOT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL,
  `fecha_create` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cod_empresa_courier`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa
- cod_courier

---

# tb_empresa_datafast

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_datafast | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| api | varchar(100) | YES |  |  |
| entityId | varchar(50) | YES |  |  |
| mid | varchar(15) | NO |  |  |
| tid | varchar(15) | NO |  |  |
| ambiente | enum('development','production') | YES |  |  |
| fase | enum('FASE1','FASE2') | NO |  |  |
| fecha_creacion | datetime | YES |  |  |
| user_creacion | int(11) | YES |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_datafast` (
  `cod_empresa_datafast` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `api` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entityId` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mid` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `tid` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `ambiente` enum('development','production') COLLATE utf8_unicode_ci DEFAULT NULL,
  `fase` enum('FASE1','FASE2') COLLATE utf8_unicode_ci NOT NULL,
  `fecha_creacion` datetime DEFAULT NULL,
  `user_creacion` int(11) DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_empresa_datafast`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa

---

# tb_empresa_facturacion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_facturacion | int(11) | NO | PRI | auto_increment |
| cod_sistema_facturacion | int(11) | YES |  |  |
| cod_empresa | int(11) | YES |  |  |
| prioridad | int(11) | NO |  |  |
| estado | enum('A','I','D') | NO |  |  |
| await_status | enum('ASIGNADA','ENTREGADA') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_facturacion` (
  `cod_empresa_facturacion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sistema_facturacion` int(11) DEFAULT NULL,
  `cod_empresa` int(11) DEFAULT NULL,
  `prioridad` int(11) NOT NULL DEFAULT '1',
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL,
  `await_status` enum('ASIGNADA','ENTREGADA') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ENTREGADA',
  PRIMARY KEY (`cod_empresa_facturacion`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sistema_facturacion
- cod_empresa

---

# tb_empresa_faqs

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_faqs | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| imagen | varchar(30) | NO |  |  |
| titulo | varchar(100) | YES |  |  |
| descripcion | varchar(600) | YES |  |  |
| posicion | int(11) | YES |  |  |
| tipo | enum('PUNTOS') | YES |  |  |
| estado | enum('A','I') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_faqs` (
  `cod_empresa_faqs` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `imagen` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `titulo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `descripcion` varchar(600) COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  `tipo` enum('PUNTOS') COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_empresa_faqs`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa

---

# tb_empresa_fidelizacion_puntos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_fidelizacion_puntos | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| divisor_puntos | int(11) | YES |  |  |
| monto_puntos | int(11) | YES |  |  |
| valor_regalo_cumple | float | NO |  |  |
| dias_regalo_cumple | int(11) | NO |  |  |
| compra_minimo_regalo_cumple | int(11) | NO |  |  |
| cant_dias_caducidad_puntos | int(11) | NO |  |  |
| cant_dias_caducidad_dinero | int(11) | NO |  |  |
| cant_dias_caducidad_saldo | int(11) | NO |  |  |
| generate_barcode | tinyint(1) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_fidelizacion_puntos` (
  `cod_fidelizacion_puntos` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `divisor_puntos` int(11) DEFAULT NULL COMMENT 'Cada cuanto saldo gana puntos',
  `monto_puntos` int(11) DEFAULT NULL COMMENT 'cantidad de puntos que gana por cada divisor de puntos',
  `valor_regalo_cumple` float NOT NULL,
  `dias_regalo_cumple` int(11) NOT NULL DEFAULT '0',
  `compra_minimo_regalo_cumple` int(11) NOT NULL DEFAULT '0',
  `cant_dias_caducidad_puntos` int(11) NOT NULL DEFAULT '90',
  `cant_dias_caducidad_dinero` int(11) NOT NULL DEFAULT '90',
  `cant_dias_caducidad_saldo` int(11) NOT NULL DEFAULT '90',
  `generate_barcode` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cod_fidelizacion_puntos`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_fidelizacion_puntos
- cod_empresa

---

# tb_empresa_forma_pago

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_forma_pago | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| cod_forma_pago | varchar(5) | YES |  |  |
| monto_maximo | float | NO |  |  |
| nombre | varchar(30) | NO |  |  |
| descripcion | text | YES |  |  |
| posicion | int(11) | YES |  |  |
| is_delivery | int(11) | NO |  |  |
| is_pickup | int(11) | NO |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_forma_pago` (
  `cod_empresa_forma_pago` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `cod_forma_pago` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `monto_maximo` float NOT NULL DEFAULT '0',
  `nombre` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8_unicode_ci,
  `posicion` int(11) DEFAULT '99',
  `is_delivery` int(11) NOT NULL DEFAULT '1',
  `is_pickup` int(11) NOT NULL DEFAULT '1',
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_empresa_forma_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=437 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa
- cod_forma_pago

---

# tb_empresa_modal_cumple

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_modal_cumple | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| imagen | varchar(30) | YES |  |  |
| fecha_actualizacion | timestamp | NO |  | on update CURRENT_TIMESTAMP |
| estado | enum('A','I') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_modal_cumple` (
  `cod_modal_cumple` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `imagen` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `estado` enum('A','I') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_modal_cumple`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_modal_cumple
- cod_empresa

---

# tb_empresa_notificaciones

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_notificacion | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | NO |  |  |
| aplicacion | enum('USUARIOS','MOTORIZADOS','DASHBOARD','OTRAS') | YES |  |  |
| token | text | YES |  |  |
| topic | varchar(25) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_notificaciones` (
  `cod_empresa_notificacion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) NOT NULL,
  `aplicacion` enum('USUARIOS','MOTORIZADOS','DASHBOARD','OTRAS') DEFAULT NULL,
  `token` text,
  `topic` varchar(25) DEFAULT NULL,
  `estado` enum('A','I','D') DEFAULT NULL,
  PRIMARY KEY (`cod_empresa_notificacion`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4
```

## Posibles relaciones

- cod_empresa_notificacion
- cod_empresa

---

# tb_empresa_pagos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_pago | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | NO |  |  |
| titulo | varchar(120) | NO |  |  |
| mensaje | text | NO |  |  |
| fecha_create | datetime | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_pagos` (
  `cod_empresa_pago` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) NOT NULL,
  `titulo` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8_unicode_ci NOT NULL,
  `fecha_create` datetime NOT NULL,
  PRIMARY KEY (`cod_empresa_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa_pago
- cod_empresa

---

# tb_empresa_paymentez

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_paymentez | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| client_code | varchar(50) | YES |  |  |
| client_key | varchar(50) | YES |  |  |
| server_code | varchar(50) | YES |  |  |
| server_key | varchar(50) | YES |  |  |
| save_card | int(11) | NO |  |  |
| ambiente | enum('development','production') | NO |  |  |
| fecha_creacion | datetime | YES |  |  |
| user_creacion | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_paymentez` (
  `cod_empresa_paymentez` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `client_code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `client_key` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `server_code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `server_key` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `save_card` int(11) NOT NULL DEFAULT '0',
  `ambiente` enum('development','production') COLLATE utf8_unicode_ci NOT NULL,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_creacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_empresa_paymentez`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa

---

# tb_empresa_payphone

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_payphone | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| identificador | varchar(25) | YES |  |  |
| token | text | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_payphone` (
  `cod_empresa_payphone` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `identificador` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `token` text COLLATE utf8_unicode_ci,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_empresa_payphone`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa

---

# tb_empresa_progresos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_progreso | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| titulo | varchar(100) | YES |  |  |
| porcentaje | float | YES |  |  |
| fecha_create | datetime | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_progresos` (
  `cod_empresa_progreso` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `titulo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `porcentaje` float DEFAULT NULL,
  `fecha_create` datetime DEFAULT NULL,
  PRIMARY KEY (`cod_empresa_progreso`)
) ENGINE=InnoDB AUTO_INCREMENT=613 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa_progreso
- cod_empresa

---

# tb_empresa_red_social

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_red_empresa | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| cod_red_social | int(11) | YES |  |  |
| descripcion | varchar(150) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_red_social` (
  `cod_red_empresa` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `cod_red_social` int(11) DEFAULT NULL,
  `descripcion` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_red_empresa`)
) ENGINE=MyISAM AUTO_INCREMENT=634 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_red_empresa
- cod_empresa
- cod_red_social

---

# tb_empresa_stripe

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_stripe | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| ambiente | enum('development','production') | YES |  |  |
| dev_public | varchar(120) | YES |  |  |
| dev_secret | varchar(120) | YES |  |  |
| pro_public | varchar(120) | YES |  |  |
| pro_secret | varchar(120) | YES |  |  |
| estado | enum('A','I') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_stripe` (
  `cod_empresa_stripe` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `ambiente` enum('development','production') COLLATE utf8_unicode_ci DEFAULT NULL,
  `dev_public` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dev_secret` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pro_public` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pro_secret` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_empresa_stripe`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa

---

# tb_empresa_sucursal_datafast

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_sucursal | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | YES |  |  |
| api | varchar(100) | YES |  |  |
| entityId | varchar(50) | YES |  |  |
| mid | varchar(15) | YES |  |  |
| tid | varchar(15) | YES |  |  |
| ambiente | enum('development','production') | YES |  |  |
| fase | enum('FASE1','FASE2') | YES |  |  |
| fecha_create | datetime | YES |  |  |
| user_create | int(11) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_sucursal_datafast` (
  `cod_empresa_sucursal` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) DEFAULT NULL,
  `api` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entityId` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mid` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tid` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ambiente` enum('development','production') COLLATE utf8_unicode_ci DEFAULT NULL,
  `fase` enum('FASE1','FASE2') COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_create` datetime DEFAULT NULL,
  `user_create` int(11) DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_empresa_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa_sucursal
- cod_sucursal

---

# tb_empresa_sucursal_paymentez

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_sucursal_paymentez | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | YES |  |  |
| client_code | varchar(50) | YES |  |  |
| client_key | varchar(50) | YES |  |  |
| server_code | varchar(50) | YES |  |  |
| server_key | varchar(50) | YES |  |  |
| save_card | int(11) | NO |  |  |
| ambiente | enum('development','production') | NO |  |  |
| fecha_creacion | datetime | YES |  |  |
| user_creacion | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_sucursal_paymentez` (
  `cod_empresa_sucursal_paymentez` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) DEFAULT NULL,
  `client_code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `client_key` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `server_code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `server_key` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `save_card` int(11) NOT NULL DEFAULT '0',
  `ambiente` enum('development','production') COLLATE utf8_unicode_ci NOT NULL,
  `fecha_creacion` datetime DEFAULT NULL,
  `user_creacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_empresa_sucursal_paymentez`)
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal

---

# tb_empresa_sucursal_payphone

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_sucursal_payphone | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | YES |  |  |
| identificador | text | YES |  |  |
| token | text | YES |  |  |
| storeid | varchar(50) | NO |  |  |
| ambiente | enum('development','production') | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_sucursal_payphone` (
  `cod_empresa_sucursal_payphone` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) DEFAULT NULL,
  `identificador` text COLLATE utf8_unicode_ci,
  `token` text COLLATE utf8_unicode_ci,
  `storeid` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `ambiente` enum('development','production') COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_empresa_sucursal_payphone`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal

---

# tb_empresa_suscripciones

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_suscripcion | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| correo | varchar(50) | YES |  |  |
| fecha | datetime | YES |  |  |
| origen | varchar(30) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_suscripciones` (
  `cod_empresa_suscripcion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `correo` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `origen` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_empresa_suscripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa_suscripcion
- cod_empresa

---

# tb_empresa_tarjeta

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_tarjeta | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| token | varchar(30) | YES |  |  |
| type | varchar(5) | YES |  |  |
| status | varchar(10) | YES |  |  |
| number | varchar(5) | YES |  |  |
| reference | varchar(10) | YES |  |  |
| expiry_month | int(11) | YES |  |  |
| expiry_year | int(11) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresa_tarjeta` (
  `cod_empresa_tarjeta` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `token` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `number` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reference` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `expiry_month` int(11) DEFAULT NULL,
  `expiry_year` int(11) DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_empresa_tarjeta`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa

---

# tb_empresas

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa | int(11) | NO | PRI | auto_increment |
| cod_tipo_empresa | int(11) | NO |  |  |
| cod_plan | int(11) | NO |  |  |
| ruc | varchar(50) | YES |  |  |
| nombre | varchar(150) | YES |  |  |
| alias | varchar(150) | NO |  |  |
| folder | varchar(60) | NO |  |  |
| direccion | varchar(300) | YES |  |  |
| telefono | varchar(100) | YES |  |  |
| correo | varchar(100) | YES |  |  |
| envio_grava_iva | int(11) | NO |  |  |
| representante_nombre | varchar(150) | YES |  |  |
| representante_documento | varchar(50) | YES |  |  |
| representante_celular | varchar(50) | YES |  |  |
| representante_correo | varchar(100) | YES |  |  |
| fecha_registro | date | YES |  |  |
| fecha_caducidad | date | YES |  |  |
| mensualidad | float | YES |  |  |
| logo | varchar(150) | YES |  |  |
| logo_min | varchar(150) | NO |  |  |
| color | varchar(10) | NO |  |  |
| url_web | varchar(150) | YES |  |  |
| url_android | varchar(150) | YES |  |  |
| url_ios | varchar(150) | YES |  |  |
| facebook_pixel | varchar(20) | NO |  |  |
| facebook_pixel_verify | varchar(250) | YES |  |  |
| api_key | varchar(100) | YES |  |  |
| timezone | varchar(100) | NO |  |  |
| paginacion | int(11) | NO |  |  |
| impuesto | double | NO |  |  |
| service_percentage | int(11) | NO |  |  |
| currency | varchar(5) | NO |  |  |
| datetime_format | varchar(15) | NO |  |  |
| cod_consumidor_final | int(11) | NO |  |  |
| programar_pedido | int(11) | NO |  |  |
| cant_dias_programar_pedido | int(11) | NO |  |  |
| fidelizacion | int(11) | NO |  |  |
| estado | enum('A','I','D') | YES |  |  |
| user_create | int(11) | NO |  |  |
| description | text | NO |  |  |
| promo_text | varchar(250) | NO |  |  |
| keywords | text | NO |  |  |
| front_header | enum('RESTAURANTE','RETAIL') | NO |  |  |
| front_menu | text | NO |  |  |
| iniciar_en_menu | int(11) | NO |  |  |
| ambiente | enum('development','production') | NO |  |  |
| hosting | varchar(30) | NO |  |  |
| recordar_ordenes | int(11) | NO |  |  |
| recordar_ordenes_tiempo | int(11) | NO |  |  |
| tipo_recorte | enum('square','rectangle') | NO |  |  |
| is_emprendedor | int(11) | NO |  |  |
| stockExterno | int(11) | NO |  |  |
| is_delivery | int(11) | YES |  |  |
| is_pickup | int(11) | YES |  |  |
| is_insitu | int(11) | YES |  |  |
| front_product_card | varchar(15) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresas` (
  `cod_empresa` int(11) NOT NULL AUTO_INCREMENT,
  `cod_tipo_empresa` int(11) NOT NULL,
  `cod_plan` int(11) NOT NULL,
  `ruc` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nombre` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `alias` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `folder` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `direccion` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefono` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `envio_grava_iva` int(11) NOT NULL DEFAULT '0',
  `representante_nombre` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `representante_documento` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `representante_celular` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `representante_correo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_registro` date DEFAULT NULL,
  `fecha_caducidad` date DEFAULT NULL,
  `mensualidad` float DEFAULT '0',
  `logo` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logo_min` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `color` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `url_web` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_android` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_ios` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `facebook_pixel` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `facebook_pixel_verify` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `api_key` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `timezone` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `paginacion` int(11) NOT NULL,
  `impuesto` double NOT NULL DEFAULT '15',
  `service_percentage` int(11) NOT NULL DEFAULT '0',
  `currency` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `datetime_format` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `cod_consumidor_final` int(11) NOT NULL,
  `programar_pedido` int(11) NOT NULL DEFAULT '0',
  `cant_dias_programar_pedido` int(11) NOT NULL DEFAULT '5',
  `fidelizacion` int(11) NOT NULL DEFAULT '0',
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_create` int(11) NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `promo_text` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `keywords` text COLLATE utf8_unicode_ci NOT NULL,
  `front_header` enum('RESTAURANTE','RETAIL') COLLATE utf8_unicode_ci NOT NULL,
  `front_menu` text COLLATE utf8_unicode_ci NOT NULL,
  `iniciar_en_menu` int(11) NOT NULL DEFAULT '0',
  `ambiente` enum('development','production') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'development',
  `hosting` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `recordar_ordenes` int(11) NOT NULL DEFAULT '0',
  `recordar_ordenes_tiempo` int(11) NOT NULL DEFAULT '30' COMMENT 'en minutos',
  `tipo_recorte` enum('square','rectangle') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'square',
  `is_emprendedor` int(11) NOT NULL DEFAULT '0',
  `stockExterno` int(11) NOT NULL DEFAULT '0',
  `is_delivery` int(11) DEFAULT '1',
  `is_pickup` int(11) DEFAULT '1',
  `is_insitu` int(11) DEFAULT '0',
  `front_product_card` varchar(15) COLLATE utf8_unicode_ci DEFAULT 'V2',
  PRIMARY KEY (`cod_empresa`)
) ENGINE=MyISAM AUTO_INCREMENT=229 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa
- cod_tipo_empresa
- cod_plan
- cod_consumidor_final

---

# tb_empresas_suscripciones

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_suscripcion | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| fecha | datetime | YES |  |  |
| origen | varchar(30) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresas_suscripciones` (
  `cod_empresa_suscripcion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `origen` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_empresa_suscripcion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa_suscripcion
- cod_empresa

---

# tb_empresas_versiones_app

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_empresa_version | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| name | varchar(20) | YES |  |  |
| code | int(11) | YES |  |  |
| texto | varchar(100) | YES |  |  |
| obligatorio | int(11) | YES |  |  |
| aplicacion | varchar(20) | YES |  |  |
| fecha_modificacion | datetime | YES |  |  |
| descripcion | text | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_empresas_versiones_app` (
  `cod_empresa_version` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `name` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `code` int(11) DEFAULT NULL,
  `texto` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `obligatorio` int(11) DEFAULT NULL,
  `aplicacion` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `descripcion` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`cod_empresa_version`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa_version
- cod_empresa

---

# tb_estado_ordenes

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_estado | varchar(15) | NO | PRI |  |
| nombre | varchar(15) | YES |  |  |
| icono | varchar(20) | NO |  |  |
| posicion | int(11) | YES |  |  |
| is_envio | int(11) | NO |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_estado_ordenes` (
  `cod_estado` varchar(15) NOT NULL,
  `nombre` varchar(15) DEFAULT NULL,
  `icono` varchar(20) NOT NULL,
  `posicion` int(11) DEFAULT NULL,
  `is_envio` int(11) NOT NULL DEFAULT '1',
  `estado` enum('A','I','D') NOT NULL DEFAULT 'A',
  PRIMARY KEY (`cod_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_estado

---

# tb_factmovil_empresa

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_factmovil_empresa | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| username | varchar(100) | YES |  |  |
| password | varchar(100) | YES |  |  |
| ambiente | enum('development','production') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_factmovil_empresa` (
  `cod_factmovil_empresa` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `username` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ambiente` enum('development','production') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_factmovil_empresa`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa

---

# tb_factura_cabecera

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_factura | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | YES |  |  |
| tipo_documento | varchar(3) | YES |  |  |
| emisor | varchar(3) | YES |  |  |
| ptoemision | varchar(3) | YES |  |  |
| secuencial | varchar(10) | YES |  |  |
| num_factura | varchar(20) | YES |  |  |
| num_autorizacion | varchar(30) | YES |  |  |
| cod_cliente | int(11) | YES |  |  |
| cod_pto_emision | int(11) | YES |  |  |
| fecha | date | YES |  |  |
| cod_gift_card | int(11) | YES |  |  |
| subtotal | float | YES |  |  |
| descuento | float | YES |  |  |
| descuento_porcentaje | float | YES |  |  |
| impuesto_0 | float | YES |  |  |
| impuesto_12 | float | YES |  |  |
| total | float | YES |  |  |
| num_items | int(11) | YES |  |  |
| user_create | int(11) | YES |  |  |
| fecha_create | datetime | YES |  |  |
| estado | enum('ENVIADA','NO ENVIADA','ANULADA') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_factura_cabecera` (
  `cod_factura` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) DEFAULT NULL,
  `tipo_documento` varchar(3) DEFAULT NULL,
  `emisor` varchar(3) DEFAULT NULL,
  `ptoemision` varchar(3) DEFAULT NULL,
  `secuencial` varchar(10) DEFAULT NULL,
  `num_factura` varchar(20) DEFAULT NULL,
  `num_autorizacion` varchar(30) DEFAULT NULL,
  `cod_cliente` int(11) DEFAULT NULL,
  `cod_pto_emision` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `cod_gift_card` int(11) DEFAULT NULL,
  `subtotal` float DEFAULT NULL,
  `descuento` float DEFAULT NULL,
  `descuento_porcentaje` float DEFAULT NULL,
  `impuesto_0` float DEFAULT NULL,
  `impuesto_12` float DEFAULT NULL,
  `total` float DEFAULT NULL,
  `num_items` int(11) DEFAULT NULL,
  `user_create` int(11) DEFAULT NULL,
  `fecha_create` datetime DEFAULT NULL,
  `estado` enum('ENVIADA','NO ENVIADA','ANULADA') DEFAULT NULL,
  PRIMARY KEY (`cod_factura`)
) ENGINE=InnoDB AUTO_INCREMENT=157 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_factura
- cod_sucursal
- cod_cliente
- cod_pto_emision
- cod_gift_card

---

# tb_factura_detalle

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_detalle | int(11) | NO | PRI | auto_increment |
| cod_factura | int(11) | YES |  |  |
| cod_producto | int(11) | YES |  |  |
| costo_producto | float | NO |  |  |
| costo_total_producto | float | NO |  |  |
| nombre_producto | varchar(150) | YES |  |  |
| precio | float | YES |  |  |
| cantidad | int(11) | YES |  |  |
| precio_total | float | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_factura_detalle` (
  `cod_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `cod_factura` int(11) DEFAULT NULL,
  `cod_producto` int(11) DEFAULT NULL,
  `costo_producto` float NOT NULL,
  `costo_total_producto` float NOT NULL,
  `nombre_producto` varchar(150) DEFAULT NULL,
  `precio` float DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio_total` float DEFAULT NULL,
  PRIMARY KEY (`cod_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=208 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_detalle
- cod_factura
- cod_producto

---

# tb_factura_pagos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_pagos | int(11) | NO | PRI | auto_increment |
| cod_factura | int(11) | YES |  |  |
| cod_tipo_pago | int(11) | YES |  |  |
| valor_pagado | float | YES |  |  |
| valor_cambio | float | YES |  |  |
| fecha | date | NO |  |  |
| detalle | varchar(150) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_factura_pagos` (
  `cod_pagos` int(11) NOT NULL AUTO_INCREMENT,
  `cod_factura` int(11) DEFAULT NULL,
  `cod_tipo_pago` int(11) DEFAULT NULL,
  `valor_pagado` float DEFAULT NULL,
  `valor_cambio` float DEFAULT NULL,
  `fecha` date NOT NULL,
  `detalle` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`cod_pagos`)
) ENGINE=InnoDB AUTO_INCREMENT=187 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_pagos
- cod_factura
- cod_tipo_pago

---

# tb_faqs

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_faq | int(11) | NO | PRI | auto_increment |
| cod_tipo_empresa | int(11) | NO |  |  |
| titulo | varchar(100) | YES |  |  |
| desc_corta | varchar(100) | YES |  |  |
| desc_larga | text | YES |  |  |
| posicion | int(11) | NO |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_faqs` (
  `cod_faq` int(11) NOT NULL AUTO_INCREMENT,
  `cod_tipo_empresa` int(11) NOT NULL,
  `titulo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `desc_corta` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `desc_larga` text COLLATE utf8_unicode_ci,
  `posicion` int(11) NOT NULL DEFAULT '99',
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_faq`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_faq
- cod_tipo_empresa

---

# tb_formas_pago

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_forma_pago | varchar(3) | NO | PRI |  |
| descripcion | varchar(50) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_formas_pago` (
  `cod_forma_pago` varchar(3) NOT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `estado` enum('A','I','D') DEFAULT NULL,
  PRIMARY KEY (`cod_forma_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
```

## Posibles relaciones

- cod_forma_pago

---

# tb_formas_pago_facturacion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_forma_pago_facturacion | int(11) | NO | PRI | auto_increment |
| cod_forma_pago | varchar(3) | YES |  |  |
| id | varchar(20) | YES |  |  |
| name_in_contifico | varchar(100) | YES |  |  |
| cod_sistema_facturacion | int(11) | YES |  |  |
| cod_contifico_empresa | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_formas_pago_facturacion` (
  `cod_forma_pago_facturacion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_forma_pago` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name_in_contifico` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cod_sistema_facturacion` int(11) DEFAULT NULL,
  `cod_contifico_empresa` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_forma_pago_facturacion`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_forma_pago_facturacion
- cod_forma_pago
- cod_sistema_facturacion
- cod_contifico_empresa

---

# tb_front_pagina_detalle

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_front_pagina_detalle | int(11) | NO | PRI | auto_increment |
| cod_front_pagina | int(11) | YES |  |  |
| cod_tipo | varchar(50) | YES |  |  |
| titulo | varchar(100) | YES |  |  |
| forma | varchar(100) | YES |  |  |
| num_columnas | int(11) | YES |  |  |
| md | int(11) | NO |  |  |
| sm | int(11) | NO |  |  |
| detalle | text | YES |  |  |
| detalle2 | text | YES |  |  |
| cod_detalle | int(11) | YES |  |  |
| posicion | int(11) | YES |  |  |
| html | text | YES |  |  |
| extra_params | text | NO |  |  |
| fecha | date | YES |  |  |
| classname | varchar(150) | NO |  |  |
| showTitle | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_front_pagina_detalle` (
  `cod_front_pagina_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `cod_front_pagina` int(11) DEFAULT NULL,
  `cod_tipo` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `titulo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `forma` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `num_columnas` int(11) DEFAULT NULL,
  `md` int(11) NOT NULL DEFAULT '1',
  `sm` int(11) NOT NULL DEFAULT '1',
  `detalle` text COLLATE utf8_unicode_ci,
  `detalle2` text COLLATE utf8_unicode_ci,
  `cod_detalle` int(11) DEFAULT NULL,
  `posicion` int(11) DEFAULT '99',
  `html` text COLLATE utf8_unicode_ci,
  `extra_params` text COLLATE utf8_unicode_ci NOT NULL,
  `fecha` date DEFAULT NULL,
  `classname` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `showTitle` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cod_front_pagina_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=391 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_front_pagina
- cod_tipo
- cod_detalle

---

# tb_front_pagina_detalle_contenido

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| cod_front_pagina_detalle | int(11) | YES |  |  |
| imagen | varchar(150) | YES |  |  |
| accion_id | enum('FILTER','PRODUCTO','NOTICIA','URL','INFO','ZOOM') | YES |  |  |
| accion_desc | varchar(250) | YES |  |  |
| posicion | int(11) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_front_pagina_detalle_contenido` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_front_pagina_detalle` int(11) DEFAULT NULL,
  `imagen` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `accion_id` enum('FILTER','PRODUCTO','NOTICIA','URL','INFO','ZOOM') COLLATE utf8_unicode_ci DEFAULT 'INFO',
  `accion_desc` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1051 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_front_pagina_detalle

---

# tb_front_pagina_tipos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_front_pagina_tipo | int(11) | NO | PRI | auto_increment |
| code | varchar(50) | YES |  |  |
| nombre | varchar(50) | YES |  |  |
| posicion | int(11) | NO |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_front_pagina_tipos` (
  `cod_front_pagina_tipo` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nombre` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) NOT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_front_pagina_tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_front_pagina_tipo

---

# tb_front_paginas

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_front_pagina | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| titulo | varchar(100) | YES |  |  |
| alias | varchar(100) | YES |  |  |
| home | int(11) | NO |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_front_paginas` (
  `cod_front_pagina` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `titulo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `alias` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home` int(11) NOT NULL DEFAULT '0',
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_front_pagina`)
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_front_pagina
- cod_empresa

---

# tb_front_scripts

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | NO |  |  |
| nombre | varchar(50) | NO |  |  |
| ubicacion | enum('head','body') | NO |  |  |
| codigo | text | NO |  |  |
| estado | enum('A','I','D') | NO |  |  |
| fecha_creacion | timestamp | NO |  | on update CURRENT_TIMESTAMP |

## SQL Structure

```sql
CREATE TABLE `tb_front_scripts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) NOT NULL,
  `nombre` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `ubicacion` enum('head','body') COLLATE utf8_unicode_ci NOT NULL,
  `codigo` text COLLATE utf8_unicode_ci NOT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa

---

# tb_gacela_sucursal

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_gacela_sucursal | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| cod_sucursal | int(11) | YES |  |  |
| ambiente | enum('development','production') | YES |  |  |
| api | varchar(100) | NO |  |  |
| token | varchar(100) | YES |  |  |
| store_id | varchar(20) | NO |  |  |
| api_key | varchar(150) | NO |  |  |
| name | varchar(200) | NO |  |  |
| address | varchar(150) | NO |  |  |
| latitude | double | NO |  |  |
| longitude | double | NO |  |  |
| email | varchar(100) | NO |  |  |
| pickup_custom_field_template | varchar(100) | NO |  |  |
| custom_field_template | varchar(100) | NO |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_gacela_sucursal` (
  `cod_gacela_sucursal` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `cod_sucursal` int(11) DEFAULT NULL,
  `ambiente` enum('development','production') DEFAULT NULL,
  `api` varchar(100) NOT NULL,
  `token` varchar(100) DEFAULT NULL,
  `store_id` varchar(20) NOT NULL,
  `api_key` varchar(150) NOT NULL,
  `name` varchar(200) NOT NULL,
  `address` varchar(150) NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `email` varchar(100) NOT NULL,
  `pickup_custom_field_template` varchar(100) NOT NULL,
  `custom_field_template` varchar(100) NOT NULL,
  `estado` enum('A','I','D') NOT NULL,
  PRIMARY KEY (`cod_gacela_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4
```

## Posibles relaciones

- cod_empresa
- cod_sucursal

---

# tb_galeria

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_galeria | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| cod_categoria_galeria | int(11) | NO |  |  |
| nombre | varchar(100) | NO |  |  |
| image_min | varchar(50) | YES |  |  |
| image_max | varchar(300) | YES |  |  |
| posicion | int(11) | YES |  |  |
| isVideo | int(11) | YES |  |  |
| url_video | varchar(300) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_galeria` (
  `cod_galeria` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `cod_categoria_galeria` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `image_min` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image_max` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  `isVideo` int(11) DEFAULT NULL,
  `url_video` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_galeria`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa
- cod_categoria_galeria

---

# tb_giftcards

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_giftcard | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| nombre | varchar(100) | NO |  |  |
| imagen | varchar(100) | YES |  |  |
| montos | varchar(50) | YES |  |  |
| posicion | int(11) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_giftcards` (
  `cod_giftcard` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `imagen` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `montos` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_giftcard`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_giftcard
- cod_empresa

---

# tb_guia

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_guia | int(11) | NO | PRI | auto_increment |
| cod_pagina | int(11) | YES |  |  |
| cod_rol | int(11) | YES |  |  |
| texto | varchar(50) | YES |  |  |
| elemento_inicio | varchar(100) | YES |  |  |
| evento_inicio | varchar(50) | YES |  |  |
| modal | int(11) | NO |  |  |
| name_modal | varchar(100) | NO |  |  |
| posicion | int(11) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_guia` (
  `cod_guia` int(11) NOT NULL AUTO_INCREMENT,
  `cod_pagina` int(11) DEFAULT NULL,
  `cod_rol` int(11) DEFAULT NULL,
  `texto` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `elemento_inicio` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `evento_inicio` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modal` int(11) NOT NULL,
  `name_modal` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `posicion` int(11) DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_guia`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_pagina
- cod_rol

---

# tb_guia_pasos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_paso | int(11) | NO | PRI | auto_increment |
| cod_guia | int(11) | YES |  |  |
| texto | varchar(500) | YES |  |  |
| elemento | varchar(100) | YES |  |  |
| evento | varchar(50) | YES |  |  |
| posicion | int(11) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_guia_pasos` (
  `cod_paso` int(11) NOT NULL AUTO_INCREMENT,
  `cod_guia` int(11) DEFAULT NULL,
  `texto` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `elemento` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `evento` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_paso`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_paso
- cod_guia

---

# tb_guia_usuario

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_guia_usuario | int(11) | NO | PRI | auto_increment |
| cod_guia | int(11) | YES |  |  |
| cod_usuario | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_guia_usuario` (
  `cod_guia_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `cod_guia` int(11) DEFAULT NULL,
  `cod_usuario` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_guia_usuario`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_guia
- cod_usuario

---

# tb_idioma_frases

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_frase | int(11) | NO | PRI | auto_increment |
| cod_idioma | int(11) | YES |  |  |
| etiqueta | varchar(100) | YES |  |  |
| texto | varchar(100) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_idioma_frases` (
  `cod_frase` int(11) NOT NULL AUTO_INCREMENT,
  `cod_idioma` int(11) DEFAULT NULL,
  `etiqueta` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `texto` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_frase`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_frase
- cod_idioma

---

# tb_idiomas

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_idioma | int(11) | NO | PRI | auto_increment |
| idioma | varchar(50) | YES |  |  |
| bandera | varchar(100) | YES |  |  |
| prefijo | varchar(5) | YES |  |  |
| posicion | int(11) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_idiomas` (
  `cod_idioma` int(11) NOT NULL AUTO_INCREMENT,
  `idioma` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bandera` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `prefijo` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_idioma`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_idioma

---

# tb_importar_categorias

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_importar_categoria | int(11) | NO | PRI | auto_increment |
| cod_categoria | int(11) | YES |  |  |
| identificador | varchar(200) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_importar_categorias` (
  `cod_importar_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `cod_categoria` int(11) DEFAULT NULL,
  `identificador` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_importar_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_importar_categoria
- cod_categoria

---

# tb_importar_productos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_importar_producto | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | YES |  |  |
| identificador | varchar(200) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_importar_productos` (
  `cod_importar_producto` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) DEFAULT NULL,
  `identificador` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_importar_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_importar_producto
- cod_producto

---

# tb_indisponibilidad

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_indisponibilidad | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | NO |  |  |
| cod_usuario | int(11) | NO |  |  |
| dia | int(11) | NO |  |  |
| hora_inicio | varchar(15) | NO |  |  |
| hora_final | varchar(15) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_indisponibilidad` (
  `cod_indisponibilidad` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) NOT NULL,
  `cod_usuario` int(11) NOT NULL,
  `dia` int(11) NOT NULL,
  `hora_inicio` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `hora_final` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_indisponibilidad`)
) ENGINE=InnoDB AUTO_INCREMENT=173 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal
- cod_usuario

---

# tb_ingredientes

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_ingrediente | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | NO |  |  |
| cod_unidad_medida | varchar(15) | YES |  |  |
| ingrediente | varchar(300) | YES |  |  |
| id_contifico | varchar(150) | NO |  |  |
| precio | float | NO |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_ingredientes` (
  `cod_ingrediente` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) NOT NULL,
  `cod_unidad_medida` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ingrediente` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_contifico` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `precio` float NOT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'A',
  PRIMARY KEY (`cod_ingrediente`)
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_ingrediente
- cod_empresa
- cod_unidad_medida

---

# tb_ingredientes_facturacion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_ingrediente_facturacion | int(11) | NO | PRI | auto_increment |
| id | varchar(30) | YES |  |  |
| cod_ingrediente | int(11) | YES |  |  |
| cod_sistema_facturacion | int(11) | YES |  |  |
| name_in_contifico | varchar(100) | YES |  |  |
| cod_contifico_empresa | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_ingredientes_facturacion` (
  `cod_ingrediente_facturacion` int(11) NOT NULL AUTO_INCREMENT,
  `id` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cod_ingrediente` int(11) DEFAULT NULL,
  `cod_sistema_facturacion` int(11) DEFAULT NULL,
  `name_in_contifico` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cod_contifico_empresa` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_ingrediente_facturacion`)
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_ingrediente_facturacion
- cod_ingrediente
- cod_sistema_facturacion
- cod_contifico_empresa

---

# tb_inlog_sucursal

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_inlog_sucursal | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | YES |  |  |
| idCliente | int(11) | YES |  |  |
| token | varchar(200) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_inlog_sucursal` (
  `cod_inlog_sucursal` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) DEFAULT NULL,
  `idCliente` int(11) DEFAULT NULL,
  `token` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_inlog_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal

---

# tb_inventario

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_inventario | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | YES |  |  |
| cod_sucursal | int(11) | YES |  |  |
| cantidad | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_inventario` (
  `cod_inventario` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) DEFAULT NULL,
  `cod_sucursal` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_inventario`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto
- cod_sucursal

---

# tb_laar_sucursal

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_laar_sucursal | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | NO |  |  |
| cod_sucursal | int(11) | NO |  |  |
| username | varchar(30) | NO |  |  |
| password | varchar(30) | NO |  |  |
| token | varchar(2000) | NO |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_laar_sucursal` (
  `cod_laar_sucursal` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) NOT NULL,
  `cod_sucursal` int(11) NOT NULL,
  `username` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_laar_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa
- cod_sucursal

---

# tb_lista_deseos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_lista_deseos | int(11) | NO | PRI | auto_increment |
| cod_libro | int(11) | YES |  |  |
| cod_usuario | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_lista_deseos` (
  `cod_lista_deseos` int(11) NOT NULL AUTO_INCREMENT,
  `cod_libro` int(11) DEFAULT NULL,
  `cod_usuario` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_lista_deseos`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_libro
- cod_usuario

---

# tb_marketing_envios

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_marketing_envio | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | NO |  |  |
| porcentaje | int(11) | YES |  |  |
| monto | float | YES |  |  |
| fecha_inicio | datetime | YES |  |  |
| fecha_fin | datetime | YES |  |  |
| cod_sucursal | int(11) | YES |  |  |
| solo_horario | int(11) | NO |  |  |
| dias | varchar(20) | NO |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_marketing_envios` (
  `cod_marketing_envio` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) NOT NULL,
  `porcentaje` int(11) DEFAULT NULL,
  `monto` float DEFAULT NULL,
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `cod_sucursal` int(11) DEFAULT NULL,
  `solo_horario` int(11) NOT NULL DEFAULT '0' COMMENT 'Para que respete la promo solo dentro del horario',
  `dias` varchar(20) NOT NULL COMMENT 'lun=1, dom=7',
  `estado` enum('A','I','D') NOT NULL,
  PRIMARY KEY (`cod_marketing_envio`)
) ENGINE=InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_marketing_envio
- cod_empresa
- cod_sucursal

---

# tb_menu_digital

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_menu_digital | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| titulo | varchar(50) | NO |  |  |
| alias | varchar(100) | YES |  |  |
| url | varchar(200) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_menu_digital` (
  `cod_menu_digital` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `titulo` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `alias` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_menu_digital`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa

---

# tb_menu_digital_imagenes

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_menu_digital_imagenes | int(11) | NO | PRI | auto_increment |
| cod_menu_digital | int(11) | YES |  |  |
| imagen | varchar(100) | YES |  |  |
| posicion | int(11) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_menu_digital_imagenes` (
  `cod_menu_digital_imagenes` int(11) NOT NULL AUTO_INCREMENT,
  `cod_menu_digital` int(11) DEFAULT NULL,
  `imagen` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_menu_digital_imagenes`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_menu_digital

---

# tb_metodos_pago

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_metodo_pago | int(11) | NO | PRI | auto_increment |
| nombre | varchar(150) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |
| cod_empresa | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_metodos_pago` (
  `cod_metodo_pago` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  `cod_empresa` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_metodo_pago`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_metodo_pago
- cod_empresa

---

# tb_modal_eventos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_modal_evento | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| titulo | varchar(100) | YES |  |  |
| descripcion | varchar(150) | YES |  |  |
| imagen | varchar(50) | YES |  |  |
| accion_id | enum('FILTER','PRODUCTO','NOTICIA','URL','ZOOM','INFO') | NO |  |  |
| accion_desc | varchar(300) | NO |  |  |
| fecha_inicio | datetime | YES |  |  |
| fecha_fin | datetime | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_modal_eventos` (
  `cod_modal_evento` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `titulo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `descripcion` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imagen` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `accion_id` enum('FILTER','PRODUCTO','NOTICIA','URL','ZOOM','INFO') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'INFO',
  `accion_desc` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_modal_evento`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_modal_evento
- cod_empresa

---

# tb_motorizado_asignacion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_motorizado_asignacion | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | YES |  |  |
| cod_motorizado | int(11) | YES |  |  |
| fecha_asignacion | datetime | YES |  |  |
| fecha_salida | datetime | YES |  |  |
| fecha_llegada | datetime | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_motorizado_asignacion` (
  `cod_motorizado_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) DEFAULT NULL,
  `cod_motorizado` int(11) DEFAULT NULL,
  `fecha_asignacion` datetime DEFAULT NULL,
  `fecha_salida` datetime DEFAULT NULL,
  `fecha_llegada` datetime DEFAULT NULL,
  PRIMARY KEY (`cod_motorizado_asignacion`)
) ENGINE=InnoDB AUTO_INCREMENT=31981 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_orden
- cod_motorizado

---

# tb_motorizado_link

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_motorizado_link | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | YES |  |  |
| token | varchar(50) | YES |  |  |
| fecha_creacion | datetime | YES |  |  |
| fecha_expiracion | datetime | YES |  |  |
| aceptada | int(11) | YES |  |  |
| phone | varchar(20) | NO |  |  |
| email | varchar(50) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_motorizado_link` (
  `cod_motorizado_link` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) DEFAULT NULL,
  `token` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT NULL,
  `fecha_expiracion` datetime DEFAULT NULL,
  `aceptada` int(11) DEFAULT '0',
  `phone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_motorizado_link`)
) ENGINE=InnoDB AUTO_INCREMENT=23629 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_orden

---

# tb_motorizado_seguimiento

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_motorizado_seguimiento | int(11) | NO | PRI | auto_increment |
| cod_motorizado | int(11) | YES |  |  |
| latitud | varchar(15) | YES |  |  |
| longitud | varchar(15) | YES |  |  |
| fecha | datetime | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_motorizado_seguimiento` (
  `cod_motorizado_seguimiento` int(11) NOT NULL AUTO_INCREMENT,
  `cod_motorizado` int(11) DEFAULT NULL,
  `latitud` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `longitud` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`cod_motorizado_seguimiento`)
) ENGINE=InnoDB AUTO_INCREMENT=284485 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_motorizado

---

# tb_niveles

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_nivel | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| nombre | varchar(60) | YES |  |  |
| imagen | varchar(30) | NO |  |  |
| punto_inicial | int(11) | YES |  |  |
| punto_final | int(11) | YES |  |  |
| dinero_x_punto | float | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |
| posicion | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_niveles` (
  `cod_nivel` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `nombre` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imagen` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `punto_inicial` int(11) DEFAULT NULL,
  `punto_final` int(11) DEFAULT NULL,
  `dinero_x_punto` float DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) NOT NULL,
  PRIMARY KEY (`cod_nivel`)
) ENGINE=MyISAM AUTO_INCREMENT=109 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_nivel
- cod_empresa

---

# tb_noticias

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_noticia | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| alias | varchar(300) | YES |  |  |
| titulo | varchar(150) | YES | MUL |  |
| desc_corta | text | YES |  |  |
| desc_larga | text | YES |  |  |
| image_min | varchar(100) | YES |  |  |
| imagen_max | varchar(100) | YES |  |  |
| fecha_create | datetime | YES |  |  |
| fecha_modificacion | datetime | YES |  |  |
| posicion | int(11) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_noticias` (
  `cod_noticia` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `alias` varchar(300) DEFAULT NULL,
  `titulo` varchar(150) DEFAULT NULL,
  `desc_corta` text,
  `desc_larga` text,
  `image_min` varchar(100) DEFAULT NULL,
  `imagen_max` varchar(100) DEFAULT NULL,
  `fecha_create` datetime DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  `estado` enum('A','I','D') DEFAULT NULL,
  PRIMARY KEY (`cod_noticia`),
  FULLTEXT KEY `titulo` (`titulo`,`desc_corta`,`desc_larga`)
) ENGINE=InnoDB AUTO_INCREMENT=215 DEFAULT CHARSET=utf8mb4
```

## Posibles relaciones

- cod_noticia
- cod_empresa

---

# tb_noticias_categoria

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_noticias_categoria | int(11) | NO | PRI | auto_increment |
| cod_noticia | int(11) | YES |  |  |
| cod_categoria | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_noticias_categoria` (
  `cod_noticias_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `cod_noticia` int(11) DEFAULT NULL,
  `cod_categoria` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_noticias_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=1008 DEFAULT CHARSET=utf8mb4
```

## Posibles relaciones

- cod_noticia
- cod_categoria

---

# tb_noticias_imagenes

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_imagen | int(11) | NO | PRI | auto_increment |
| cod_noticia | int(11) | YES |  |  |
| nombre_img | varchar(300) | YES |  |  |
| posicion | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_noticias_imagenes` (
  `cod_imagen` int(11) NOT NULL AUTO_INCREMENT,
  `cod_noticia` int(11) DEFAULT NULL,
  `nombre_img` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_imagen`)
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_imagen
- cod_noticia

---

# tb_notificaciones

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_notificacion | int(11) | NO | PRI | auto_increment |
| cod_empresa_notificacion | int(11) | YES |  |  |
| cod_usuario | int(11) | YES |  |  |
| tipo | varchar(100) | NO |  |  |
| titulo | varchar(200) | YES |  |  |
| detalle | text | YES |  |  |
| fecha | datetime | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_notificaciones` (
  `cod_notificacion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa_notificacion` int(11) DEFAULT NULL,
  `cod_usuario` int(11) DEFAULT NULL,
  `tipo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `titulo` varchar(200) CHARACTER SET utf8mb4 DEFAULT NULL,
  `detalle` text COLLATE utf8mb4_unicode_ci,
  `fecha` datetime DEFAULT NULL,
  `estado` enum('A','I','D') CHARACTER SET utf8mb4 DEFAULT NULL,
  PRIMARY KEY (`cod_notificacion`)
) ENGINE=InnoDB AUTO_INCREMENT=13893 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

## Posibles relaciones

- cod_notificacion
- cod_empresa_notificacion
- cod_usuario

---

# tb_notificaciones_tipo

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_notificacion_tipo | varchar(100) | NO | PRI |  |
| tipo | varchar(100) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |
| posicion | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_notificaciones_tipo` (
  `cod_notificacion_tipo` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `tipo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_notificacion_tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_notificacion_tipo

---

# tb_orden_cabecera

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_orden | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | NO |  |  |
| cod_usuario | int(11) | YES |  |  |
| cod_sucursal | int(11) | NO |  |  |
| fecha | datetime | YES |  |  |
| subtotal0 | decimal(10,2) | YES |  |  |
| subtotal12 | decimal(10,2) | YES |  |  |
| subtotal | decimal(10,2) | YES |  |  |
| descuento | decimal(10,2) | YES |  |  |
| envio | decimal(10,2) | YES |  |  |
| envio_iva | decimal(10,2) | YES |  |  |
| iva | decimal(10,2) | YES |  |  |
| iva_porcentaje | int(11) | NO |  |  |
| service | decimal(10,2) | YES |  |  |
| giftcard | decimal(10,2) | YES |  |  |
| total | decimal(10,2) | YES |  |  |
| cod_descuento | varchar(30) | YES |  |  |
| cod_giftcard | varchar(15) | YES |  |  |
| is_envio | int(11) | NO |  |  |
| is_programado | int(11) | YES |  |  |
| is_express | int(11) | YES |  |  |
| hora_retiro | datetime | NO |  |  |
| pago | varchar(2) | NO |  |  |
| is_suelto | int(11) | NO |  |  |
| monto_suelto | float | NO |  |  |
| latitud | varchar(20) | NO |  |  |
| longitud | varchar(20) | NO |  |  |
| distancia | varchar(20) | NO |  |  |
| nombres | varchar(70) | NO |  |  |
| cedula | varchar(15) | NO |  |  |
| correo | varchar(50) | NO |  |  |
| telefono | varchar(25) | NO |  |  |
| referencia | varchar(150) | NO |  |  |
| referencia2 | varchar(100) | NO |  |  |
| observacion | text | NO |  |  |
| datos_facturacion | text | NO |  |  |
| medio_compra | varchar(15) | NO |  |  |
| calificada | int(11) | NO |  |  |
| cod_courier | int(11) | NO |  |  |
| is_gacela | int(11) | NO |  |  |
| order_token | varchar(120) | NO |  |  |
| api_version | varchar(5) | NO |  |  |
| app_version | int(11) | NO |  |  |
| estado | varchar(25) | NO |  |  |
| is_altademanda | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_orden_cabecera` (
  `cod_orden` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) NOT NULL,
  `cod_usuario` int(11) DEFAULT NULL,
  `cod_sucursal` int(11) NOT NULL,
  `fecha` datetime DEFAULT NULL,
  `subtotal0` decimal(10,2) DEFAULT NULL,
  `subtotal12` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `descuento` decimal(10,2) DEFAULT NULL,
  `envio` decimal(10,2) DEFAULT NULL,
  `envio_iva` decimal(10,2) DEFAULT NULL,
  `iva` decimal(10,2) DEFAULT NULL,
  `iva_porcentaje` int(11) NOT NULL DEFAULT '12',
  `service` decimal(10,2) DEFAULT NULL,
  `giftcard` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `cod_descuento` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cod_giftcard` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_envio` int(11) NOT NULL DEFAULT '1',
  `is_programado` int(11) DEFAULT '0',
  `is_express` int(11) DEFAULT '0',
  `hora_retiro` datetime NOT NULL,
  `pago` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `is_suelto` int(11) NOT NULL,
  `monto_suelto` float NOT NULL,
  `latitud` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `longitud` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `distancia` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `nombres` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `cedula` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `correo` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `telefono` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `referencia` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `referencia2` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `observacion` text COLLATE utf8_unicode_ci NOT NULL,
  `datos_facturacion` text COLLATE utf8_unicode_ci NOT NULL,
  `medio_compra` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `calificada` int(11) NOT NULL DEFAULT '0',
  `cod_courier` int(11) NOT NULL,
  `is_gacela` int(11) NOT NULL DEFAULT '0',
  `order_token` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `api_version` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'v3',
  `app_version` int(11) NOT NULL,
  `estado` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `is_altademanda` int(11) DEFAULT '0',
  PRIMARY KEY (`cod_orden`)
) ENGINE=InnoDB AUTO_INCREMENT=176457 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_orden
- cod_empresa
- cod_usuario
- cod_sucursal
- cod_descuento
- cod_giftcard
- cod_courier

---

# tb_orden_calificacion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_orden_calificacion | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | YES |  |  |
| calificacion | int(11) | YES |  |  |
| texto | varchar(300) | NO |  |  |
| fecha | timestamp | NO |  | on update CURRENT_TIMESTAMP |

## SQL Structure

```sql
CREATE TABLE `tb_orden_calificacion` (
  `cod_orden_calificacion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) DEFAULT NULL,
  `calificacion` int(11) DEFAULT NULL,
  `texto` varchar(300) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cod_orden_calificacion`)
) ENGINE=InnoDB AUTO_INCREMENT=3036 DEFAULT CHARSET=utf8mb4
```

## Posibles relaciones

- cod_orden

---

# tb_orden_cancelacion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_orden_cancelacion | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | YES |  |  |
| motivo | varchar(300) | YES |  |  |
| fecha_create | datetime | YES |  |  |
| user_create | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_orden_cancelacion` (
  `cod_orden_cancelacion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) DEFAULT NULL,
  `motivo` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_create` datetime DEFAULT NULL,
  `user_create` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_orden_cancelacion`)
) ENGINE=InnoDB AUTO_INCREMENT=4596 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_orden

---

# tb_orden_courier_canceled

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_orden_courier_canceled | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | YES |  |  |
| cod_courier | int(11) | YES |  |  |
| orden_token | varchar(20) | YES |  |  |
| motivo | varchar(100) | YES |  |  |
| fecha | date | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_orden_courier_canceled` (
  `cod_orden_courier_canceled` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) DEFAULT NULL,
  `cod_courier` int(11) DEFAULT NULL,
  `orden_token` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `motivo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  PRIMARY KEY (`cod_orden_courier_canceled`)
) ENGINE=InnoDB AUTO_INCREMENT=233 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_orden
- cod_courier

---

# tb_orden_cuponera

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | YES |  |  |
| codigo | varchar(20) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_orden_cuponera` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) DEFAULT NULL,
  `codigo` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_orden

---

# tb_orden_datos_facturacion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_orden_dato_facturacion | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | YES |  |  |
| nombre | varchar(200) | YES |  |  |
| num_documento | varchar(50) | YES |  |  |
| direccion | varchar(250) | YES |  |  |
| telefono | varchar(50) | YES |  |  |
| correo | varchar(150) | YES |  |  |
| is_extranjero | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_orden_datos_facturacion` (
  `cod_orden_dato_facturacion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) DEFAULT NULL,
  `nombre` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `num_documento` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `direccion` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefono` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correo` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_extranjero` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cod_orden_dato_facturacion`)
) ENGINE=InnoDB AUTO_INCREMENT=13398 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_orden_dato_facturacion
- cod_orden

---

# tb_orden_destino

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_orden_destino | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | NO |  |  |
| num_casa | int(11) | NO |  |  |
| cod_postal | int(11) | NO |  |  |
| cod_ciudad | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_orden_destino` (
  `cod_orden_destino` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) NOT NULL,
  `num_casa` int(11) NOT NULL,
  `cod_postal` int(11) NOT NULL,
  `cod_ciudad` int(11) NOT NULL,
  PRIMARY KEY (`cod_orden_destino`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_orden
- cod_postal
- cod_ciudad

---

# tb_orden_detalle

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_orden_detalle | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | YES |  |  |
| cod_producto | int(11) | YES |  |  |
| descripcion | text | NO |  |  |
| comentarios | varchar(200) | NO |  |  |
| precio | float | YES |  |  |
| precio_no_tax | float | NO |  |  |
| descuento | float | NO |  |  |
| descuento_porcentaje | float | NO |  |  |
| desc_text | varchar(8) | NO |  |  |
| cantidad | int(11) | YES |  |  |
| base_0 | float | NO |  |  |
| base_12 | float | NO |  |  |
| subtotal_0 | float | NO |  |  |
| subtotal_12 | float | NO |  |  |
| precio_final | float | YES |  |  |
| adicional_total | float | NO |  |  |
| adicional_no_tax_unidad | float | NO |  |  |
| adicional_no_tax_total | float | NO |  |  |
| cod_promocion | int(11) | YES |  |  |
| es_regalo | tinyint(1) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_orden_detalle` (
  `cod_orden_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) DEFAULT NULL,
  `cod_producto` int(11) DEFAULT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `comentarios` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio` float DEFAULT NULL,
  `precio_no_tax` float NOT NULL,
  `descuento` float NOT NULL,
  `descuento_porcentaje` float NOT NULL,
  `desc_text` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `base_0` float NOT NULL,
  `base_12` float NOT NULL,
  `subtotal_0` float NOT NULL,
  `subtotal_12` float NOT NULL,
  `precio_final` float DEFAULT NULL,
  `adicional_total` float NOT NULL,
  `adicional_no_tax_unidad` float NOT NULL,
  `adicional_no_tax_total` float NOT NULL,
  `cod_promocion` int(11) DEFAULT NULL COMMENT 'Promoción que generó el descuento en este item',
  `es_regalo` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = producto gratis por promoción avanzada',
  PRIMARY KEY (`cod_orden_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=219150 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

## Posibles relaciones

- cod_orden
- cod_producto
- cod_promocion

---

# tb_orden_devolucion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | varchar(50) | YES |  |  |
| fecha | datetime | YES |  |  |
| estado | varchar(25) | YES |  |  |
| respuesta | text | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_orden_devolucion` (
  `id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `estado` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `respuesta` text COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

---

# tb_orden_errores

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_orden_errores | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | YES |  |  |
| tipo | enum('FACTURA','COURIER') | YES |  |  |
| proveedor | varchar(30) | YES |  |  |
| motivo | text | YES |  |  |
| fecha | datetime | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_orden_errores` (
  `cod_orden_errores` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) DEFAULT NULL,
  `tipo` enum('FACTURA','COURIER') COLLATE utf8_unicode_ci DEFAULT NULL,
  `proveedor` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `motivo` text COLLATE utf8_unicode_ci,
  `fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`cod_orden_errores`)
) ENGINE=InnoDB AUTO_INCREMENT=1118 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_orden

---

# tb_orden_evento

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | YES |  |  |
| cod_orden_detalle | int(11) | NO |  |  |
| dia | date | YES |  |  |
| estado | enum('EJECUTAR','EJECUTADO') | NO |  |  |
| cod_producto | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_orden_evento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) DEFAULT NULL,
  `cod_orden_detalle` int(11) NOT NULL DEFAULT '0',
  `dia` date DEFAULT NULL,
  `estado` enum('EJECUTAR','EJECUTADO') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'EJECUTAR',
  `cod_producto` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_orden
- cod_orden_detalle
- cod_producto

---

# tb_orden_factura_electronica

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_orden_factura_electronica | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | YES |  |  |
| tipo | enum('FAC','DNA') | NO |  |  |
| num_factura | varchar(20) | YES |  |  |
| clave_acceso | varchar(100) | YES |  |  |
| estado | varchar(50) | YES |  |  |
| cod_sistema_facturacion | int(11) | NO |  |  |
| cod_contifico_empresa | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_orden_factura_electronica` (
  `cod_orden_factura_electronica` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) DEFAULT NULL,
  `tipo` enum('FAC','DNA') COLLATE utf8_unicode_ci NOT NULL,
  `num_factura` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `clave_acceso` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cod_sistema_facturacion` int(11) NOT NULL,
  `cod_contifico_empresa` int(11) NOT NULL,
  PRIMARY KEY (`cod_orden_factura_electronica`)
) ENGINE=InnoDB AUTO_INCREMENT=713 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_orden
- cod_sistema_facturacion
- cod_contifico_empresa

---

# tb_orden_historial

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_orden_historial | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | NO |  |  |
| estado | varchar(50) | NO |  |  |
| fecha | datetime | NO |  |  |
| observacion | text | NO |  |  |
| order_token | varchar(150) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_orden_historial` (
  `cod_orden_historial` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) NOT NULL,
  `estado` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `fecha` datetime NOT NULL,
  `observacion` text COLLATE utf8_unicode_ci NOT NULL,
  `order_token` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_orden_historial`)
) ENGINE=InnoDB AUTO_INCREMENT=440022 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_orden

---

# tb_orden_inventario

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_orden_inventario | int(11) | NO | PRI | auto_increment |
| cod_contifico_empresa | int(11) | YES |  |  |
| cod_orden | int(11) | YES |  |  |
| tipo | enum('EGR','ING','TRA','AJU') | NO |  |  |
| codigo | varchar(150) | NO |  |  |
| id | varchar(100) | NO |  |  |
| fecha | datetime | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_orden_inventario` (
  `cod_orden_inventario` int(11) NOT NULL AUTO_INCREMENT,
  `cod_contifico_empresa` int(11) DEFAULT NULL,
  `cod_orden` int(11) DEFAULT NULL,
  `tipo` enum('EGR','ING','TRA','AJU') COLLATE utf8_unicode_ci NOT NULL,
  `codigo` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`cod_orden_inventario`)
) ENGINE=InnoDB AUTO_INCREMENT=789 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_contifico_empresa
- cod_orden

---

# tb_orden_json_entrante

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_orden_json_entrante | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | NO |  |  |
| json | text | YES |  |  |
| fecha_create | datetime | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_orden_json_entrante` (
  `cod_orden_json_entrante` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) NOT NULL,
  `json` text COLLATE utf8_unicode_ci,
  `fecha_create` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cod_orden_json_entrante`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_orden

---

# tb_orden_motorizado

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_orden_motorizado | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | NO |  |  |
| nombre | varchar(100) | NO |  |  |
| apellido | varchar(100) | NO |  |  |
| num_documento | varchar(15) | NO |  |  |
| placa | varchar(50) | NO |  |  |
| foto | varchar(150) | NO |  |  |
| telefono | varchar(20) | NO |  |  |
| proceso | varchar(150) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_orden_motorizado` (
  `cod_orden_motorizado` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `num_documento` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `placa` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `foto` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `proceso` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_orden_motorizado`)
) ENGINE=InnoDB AUTO_INCREMENT=66741 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_orden

---

# tb_orden_pagos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_orden_pagos | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | YES |  |  |
| forma_pago | varchar(3) | YES |  |  |
| monto | float | YES |  |  |
| observacion | varchar(50) | NO |  |  |
| observacion2 | varchar(30) | NO |  |  |
| cod_proveedor_botonpagos | int(11) | NO |  |  |
| lote | varchar(10) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_orden_pagos` (
  `cod_orden_pagos` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) DEFAULT NULL,
  `forma_pago` varchar(3) DEFAULT NULL,
  `monto` float DEFAULT NULL,
  `observacion` varchar(50) NOT NULL,
  `observacion2` varchar(30) NOT NULL,
  `cod_proveedor_botonpagos` int(11) NOT NULL DEFAULT '2',
  `lote` varchar(10) NOT NULL,
  PRIMARY KEY (`cod_orden_pagos`)
) ENGINE=InnoDB AUTO_INCREMENT=185183 DEFAULT CHARSET=utf8mb4
```

## Posibles relaciones

- cod_orden
- cod_proveedor_botonpagos

---

# tb_orden_puntos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_orden_puntos | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | YES |  |  |
| estado | int(11) | YES |  |  |
| fecha | timestamp | NO |  | on update CURRENT_TIMESTAMP |

## SQL Structure

```sql
CREATE TABLE `tb_orden_puntos` (
  `cod_orden_puntos` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) DEFAULT NULL,
  `estado` int(11) DEFAULT '0',
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cod_orden_puntos`)
) ENGINE=InnoDB AUTO_INCREMENT=117865 DEFAULT CHARSET=utf8mb4
```

## Posibles relaciones

- cod_orden

---

# tb_orden_recipientes

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_orden_recipiente | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | YES |  |  |
| cod_recipiente | int(11) | YES |  |  |
| cantidad | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_orden_recipientes` (
  `cod_orden_recipiente` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) DEFAULT NULL,
  `cod_recipiente` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_orden_recipiente`)
) ENGINE=InnoDB AUTO_INCREMENT=717 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_orden_recipiente
- cod_orden
- cod_recipiente

---

# tb_orden_runfood

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_orden_runfood | int(11) | NO | PRI | auto_increment |
| cod_orden | int(11) | YES |  |  |
| id | varchar(30) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_orden_runfood` (
  `cod_orden_runfood` int(11) NOT NULL AUTO_INCREMENT,
  `cod_orden` int(11) DEFAULT NULL,
  `id` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`cod_orden_runfood`)
) ENGINE=InnoDB AUTO_INCREMENT=66634 DEFAULT CHARSET=utf8mb4
```

## Posibles relaciones

- cod_orden

---

# tb_ordenes_flota

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| cod_flota | int(11) | YES |  |  |
| cod_orden | int(11) | YES |  |  |
| fecha_creacion | datetime | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_ordenes_flota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_flota` int(11) DEFAULT NULL,
  `cod_orden` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8559 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_flota
- cod_orden

---

# tb_pagina_rol

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_pagina_rol | int(11) | NO | PRI | auto_increment |
| cod_pagina | int(11) | YES |  |  |
| cod_rol | int(11) | YES |  |  |
| cod_empresa | int(11) | NO |  |  |
| posicion | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_pagina_rol` (
  `cod_pagina_rol` int(11) NOT NULL AUTO_INCREMENT,
  `cod_pagina` int(11) DEFAULT NULL,
  `cod_rol` int(11) DEFAULT NULL,
  `cod_empresa` int(11) NOT NULL,
  `posicion` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cod_pagina_rol`)
) ENGINE=MyISAM AUTO_INCREMENT=7351 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_pagina
- cod_rol
- cod_empresa

---

# tb_paginas

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_pagina | int(11) | NO | PRI | auto_increment |
| cod_padre | int(11) | NO |  |  |
| id | varchar(100) | NO |  |  |
| icono | varchar(100) | NO |  |  |
| nombre | varchar(50) | YES |  |  |
| titulo | varchar(100) | NO |  |  |
| data_translate | varchar(150) | NO |  |  |
| posicion | int(11) | NO |  |  |
| estado | enum('A','MANTENIMIENTO','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_paginas` (
  `cod_pagina` int(11) NOT NULL AUTO_INCREMENT,
  `cod_padre` int(11) NOT NULL,
  `id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `icono` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `nombre` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `titulo` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `data_translate` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `posicion` int(11) NOT NULL,
  `estado` enum('A','MANTENIMIENTO','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_pagina`)
) ENGINE=MyISAM AUTO_INCREMENT=86 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_pagina
- cod_padre

---

# tb_paises

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_pais | char(3) | NO | PRI |  |
| nombre | char(52) | NO |  |  |
| continente | varchar(50) | NO |  |  |
| region | varchar(26) | NO |  |  |
| area | float | NO |  |  |
| independencia | smallint(6) | YES |  |  |
| poblacion | int(11) | NO |  |  |
| expectativaDeVida | float | YES |  |  |
| productoInternoBruto | float | YES |  |  |
| productoInternoBrutoAntiguo | float | YES |  |  |
| nombreLocal | varchar(45) | NO |  |  |
| gobierno | varchar(45) | NO |  |  |
| jefeDeEstado | varchar(60) | YES |  |  |
| capital | int(11) | YES |  |  |
| codigo2 | char(2) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_paises` (
  `cod_pais` char(3) NOT NULL DEFAULT '',
  `nombre` char(52) NOT NULL DEFAULT '',
  `continente` varchar(50) NOT NULL DEFAULT 'Asia',
  `region` varchar(26) NOT NULL DEFAULT '',
  `area` float NOT NULL DEFAULT '0',
  `independencia` smallint(6) DEFAULT NULL,
  `poblacion` int(11) NOT NULL DEFAULT '0',
  `expectativaDeVida` float DEFAULT NULL,
  `productoInternoBruto` float DEFAULT NULL,
  `productoInternoBrutoAntiguo` float DEFAULT NULL,
  `nombreLocal` varchar(45) NOT NULL DEFAULT '',
  `gobierno` varchar(45) NOT NULL DEFAULT '',
  `jefeDeEstado` varchar(60) DEFAULT NULL,
  `capital` int(11) DEFAULT NULL,
  `codigo2` char(2) NOT NULL DEFAULT '',
  PRIMARY KEY (`cod_pais`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_pais

---

# tb_pedidosya_sucursales

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_pedidosya_sucursal | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | NO |  |  |
| cod_sucursal | int(11) | YES |  |  |
| ambiente | enum('development','production') | YES |  |  |
| token | varchar(100) | YES |  |  |
| recolectar_dinero | int(11) | NO |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_pedidosya_sucursales` (
  `cod_pedidosya_sucursal` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) NOT NULL,
  `cod_sucursal` int(11) DEFAULT NULL,
  `ambiente` enum('development','production') COLLATE utf8_unicode_ci DEFAULT NULL,
  `token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `recolectar_dinero` int(11) NOT NULL DEFAULT '1',
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_pedidosya_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_pedidosya_sucursal
- cod_empresa
- cod_sucursal

---

# tb_permisos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_permiso | int(11) | NO | PRI | auto_increment |
| identificador | varchar(60) | YES |  |  |
| nombre | varchar(100) | YES |  |  |
| grupo | varchar(50) | NO |  |  |
| descripcion | text | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_permisos` (
  `cod_permiso` int(11) NOT NULL AUTO_INCREMENT,
  `identificador` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `grupo` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8_unicode_ci,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_permiso`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_permiso

---

# tb_permisos_empresas

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_permiso_empresa | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| identificador | varchar(60) | YES |  |  |
| habilitado | int(11) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_permisos_empresas` (
  `cod_permiso_empresa` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `identificador` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `habilitado` int(11) DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_permiso_empresa`)
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_permiso_empresa
- cod_empresa

---

# tb_personal

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_personal | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| cod_usuario | int(11) | YES |  |  |
| nombre | varchar(60) | YES |  |  |
| apellido | varchar(60) | YES |  |  |
| correo | varchar(50) | YES |  |  |
| telefono | varchar(20) | YES |  |  |
| imagen | varchar(60) | YES |  |  |
| fecha_nacimiento | date | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_personal` (
  `cod_personal` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `cod_usuario` int(11) DEFAULT NULL,
  `nombre` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `apellido` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correo` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imagen` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_personal`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa
- cod_usuario

---

# tb_picker_sucursal

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_picker_sucursal | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| cod_sucursal | int(11) | YES |  |  |
| ambiente | enum('development','production') | YES |  |  |
| api | varchar(100) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_picker_sucursal` (
  `cod_picker_sucursal` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `cod_sucursal` int(11) DEFAULT NULL,
  `ambiente` enum('development','production') COLLATE utf8_unicode_ci DEFAULT NULL,
  `api` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_picker_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa
- cod_sucursal

---

# tb_planes

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_plan | int(11) | NO | PRI | auto_increment |
| nombre | varchar(500) | NO |  |  |
| precio_mensual | float | NO |  |  |
| posicion | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_planes` (
  `cod_plan` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `precio_mensual` float NOT NULL,
  `posicion` int(11) NOT NULL,
  PRIMARY KEY (`cod_plan`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_plan

---

# tb_plantilla_correo

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_plantilla_correo | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(10) | YES |  |  |
| cod_tipo_correo | int(10) | YES |  |  |
| nombre | varchar(150) | YES |  |  |
| html | text | YES |  |  |
| asunto | varchar(300) | NO |  |  |
| correos | varchar(1500) | NO |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_plantilla_correo` (
  `cod_plantilla_correo` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(10) DEFAULT NULL,
  `cod_tipo_correo` int(10) DEFAULT NULL,
  `nombre` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `html` text COLLATE utf8_unicode_ci,
  `asunto` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `correos` varchar(1500) COLLATE utf8_unicode_ci NOT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_plantilla_correo`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa
- cod_tipo_correo

---

# tb_pos_fidelizacion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_pos_fidelizacion | int(11) | NO | PRI | auto_increment |
| user_id | int(11) | NO |  |  |
| secuencial | varchar(100) | NO |  |  |
| casher_id | int(11) | NO |  |  |
| office_id | int(11) | NO |  |  |
| token | varchar(100) | NO |  |  |
| points | int(11) | NO |  |  |
| total | int(11) | NO |  |  |
| create_at | datetime | NO |  |  |
| status | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_pos_fidelizacion` (
  `cod_pos_fidelizacion` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `secuencial` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `casher_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `token` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `points` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `create_at` datetime NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`cod_pos_fidelizacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

---

# tb_preorden_json

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_preorden | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| json | text | YES |  |  |
| fecha_create | datetime | YES |  |  |
| estado | enum('VALIDADA','CREANDO_ORDEN','PAGADA','FALLADA','CERRADA') | YES |  |  |
| status_detail | int(11) | YES |  |  |
| paymentId | varchar(60) | YES |  |  |
| paymentAuth | varchar(15) | YES |  |  |
| cod_orden | int(11) | YES |  |  |
| amount | float | YES |  |  |
| fecha_update | datetime | YES |  |  |
| motivo_fallo | varchar(40) | YES |  |  |
| webhook | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_preorden_json` (
  `cod_preorden` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `json` text COLLATE utf8mb4_unicode_ci,
  `fecha_create` datetime DEFAULT NULL,
  `estado` enum('VALIDADA','CREANDO_ORDEN','PAGADA','FALLADA','CERRADA') CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `status_detail` int(11) DEFAULT NULL,
  `paymentId` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paymentAuth` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cod_orden` int(11) DEFAULT '0',
  `amount` float DEFAULT NULL,
  `fecha_update` datetime DEFAULT NULL,
  `motivo_fallo` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `webhook` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_preorden`)
) ENGINE=InnoDB AUTO_INCREMENT=111591 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

## Posibles relaciones

- cod_preorden
- cod_usuario
- cod_orden

---

# tb_preorden_token_json

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_preorden | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| token | varchar(100) | YES |  |  |
| json | text | YES |  |  |
| fecha_create | datetime | YES |  |  |
| estado | enum('VALIDADA','CREANDO_ORDEN','PAGADA','FALLADA','CERRADA') | YES |  |  |
| paymentId | varchar(15) | YES |  |  |
| paymentAuth | varchar(15) | YES |  |  |
| cod_orden | int(11) | YES |  |  |
| amount | float | YES |  |  |
| fecha_update | datetime | YES |  |  |
| motivo_fallo | varchar(40) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_preorden_token_json` (
  `cod_preorden` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `json` text COLLATE utf8_unicode_ci,
  `fecha_create` datetime DEFAULT NULL,
  `estado` enum('VALIDADA','CREANDO_ORDEN','PAGADA','FALLADA','CERRADA') COLLATE utf8_unicode_ci DEFAULT NULL,
  `paymentId` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `paymentAuth` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cod_orden` int(11) DEFAULT '0',
  `amount` float DEFAULT NULL,
  `fecha_update` datetime DEFAULT NULL,
  `motivo_fallo` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_preorden`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_preorden
- cod_usuario
- cod_orden

---

# tb_prioridad

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_prioridad | int(11) | NO | PRI | auto_increment |
| nombre | varchar(100) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |
| posicion | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_prioridad` (
  `cod_prioridad` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_prioridad`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

---

# tb_producto_agotado_historial

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | YES |  |  |
| cod_sucursal | int(11) | YES |  |  |
| cod_usuario | int(11) | YES |  |  |
| estado | varchar(15) | NO |  |  |
| minutos | int(11) | YES |  |  |
| fecha_inicio | datetime | YES |  |  |
| fecha_fin | datetime | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_producto_agotado_historial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) DEFAULT NULL,
  `cod_sucursal` int(11) DEFAULT NULL,
  `cod_usuario` int(11) DEFAULT NULL,
  `estado` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `minutos` int(11) DEFAULT NULL,
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19171 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto
- cod_sucursal
- cod_usuario

---

# tb_producto_caracteristica

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto_caracteristica | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | YES |  |  |
| caracteristica | varchar(100) | YES |  |  |
| tipo | enum('TEXTO','COLOR') | YES |  |  |
| posicion | int(11) | YES |  |  |
| estado | enum('A','I') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_producto_caracteristica` (
  `cod_producto_caracteristica` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) DEFAULT NULL,
  `caracteristica` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tipo` enum('TEXTO','COLOR') COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  `estado` enum('A','I') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_producto_caracteristica`)
) ENGINE=InnoDB AUTO_INCREMENT=266 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto

---

# tb_producto_caracteristica_detalle

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto_caracteristica_detalle | int(11) | NO | PRI | auto_increment |
| cod_producto_caracteristica | int(11) | YES |  |  |
| detalle | varchar(100) | YES |  |  |
| detalle2 | varchar(15) | YES |  |  |
| posicion | int(11) | YES |  |  |
| estado | enum('A','I') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_producto_caracteristica_detalle` (
  `cod_producto_caracteristica_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto_caracteristica` int(11) DEFAULT NULL,
  `detalle` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `detalle2` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  `estado` enum('A','I') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_producto_caracteristica_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=763 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto_caracteristica

---

# tb_producto_descuento

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto_descuento | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | YES |  |  |
| cod_sucursal | int(11) | YES |  |  |
| fecha_inicio | datetime | YES |  |  |
| fecha_fin | datetime | YES |  |  |
| is_porcentaje | int(11) | YES |  |  |
| valor | float | YES |  |  |
| cantidad | int(11) | NO |  |  |
| texto | varchar(10) | NO |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_producto_descuento` (
  `cod_producto_descuento` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) DEFAULT NULL,
  `cod_sucursal` int(11) DEFAULT NULL,
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `is_porcentaje` int(11) DEFAULT NULL,
  `valor` float DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `texto` varchar(10) NOT NULL,
  `estado` enum('A','I','D') NOT NULL,
  PRIMARY KEY (`cod_producto_descuento`)
) ENGINE=InnoDB AUTO_INCREMENT=17373 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_producto
- cod_sucursal

---

# tb_producto_empaque_detalle

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | YES |  |  |
| unidades | int(11) | YES |  |  |
| alto | decimal(8,2) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_producto_empaque_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) DEFAULT NULL,
  `unidades` int(11) DEFAULT NULL,
  `alto` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto

---

# tb_producto_evento

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | NO |  |  |
| dias_anticipacion | int(11) | NO |  |  |
| dias_fin | int(11) | NO |  |  |
| activo | tinyint(1) | NO |  |  |
| titulo | varchar(255) | YES |  |  |
| descripcion | varchar(255) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_producto_evento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) NOT NULL,
  `dias_anticipacion` int(11) NOT NULL DEFAULT '1',
  `dias_fin` int(11) NOT NULL DEFAULT '365',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `titulo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=211 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto

---

# tb_producto_extras

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto_extra | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | YES |  |  |
| titulo | varchar(150) | YES |  |  |
| cantidad | int(11) | YES |  |  |
| costo_adicional | float | YES |  |  |
| posicion | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_producto_extras` (
  `cod_producto_extra` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) DEFAULT NULL,
  `titulo` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `costo_adicional` float DEFAULT NULL,
  `posicion` int(11) NOT NULL,
  PRIMARY KEY (`cod_producto_extra`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto_extra
- cod_producto

---

# tb_producto_extras_detalle

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto_extra_detalle | int(11) | NO | PRI | auto_increment |
| cod_producto_extra | int(11) | YES |  |  |
| cod_producto | int(11) | YES |  |  |
| posicion | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_producto_extras_detalle` (
  `cod_producto_extra_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto_extra` int(11) DEFAULT NULL,
  `cod_producto` int(11) DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_producto_extra_detalle`)
) ENGINE=MyISAM AUTO_INCREMENT=653 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto_extra_detalle
- cod_producto_extra
- cod_producto

---

# tb_productos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto | int(11) | NO | PRI | auto_increment |
| cod_producto_padre | int(11) | YES |  |  |
| cod_empresa | int(11) | YES |  |  |
| open_detalle | int(11) | NO |  |  |
| is_combo | int(11) | NO |  |  |
| alias | varchar(200) | NO |  |  |
| nombre | varchar(150) | YES |  |  |
| dia | int(11) | NO |  |  |
| peso | int(11) | NO |  |  |
| volumen | int(11) | NO |  |  |
| sku | varchar(100) | NO |  |  |
| noStock | int(11) | NO |  |  |
| cobra_iva | int(11) | NO |  |  |
| iva_porcentaje | int(11) | NO |  |  |
| iva_valor | double | NO |  |  |
| bien | enum('Producto','Servicio') | NO |  |  |
| costo | double | NO |  |  |
| precio_no_tax | double | NO |  |  |
| precio | varchar(15) | NO |  |  |
| precio_anterior | float | NO |  |  |
| desc_corta | text | YES |  |  |
| desc_larga | text | YES |  |  |
| image_min | varchar(500) | YES |  |  |
| image_max | varchar(500) | YES |  |  |
| fecha_create | datetime | NO |  |  |
| user_create | int(11) | NO |  |  |
| iscategoria | int(11) | YES |  |  |
| variante_visualizacion | enum('LISTA','SELECCIONAR') | NO |  |  |
| posicion | int(11) | YES |  |  |
| id_contifico | varchar(255) | NO |  |  |
| intervalo | varchar(255) | YES |  |  |
| fecha_modificacion | timestamp | NO |  | on update CURRENT_TIMESTAMP |
| estado | enum('A','I','D') | YES |  |  |
| image_path | text | YES |  |  |
| oahu_arma_bowl | varchar(255) | YES |  |  |
| tiempo_preparacion | int(11) | YES |  |  |
| venta_delivery | tinyint(1) | NO |  |  |
| venta_pickup | tinyint(1) | NO |  |  |
| venta_mesa | tinyint(1) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos` (
  `cod_producto` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto_padre` int(11) DEFAULT NULL,
  `cod_empresa` int(11) DEFAULT NULL,
  `open_detalle` int(11) NOT NULL,
  `is_combo` int(11) NOT NULL DEFAULT '0',
  `alias` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dia` int(11) NOT NULL DEFAULT '0',
  `peso` int(11) NOT NULL DEFAULT '0',
  `volumen` int(11) NOT NULL DEFAULT '0',
  `sku` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `noStock` int(11) NOT NULL DEFAULT '0',
  `cobra_iva` int(11) NOT NULL,
  `iva_porcentaje` int(11) NOT NULL,
  `iva_valor` double NOT NULL,
  `bien` enum('Producto','Servicio') COLLATE utf8_unicode_ci NOT NULL,
  `costo` double NOT NULL,
  `precio_no_tax` double NOT NULL,
  `precio` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `precio_anterior` float NOT NULL,
  `desc_corta` text COLLATE utf8_unicode_ci,
  `desc_larga` text COLLATE utf8_unicode_ci,
  `image_min` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image_max` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_create` datetime NOT NULL,
  `user_create` int(11) NOT NULL,
  `iscategoria` int(11) DEFAULT NULL,
  `variante_visualizacion` enum('LISTA','SELECCIONAR') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'LISTA',
  `posicion` int(11) DEFAULT NULL,
  `id_contifico` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `intervalo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_modificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  `image_path` text COLLATE utf8_unicode_ci,
  `oahu_arma_bowl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tiempo_preparacion` int(11) DEFAULT '0',
  `venta_delivery` tinyint(1) NOT NULL DEFAULT '1',
  `venta_pickup` tinyint(1) NOT NULL DEFAULT '1',
  `venta_mesa` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cod_producto`)
) ENGINE=MyISAM AUTO_INCREMENT=10301 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto
- cod_producto_padre
- cod_empresa

---

# tb_productos_archivos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_archivo | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | NO |  |  |
| nombre_archivo | varchar(300) | NO |  |  |
| posicion | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos_archivos` (
  `cod_archivo` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) NOT NULL,
  `nombre_archivo` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `posicion` int(11) NOT NULL,
  PRIMARY KEY (`cod_archivo`)
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_archivo
- cod_producto

---

# tb_productos_categorias

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto_categoria | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | YES |  |  |
| cod_categoria | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos_categorias` (
  `cod_producto_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) DEFAULT NULL,
  `cod_categoria` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_producto_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=52717 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_producto_categoria
- cod_producto
- cod_categoria

---

# tb_productos_descripciones

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_productos_descripciones | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | NO |  |  |
| descripcion | text | YES |  |  |
| titulo | varchar(50) | NO |  |  |
| posicion | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos_descripciones` (
  `cod_productos_descripciones` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) NOT NULL,
  `descripcion` text COLLATE utf8_unicode_ci,
  `titulo` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `posicion` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_productos_descripciones`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto

---

# tb_productos_detalle

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto_detalle | int(11) | NO | PRI | auto_increment |
| cod_producto_padre | int(11) | NO |  |  |
| cod_producto_hijo | int(11) | NO |  |  |
| cantidad | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos_detalle` (
  `cod_producto_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto_padre` int(11) NOT NULL,
  `cod_producto_hijo` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  PRIMARY KEY (`cod_producto_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto_detalle
- cod_producto_padre
- cod_producto_hijo

---

# tb_productos_dias

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto_dias | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | YES |  |  |
| dia | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos_dias` (
  `cod_producto_dias` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) DEFAULT NULL,
  `dia` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_producto_dias`)
) ENGINE=InnoDB AUTO_INCREMENT=28172 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto_dias
- cod_producto

---

# tb_productos_envio_facturacion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | varchar(50) | YES |  |  |
| alias | varchar(15) | YES |  |  |
| name_in_contifico | varchar(100) | NO |  |  |
| cod_empresa | int(11) | YES |  |  |
| cod_sistema_facturacion | int(11) | YES |  |  |
| cod_contifico_empresa | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos_envio_facturacion` (
  `id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `alias` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name_in_contifico` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `cod_empresa` int(11) DEFAULT NULL,
  `cod_sistema_facturacion` int(11) DEFAULT NULL,
  `cod_contifico_empresa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa
- cod_sistema_facturacion
- cod_contifico_empresa

---

# tb_productos_facturacion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto_facturacion | int(11) | NO | PRI | auto_increment |
| id | varchar(50) | NO |  |  |
| cod_producto | int(11) | YES |  |  |
| cod_sistema_facturacion | int(11) | YES |  |  |
| name_in_contifico | varchar(100) | NO |  |  |
| cod_contifico_empresa | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos_facturacion` (
  `cod_producto_facturacion` int(11) NOT NULL AUTO_INCREMENT,
  `id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `cod_producto` int(11) DEFAULT NULL,
  `cod_sistema_facturacion` int(11) DEFAULT NULL,
  `name_in_contifico` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `cod_contifico_empresa` int(11) NOT NULL,
  PRIMARY KEY (`cod_producto_facturacion`)
) ENGINE=InnoDB AUTO_INCREMENT=541 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto_facturacion
- cod_producto
- cod_sistema_facturacion
- cod_contifico_empresa

---

# tb_productos_imagenes

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_imagen | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | NO |  |  |
| nombre_img | varchar(300) | NO |  |  |
| tipo | enum('IMAGEN','VIDEO') | NO |  |  |
| posicion | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos_imagenes` (
  `cod_imagen` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) NOT NULL,
  `nombre_img` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `tipo` enum('IMAGEN','VIDEO') COLLATE utf8_unicode_ci NOT NULL,
  `posicion` int(11) NOT NULL,
  PRIMARY KEY (`cod_imagen`)
) ENGINE=MyISAM AUTO_INCREMENT=1089 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_imagen
- cod_producto

---

# tb_productos_ingredientes

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto_ingrediente | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | YES |  |  |
| cod_ingrediente | int(11) | YES |  |  |
| valor | float | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos_ingredientes` (
  `cod_producto_ingrediente` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) DEFAULT NULL,
  `cod_ingrediente` int(11) DEFAULT NULL,
  `valor` float DEFAULT NULL,
  PRIMARY KEY (`cod_producto_ingrediente`)
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto_ingrediente
- cod_producto
- cod_ingrediente

---

# tb_productos_kiosco

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto_kiosco | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | YES |  |  |
| cod_sucursal | int(11) | YES |  |  |
| precio | float | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |
| is_custom | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos_kiosco` (
  `cod_producto_kiosco` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) DEFAULT NULL,
  `cod_sucursal` int(11) DEFAULT NULL,
  `precio` float DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_custom` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_producto_kiosco`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto_kiosco
- cod_producto
- cod_sucursal

---

# tb_productos_opciones

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto_opcion | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | NO |  |  |
| titulo | varchar(200) | YES |  |  |
| cantidad | int(11) | YES |  |  |
| cantidad_min | int(11) | NO |  |  |
| isCheck | int(11) | NO |  |  |
| isDatabase | int(11) | NO |  |  |
| posicion | int(11) | NO |  |  |
| productos | text | YES |  |  |
| descripcion | varchar(100) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos_opciones` (
  `cod_producto_opcion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) NOT NULL,
  `titulo` varchar(200) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `cantidad_min` int(11) NOT NULL,
  `isCheck` int(11) NOT NULL DEFAULT '0',
  `isDatabase` int(11) NOT NULL DEFAULT '0',
  `posicion` int(11) NOT NULL DEFAULT '99',
  `productos` text,
  `descripcion` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`cod_producto_opcion`)
) ENGINE=InnoDB AUTO_INCREMENT=7290 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_producto_opcion
- cod_producto

---

# tb_productos_opciones_detalle

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto_opciones_detalle | int(11) | NO | PRI | auto_increment |
| cod_producto_opcion | int(11) | YES |  |  |
| item | varchar(200) | YES |  |  |
| aumentar_precio | int(11) | NO |  |  |
| precio | float | YES |  |  |
| grava_iva | int(11) | NO |  |  |
| debitInventario | int(11) | NO |  |  |
| posicion | int(11) | YES |  |  |
| detalle | varchar(100) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos_opciones_detalle` (
  `cod_producto_opciones_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto_opcion` int(11) DEFAULT NULL,
  `item` varchar(200) DEFAULT NULL,
  `aumentar_precio` int(11) NOT NULL DEFAULT '0',
  `precio` float DEFAULT '0',
  `grava_iva` int(11) NOT NULL DEFAULT '1',
  `debitInventario` int(11) NOT NULL DEFAULT '1',
  `posicion` int(11) DEFAULT '0',
  `detalle` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`cod_producto_opciones_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=52901 DEFAULT CHARSET=utf8mb4
```

## Posibles relaciones

- cod_producto_opciones_detalle
- cod_producto_opcion

---

# tb_productos_opciones_detalle_facturacion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_opcion_detalle_facturacion | int(11) | NO | PRI | auto_increment |
| cod_producto_opciones_detalle | int(11) | NO | MUL |  |
| cod_contifico_empresa | int(11) | NO |  |  |
| id_runfood | varchar(50) | NO |  |  |
| nombre_runfood | varchar(200) | YES |  |  |
| created_at | timestamp | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos_opciones_detalle_facturacion` (
  `cod_opcion_detalle_facturacion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto_opciones_detalle` int(11) NOT NULL,
  `cod_contifico_empresa` int(11) NOT NULL,
  `id_runfood` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `nombre_runfood` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cod_opcion_detalle_facturacion`),
  UNIQUE KEY `uq_detalle_empresa` (`cod_producto_opciones_detalle`,`cod_contifico_empresa`),
  CONSTRAINT `tb_productos_opciones_detalle_facturacion_ibfk_1` FOREIGN KEY (`cod_producto_opciones_detalle`) REFERENCES `tb_productos_opciones_detalle` (`cod_producto_opciones_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_opcion_detalle_facturacion
- cod_producto_opciones_detalle
- cod_contifico_empresa

---

# tb_productos_opciones_ingredientes

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto_opcion_ingrediente | int(11) | NO | PRI | auto_increment |
| cod_producto_opcion | int(11) | YES |  |  |
| cod_ingrediente | int(11) | YES |  |  |
| valor | float | YES |  |  |
| es_principal | tinyint(1) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos_opciones_ingredientes` (
  `cod_producto_opcion_ingrediente` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto_opcion` int(11) DEFAULT NULL,
  `cod_ingrediente` int(11) DEFAULT NULL,
  `valor` float DEFAULT NULL,
  `es_principal` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cod_producto_opcion_ingrediente`)
) ENGINE=InnoDB AUTO_INCREMENT=470 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto_opcion_ingrediente
- cod_producto_opcion
- cod_ingrediente

---

# tb_productos_preferencia

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto_preferencia | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | YES |  |  |
| descripcion | varchar(150) | YES |  |  |
| posicion | int(11) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos_preferencia` (
  `cod_producto_preferencia` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) DEFAULT NULL,
  `descripcion` varchar(150) DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  `estado` enum('A','I','D') DEFAULT NULL,
  PRIMARY KEY (`cod_producto_preferencia`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_producto_preferencia
- cod_producto

---

# tb_productos_sucursal

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto_sucursal | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | YES |  |  |
| cod_sucursal | int(11) | YES |  |  |
| replacePrice | int(11) | NO |  |  |
| precio | float | YES |  |  |
| precio_anterior | float | YES |  |  |
| precio_no_tax | float | YES |  |  |
| iva_valor | float | YES |  |  |
| estado | enum('A','I','D','AGOTADO') | YES |  |  |
| agotado_inicio | datetime | YES |  |  |
| agotado_fin | datetime | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos_sucursal` (
  `cod_producto_sucursal` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) DEFAULT NULL,
  `cod_sucursal` int(11) DEFAULT NULL,
  `replacePrice` int(11) NOT NULL DEFAULT '0',
  `precio` float DEFAULT NULL,
  `precio_anterior` float DEFAULT NULL,
  `precio_no_tax` float DEFAULT NULL,
  `iva_valor` float DEFAULT NULL,
  `estado` enum('A','I','D','AGOTADO') DEFAULT NULL,
  `agotado_inicio` datetime DEFAULT NULL,
  `agotado_fin` datetime DEFAULT NULL,
  PRIMARY KEY (`cod_producto_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=33030 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_producto_sucursal
- cod_producto
- cod_sucursal

---

# tb_productos_tags

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | NO | UNI |  |
| tag_id | int(11) | NO | MUL |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cod_producto` (`cod_producto`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `tb_productos_tags_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `tb_tags` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1729 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto

---

# tb_productos_usuarios

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto | int(11) | YES |  |  |
| cod_usuario | int(11) | YES |  |  |
| fecha_modificacion | timestamp | NO |  | on update CURRENT_TIMESTAMP |

## SQL Structure

```sql
CREATE TABLE `tb_productos_usuarios` (
  `cod_producto` int(11) DEFAULT NULL,
  `cod_usuario` int(11) DEFAULT NULL,
  `fecha_modificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto
- cod_usuario

---

# tb_productos_variante

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto_variante | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | YES |  |  |
| atributo | varchar(50) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_productos_variante` (
  `cod_producto_variante` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) DEFAULT NULL,
  `atributo` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_producto_variante`)
) ENGINE=MyISAM AUTO_INCREMENT=1994 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto_variante
- cod_producto

---

# tb_programa_usuario

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_programa_usuario | int(11) | NO | PRI | auto_increment |
| cod_programa | int(11) | YES |  |  |
| cod_usuario | int(11) | YES |  |  |
| precio | float | YES |  |  |
| nombre_alumno | varchar(60) | YES |  |  |
| observacion | text | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |
| fecha_create | datetime | YES |  |  |
| fecha_update | datetime | YES |  |  |
| user_update | int(11) | YES |  |  |
| fecha_last_pago | date | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_programa_usuario` (
  `cod_programa_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `cod_programa` int(11) DEFAULT NULL,
  `cod_usuario` int(11) DEFAULT NULL,
  `precio` float DEFAULT NULL,
  `nombre_alumno` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `observacion` text COLLATE utf8_unicode_ci,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_create` datetime DEFAULT NULL,
  `fecha_update` datetime DEFAULT NULL,
  `user_update` int(11) DEFAULT NULL,
  `fecha_last_pago` date DEFAULT NULL,
  PRIMARY KEY (`cod_programa_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_programa
- cod_usuario

---

# tb_programas

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_programa | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| nombre | varchar(60) | YES |  |  |
| descripcion | text | YES |  |  |
| precio | float | YES |  |  |
| posicion | int(11) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_programas` (
  `cod_programa` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `nombre` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8_unicode_ci,
  `precio` float DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_programa`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_programa
- cod_empresa

---

# tb_promocion_pos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_promocion_pos | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | YES |  |  |
| dia | int(11) | NO |  |  |
| fecha_inicio | date | YES |  |  |
| fecha_fin | date | YES |  |  |
| precio | float | YES |  |  |
| precio_tarjeta | float | NO |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_promocion_pos` (
  `cod_promocion_pos` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) DEFAULT NULL,
  `dia` int(11) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `precio` float DEFAULT NULL,
  `precio_tarjeta` float NOT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_promocion_pos`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto

---

# tb_promocion_producto_gratis

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | YES |  |  |
| cod_producto | int(11) | YES |  |  |
| fecha_inicio | date | YES |  |  |
| fecha_fin | date | YES |  |  |
| imagen | varchar(250) | YES |  |  |
| tipo | enum('REGISTRO','FIRST_ORDER','PURCHASE') | YES |  |  |
| producto_nombre | varchar(150) | NO |  |  |
| is_web | int(11) | YES |  |  |
| is_app | int(11) | YES |  |  |
| monto_minimo | int(11) | YES |  |  |
| titulo | varchar(200) | NO |  |  |
| descripcion | text | NO |  |  |
| descripcion_no_aplica | varchar(200) | NO |  |  |
| estado | enum('A','I') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_promocion_producto_gratis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) DEFAULT NULL,
  `cod_producto` int(11) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `imagen` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tipo` enum('REGISTRO','FIRST_ORDER','PURCHASE') COLLATE utf8_unicode_ci DEFAULT NULL,
  `producto_nombre` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `is_web` int(11) DEFAULT '0',
  `is_app` int(11) DEFAULT '0',
  `monto_minimo` int(11) DEFAULT '0',
  `titulo` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8_unicode_ci NOT NULL,
  `descripcion_no_aplica` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `estado` enum('A','I') COLLATE utf8_unicode_ci DEFAULT 'A',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal
- cod_producto

---

# tb_promociones

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_promocion | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| titulo | varchar(200) | YES |  |  |
| descripcion_corta | text | YES |  |  |
| descripcion_larga | text | YES |  |  |
| imagen_min | varchar(200) | YES |  |  |
| imagen_max | varchar(200) | YES |  |  |
| dia | int(11) | NO |  |  |
| fecha_inicio | date | YES |  |  |
| fecha_fin | date | YES |  |  |
| posicion | int(11) | YES |  |  |
| user_create | int(11) | YES |  |  |
| fecha_create | datetime | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_promociones` (
  `cod_promocion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `titulo` varchar(200) DEFAULT NULL,
  `descripcion_corta` text,
  `descripcion_larga` text,
  `imagen_min` varchar(200) DEFAULT NULL,
  `imagen_max` varchar(200) DEFAULT NULL,
  `dia` int(11) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  `user_create` int(11) DEFAULT NULL,
  `fecha_create` datetime DEFAULT NULL,
  `estado` enum('A','I','D') DEFAULT NULL,
  PRIMARY KEY (`cod_promocion`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_promocion
- cod_empresa

---

# tb_proveedor_botonpagos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_proveedor_botonpagos | int(11) | NO | PRI | auto_increment |
| identificador | varchar(15) | NO |  |  |
| nombre | varchar(50) | YES |  |  |
| imagen | varchar(50) | YES |  |  |
| posicion | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_proveedor_botonpagos` (
  `cod_proveedor_botonpagos` int(11) NOT NULL AUTO_INCREMENT,
  `identificador` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `nombre` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imagen` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) DEFAULT '99',
  PRIMARY KEY (`cod_proveedor_botonpagos`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

---

# tb_proveedores

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_proveedor | int(11) | NO | PRI | auto_increment |
| cod_tipo_documento | int(11) | NO |  |  |
| num_documento | varchar(10) | NO |  |  |
| nombre | varchar(15) | NO |  |  |
| telefono | varchar(11) | NO |  |  |
| logo | varchar(150) | NO |  |  |
| correo | varchar(30) | NO |  |  |
| direccion | varchar(50) | NO |  |  |
| cod_empresa | int(11) | NO |  |  |
| observacion | text | NO |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_proveedores` (
  `cod_proveedor` int(11) NOT NULL AUTO_INCREMENT,
  `cod_tipo_documento` int(11) NOT NULL,
  `num_documento` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `nombre` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `telefono` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  `logo` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `correo` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `direccion` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `cod_empresa` int(11) NOT NULL,
  `observacion` text COLLATE utf8_unicode_ci NOT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_proveedor`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_proveedor
- cod_tipo_documento
- cod_empresa

---

# tb_ptos_emision

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_pto_emision | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | YES |  |  |
| pto_emision | int(11) | YES |  |  |
| secuencialFAC | int(11) | YES |  |  |
| secuencialDNA | int(11) | YES |  |  |
| secuencialCOT | int(11) | NO |  |  |
| autorizacion | varchar(20) | YES |  |  |
| bloque_inicial | int(11) | YES |  |  |
| bloque_final | int(11) | YES |  |  |
| ip | varchar(30) | NO |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_ptos_emision` (
  `cod_pto_emision` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) DEFAULT NULL,
  `pto_emision` int(11) DEFAULT NULL,
  `secuencialFAC` int(11) DEFAULT NULL,
  `secuencialDNA` int(11) DEFAULT NULL,
  `secuencialCOT` int(11) NOT NULL DEFAULT '1',
  `autorizacion` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bloque_inicial` int(11) DEFAULT NULL,
  `bloque_final` int(11) DEFAULT NULL,
  `ip` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_pto_emision`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_pto_emision
- cod_sucursal

---

# tb_recipientes

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_recipiente | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| nombre | varchar(100) | YES |  |  |
| precio | float | YES |  |  |
| estado | enum('A','I') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_recipientes` (
  `cod_recipiente` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `precio` float DEFAULT NULL,
  `estado` enum('A','I') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_recipiente`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_recipiente
- cod_empresa

---

# tb_recipientes_facturacion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_recipiente_facturacion | int(11) | NO | PRI | auto_increment |
| cod_recipiente | int(11) | YES |  |  |
| id | varchar(100) | YES |  |  |
| name_in_contifico | varchar(100) | YES |  |  |
| cod_sistema_facturacion | int(11) | YES |  |  |
| cod_contifico_empresa | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_recipientes_facturacion` (
  `cod_recipiente_facturacion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_recipiente` int(11) DEFAULT NULL,
  `id` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name_in_contifico` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cod_sistema_facturacion` int(11) DEFAULT NULL,
  `cod_contifico_empresa` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_recipiente_facturacion`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_recipiente_facturacion
- cod_recipiente
- cod_sistema_facturacion
- cod_contifico_empresa

---

# tb_red_social

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_red | int(11) | NO | PRI | auto_increment |
| codigo | varchar(15) | NO |  |  |
| nombre | varchar(150) | YES |  |  |
| icono | varchar(75) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_red_social` (
  `cod_red` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icono` varchar(75) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_red`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_red

---

# tb_rol_pagos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_rol_pago | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| cod_empleado | int(11) | YES |  |  |
| fecha_pago | datetime | YES |  |  |
| fecha_inicio_laboral | date | YES |  |  |
| fecha_fin_laboral | date | YES |  |  |
| sueldo | double | YES |  |  |
| valor_sueldo | double | YES |  |  |
| horas_extra_50 | double | YES |  |  |
| horas_extra_100 | double | YES |  |  |
| fondo_reserva | double | YES |  |  |
| comision | double | YES |  |  |
| bono | double | YES |  |  |
| aporte_iess | double | YES |  |  |
| prestamo | double | YES |  |  |
| atraso | double | YES |  |  |
| total_ingreso | double | YES |  |  |
| total_egreso | double | YES |  |  |
| total_pagado | double | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_rol_pagos` (
  `cod_rol_pago` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `cod_empleado` int(11) DEFAULT NULL,
  `fecha_pago` datetime DEFAULT NULL,
  `fecha_inicio_laboral` date DEFAULT NULL,
  `fecha_fin_laboral` date DEFAULT NULL,
  `sueldo` double DEFAULT NULL,
  `valor_sueldo` double DEFAULT NULL,
  `horas_extra_50` double DEFAULT NULL,
  `horas_extra_100` double DEFAULT NULL,
  `fondo_reserva` double DEFAULT NULL,
  `comision` double DEFAULT NULL,
  `bono` double DEFAULT NULL,
  `aporte_iess` double DEFAULT NULL,
  `prestamo` double DEFAULT NULL,
  `atraso` double DEFAULT NULL,
  `total_ingreso` double DEFAULT NULL,
  `total_egreso` double DEFAULT NULL,
  `total_pagado` double DEFAULT NULL,
  PRIMARY KEY (`cod_rol_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_rol_pago
- cod_empresa
- cod_empleado

---

# tb_roles

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_rol | int(11) | NO | PRI | auto_increment |
| nombre | varchar(150) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_roles` (
  `cod_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_rol`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_rol

---

# tb_roles_empresa

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_roles_empresa | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| cod_rol | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_roles_empresa` (
  `cod_roles_empresa` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `cod_rol` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_roles_empresa`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_empresa
- cod_rol

---

# tb_runfood_producto_opcion_detalle

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_runfood_producto_opcion_detalle | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | NO |  |  |
| cod_runfood | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_runfood_producto_opcion_detalle` (
  `cod_runfood_producto_opcion_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) NOT NULL,
  `cod_runfood` int(11) NOT NULL,
  PRIMARY KEY (`cod_runfood_producto_opcion_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto
- cod_runfood

---

# tb_runfood_productos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_runfood_producto | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | NO |  |  |
| cod_runfood | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_runfood_productos` (
  `cod_runfood_producto` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) NOT NULL,
  `cod_runfood` int(11) NOT NULL,
  PRIMARY KEY (`cod_runfood_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_runfood_producto
- cod_producto
- cod_runfood

---

# tb_runfood_sucursal

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_runfood_sucursal | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | YES |  |  |
| dominio | varchar(150) | YES |  |  |
| usuario_id | int(11) | YES |  |  |
| facturar | int(11) | YES |  |  |
| tipo_documento | varchar(3) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_runfood_sucursal` (
  `cod_runfood_sucursal` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) DEFAULT NULL,
  `dominio` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `facturar` int(11) DEFAULT '0',
  `tipo_documento` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'FAC',
  PRIMARY KEY (`cod_runfood_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal

---

# tb_salsas

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_salsa | int(11) | NO | PRI | auto_increment |
| salsa | varchar(300) | YES |  |  |
| posicion | int(11) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_salsas` (
  `cod_salsa` int(11) NOT NULL AUTO_INCREMENT,
  `salsa` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_salsa`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_salsa

---

# tb_shopping_car

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_shopping_car | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | NO |  |  |
| cod_usuario | int(11) | NO |  |  |
| cod_producto | int(11) | NO |  |  |
| cantidad | int(11) | NO |  |  |
| detalle | text | NO |  |  |
| fecha | date | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_shopping_car` (
  `cod_shopping_car` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) NOT NULL,
  `cod_usuario` int(11) NOT NULL,
  `cod_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `detalle` text COLLATE utf8_unicode_ci NOT NULL,
  `fecha` date NOT NULL,
  PRIMARY KEY (`cod_shopping_car`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa
- cod_usuario
- cod_producto

---

# tb_sistema_facturacion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_sistema_facturacion | int(11) | NO | PRI | auto_increment |
| identificador | varchar(15) | NO |  |  |
| nombre | varchar(100) | YES |  |  |
| imagen | varchar(150) | NO |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_sistema_facturacion` (
  `cod_sistema_facturacion` int(11) NOT NULL AUTO_INCREMENT,
  `identificador` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imagen` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_sistema_facturacion`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

---

# tb_size_crop

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_size_crop | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| tipo | enum('productos','productos_gallery','bannner','blog','blog_gallery') | YES |  |  |
| size_min_width | int(11) | YES |  |  |
| size_min_height | int(11) | YES |  |  |
| size_max_width | int(11) | YES |  |  |
| size_max_height | int(11) | YES |  |  |
| quality | float | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_size_crop` (
  `cod_size_crop` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `tipo` enum('productos','productos_gallery','bannner','blog','blog_gallery') COLLATE utf8_unicode_ci DEFAULT NULL,
  `size_min_width` int(11) DEFAULT NULL,
  `size_min_height` int(11) DEFAULT NULL,
  `size_max_width` int(11) DEFAULT NULL,
  `size_max_height` int(11) DEFAULT NULL,
  `quality` float NOT NULL,
  PRIMARY KEY (`cod_size_crop`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa

---

# tb_steps_timeline

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| estado | varchar(10) | YES |  |  |
| tipo | varchar(10) | YES |  |  |
| titulo | varchar(100) | YES |  |  |
| desc_complete | varchar(100) | YES |  |  |
| desc_no_complete | varchar(100) | YES |  |  |
| imagen | varchar(15) | YES |  |  |
| posicion | int(11) | YES |  |  |
| cod_step | int(11) | NO | PRI | auto_increment |

## SQL Structure

```sql
CREATE TABLE `tb_steps_timeline` (
  `estado` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tipo` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `titulo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `desc_complete` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `desc_no_complete` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imagen` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  `cod_step` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`cod_step`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_step

---

# tb_stock

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_stock | int(11) | NO | PRI | auto_increment |
| sku | varchar(25) | YES |  |  |
| cod_sucursal | int(11) | YES |  |  |
| cantidad | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_stock` (
  `cod_stock` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cod_sucursal` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cod_stock`)
) ENGINE=InnoDB AUTO_INCREMENT=536 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal

---

# tb_sucursal_alta_demanda

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | YES |  |  |
| fecha_inicio | datetime | YES |  |  |
| fecha_fin | datetime | YES |  |  |
| cod_usuario | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_sucursal_alta_demanda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) DEFAULT NULL,
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `cod_usuario` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal
- cod_usuario

---

# tb_sucursal_cobertura

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | NO |  |  |
| zone | polygon | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_sucursal_cobertura` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) NOT NULL,
  `zone` polygon NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3723 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal

---

# tb_sucursal_costo_envio

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_sucursal_costo_envio | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | YES |  |  |
| base_dinero | float | YES |  |  |
| base_km | float | YES |  |  |
| adicional_km | float | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_sucursal_costo_envio` (
  `cod_sucursal_costo_envio` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) DEFAULT NULL,
  `base_dinero` float DEFAULT NULL,
  `base_km` float DEFAULT NULL,
  `adicional_km` float DEFAULT NULL,
  PRIMARY KEY (`cod_sucursal_costo_envio`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal

---

# tb_sucursal_costo_envio_rango

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | YES |  |  |
| distancia_ini | float | YES |  |  |
| distancia_fin | float | YES |  |  |
| precio | float | YES |  |  |
| cod_tarifa | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_sucursal_costo_envio_rango` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) DEFAULT NULL,
  `distancia_ini` float DEFAULT NULL COMMENT 'Distancia inicial incluida',
  `distancia_fin` float DEFAULT NULL COMMENT 'Distancia Final no incluida',
  `precio` float DEFAULT '0',
  `cod_tarifa` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1911 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal
- cod_tarifa

---

# tb_sucursal_courier

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_sucursal_courier | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | YES |  |  |
| cod_courier | int(11) | YES |  |  |
| validar_cobertura | int(11) | NO |  |  |
| estado | enum('A','I') | YES |  |  |
| prioridad | int(11) | YES |  |  |
| detalle | varchar(30) | NO |  |  |
| fecha_create | timestamp | NO |  | on update CURRENT_TIMESTAMP |

## SQL Structure

```sql
CREATE TABLE `tb_sucursal_courier` (
  `cod_sucursal_courier` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) DEFAULT NULL,
  `cod_courier` int(11) DEFAULT NULL,
  `validar_cobertura` int(11) NOT NULL DEFAULT '0' COMMENT 'Si esta en 1 se valida estrictamente esta cobertura',
  `estado` enum('A','I') COLLATE utf8_unicode_ci DEFAULT 'A',
  `prioridad` int(11) DEFAULT '5',
  `detalle` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `fecha_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cod_sucursal_courier`)
) ENGINE=InnoDB AUTO_INCREMENT=416 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal
- cod_courier

---

# tb_sucursal_disponibilidad

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_sucursal_disponibilidad | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | NO |  |  |
| dia | int(11) | NO |  |  |
| hora_ini | time | NO |  |  |
| hora_fin | time | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_sucursal_disponibilidad` (
  `cod_sucursal_disponibilidad` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) NOT NULL,
  `dia` int(11) NOT NULL,
  `hora_ini` time NOT NULL,
  `hora_fin` time NOT NULL,
  PRIMARY KEY (`cod_sucursal_disponibilidad`)
) ENGINE=InnoDB AUTO_INCREMENT=39049 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal

---

# tb_sucursal_festivos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_sucursal_festivos | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | YES |  |  |
| fecha | date | YES |  |  |
| hora_inicio | varchar(10) | YES |  |  |
| hora_fin | varchar(10) | YES |  |  |
| fecha_inicio | datetime | YES |  |  |
| fecha_fin | datetime | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_sucursal_festivos` (
  `cod_sucursal_festivos` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `hora_inicio` varchar(10) DEFAULT NULL,
  `hora_fin` varchar(10) DEFAULT NULL,
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  PRIMARY KEY (`cod_sucursal_festivos`)
) ENGINE=InnoDB AUTO_INCREMENT=10531 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_sucursal

---

# tb_sucursal_flota

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | YES |  |  |
| cod_flota | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_sucursal_flota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) DEFAULT NULL,
  `cod_flota` int(11) DEFAULT NULL COMMENT 'EMPRESA DE TIPO COURIER',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal
- cod_flota

---

# tb_sucursal_forma_pago

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_sucursal_forma_pago | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | NO | MUL |  |
| cod_forma_pago | varchar(50) | NO |  |  |
| monto_maximo | decimal(10,2) | YES |  |  |
| descripcion | text | YES |  |  |
| is_delivery | tinyint(1) | YES |  |  |
| is_pickup | tinyint(1) | YES |  |  |
| estado | varchar(1) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_sucursal_forma_pago` (
  `cod_sucursal_forma_pago` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) NOT NULL,
  `cod_forma_pago` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `monto_maximo` decimal(10,2) DEFAULT '0.00',
  `descripcion` text COLLATE utf8_unicode_ci,
  `is_delivery` tinyint(1) DEFAULT '1',
  `is_pickup` tinyint(1) DEFAULT '1',
  `estado` varchar(1) COLLATE utf8_unicode_ci DEFAULT 'A',
  PRIMARY KEY (`cod_sucursal_forma_pago`),
  UNIQUE KEY `uq_suc_fp` (`cod_sucursal`,`cod_forma_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal
- cod_forma_pago

---

# tb_sucursal_tiempo_programar

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_sucursal_tiempo_programar | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | YES |  |  |
| tipo | enum('DELIVERY','PICKUP') | YES |  |  |
| hora_apertura | int(11) | YES |  |  |
| hora_cierre | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_sucursal_tiempo_programar` (
  `cod_sucursal_tiempo_programar` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) DEFAULT NULL,
  `tipo` enum('DELIVERY','PICKUP') COLLATE utf8_unicode_ci DEFAULT NULL,
  `hora_apertura` int(11) DEFAULT NULL,
  `hora_cierre` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_sucursal_tiempo_programar`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal

---

# tb_sucursales

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_sucursal | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| cod_ciudad | int(11) | NO |  |  |
| nombre | varchar(150) | YES |  |  |
| direccion | varchar(200) | YES |  |  |
| latitud | varchar(150) | YES |  |  |
| longitud | varchar(150) | YES |  |  |
| distancia_km | float | NO |  |  |
| hora_ini | time | YES |  |  |
| hora_fin | time | YES |  |  |
| intervalo | int(11) | NO |  |  |
| emisor | varchar(150) | NO |  |  |
| telefono | varchar(20) | NO |  |  |
| correo | varchar(150) | NO |  |  |
| image | varchar(50) | NO |  |  |
| image_min | varchar(50) | NO |  |  |
| transferencia_img | varchar(50) | NO |  |  |
| banner_xl | varchar(50) | NO |  |  |
| delivery | int(11) | NO |  |  |
| insite | int(11) | NO |  |  |
| pickup | int(11) | NO |  |  |
| show_banner | int(11) | NO |  |  |
| tipo_bodega | int(11) | NO |  |  |
| programar_pedido | int(11) | NO |  |  |
| grava_iva | int(11) | NO |  |  |
| envio_grava_iva | int(11) | NO |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_sucursales` (
  `cod_sucursal` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `cod_ciudad` int(11) NOT NULL,
  `nombre` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `direccion` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `latitud` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `longitud` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `distancia_km` float NOT NULL,
  `hora_ini` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `intervalo` int(11) NOT NULL DEFAULT '30',
  `emisor` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `correo` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `image_min` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `transferencia_img` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `banner_xl` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `delivery` int(11) NOT NULL DEFAULT '1',
  `insite` int(11) NOT NULL DEFAULT '0',
  `pickup` int(11) NOT NULL DEFAULT '1',
  `show_banner` int(11) NOT NULL DEFAULT '1',
  `tipo_bodega` int(11) NOT NULL DEFAULT '0',
  `programar_pedido` int(11) NOT NULL DEFAULT '0',
  `grava_iva` int(11) NOT NULL DEFAULT '1',
  `envio_grava_iva` int(11) NOT NULL DEFAULT '0',
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_sucursal`)
) ENGINE=MyISAM AUTO_INCREMENT=418 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal
- cod_empresa
- cod_ciudad

---

# tb_system_notification

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_system_notification | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| icono | varchar(30) | NO |  |  |
| titulo | varchar(150) | YES |  |  |
| detalle | varchar(300) | YES |  |  |
| url | varchar(100) | YES |  |  |
| fecha | datetime | YES |  |  |
| estado | enum('A','I','D','N') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_system_notification` (
  `cod_system_notification` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `icono` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `detalle` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `estado` enum('A','I','D','N') COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  PRIMARY KEY (`cod_system_notification`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

## Posibles relaciones

- cod_usuario

---

# tb_system_notification_tipos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_tipo_system_notification | int(11) | NO | PRI | auto_increment |
| nombre | varchar(100) | NO |  |  |
| icono | varchar(50) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_system_notification_tipos` (
  `cod_tipo_system_notification` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `icono` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT 'A',
  PRIMARY KEY (`cod_tipo_system_notification`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_tipo_system_notification

---

# tb_tags

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| nombre | varchar(70) | NO |  |  |
| icono | varchar(20) | YES |  |  |
| color | varchar(7) | YES |  |  |
| cod_empresa | int(11) | YES |  |  |
| es_predefinido | tinyint(1) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(70) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cod_empresa` int(11) DEFAULT NULL,
  `es_predefinido` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

## Posibles relaciones

- cod_empresa

---

# tb_tarifa

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_tarifa | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | NO |  |  |
| nombre | varchar(50) | NO |  |  |
| peso_max_kg | decimal(8,2) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_tarifa` (
  `cod_tarifa` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) NOT NULL,
  `nombre` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Estándar',
  `peso_max_kg` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`cod_tarifa`)
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal

---

# tb_tarjetas

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_tarjeta | int(11) | NO | PRI | auto_increment |
| id1 | varchar(5) | YES |  |  |
| id2 | varchar(5) | YES |  |  |
| nombre | varchar(100) | NO |  |  |
| imagen | varchar(255) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_tarjetas` (
  `cod_tarjeta` int(11) NOT NULL AUTO_INCREMENT,
  `id1` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id2` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `imagen` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_tarjeta`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_tarjeta

---

# tb_taste_portafolio

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_taste_portafolio | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | NO |  |  |
| path | varchar(255) | NO |  |  |
| categories | json | NO |  |  |
| cities | json | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_taste_portafolio` (
  `cod_taste_portafolio` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `categories` json NOT NULL,
  `cities` json NOT NULL,
  PRIMARY KEY (`cod_taste_portafolio`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa

---

# tb_telegram

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_telegram | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| token | varchar(100) | YES |  |  |
| botname | varchar(150) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_telegram` (
  `cod_telegram` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `token` varchar(100) DEFAULT NULL,
  `botname` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`cod_telegram`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_empresa

---

# tb_telegram_grupos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_telegram_grupo | int(11) | NO | PRI | auto_increment |
| cod_chat | varchar(50) | YES |  |  |
| cod_empresa | int(11) | YES |  |  |
| nombre | varchar(100) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_telegram_grupos` (
  `cod_telegram_grupo` int(11) NOT NULL AUTO_INCREMENT,
  `cod_chat` varchar(50) DEFAULT NULL,
  `cod_empresa` int(11) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `estado` enum('A','I','D') DEFAULT NULL,
  PRIMARY KEY (`cod_telegram_grupo`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_telegram_grupo
- cod_chat
- cod_empresa

---

# tb_telegram_sucursal

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_telegram_sucursal | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | YES |  |  |
| code | varchar(20) | YES |  |  |
| estado | enum('PENDIENTE','ACTIVO','INACTIVO') | YES |  |  |
| chat_id | varchar(50) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_telegram_sucursal` (
  `cod_telegram_sucursal` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) DEFAULT NULL,
  `code` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('PENDIENTE','ACTIVO','INACTIVO') COLLATE utf8_unicode_ci DEFAULT NULL,
  `chat_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_telegram_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_sucursal

---

# tb_telegram_usuarios

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| chat_id | varchar(50) | NO |  |  |
| user_id | varchar(50) | NO |  |  |
| code | varchar(15) | NO |  |  |
| estado | enum('A','P','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_telegram_usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `chat_id` varchar(50) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `code` varchar(15) NOT NULL,
  `estado` enum('A','P','I','D') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_usuario

---

# tb_telegram_usuarios_ubicacion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_ubicacion | int(11) | NO | PRI | auto_increment |
| cod_telegram_usuario | int(11) | YES |  |  |
| cod_chat | varchar(50) | YES |  |  |
| fecha | datetime | YES |  |  |
| latitud | varchar(25) | YES |  |  |
| longitud | varchar(25) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_telegram_usuarios_ubicacion` (
  `cod_ubicacion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_telegram_usuario` int(11) DEFAULT NULL,
  `cod_chat` varchar(50) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `latitud` varchar(25) DEFAULT NULL,
  `longitud` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`cod_ubicacion`)
) ENGINE=MyISAM AUTO_INCREMENT=101 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_ubicacion
- cod_telegram_usuario
- cod_chat

---

# tb_test

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| test | varchar(100) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_test` (
  `test` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

---

# tb_timeline_detalles

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_timeline_detalle | int(11) | NO | PRI | auto_increment |
| titulo | varchar(200) | YES |  |  |
| subtitulo | varchar(200) | YES |  |  |
| imagen | varchar(200) | YES |  |  |
| posicion | int(11) | NO |  |  |
| cod_timeline | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_timeline_detalles` (
  `cod_timeline_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subtitulo` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imagen` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `posicion` int(11) NOT NULL,
  `cod_timeline` int(11) NOT NULL,
  PRIMARY KEY (`cod_timeline_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=187 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_timeline_detalle
- cod_timeline

---

# tb_timelines

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_timeline | int(11) | NO | PRI | auto_increment |
| nombre | varchar(200) | YES |  |  |
| estado | enum('A','I','D') | NO |  |  |
| cod_empresa | int(11) | NO |  |  |
| cod_producto | int(11) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_timelines` (
  `cod_timeline` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'A',
  `cod_empresa` int(11) NOT NULL,
  `cod_producto` int(11) NOT NULL,
  PRIMARY KEY (`cod_timeline`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_timeline
- cod_empresa
- cod_producto

---

# tb_tipo_correo

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_tipo_correo | int(11) | NO | PRI | auto_increment |
| nombre | varchar(150) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_tipo_correo` (
  `cod_tipo_correo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_tipo_correo`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

---

# tb_tipo_dinero

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_tipo_pago | int(11) | NO | PRI | auto_increment |
| nombre | varchar(150) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_tipo_dinero` (
  `cod_tipo_pago` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_tipo_pago`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_tipo_pago

---

# tb_tipo_empresas

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_tipo_empresa | int(11) | NO | PRI | auto_increment |
| tipo | varchar(100) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_tipo_empresas` (
  `cod_tipo_empresa` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_tipo_empresa`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_tipo_empresa

---

# tb_transaccion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_transaccion | varchar(25) | NO | PRI |  |
| cod_usuario | int(11) | YES |  |  |
| fecha | datetime | YES |  |  |
| monto | float | NO |  |  |
| idPayphone | varchar(15) | NO |  |  |
| estado | varchar(15) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_transaccion` (
  `cod_transaccion` varchar(25) NOT NULL,
  `cod_usuario` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `monto` float NOT NULL,
  `idPayphone` varchar(15) NOT NULL,
  `estado` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`cod_transaccion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_usuario

---

# tb_unidades_medidas

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_unidad_medida | varchar(15) | NO | PRI |  |
| nombre | varchar(100) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_unidades_medidas` (
  `cod_unidad_medida` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_unidad_medida`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_unidad_medida

---

# tb_updates

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_update | int(11) | NO | PRI | auto_increment |
| titulo | varchar(100) | YES |  |  |
| detalle | text | YES |  |  |
| desc_corta | varchar(250) | YES |  |  |
| url | text | NO |  |  |
| tipo_multimedia | int(11) | NO |  |  |
| fecha_create | datetime | NO |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_updates` (
  `cod_update` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `detalle` text COLLATE utf8_unicode_ci,
  `desc_corta` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` text COLLATE utf8_unicode_ci NOT NULL,
  `tipo_multimedia` int(11) NOT NULL,
  `fecha_create` datetime NOT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_update`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_update

---

# tb_updates_detalle

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_update_detalle | int(11) | NO | PRI | auto_increment |
| cod_update | int(11) | NO |  |  |
| cod_empresa | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_updates_detalle` (
  `cod_update_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `cod_update` int(11) NOT NULL,
  `cod_empresa` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_update_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_update_detalle
- cod_update
- cod_empresa

---

# tb_updates_visualizado

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_update_visualizado | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| cod_update | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_updates_visualizado` (
  `cod_update_visualizado` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `cod_update` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_update_visualizado`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_update_visualizado
- cod_usuario
- cod_update

---

# tb_usuario_bloqueo

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_usuario_bloqueo | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| descripcion | text | YES |  |  |
| fecha_inicio | datetime | YES |  |  |
| fecha_fin | datetime | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_usuario_bloqueo` (
  `cod_usuario_bloqueo` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `descripcion` text COLLATE utf8_unicode_ci,
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_usuario_bloqueo`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_usuario

---

# tb_usuario_cards

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_usuario_cards | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| cod_sucursal_created | int(11) | NO |  |  |
| token | varchar(100) | YES | UNI |  |
| type | varchar(3) | YES |  |  |
| status | varchar(10) | YES |  |  |
| bin | varchar(6) | YES |  |  |
| number | varchar(4) | YES |  |  |
| reference | varchar(15) | YES |  |  |
| expiry_month | int(11) | YES |  |  |
| expiry_year | int(11) | YES |  |  |
| alias | varchar(100) | NO |  |  |
| predeterminada | int(11) | NO |  |  |
| estado | enum('A','I') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_usuario_cards` (
  `cod_usuario_cards` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `cod_sucursal_created` int(11) NOT NULL,
  `token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bin` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `number` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reference` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `expiry_month` int(11) DEFAULT NULL,
  `expiry_year` int(11) DEFAULT NULL,
  `alias` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `predeterminada` int(11) NOT NULL,
  `estado` enum('A','I') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_usuario_cards`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=236 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_usuario
- cod_sucursal_created

---

# tb_usuario_client_email

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_usuario_client_email | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| correo_user | varchar(60) | YES |  |  |
| correo_pass | varchar(20) | YES |  |  |
| imap_server | varchar(100) | YES |  |  |
| imap_port | int(11) | YES |  |  |
| smtp_server | varchar(100) | YES |  |  |
| smtp_port | int(11) | YES |  |  |
| folders | text | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_usuario_client_email` (
  `cod_usuario_client_email` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `correo_user` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correo_pass` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imap_server` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imap_port` int(11) DEFAULT NULL,
  `smtp_server` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `smtp_port` int(11) DEFAULT NULL,
  `folders` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_usuario_client_email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_usuario

---

# tb_usuario_cliente

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_usuario | int(11) | YES |  |  |
| cod_cliente | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_usuario_cliente` (
  `cod_usuario` int(11) DEFAULT NULL,
  `cod_cliente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_usuario
- cod_cliente

---

# tb_usuario_codigo_login

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_usuario_code | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| codigo | varchar(10) | YES |  |  |
| fecha_creacion | datetime | YES |  |  |
| fecha_expiracion | datetime | YES |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_usuario_codigo_login` (
  `cod_usuario_code` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `codigo` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT NULL,
  `fecha_expiracion` datetime DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_usuario_code`)
) ENGINE=InnoDB AUTO_INCREMENT=17576 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_usuario_code
- cod_usuario

---

# tb_usuario_codigo_registro

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_usuario_code | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| correo | varchar(100) | YES |  |  |
| codigo | varchar(10) | NO |  |  |
| fecha_creacion | datetime | YES |  |  |
| fecha_expiracion | datetime | YES |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_usuario_codigo_registro` (
  `cod_usuario_code` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `correo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `codigo` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `fecha_creacion` datetime DEFAULT NULL,
  `fecha_expiracion` datetime DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_usuario_code`)
) ENGINE=InnoDB AUTO_INCREMENT=78927 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_usuario_code
- cod_empresa

---

# tb_usuario_codigo_telefono

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_usuario_code | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | NO |  |  |
| codigo | varchar(6) | NO |  |  |
| fecha_creacion | datetime | NO |  |  |
| fecha_expiracion | datetime | NO |  |  |
| estado | enum('A','I') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_usuario_codigo_telefono` (
  `cod_usuario_code` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) NOT NULL,
  `codigo` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL,
  `fecha_expiracion` datetime NOT NULL,
  `estado` enum('A','I') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_usuario_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3725 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_usuario_code
- cod_usuario

---

# tb_usuario_cupon

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_usuario_cupon | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| cupon | varchar(100) | YES |  |  |
| fecha | datetime | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_usuario_cupon` (
  `cod_usuario_cupon` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `cupon` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`cod_usuario_cupon`)
) ENGINE=MyISAM AUTO_INCREMENT=71 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_usuario

---

# tb_usuario_direcciones

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_usuario_direccion | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | NO |  |  |
| nombre | varchar(50) | YES |  |  |
| direccion | varchar(150) | YES |  |  |
| referencia | varchar(200) | NO |  |  |
| latitud | varchar(15) | YES |  |  |
| longitud | varchar(15) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_usuario_direcciones` (
  `cod_usuario_direccion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `referencia` varchar(200) NOT NULL,
  `latitud` varchar(15) DEFAULT NULL,
  `longitud` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`cod_usuario_direccion`)
) ENGINE=InnoDB AUTO_INCREMENT=40452 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_usuario_direccion
- cod_usuario

---

# tb_usuario_giftcard

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_usuario_giftcard | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| cod_giftcard | int(11) | YES |  |  |
| fecha | timestamp | NO |  | on update CURRENT_TIMESTAMP |
| cod_usuario_receptor | int(11) | NO |  |  |
| fecha_utilizacion | datetime | NO |  |  |
| estado | enum('A','I','D') | NO |  |  |
| monto | float | YES |  |  |
| codigo | varchar(20) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_usuario_giftcard` (
  `cod_usuario_giftcard` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `cod_giftcard` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cod_usuario_receptor` int(11) NOT NULL,
  `fecha_utilizacion` datetime NOT NULL,
  `estado` enum('A','I','D') NOT NULL,
  `monto` float DEFAULT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`cod_usuario_giftcard`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4
```

## Posibles relaciones

- cod_usuario
- cod_giftcard
- cod_usuario_receptor

---

# tb_usuario_giftcards_compradas

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_usuario_giftcard_comprada | int(11) | NO | PRI | auto_increment |
| cod_giftcard | int(11) | YES |  |  |
| cod_usuario | int(11) | YES |  |  |
| codigo | varchar(10) | YES |  |  |
| monto | float | YES |  |  |
| nombre | varchar(150) | YES |  |  |
| correo | varchar(50) | YES |  |  |
| fecha | datetime | YES |  |  |
| estado | enum('A','I','U','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_usuario_giftcards_compradas` (
  `cod_usuario_giftcard_comprada` int(11) NOT NULL AUTO_INCREMENT,
  `cod_giftcard` int(11) DEFAULT NULL,
  `cod_usuario` int(11) DEFAULT NULL,
  `codigo` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `monto` float DEFAULT NULL,
  `nombre` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correo` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `estado` enum('A','I','U','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_usuario_giftcard_comprada`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_usuario_giftcard_comprada
- cod_giftcard
- cod_usuario

---

# tb_usuario_intento_pago

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_usuario_intento_pago | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| cod_proveedor_botonpagos | int(11) | YES |  |  |
| fecha | datetime | YES |  |  |
| monto | varchar(10) | YES |  |  |
| origen | varchar(10) | YES |  |  |
| tipo | enum('success','failure') | YES |  |  |
| fraude | int(11) | NO |  |  |
| json | text | YES |  |  |
| estado | enum('I','A') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_usuario_intento_pago` (
  `cod_usuario_intento_pago` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `cod_proveedor_botonpagos` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `monto` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `origen` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tipo` enum('success','failure') COLLATE utf8_unicode_ci DEFAULT NULL,
  `fraude` int(11) NOT NULL DEFAULT '0',
  `json` text COLLATE utf8_unicode_ci,
  `estado` enum('I','A') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_usuario_intento_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=13889 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_usuario
- cod_proveedor_botonpagos

---

# tb_usuario_keystore

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_usuario_keystore | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| clave | varchar(100) | YES |  |  |
| temporal | int(11) | YES |  |  |
| fecha_create | datetime | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_usuario_keystore` (
  `cod_usuario_keystore` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `clave` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `temporal` int(11) DEFAULT NULL,
  `fecha_create` datetime DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_usuario_keystore`)
) ENGINE=InnoDB AUTO_INCREMENT=1669 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_usuario

---

# tb_usuario_mis_giftcards

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_usuario_mis_giftcard | int(11) | NO | PRI | auto_increment |
| codigo | int(11) | YES |  |  |
| cod_usuario | int(11) | YES |  |  |
| fecha | datetime | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_usuario_mis_giftcards` (
  `cod_usuario_mis_giftcard` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` int(11) DEFAULT NULL,
  `cod_usuario` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`cod_usuario_mis_giftcard`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_usuario_mis_giftcard
- cod_usuario

---

# tb_usuario_purchase_code

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| codigo | varchar(300) | YES |  |  |
| fecha_create | datetime | YES |  |  |
| fecha_expiracion | datetime | YES |  |  |
| estado | enum('CREADO','USADO') | YES |  |  |
| cod_orden | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_usuario_purchase_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `codigo` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_create` datetime DEFAULT NULL,
  `fecha_expiracion` datetime DEFAULT NULL,
  `estado` enum('CREADO','USADO') COLLATE utf8_unicode_ci DEFAULT NULL,
  `cod_orden` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10066 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_usuario
- cod_orden

---

# tb_usuarios

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_usuario | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | YES |  |  |
| cod_rol | int(11) | YES |  |  |
| nombre | varchar(150) | YES |  |  |
| apellido | varchar(150) | NO |  |  |
| imagen | varchar(200) | NO |  |  |
| correo | varchar(100) | NO |  |  |
| usuario | varchar(50) | YES |  |  |
| password | varchar(100) | YES |  |  |
| fecha_nacimiento | date | YES |  |  |
| telefono | varchar(15) | NO |  |  |
| telefono_verificado | int(11) | NO |  |  |
| direccion | varchar(300) | YES |  |  |
| num_documento | varchar(20) | NO |  |  |
| cod_idioma | int(11) | NO |  |  |
| cod_sucursal | int(11) | NO |  |  |
| estado | enum('A','I','D') | YES |  |  |
| fecha_create | timestamp | NO |  |  |
| recuperacion_pass | int(11) | NO |  |  |
| latitud | varchar(15) | NO |  |  |
| longitud | varchar(15) | NO |  |  |
| placa | varchar(15) | NO |  |  |
| is_active | int(11) | NO |  |  |
| fecha_ubicacion | datetime | YES |  |  |
| motivo_bloqueo | text | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_usuarios` (
  `cod_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) DEFAULT NULL,
  `cod_rol` int(11) DEFAULT NULL,
  `nombre` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `apellido` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `imagen` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `correo` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `usuario` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `telefono` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `telefono_verificado` int(11) NOT NULL DEFAULT '0',
  `direccion` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `num_documento` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cod_idioma` int(11) NOT NULL DEFAULT '1',
  `cod_sucursal` int(11) NOT NULL DEFAULT '0',
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `recuperacion_pass` int(11) NOT NULL DEFAULT '0',
  `latitud` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `longitud` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `placa` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `is_active` int(11) NOT NULL DEFAULT '1',
  `fecha_ubicacion` datetime DEFAULT NULL,
  `motivo_bloqueo` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`cod_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=115740 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_usuario
- cod_empresa
- cod_rol
- cod_idioma
- cod_sucursal

---

# tb_usuarios_datos_facturacion

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_usuario_dato_facturacion | int(11) | NO | PRI | auto_increment |
| cod_usuario | int(11) | YES |  |  |
| nombre | varchar(200) | YES |  |  |
| num_documento | varchar(50) | YES |  |  |
| direccion | varchar(250) | YES |  |  |
| telefono | varchar(50) | YES |  |  |
| correo | varchar(150) | YES |  |  |
| is_extranjero | int(11) | NO |  |  |
| tipo_documento | enum('DNI','RUCN','RUCJ') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_usuarios_datos_facturacion` (
  `cod_usuario_dato_facturacion` int(11) NOT NULL AUTO_INCREMENT,
  `cod_usuario` int(11) DEFAULT NULL,
  `nombre` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `num_documento` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `direccion` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefono` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correo` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_extranjero` int(11) NOT NULL DEFAULT '1',
  `tipo_documento` enum('DNI','RUCN','RUCJ') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_usuario_dato_facturacion`)
) ENGINE=InnoDB AUTO_INCREMENT=13007 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_usuario_dato_facturacion
- cod_usuario

---

# tb_variante_caracteristica

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_variante_caracteristica | int(11) | NO | PRI | auto_increment |
| cod_producto | int(11) | YES |  |  |
| cod_caracteristica_detalle | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_variante_caracteristica` (
  `cod_variante_caracteristica` int(11) NOT NULL AUTO_INCREMENT,
  `cod_producto` int(11) DEFAULT NULL,
  `cod_caracteristica_detalle` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_variante_caracteristica`)
) ENGINE=InnoDB AUTO_INCREMENT=785 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_producto
- cod_caracteristica_detalle

---

# tb_version_web

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| title | varchar(50) | YES |  |  |
| version | varchar(30) | YES |  |  |
| descripcion | varchar(150) | YES |  |  |
| filename | varchar(60) | YES |  |  |
| fecha_creacion | date | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_version_web` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `descripcion` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `filename` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_creacion` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

---

# tb_visitantes

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_visitante | int(11) | NO | PRI | auto_increment |
| nombres | varchar(60) | YES |  |  |
| apellidos | varchar(60) | YES |  |  |
| email | varchar(60) | YES |  |  |
| celular | varchar(60) | YES |  |  |
| institucion | varchar(100) | YES |  |  |
| cod_pais | varchar(10) | YES |  |  |
| ciudad | varchar(60) | YES |  |  |
| cod_usuario | int(11) | YES |  |  |
| cod_feria | int(11) | YES |  |  |
| cod_medio | int(11) | YES |  |  |
| qr | varchar(100) | YES |  |  |
| user_create | int(11) | YES |  |  |
| fecha_create | datetime | YES |  |  |
| cod_empresa | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_visitantes` (
  `cod_visitante` int(11) NOT NULL AUTO_INCREMENT,
  `nombres` varchar(60) DEFAULT NULL,
  `apellidos` varchar(60) DEFAULT NULL,
  `email` varchar(60) DEFAULT NULL,
  `celular` varchar(60) DEFAULT NULL,
  `institucion` varchar(100) DEFAULT NULL,
  `cod_pais` varchar(10) DEFAULT NULL,
  `ciudad` varchar(60) DEFAULT NULL,
  `cod_usuario` int(11) DEFAULT NULL,
  `cod_feria` int(11) DEFAULT NULL,
  `cod_medio` int(11) DEFAULT NULL,
  `qr` varchar(100) DEFAULT NULL,
  `user_create` int(11) DEFAULT NULL,
  `fecha_create` datetime DEFAULT NULL,
  `cod_empresa` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_visitante`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_visitante
- cod_pais
- cod_usuario
- cod_feria
- cod_medio
- cod_empresa

---

# tb_web_adicionales

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_web_adicionales | int(11) | NO | PRI | auto_increment |
| cod_categoria | int(11) | YES |  |  |
| titulo | varchar(150) | YES |  |  |
| cod_categoria_items | int(11) | YES |  |  |
| posicion | int(11) | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_web_adicionales` (
  `cod_web_adicionales` int(11) NOT NULL AUTO_INCREMENT,
  `cod_categoria` int(11) DEFAULT NULL,
  `titulo` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cod_categoria_items` int(11) DEFAULT NULL,
  `posicion` int(11) DEFAULT '99',
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cod_web_adicionales`)
) ENGINE=InnoDB AUTO_INCREMENT=171 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_categoria
- cod_categoria_items

---

# tb_web_esquema

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_web_esquema | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | NO |  |  |
| titulo | varchar(100) | NO |  |  |
| forma | enum('lista_4','slide_4','banner','youtube') | NO |  |  |
| tipo | enum('ordenar','anuncios','youtube') | NO |  |  |
| plataforma | enum('WEB','APP') | NO |  |  |
| num_columnas | int(11) | NO |  |  |
| detalle | varchar(150) | NO |  |  |
| cod_detalle | int(11) | NO |  |  |
| posicion | int(11) | NO |  |  |
| fecha_create | datetime | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_web_esquema` (
  `cod_web_esquema` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) NOT NULL,
  `titulo` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `forma` enum('lista_4','slide_4','banner','youtube') COLLATE utf8_unicode_ci NOT NULL,
  `tipo` enum('ordenar','anuncios','youtube') COLLATE utf8_unicode_ci NOT NULL,
  `plataforma` enum('WEB','APP') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'WEB',
  `num_columnas` int(11) NOT NULL,
  `detalle` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `cod_detalle` int(11) NOT NULL,
  `posicion` int(11) NOT NULL,
  `fecha_create` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cod_web_esquema`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_empresa
- cod_detalle

---

# tb_web_modulos_productos

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_web_modulos_producto | int(11) | NO | PRI | auto_increment |
| nombre | varchar(300) | YES |  |  |
| cod_empresa | int(11) | YES |  |  |
| descripcion | varchar(500) | NO |  |  |
| modulo | enum('HOME','SUGERENCIAS') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_web_modulos_productos` (
  `cod_web_modulos_producto` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(300) DEFAULT NULL,
  `cod_empresa` int(11) DEFAULT NULL,
  `descripcion` varchar(500) NOT NULL,
  `modulo` enum('HOME','SUGERENCIAS') NOT NULL DEFAULT 'HOME',
  PRIMARY KEY (`cod_web_modulos_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_web_modulos_producto
- cod_empresa

---

# tb_web_modulos_productos_detalle

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_web_modulos_producto_detalle | int(11) | NO | PRI | auto_increment |
| cod_web_modulos_producto | int(11) | YES |  |  |
| cod_producto | int(11) | YES |  |  |
| posicion | int(11) | YES |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_web_modulos_productos_detalle` (
  `cod_web_modulos_producto_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `cod_web_modulos_producto` int(11) DEFAULT NULL,
  `cod_producto` int(11) DEFAULT NULL,
  `posicion` int(11) DEFAULT NULL,
  PRIMARY KEY (`cod_web_modulos_producto_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=4411 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_web_modulos_producto_detalle
- cod_web_modulos_producto
- cod_producto

---

# tb_web_servicios

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_web_servicio | int(11) | NO | PRI | auto_increment |
| cod_empresa | int(11) | NO |  |  |
| titulo | varchar(100) | YES |  |  |
| precio | double | YES |  |  |
| descripcion | text | YES |  |  |
| imagen | text | YES |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_web_servicios` (
  `cod_web_servicio` int(11) NOT NULL AUTO_INCREMENT,
  `cod_empresa` int(11) NOT NULL,
  `titulo` varchar(100) DEFAULT NULL,
  `precio` double DEFAULT NULL,
  `descripcion` text,
  `imagen` text,
  `estado` enum('A','I','D') NOT NULL,
  PRIMARY KEY (`cod_web_servicio`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1
```

## Posibles relaciones

- cod_web_servicio
- cod_empresa

---

# tb_zonas_sucursal

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_zona_sucursal | int(11) | NO | PRI | auto_increment |
| cod_sucursal | int(11) | YES |  |  |
| nombre | varchar(150) | YES |  |  |
| costo | float | YES |  |  |
| tiempo | int(11) | YES |  |  |
| estado | enum('A','I','D') | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `tb_zonas_sucursal` (
  `cod_zona_sucursal` int(11) NOT NULL AUTO_INCREMENT,
  `cod_sucursal` int(11) DEFAULT NULL,
  `nombre` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `costo` float DEFAULT NULL,
  `tiempo` int(11) DEFAULT NULL,
  `estado` enum('A','I','D') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`cod_zona_sucursal`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

## Posibles relaciones

- cod_zona_sucursal
- cod_sucursal

---

# timezones

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| id | int(11) | NO | PRI | auto_increment |
| code | varchar(30) | NO |  |  |
| timezone | varchar(499) | NO |  |  |

## SQL Structure

```sql
CREATE TABLE `timezones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `timezone` varchar(499) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=422 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
```

---

# view_asignacion_motorizado

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|

## SQL Structure

```sql
CREATE ALGORITHM=UNDEFINED DEFINER=CURRENT_USER SQL SECURITY DEFINER VIEW `view_asignacion_motorizado` AS select `oc`.`cod_orden` AS `cod_orden`,`u`.`nombre` AS `nombre`,`u`.`apellido` AS `apellido`,`u`.`cedula` AS `num_documento`,`u`.`imagen` AS `foto`,`u`.`telefono` AS `telefono`,`oc`.`cod_courier` AS `is_gacela`,`u`.`cod_usuario` AS `cod_usuario` from ((`tb_orden_cabecera` `oc` join `tb_motorizado_asignacion` `ma` on((`ma`.`cod_orden` = `oc`.`cod_orden`))) join `tb_usuarios` `u` on((`u`.`cod_usuario` = `ma`.`cod_motorizado`))) where ((`u`.`cod_rol` = 17) and (`oc`.`cod_courier` in ('0','1'))) union select `u`.`cod_orden` AS `cod_orden`,`u`.`nombre` AS `nombre`,`u`.`apellido` AS `apellido`,`u`.`num_documento` AS `num_documento`,`u`.`foto` AS `foto`,`u`.`telefono` AS `telefono`,`oc`.`cod_courier` AS `is_gacela`,0 AS `cod_usuario` from (`tb_orden_motorizado` `u` join `tb_orden_cabecera` `oc` on((`u`.`cod_orden` = `oc`.`cod_orden`)))
```

---

# vw_producto_sucursal

## Columnas

| Campo | Tipo | Null | Key | Extra |
|---|---|---|---|---|
| cod_producto | int(11) | NO |  |  |
| cod_producto_padre | int(11) | YES |  |  |
| cod_empresa | int(11) | YES |  |  |
| alias | varchar(200) | NO |  |  |
| nombre | varchar(150) | YES |  |  |
| desc_corta | text | YES |  |  |
| desc_larga | text | YES |  |  |
| image_min | varchar(500) | YES |  |  |
| image_max | varchar(500) | YES |  |  |
| agotado_inicio | datetime | YES |  |  |
| agotado_fin | datetime | YES |  |  |
| estado | enum('A','I','D') | YES |  |  |
| is_combo | int(11) | NO |  |  |
| open_detalle | int(11) | NO |  |  |
| fecha_modificacion | timestamp | NO |  |  |
| dia | int(11) | NO |  |  |
| sku | varchar(100) | NO |  |  |
| cobra_iva | int(11) | NO |  |  |
| posicion | int(11) | YES |  |  |
| peso | int(11) | NO |  |  |
| sucursal | varchar(150) | YES |  |  |
| cod_sucursal_original | int(11) | NO |  |  |
| cod_sucursal | bigint(11) | YES |  |  |
| precio_no_tax | double | YES |  |  |
| iva_valor | double | YES |  |  |
| precio | varchar(15) | YES |  |  |
| precio_anterior | double | YES |  |  |
| venta_delivery | tinyint(1) | NO |  |  |
| venta_pickup | tinyint(1) | NO |  |  |
| venta_mesa | tinyint(1) | NO |  |  |

## SQL Structure

```sql
CREATE ALGORITHM=UNDEFINED DEFINER=CURRENT_USER SQL SECURITY DEFINER VIEW `vw_producto_sucursal` AS select `p`.`cod_producto` AS `cod_producto`,`p`.`cod_producto_padre` AS `cod_producto_padre`,`p`.`cod_empresa` AS `cod_empresa`,`p`.`alias` AS `alias`,`p`.`nombre` AS `nombre`,`p`.`desc_corta` AS `desc_corta`,`p`.`desc_larga` AS `desc_larga`,`p`.`image_min` AS `image_min`,`p`.`image_max` AS `image_max`,`ps`.`agotado_inicio` AS `agotado_inicio`,`ps`.`agotado_fin` AS `agotado_fin`,`p`.`estado` AS `estado`,`p`.`is_combo` AS `is_combo`,`p`.`open_detalle` AS `open_detalle`,`p`.`fecha_modificacion` AS `fecha_modificacion`,`p`.`dia` AS `dia`,`p`.`sku` AS `sku`,`p`.`cobra_iva` AS `cobra_iva`,`p`.`posicion` AS `posicion`,`p`.`peso` AS `peso`,`s`.`nombre` AS `sucursal`,`s`.`cod_sucursal` AS `cod_sucursal_original`,coalesce(`ps`.`cod_sucursal`,0) AS `cod_sucursal`,coalesce(`ps`.`precio_no_tax`,`p`.`precio_no_tax`) AS `precio_no_tax`,coalesce(`ps`.`iva_valor`,`p`.`iva_valor`) AS `iva_valor`,coalesce(`ps`.`precio`,`p`.`precio`) AS `precio`,coalesce(`ps`.`precio_anterior`,`p`.`precio_anterior`) AS `precio_anterior`,`p`.`venta_delivery` AS `venta_delivery`,`p`.`venta_pickup` AS `venta_pickup`,`p`.`venta_mesa` AS `venta_mesa` from ((`tb_productos` `p` left join `tb_productos_sucursal` `ps` on(((`p`.`cod_producto` = `ps`.`cod_producto`) and (`ps`.`estado` = 'A')))) join `tb_sucursales` `s` on(((`s`.`cod_sucursal` = `ps`.`cod_sucursal`) and (`s`.`estado` = 'A'))))
```

## Posibles relaciones

- cod_producto
- cod_producto_padre
- cod_empresa
- cod_sucursal_original
- cod_sucursal

---

# Convenciones

- Prefijo tablas: `tb_`
- Prefijo IDs: `cod_`
- Proyecto PHP puro sin framework
- Base de datos MySQL
