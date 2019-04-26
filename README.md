# Plugin para Magento 2 (>= 2.0.5.x)

![stability-stable](https://img.shields.io/badge/stability-stable-green.svg)

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
