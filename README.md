Plugin para Magento 2.x - ComproPago
====================================================

## Descripción
Este módulo provee el servicio de ComproPago para poder generar órdenes de pago dentro de la plataforma de e-commerce Magento.
Con ComproPago puedes recibir pagos en OXXO, 7Eleven y más tiendas en todo México.


[Registrarse en ComproPago](https://compropago.com)


## Ayuda y Soporte de ComproPago

- [Centro de ayuda y soporte](https://compropago.com/ayuda-y-soporte)
- [Solicitar integración](https://compropago.com/integracion)
- [Guía para comenzar a utilizar ComproPago](https://compropago.com/ayuda-y-soporte/como-comenzar-a-usar-compropago)
- [Información de contacto](https://compropago.com/contacto)

## Requisitos Previos
* [Magento >= 2.0.5](https://magento.com/)
* [PHP >= 7.0.8](http://www.php.net/)
* [PHP JSON extension](http://php.net/manual/en/book.json.php)
* [PHP cURL extension](http://php.net/manual/en/book.curl.php)

## Instalación:

1. Descargar el archivo zip de la última versión estable desde [aquí][Magento-Connect].
2. Descomprimir el contenido del archivo zip.
3. Copiar la carpeta **Compropago** dentro de la carpeta **app/code/** dentro de la instalación de magento.
4. Ejecutar los siguientes comandos desde el CLI de magento 2 desde la carpeta raíz de la instalación:


   ```bash
   bin/magento module:enable Compropago_Magento2
   bin/magento setup:upgrade
   bin/magento setup:di:compile
   ```
**NOTA:** En sistemas \*nix (Linux, MacOS) probablemente tenga que dar permisos de ejecución al shell de magento:

   ```
   sudo chmod +x bin/magento
   ```

5. Ingresar al panel de administración de Magento 2 en la sección **Stores / Configuration / Sales / Payment Methods** y buscar la opción **ComproPago Payment Method**
6. Llenar la configuración con los datos que se solicitan.



      - **Enabled:** Activa el plugin de ComproPago dentro de Magento.


      - **Title:** Así aparecerá el nombre del plugin dentro del checkout.


      - **Public Key:** Llave pública. Podrás obtenerla desde el panel de tu cuenta de ComproPago en el apartado de **Configuración**.
                           Es importante que introduzcas la llave pública para modo pruebas o modo activo dependiendo de cómo estés trabajando o de lo contrario degenerará en un error.


      - **Private Key:** Llave privada. Funciona de igual manera que la llave pública.


      - **Live Mode:** Modo activo. Si estás trabajando en producción selecciona "yes". Es importante que las llaves coincidan con el modo
                          correspondiente.



7. Borrar el caché de magento con el siguiente comando:

   ```bash
   bin/magento chache:flush
   ```


## ¿Cómo trabaja el módulo?
Una vez que el cliente sabe que comprar y continúa con el proceso, seleccionará la opción de elegir el método de pago.
Aquí aparecerá la opción de pago con ComproPago, selecciona el establecimiento de su conveniencia y el botón de **continuar**.

Al completar el proceso de compra dentro de la tienda, el sistema proporcionará un recibo de pago,
por lo que solo resta realizar el pago en el establecimiento que seleccionó anteriormente.

Una vez que el cliente generó su órden de pago, dentro del panel de control de ComproPago la orden se muestra como
"PENDIENTE". Sólo resta que el cliente realice el depósito a la brevedad posible.



## Documentación
### Documentación ComproPago Plugin Magento

### Documentación de ComproPago
**[API de ComproPago](https://compropago.com/documentacion/api)**

ComproPago ofrece una API tipo REST para integrar pagos en efectivo en tu comercio electrónico o tus aplicaciones.


**[General](https://compropago.com/documentacion)**

Información sobre Horarios y Comisiones, como Transferir tu dinero y la Seguridad que proporciona ComproPago.


**[Herramientas](https://compropago.com/documentacion/boton-pago)**
* Botón de pago
* Modo de pruebas/activo
* WebHooks
* Librerías y Plugins
* Shopify

[Magento-Connect]: https://github.com/compropago/plugin-magento-2/releases/tag/1.1.0
[Compropago-Panel]: https://compropago.com/panel/configuracion
[Compropago-Webhooks]: https://compropago.com/panel/webhooks
