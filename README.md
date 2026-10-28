# Nifty Agency — sitio + CRM (Fase 1)

Sitio nuevo de nifty-agency.com en PHP + MySQL, sin WordPress. Se publica en **Vercel** (runtime `vercel-php`) con la base MySQL en Hostinger. También funciona en un hosting PHP tradicional con `config.php`.

## Qué incluye esta fase

**Sitio público**
- `/` Home renovada (todo el contenido en HTML real: lo leen Google y los motores de IA).
- `/servicios` Los 6 servicios con anclas (`#performance`, `#seo-geo`, etc.) y preguntas frecuentes.
- `/la-fabrica` Video grande + sección **Sistema para peluquerías** + sección **LMS (Eunoia Campus)** + desarrollos a medida + formulario.
- `/casos-de-exito` y `/casos-de-exito/{slug}` Eunoia Campus, Dar Grieco, Thermomix, ACO Workspace, BTM, CMG Hair Studio y Dut Mora.
- `/diagnostico-ia` **Diagnóstico IA-friendly**: analiza el sitio real (acceso de 8 bots de IA en robots.txt, contenido sin JavaScript, datos estructurados, llms.txt, sitemap, títulos, señales de confianza) + cuestionario de reputación. Puntaje 0-100 y recomendaciones.
- `/plan-de-pauta` **Plan de pauta de prueba**: brief → mix de canales, montos, estructura de campañas, embudo, estimaciones, prerrequisitos y hoja de ruta de 90 días.
- `/contacto`, `/soporte`, `/privacidad`, `robots.txt`, `llms.txt` y `sitemap.xml` dinámicos.
- Redirecciones 301 desde las URLs del WordPress (`/performance`, `/contenido`, `/seo-y-geo`, la página de peluquerías, etc.).

**Cada formulario** crea un lead en el CRM, envía el resultado al cliente con el diseño de Nifty y avisa al equipo (diana, pedro, francisco, andreina) con botones de CRM, WhatsApp y respuesta.

**Backend `/admin`**
- Dashboard: leads por día, por origen, embudo, puntaje IA promedio, pauta declarada.
- Leads: filtros, exportar CSV, ficha con diagnóstico/plan, estado, responsable, notas, línea de tiempo y envío de correos (con copia al equipo y botón de agenda).
- Casos de éxito: alta/edición, fotos (principal, destacada y galería), reseñas vinculadas y **generación de títulos, tarjetas de datos y bloques con IA** a partir de los hechos.
- Reseñas, Ajustes (correos del equipo, WhatsApp, agenda, videos, tipo de cambio, GTM) y Usuarios.

## Publicación en Vercel

1. **Base de datos (Vercel Storage):** en el proyecto → pestaña **Storage** → **Create Database** → **Neon (Serverless Postgres)**, plan gratis, región São Paulo si está disponible. Conectala al proyecto `nifty`: Vercel agrega solo `DATABASE_URL` y las demás variables de Postgres.
2. **Variables de entorno** (Settings → Environment Variables, entorno Production):
   - `SMTP_USER`, `SMTP_PASS` (contraseña de aplicación de Google Workspace), opcional `SMTP_FROM`, `SMTP_FROM_NAME`
   - `APP_KEY` (cadena larga aleatoria), opcional `BASE_URL` y `ANTHROPIC_API_KEY`
3. **Redeploy** y entrar a `/admin/setup` (crea las tablas, carga los casos y el primer administrador).
4. Dominio: en Vercel → Domains agregar `nifty-agency.com` cuando esté listo para reemplazar el WordPress.

Sin base conectada, el sitio público igual se ve en **modo vista previa**: formularios y admin muestran un aviso hasta conectarla. Las sesiones y las fotos subidas desde el admin se guardan en la base (tablas `sessions` y `media`); límite de 4 MB por foto.

El código también funciona con MySQL (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) o con `config.php` en un hosting PHP tradicional.

## Instalación en hosting PHP tradicional (alternativa)

1. Crear la base MySQL en hPanel (anotar host, base, usuario y clave).
2. Conectar el repo de GitHub al dominio (Git → deploy en `public_html`).
3. En el servidor, copiar `config.sample.php` como **`config.php`** y completarlo:
   - `db`: datos de la base.
   - `smtp`: casilla de Google Workspace + **contraseña de aplicación** (Cuenta de Google → Seguridad → Contraseñas de aplicaciones).
   - `anthropic.api_key` (opcional): activa la generación de casos con IA y las lecturas personalizadas de los diagnósticos.
   - `app_key`: una cadena larga aleatoria.
4. Entrar a **`/admin/setup`**: crea las tablas, carga los 7 casos y las reseñas, y el primer administrador.
5. En **Admin → Ajustes**: cargar el WhatsApp de Nifty y la URL del video de La Fábrica.
6. Subir el logo a `assets/img/nifty-logo.png` (mientras tanto se usa el del WordPress).
7. Copiar las imágenes que hoy están en `wp-content/uploads` (fotos del equipo y capturas de Eunoia) antes de dar de baja el WordPress, o reemplazarlas desde el admin.

Sin SMTP configurado los correos se guardan en `storage/mail/` (modo prueba), útil para revisar antes de salir en vivo.

## Probar en local

```bash
cp config.sample.php config.php   # usar dsn sqlite:/ruta/test.sqlite para probar sin MySQL
php app/cli.php migrate && php app/cli.php seed
php app/cli.php user "Diana Ponce" diana@nifty-agency.com "una-clave-larga" admin
php -S localhost:8000 index.php
```

## Estructura

```
index.php            enrutador
app/bootstrap.php    config, DB, sesión, seguridad, helpers
app/controllers/     public.php · tools.php · admin.php
app/lib/             ai_audit.php (diagnóstico IA) · ad_plan.php (plan de pauta) · claude.php · icons.php · PHPMailer
app/schema.php       tablas (MySQL y SQLite) · app/seed.php contenido inicial
views/               plantillas (layouts, páginas, herramientas, admin)
assets/              css y js
uploads/             fotos subidas desde el admin
```

## Próximas fases

- **Fase 2 — CRM comercial y clientes:** propuestas desde el backend con el look del sitio (envío al cliente con copia al equipo, aprobación con firma y forma de pago), migración de las propuestas de WordPress, conversión automática a cliente al aprobarse, ficha de cliente con su ecosistema (GA4, GTM, Ads, accesos, usuarios clave), fee mensual, correos con registro de respuestas (IMAP de Workspace) y acceso con usuario cliente.
- **Fase 3 — Operación:** pestaña de paid media mensual (estrategia, presupuestos por mes y canal, reportes) con integración a la API de Master Metrics, tickets de soporte migrados del plugin actual con su panel de estadísticas, e integración con el sistema administrativo.
