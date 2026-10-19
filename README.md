# VitaSoporte

Catálogo wellness de fajas y prendas de compresión con pedido simple (un producto → WhatsApp).

## Instalación

1. Clona este repositorio dentro de la carpeta `htdocs` de XAMPP (queda la carpeta `vitasoporte`).
2. Enciende Apache y MySQL en XAMPP.
3. Importa la base de datos (ver abajo).

## URL local

- Público: http://localhost/vitasoporte/
- Admin: http://localhost/vitasoporte/admin/login.php

## Credenciales demo

- Correo: `admin@demo.com`
- Contraseña: `Admin123!`

## Importar base de datos

```bash
/Applications/XAMPP/xamppfiles/bin/mysql -u root < /Applications/XAMPP/xamppfiles/htdocs/vitasoporte/bd/vitasoporte.sql
```

O importa `bd/vitasoporte.sql` desde phpMyAdmin.

## Stack

PHP + mysqli + CSS propio + sesiones. Imágenes locales en `img/`. Pedido de un producto a la vez; sin carrito ni pasarela.
