# VitaSoporte

Catálogo wellness de fajas y prendas de compresión con **bolsa de compras** (varios productos, tallas y cantidades) y confirmación por WhatsApp.

## Instalación

1. Clona este repositorio dentro de la carpeta `htdocs` de XAMPP (queda la carpeta `vitasoporte`).
2. Enciende Apache y MySQL en XAMPP.
3. Importa la base de datos (ver abajo). **Si ya la habías importado, vuelve a importarla**: cambió la estructura de pedidos y categorías.

## URL local

- Público: http://localhost/vitasoporte/
- Catálogo: http://localhost/vitasoporte/catalogo.php
- Bolsa: http://localhost/vitasoporte/bolsa.php
- Admin: http://localhost/vitasoporte/admin/login.php

## Credenciales admin

- Correo: `vitasoporte4@gmail.com`
- Contraseña: `vitasoporte2008`

## Tienda física

Centro Comercial San Victorino · Punto Once (Bogotá). WhatsApp oficial: `+57 310 570 1412`.

## Importar base de datos

```bash
/Applications/XAMPP/xamppfiles/bin/mysql -u root < /Applications/XAMPP/xamppfiles/htdocs/vitasoporte/bd/vitasoporte.sql
```

O importa `bd/vitasoporte.sql` desde phpMyAdmin.

## Flujo

1. **Catálogo** → elige la **talla** (S/M/L/XL o única) de cada prenda → «Agregar a la bolsa» o «Pedir ya».
2. **Bolsa** (ícono en el menú) → ves el listado con cantidad editable por prenda, quitas ítems o agregas otros; total a la vista.
3. **Pedido** → confirmas el resumen y dejas tus datos (nombre, teléfono, dirección); la tienda ve **todo eso en su panel**.
4. **WhatsApp** → el botón abre el número oficial con el detalle del pedido (productos, tallas, cantidades y total).
5. **Admin** → bandeja con datos del cliente por pedido y opción de **eliminar pedidos**; categorías con **imagen** (subir/quitar desde el panel, se ve en el inicio).

## Diseño

Paleta nude/café: `--nude #F5E9DF`, `--champagne #E2C8B5`, `--rosa #C9A7A0`, `--cafe #493D37`, `--cafe-deep #302722`, `--cafe-mid #65564E`, `--perla #FAF6F2`, `--ink #292421`, `--muted #8A7B72`, `--line #E5DAD1`.

Las fotos de los productos son las prendas reales (recuperadas del sitio original del equipo) y cada una corresponde a su producto.

## Stack

PHP + mysqli + CSS propio + sesiones. Imágenes locales en `img/`. Sin pasarela de pago: coordinación por WhatsApp.
