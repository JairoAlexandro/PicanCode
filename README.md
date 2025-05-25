# PicanCode

## Descripción

PicanCode es un proyecto en Symfony que incluye:

* Login público para usuarios normales.
* Área de gestión bajo `/gestion` con acceso exclusivo para un único admin (`picanadmin`).
* CRUD completo para Usuarios, Posts, Comentarios, Likes, Seguidores y Mensajes.
* Panel de gestión con sidebar para navegar entre secciones.

## Requisitos

* Windows 10/11 con WSL2 instalado.
* Distribución Linux (Ubuntu recomendado) en WSL.
* Docker
* Dbeaver
* Devilbox (Docker-based env) clonado en tu usuario de WSL.
* PHP 8.1+
* Composer
* Node.js & npm para assets
* Symfony CLI (opcional)

## Instalación

1. **Configurar Devilbox en WSL**
   Clona Devilbox en tu directorio de usuario:

   ```bash
   cd ~
   git clone https://github.com/cytopia/devilbox.git
   cd devilbox
   cp env-example .env
   ```

2. **Arrancar Devilbox**
   Inicia los contenedores en segundo plano:

   ```bash
   docker-compose up -d
   ```

3. **Entrar al shell de Devilbox**

   ```bash
   ./shell.sh
   ```

4. **Clonar PicanCode dentro de Devilbox**

   ```bash
   cd /shared/httpd
   mkdir PicanCode
   cd PicanCode
   git clone https://github.com/JairoAlexandro/PicanCode.git html
   ```

5. **Instalar dependencias y assets**

   ```bash
   cd html
   composer install
   ```

   ```bash
   npm install
   npm run dev

   ````

6. **Crear esquema de base de datos y cargar fixtures**  
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   php bin/console doctrine:fixtures:load
   ````

7. **Crear enlace simbólico para Devilbox**

   ```bash
   cd /shared/httpd/PicanCode
   # Ver directorio html
   ls html/
   # Crear enlace simbólico entre html/public y htdocs
   ln -s html/public htdocs
   # Verificar
   ll
   ```

## Uso

Después de crear el enlace simbólico:

1. Abre tu navegador y ve a [http://localhost](http://localhost).
2. Haz click en la pestaña **VirtualHost**.
3. Selecciona tu proyecto **PicanCode** para verlo en el navegador.

## Configuración de hosts en Windows

En Windows, edita el archivo `hosts` para mapear tu proyecto:

1. Abre el Bloc de notas (u otro editor) con permisos de administrador.

2. Abre el fichero:

   ```
   C:\Windows\System32\drivers\etc\hosts
   ```

3. Añade al final:

   ```
   127.0.0.1 picancode.dvl.to
   ```

4. Guarda los cambios.

## Acceso al directorio desde Windows

Para abrir los archivos de tu proyecto en un IDE (por ejemplo, PHPStorm), navega a:

```
//wsl.localhost/Ubuntu/home/<tuUsuario>/devilbox/data/www/
```

## Estructura de carpetas

```
src/
├── Controller/
│   ├── SecurityController.php
│   ├── ProfileController.php
│   ├── UserController.php      ← CRUD bajo /gestion/user
│   └── ...
└── templates/
    ├── base.html.twig          ← layout general
    ├── security/login.html.twig
    └── gestion/
        ├── base.html.twig      ← layout con sidebar
        ├── panel.html.twig     ← panel de gestión
        └── user/…              ← plantillas CRUD
```
## Visualizar la base de datos

Para explorar la base de datos desde tu equipo, puedes usar DBeaver:

Abre DBeaver.

En el panel izquierdo, haz clic derecho y selecciona Nueva conexión.

Elige MySQL como tipo de conexión.

En Server Host, asegúrate de que aparece localhost (ya viene por defecto).

Haz clic en Siguiente o Conectar.

Introduce las credenciales (usuario: db, contraseña: db en Devilbox) si se solicitan.

Conéctate y verás tu base de datos picancode en el árbol de la izquierda.

Haz doble clic sobre cualquier tabla para ver sus datos o haz clic derecho y selecciona Editar datos o Ver SQL para ejecutar consultas.

## Funcionar el proyecto
En el .env-example esta todo lo necesario para que creeis el .env, simplemente copiad y pegadlo, en en database url, en la parte de "root:" root puede ser vuestro usuario y despues de los : ahí iría vuestra contraseña, pero por defecto se usa root: y sin contraseña.

## Test

1. Para ejecutar los test del back, lo primero es tener una copia de la base de datos llamada "picancode_test" la cual la puedes crear con los siguientes comandos:

   ```bash
   # crea la BD de test
   php bin/console doctrine:database:create --env=test

   # las migraciones
   php bin/console doctrine:migrations:migrate --env=test

   # En caso de que las migraciones te den fallo, usa este comando
   php bin/console doctrine:schema:update --force --env=test
   ```

 Despues tienes que entrar en el shell de devilbox, estar en la ruta /shared/httpd/PicanCode/html y ejecutar el siguiente comando:

   ```bash
   ./vendor/bin/phpunit --testdox
   ```

 Puede que tengas que retocar el .env.test para la base de datos de los test, pero como esta ya puesto deberia bastar.

2. Para ejecutar los test del front, tienes que entrar en el shell de devilbox y estar en la ruta /shared/httpd/PicanCode/html/assets y ejecutar el siguiente comando:

   ```bash
   npm run test
   ```

## Licencia

Licencia MIT. ¡A tope con el código! 🚀
