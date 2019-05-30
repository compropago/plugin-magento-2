# Plugin para Magento 2 (>= 2.0.5.x)

![stability-stable](https://img.shields.io/badge/stability-stable-green.svg)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/compropago/magento2.svg?style=flat)](https://packagist.org/packages/compropago/magento2)
[![Software License](https://img.shields.io/badge/license-APACHE-brightgreen.svg?style=flat)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/compropago/magento2.svg?style=flat)](https://packagist.org/packages/compropago/magento2)

## Descripción

Este módulo permite la integración del servicio de ComproPago en tu eCommerce de Magento.
Con ComproPago puede recibir pagos vía SPEI y en efectivo.

- [Registrarse en ComproPago](https://compropago.com/)
- [Comisiones](https://www.compropago.com/comisiones/)

## Ayuda

- [Centro de ayuda y soporte](https://compropago.com/ayuda-y-soporte)
- [Solicitar integración](https://compropago.com/integracion)
- [Guía para empezar a usar ComproPago](https://compropago.com/ayuda-y-soporte/como-comenzar-a-usar-compropago)
- [Información de contacto](https://compropago.com/contacto)

## Requerimientos

- [Magento >= 2.0.5](https://magento.com/)
- [PHP >= 7.0.8](http://www.php.net/)
- [PHP JSON extension](http://php.net/manual/en/book.json.php)
- [PHP cURL extension](http://php.net/manual/en/book.curl.php)
- [Composer](https://getcomposer.org)

## Instalación

Por favor revisar la siguiente [documentación](http://demo.compropago.com/plugins/magento2).

Debe tener accessos a su consola de sistema para ejecutar los siguientes comandos:

- Posicionarse dentro de la carpeta de instalacion de Magento2:

  ```bash
  cd path/to/magento2
  ```

- Descargar el modulo de ComproPago para Magento2 via Composer.

  - Desactivar la restricción de repositorios de la instalación de Magento2
    eliminando las siguientes lineas del archivo **composer.json** en la raiz
    de la instalación de Magento2:
    ```json
    {
        "repositories": [
            {
                "type": "composer",
                "url": "https://repo.magento.com/"
            }
        ]
    }
    ```

  - Descargar la ultima versión del modulo mediante Composer:
    ```bash
    composer require compropago/magento2 && composer -o dumpautoload
    ```

- Activar el modulo de ComproPago en Magento2:

  ```bash
  php bin/magento module:enable Compropago_Magento2
  ```

- Actualizar la injección de dependencias en magento:

  ```bash
  php bin/magento setup:upgrade
  ```

- Ejecutar la limpieza de cache del sistema:

  ```bash
  php bin/magento cache:flush
  ```

- Configurar el modulo dentro del panel de administración de magento en la
  sección **Stores > Configuration > Sales > Payment Methods > Other Payments**.
  
  ## Documentación

**[API de ComproPago](https://compropago.com/documentacion/api)**

ComproPago te ofrece un API REST para integrar pagos en efectivo en tu comercio electrónico o aplicaciones.

**[General](https://compropago.com/documentacion)**

Información de comisiones y horarios, como transferir tu dinero y la seguridad que proporciona ComproPago.

**[Otras formas de integración](https://compropago.com/soluciones/)**

- [Botón de pago](https://compropago.com/documentacion/boton-pago)
- [Librerías > sección SDK](http://demo.compropago.com/)
- [Plugins > sección Plugins](http://demo.compropago.com/)

## Soporte

En caso de tener alguna pregunta o requerir el apoyo técnico, por favor contactar al correo: **soporte@compropago.com**, proporcionando la siguiente información:

- Nombre completo (Propietario de la cuenta).
- Url del sitio web de la tienda.
- Teléfono local y celular.
- Correo electrónico del propietario de la cuenta.
- Texto detallado de la duda o requerimiento.
- En caso de presentar algún problema técnico, por favor enviar capturas de pantalla o evidencia para una respuesta más rápida.

