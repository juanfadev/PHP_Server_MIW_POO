# PHP Server

Desplegado en: https://miwpoophp.herokuapp.com/

Caracteristicas a tener en cuenta:
* Proyecto preparado para PHPUnit
* Uso de Composer
* Leer/Escribir JSON
* Manual parse JSON a object
* Negociación de contenido (JSON/text-plain/text-html)
* Script JSON-LD en cabecera de HTML bien formado.

Algunos de los endpoint devuelven únicamente JSON (lista de entidades). Sin embargo, todas las entidades (Ejemplo: https://miwpoophp.herokuapp.com/Places/1) devuelven un JSON o un HTML bien formado con un JSON-LD en la cabecera.

Se valida que el @type sea correcto, y sea un JSON válido, sino no se puede escribir. El resto de validación de campos se hace contra el servicio de Google desde el cliente.
