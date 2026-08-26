# JasHei - Base de inicio de sesión

Este repositorio contiene una base mínima para iniciar un sistema médico con:

- Pantalla de inicio de sesión (`public/index.php`)
- Panel autenticado (`public/dashboard.php`)
- Cierre de sesión (`public/logout.php`)
- Esquema SQL con tabla `users` y usuario administrador inicial (`sql/schema.sql`)

## Credenciales iniciales

- Usuario: `admin@jashei.local`
- Contraseña: `Admin123*`

## Configuración rápida

1. Crear base de datos y tabla:
   ```bash
   mysql -u root -p < sql/schema.sql
   ```
2. Configurar variables de entorno para conexión:
   - `DB_HOST`
   - `DB_PORT`
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASS`
3. Levantar servidor PHP en la raíz del proyecto:
   ```bash
   php -S 0.0.0.0:8000 -t public
   ```
