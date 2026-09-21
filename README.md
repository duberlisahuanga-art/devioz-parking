# Devioz Parking

Guía rápida de la estructura modular del proyecto. La aplicación está construida con Laravel 13, Vue 3, Inertia y Vite.

> **Estado actual:** este repositorio parte del starter kit de Laravel. Las carpetas y entidades específicas de parking (`Parking`, `Space`, `Reservation`, `Vehicle`, `Payment`, etc.) todavía deben incorporarse cuando se implemente ese dominio.

## Mapa modular

```text
 devioz-parking/
 |
 +-- BACKEND ........................ lógica del servidor
 |   +-- app/
 |   |   +-- Models/ ................ modelos Eloquent (actual: User)
 |   |   +-- Http/Controllers/ ....... controladores web/API
 |   |   +-- Services/ ............... servicios reutilizables (por añadir)
 |   |   +-- Policies/ ............... autorización por roles (por añadir)
 |   |   +-- Actions/ ................ acciones de aplicación
 |   |   +-- Providers/ .............. registro y configuración de servicios
 |   |   +-- Concerns/ ............... reglas compartidas
 |   +-- routes/
 |   |   +-- web.php ................. rutas web/Inertia (actual)
 |   |   +-- settings.php ........... rutas de configuración de usuario
 |   |   +-- console.php ............. comandos Artisan
 |   |   +-- api.php ................. reservado para API (aún no existe)
 |   +-- config/ ..................... configuración de Laravel y paquetes
 |
 +-- FRONTEND  Vue 3 + Inertia ...... interfaz de usuario
 |   +-- resources/js/
 |   |   +-- pages/ .................. páginas Vue (actual: Welcome, Dashboard, Auth, Settings)
 |   |   +-- components/ ............. componentes reutilizables
 |   |   +-- layouts/ ................ layouts de aplicación, autenticación y ajustes
 |   |   +-- composables/ ............. lógica reactiva compartida
 |   |   +-- lib/ .................... utilidades del frontend
 |   |   +-- types/ .................. tipos TypeScript
 |   |   +-- app.ts .................. entrada de Inertia
 |   +-- resources/css/ .............. estilos Tailwind
 |
 +-- DATABASE  estructura de datos ... persistencia y datos de prueba
     +-- database/
         +-- migrations/ ............. creación y cambios de tablas
         +-- seeders/ ................ datos iniciales o de prueba
         +-- factories/ .............. generadores de datos falsos
```

## Resumen de carpetas

| Módulo | Carpeta | Responsabilidad |
| --- | --- | --- |
| Backend | `app/Models/` | Modelos Eloquent y relaciones con la base de datos. Aquí vivirían `User`, `Parking`, `Space`, `Reservation`, `Vehicle` y `Payment`. |
| Backend | `app/Http/Controllers/` | Reciben solicitudes, validan el flujo y devuelven respuestas web, Inertia o JSON. |
| Backend | `app/Services/` | Lógica de negocio reutilizable que no debe quedar concentrada en controladores. **Aún no existe.** |
| Backend | `app/Policies/` | Reglas de autorización para determinar qué puede hacer cada usuario o rol. **Aún no existe.** |
| Backend | `routes/` | Define las entradas de la aplicación: web, API cuando se habilite, ajustes y consola. |
| Backend | `config/` | Configuración de Laravel, autenticación, base de datos, correo, colas, sesiones y paquetes. |
| Frontend | `resources/js/pages/` | Páginas completas renderizadas por Inertia. En este repositorio la carpeta real está en minúscula. |
| Frontend | `resources/js/components/` | Componentes Vue reutilizables, como botones, modales, tablas y controles de formulario. |
| Frontend | `resources/js/layouts/` | Estructuras compartidas de las páginas, como `AppLayout`, `AuthLayout` y ajustes. |
| Frontend | `resources/js/composables/` | Composables Vue para compartir estado y comportamiento reactivo, por ejemplo apariencia o autenticación. |
| Frontend | `resources/css/` | Estilos globales y configuración de Tailwind CSS. |
| Database | `database/migrations/` | Versionado de la estructura de tablas. Las tablas de parking se añadirían aquí. |
| Database | `database/seeders/` | Datos base o de demostración, como `DatabaseSeeder` y futuros `ParkingSeeder`. |
| Database | `database/factories/` | Fábricas para generar usuarios y datos falsos en pruebas y desarrollo. |

## Flujo entre módulos

```text
Navegador
   |
   v
routes/web.php o routes/api.php
   |
   v
Controller -> Service -> Model Eloquent -> Database
   |
   +--> respuesta Inertia/JSON
             |
             v
resources/js/pages + components + layouts
```

## Comandos clave con Sail

Sustituye `sail` por `./vendor/bin/sail` si el alias no está configurado.

### Arranque común

```bash
sail up -d
sail down
sail artisan migrate
sail artisan migrate:fresh --seed
```

### Backend

```bash
sail artisan make:model Parking -m
sail artisan make:controller ParkingController
sail artisan make:controller Api/ParkingController --api
sail artisan make:policy ParkingPolicy --model=Parking
sail artisan route:list
sail artisan test
sail composer run lint
sail composer run types:check
```

`app/Services/` no tiene un generador Artisan dedicado: crea allí una clase de servicio siguiendo las convenciones del proyecto.

### Frontend

```bash
sail npm install
sail npm run dev
sail npm run build
sail npm run check
sail npm run types:check
```

### Database

```bash
sail artisan make:migration create_parkings_table
sail artisan make:seeder ParkingSeeder
sail artisan make:factory ParkingFactory --model=Parking
sail artisan migrate
sail artisan db:seed --class=ParkingSeeder
```

## Documentación oficial

## CCTV

El panel de monitoreo admin está disponible en `/admin/cameras`. En el MVP las cámaras muestran estado online/offline y un placeholder cuando no tienen `stream_url`; el botón de prueba alterna el estado de forma simulada.

Para producción, los streams RTSP deben convertirse a HLS mediante un servidor compatible, como MediaMTX. La aplicación debe recibir la URL HLS generada en `cameras.stream_url` y servirla con HTTPS para que el navegador pueda reproducirla.

La webcam local funciona en `http://localhost` porque los navegadores consideran localhost un contexto seguro. Si se accede mediante una IP o dominio por HTTP y `getUserMedia` es bloqueado, usa HTTPS con un certificado local (por ejemplo, mkcert) o habilita temporalmente el origen en la configuración de desarrollo de Chrome; no se recomienda ese flag en producción.

- [Laravel](https://laravel.com/docs)
- [Laravel Sail](https://laravel.com/docs/sail)
- [Laravel Routing](https://laravel.com/docs/routing)
- [Laravel Controllers](https://laravel.com/docs/controllers)
- [Laravel Eloquent](https://laravel.com/docs/eloquent)
- [Laravel Authorization y Policies](https://laravel.com/docs/authorization)
- [Laravel Migrations](https://laravel.com/docs/migrations)
- [Laravel Seeding](https://laravel.com/docs/seeding)
- [Laravel Factories](https://laravel.com/docs/eloquent-factories)
- [Vue 3](https://vuejs.org/guide/introduction.html)
- [Inertia.js para Vue](https://inertiajs.com/vue)
- [Vite](https://vite.dev/guide/)
- [Tailwind CSS](https://tailwindcss.com/docs)
