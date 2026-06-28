# RefuMap - Backend

Este es el backend oficial de RefuMap/AcopioSOS, construido sobre Laravel y PostgreSQL.

## Requisitos

- PHP 8.2+
- Composer
- PostgreSQL (Neon o local)
- Node.js (opcional, si compilas vistas/correo localmente)

## Instalación Local

1. Clona este repositorio.
2. Instala dependencias:
   ```bash
   composer install
   ```
3. Copia el archivo de configuración:
   ```bash
   cp .env.example .env
   ```
4. Genera la clave de aplicación:
   ```bash
   php artisan key:generate
   ```
5. Configura tu `.env` con los datos de base de datos (`DB_CONNECTION=pgsql`, etc.).
6. Configura las variables para los usuarios de producción iniciales:
   ```env
   SEED_ADMIN_NAME="Admin RefuMap"
   SEED_ADMIN_EMAIL=admin@refumap.local
   SEED_ADMIN_PASSWORD=TuPasswordSeguro123!
   
   SEED_OPERATOR_NAME="Operador RefuMap"
   SEED_OPERATOR_EMAIL=operador@refumap.local
   SEED_OPERATOR_PASSWORD=TuPasswordSeguro123!
   ```
   **ADVERTENCIA**: Nunca uses contraseñas como `admin1234` o `password` en producción.
7. Ejecuta las migraciones y seeders:
   ```bash
   php artisan migrate:fresh --seed
   ```
8. Inicia el servidor:
   ```bash
   php artisan serve
   ```

## Producción (Laravel Cloud + Neon PostgreSQL)

1. En tu entorno de producción, asegúrate de tener configurado:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `DB_CONNECTION=pgsql`
   - `DB_SSLMODE=require` (para Neon)
2. Ejecuta el caché de configuración para optimizar:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
3. Ejecuta migraciones:
   ```bash
   php artisan migrate --force
   ```
4. Si es la primera vez, corre el seeder de base de datos (con las variables `SEED_*` correctamente configuradas):
   ```bash
   php artisan db:seed --force
   ```

## Smoke Test Post-Despliegue

Verifica que el backend esté funcionando:
```bash
php artisan migrate:status
php artisan route:list
php artisan test # Si aplica
```
