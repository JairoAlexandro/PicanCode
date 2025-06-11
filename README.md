# PicanCode

## Descripción

PicanCode es un proyecto basado en Symfony que ofrece:

- **Login público** para usuarios normales.  
- **Área de gestión** (`/gestion`) de acceso exclusivo para el admin `picanadmin`.  
- **CRUD completo** para Usuarios, Posts, Comentarios, Likes, Seguidores y Mensajes.  
- **Panel de gestión** con sidebar para navegar entre secciones.

---

## 📑 Tabla de contenidos

1. [Requisitos](#requisitos)  
2. [Instalación](#instalación)  
3. [Configuración de hosts (Windows)](#configuración-de-hosts-windows)  
4. [Uso](#uso)  
5. [Acceso al directorio desde Windows](#acceso-al-directorio-desde-windows)  
6. [Visualizar la base de datos](#visualizar-la-base-de-datos)  
7. [Variables de entorno](#variables-de-entorno)  
8. [Estructura de carpetas](#estructura-de-carpetas)  
9. [Tests](#tests)  
10. [Licencia](#licencia)  

---

## Requisitos

- Windows 10/11 con WSL2 (Ubuntu recomendado)  
- Docker & Devilbox (entorno Docker-based)  
- DBeaver (GUI para bases de datos)  
- PHP 8.1+  
- Composer  
- Node.js & npm  
- Symfony CLI (opcional)

---

## Instalación

1. **Configurar Devilbox en WSL**  
   \`\`\`bash
   cd ~
   git clone https://github.com/cytopia/devilbox.git
   cd devilbox
   cp env-example .env
   \`\`\`
2. **Arrancar los contenedores**  
   \`\`\`bash
   docker-compose up -d
   \`\`\`
3. **Entrar al shell de Devilbox**  
   \`\`\`bash
   ./shell.sh
   \`\`\`
4. **Clonar PicanCode**  
   \`\`\`bash
   cd /shared/httpd
   mkdir PicanCode && cd PicanCode
   git clone https://github.com/JairoAlexandro/PicanCode.git html
   \`\`\`
5. **Instalar dependencias y compilar assets**  
   \`\`\`bash
   cd html
   composer install
   npm install
   npm run dev
   \`\`\`
6. **Crear esquema de base de datos y cargar fixtures**  
   \`\`\`bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   # (Si hay errores de migraciones)
   php bin/console doctrine:schema:update --force
   php bin/console doctrine:fixtures:load
   \`\`\`
7. **Crear enlace simbólico para el virtual host**  
   \`\`\`bash
   cd /shared/httpd/PicanCode
   ln -s html/public htdocs
   ll
   \`\`\`

---

## Configuración de hosts (Windows)

1. Abre el Bloc de notas como **Administrador**.  
2. Edita \`C:\Windows\System32\drivers\etc\hosts\`.  
3. Añade al final:
   \`\`\`
   127.0.0.1 picancode.dvl.to
   \`\`\`
4. Guarda y cierra.

---

## Uso

1. Navega a [http://localhost](http://localhost).  
2. En la pestaña **VirtualHost**, selecciona **PicanCode**.  
3. Accede a las rutas públicas o al panel \`/gestion\` como admin.

---

## Acceso al directorio desde Windows

Para editar el código desde tu IDE (PHPStorm, VSCode, etc.):

\`\`\`
\\wsl.localhost\Ubuntu\home\<tuUsuario>\devilbox\data\www\PicanCode\html
\`\`\`

---

## Visualizar la base de datos

Abre DBeaver y sigue estos pasos:

1. **New Database Connection** → **MySQL** → **Next**  
2. Configura:
   - **Server Host**: \`localhost\`  
   - **Port**: \`3306\`  
   - **Database**: \`picancode\` (opcional)  
   - **Username**: \`db\`  
   - **Password**: \`db\`  
3. **Finish** para conectar.  
4. En el panel izquierdo verás \`picancode\`.  
   - Doble clic en una tabla para ver datos.  
   - Clic derecho → **View Data** / **Edit Data**.  
   - Para consultas: clic derecho → **SQL Editor** → **New SQL Script** → Escribe y **Execute** (▶️).

---

## Variables de entorno

Copia el fichero de ejemplo y ajusta tus credenciales:

\`\`\`bash
cp .env-example .env
\`\`\`

En la línea \`DATABASE_URL\` coloca tu usuario y contraseña:
\`\`\`
DATABASE_URL="mysql://root:<tu_contraseña>@127.0.0.1:3306/picancode"
\`\`\`

---

## Estructura de carpetas

\`\`\`bash
project-root/
├── assets/                   ← Frontend (JS, CSS, imágenes)
│   ├── controllers/          ← Stimulus / vanilla-JS
│   └── react/                ← App React independiente
│       └── controllers/…
├── src/                      ← Backend Symfony (PHP)
│   ├── Controller/
│   │   ├── Front/            ← Endpoints públicos
│   │   ├── SecurityController.php
│   │   ├── ProfileController.php
│   │   └── UserController.php
│   ├── Dto/
│   ├── Entity/
│   ├── Form/
│   ├── Repository/
│   ├── Security/
│   ├── Service/
│   └── Kernel.php
├── templates/                ← Vistas Twig
│   ├── base.html.twig
│   ├── security/login.html.twig
│   ├── gestion/
│   │   ├── base.html.twig
│   │   ├── panel.html.twig
│   │   └── user/…
│   ├── post/…
│   ├── registration/…
│   └── user/…
├── tests/                    ← PHPUnit & Vitest
│   └── Controller/
│       ├── Front/PostControllerTest.php
│       └── User/
│           ├── HomeControllerTest.php
│           ├── ProfileControllerTest.php
│           ├── RegistrationControllerTest.php
│           └── UserControllerTest.php
├── config/                   ← Symfony (routes, services…)
├── migrations/               ← Doctrine migrations
├── public/                   ← Document root & assets compilados
│   └── index.php
├── node_modules/
├── vite.config.js
├── vitest.config.js
├── app.js
└── bootstrap.js
```

# Tests

### 1. Backend (PHPUnit)
```bash
# Crear y preparar BD de test
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test
# (O si fallan migraciones)
php bin/console doctrine:schema:update --force --env=test

# Ejecutar tests con cobertura
./vendor/bin/phpunit --testdox
XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-text
```

![Tests Back](public/tests/Back.png)

### 2. Frontend (Vitest)
```bash
cd assets
npm run test
```

![Tests Front](public/tests/Front.png)

> **Cobertura total:** > 60%

---

## Licencia

Este proyecto está bajo **Licencia MIT**.
