Plugin para Magento 2.x - ComproPago
====================================================

## Descripción
Este modulo provee el servicio de ComproPago para poder generar intenciones de pago dentro de la plataforma Magento.
Con ComproPago puede recibir pagos en OXXO, 7Eleven y muchas tiendas más en todo México.
[Registrarse en ComproPago](https://compropago.com)


## Ayuda y Soporte de ComproPago

- [Centro de ayuda y soporte](https://compropago.com/ayuda-y-soporte)
- [Solicitar Integración](https://compropago.com/integracion)
- [Guía para Empezar a usar ComproPago](https://compropago.com/ayuda-y-soporte/como-comenzar-a-usar-compropago)
- [Información de Contacto](https://compropago.com/contacto)

## Requerimientos
* [Magento 1.7.x, 1.8.x, 1.9.x](https://magento.com/)
* [PHP >= 5.5](http://www.php.net/)
* [PHP JSON extension](http://php.net/manual/en/book.json.php)
* [PHP cURL extension](http://php.net/manual/en/book.curl.php)

## Instalación:

1. Descargar el archivo zip de la ultima vercion estable desde aquí desde [aquí][Magento-Connect]
2. Descomprimir el contenido del archivo zip
3. Copiar la carpeta **Compropago** que resulto de descomprimir el archivo zip, dentro de la carpeta **app/code/** de su instalacion de magento
4. Ejecutar los siguientes comandos desde el CLI de magento 2:


   ```bash
   bin/magento module:enable Compropago_Magento2
   bin/magento setup:upgrade
   bin/magento setup:di:compile
   ```
5. Ingresar al panel de administración de Magento 2 en la seccion **Stores / Configuration / Sales / Payment Methods** y buscar la pestañe **ComproPago Payment Method**
6. Llenaer la configuracion con los datos que se solicitan
7. Borrar el cache de magento con el siguiente comando:

   ```bash
   bin/magento chache:flush
   ```


## ¿Cómo trabaja el modulo?
Una vez que el cliente sabe que comprar y continua con el proceso de compra entrará a la opción de elegir metodo de pago
justo aqui aparece la opción de pagar con ComproPago, seleccionamos el establecimiento de nuestra conveniencia y le
damos continuar

Al completar el proceso de compra dentro de la tienda el sistema nos proporcionara un recibo de pago como el siguiente,
solo falta realizar el pago en el establecimiento que seleccionamos.

Una vez que el cliente genero su intención de pago, dentro del panel de control de ComproPago la orden se muestra como
"PENDIENTE" esto significa que el usuario esta por ir a hacer el deposito.



## Documentación
### Documentación ComproPago Plugin Magento

### Documentación de ComproPago
**[API de ComproPago](https://compropago.com/documentacion/api)**

ComproPago te ofrece un API tipo REST para integrar pagos en efectivo en tu comercio electrónico o tus aplicaciones.


**[General](https://compropago.com/documentacion)**

Información de Comisiones y Horarios, como Transferir tu dinero y la Seguridad que proporciona ComproPAgo


**[Herramientas](https://compropago.com/documentacion/boton-pago)**
* Botón de pago
* Modo de pruebas/activo
* WebHooks
* Librerías y Plugins
* Shopify

[Magento-Connect]: https://github.com/compropago/plugin-magento-2/releases/tag/1.1.0
[Compropago-Panel]: https://compropago.com/panel/configuracion
[Compropago-Webhooks]: https://compropago.com/panel/webhooks
