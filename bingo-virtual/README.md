# 🎰 BINGO VIRTUAL

Sistema completo de Bingo en línea desarrollado con PHP, MySQL, HTML, CSS y JavaScript puro.

## 📋 Características Principales

### 🎯 Para Administradores
- ✅ Crear y gestionar sorteos
- ✅ Generación automática de cartones (hasta 100)
- ✅ Configuración de premios personalizables
- ✅ Confirmación manual de pagos
- ✅ Sorteo en vivo con dos modos:
  - **Modo Manual**: Ingresar o extraer números manualmente
  - **Modo Automático**: Extracción automática cada X segundos
- ✅ Narrador de voz con Web Speech API
- ✅ Validación automática de reclamos de BINGO
- ✅ Panel de control en tiempo real
- ✅ Gestión de ganadores

### 👥 Para Jugadores
- ✅ Registro e inicio de sesión
- ✅ Ver sorteos disponibles
- ✅ Seleccionar y reservar cartones
- ✅ Visualización de cartón virtual interactivo
- ✅ Marcado automático de números
- ✅ Cantar BINGO y reclamar premios
- ✅ Notificaciones en tiempo real
- ✅ Efectos visuales (confetti, animaciones)

### 🎨 Diseño y Experiencia
- ✅ Tema de casino (negro, dorado, azul eléctrico)
- ✅ Animaciones CSS suaves
- ✅ Bolitas numeradas animadas
- ✅ Responsive y mobile-friendly
- ✅ Efectos de sonido y narración
- ✅ Confetti al ganar

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP (POO)
- **Frontend**: HTML5, CSS3, JavaScript ES6
- **Base de Datos**: MySQL 5.7+
- **APIs**: Web Speech API para narración

## 📦 Requisitos del Sistema

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Navegador web moderno con soporte para:
  - Web Speech API
  - Canvas API
  - ES6 JavaScript

## 🚀 Instalación

### Paso 1: Clonar o Copiar el Proyecto

```bash
# Si tienes git
git clone <url-del-repositorio>

# O copiar la carpeta bingo-virtual a tu servidor web
cp -r bingo-virtual /var/www/html/
# O para XAMPP/WAMP
cp -r bingo-virtual C:/xampp/htdocs/
```

### Paso 2: Configurar la Base de Datos

1. Accede a phpMyAdmin (http://localhost/phpmyadmin)

2. Crea una nueva base de datos:
   ```sql
   CREATE DATABASE bingo_virtual CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. Importa el schema:
   - Selecciona la base de datos `bingo_virtual`
   - Ve a la pestaña "Importar"
   - Selecciona el archivo `database/schema.sql`
   - Haz clic en "Continuar"

   **O ejecuta el archivo SQL manualmente:**
   ```bash
   mysql -u root -p bingo_virtual < database/schema.sql
   ```

### Paso 3: Configurar la Conexión

Edita el archivo `includes/config.php` y ajusta los parámetros de conexión:

```php
define('DB_HOST', 'localhost');      // Host de la BD
define('DB_NAME', 'bingo_virtual');  // Nombre de la BD
define('DB_USER', 'root');           // Usuario de MySQL
define('DB_PASS', '');               // Contraseña de MySQL
```

### Paso 4: Configurar Permisos (Linux/Mac)

```bash
chmod -R 755 bingo-virtual/
chmod -R 777 bingo-virtual/logs/
```

### Paso 5: Acceder a la Aplicación

Abre tu navegador y accede a:
```
http://localhost/bingo-virtual/
```

## 👤 Credenciales por Defecto

### Administrador
- **Email**: admin@bingo.com
- **Contraseña**: password

**⚠️ IMPORTANTE**: Cambia la contraseña del administrador después de la primera instalación.

### Jugadores
Los jugadores pueden registrarse desde la página de login.

## 📖 Guía de Uso

### Para Administradores

#### 1. Crear un Sorteo
1. Accede con las credenciales de admin
2. Click en "Crear Nuevo Sorteo"
3. Completa el formulario:
   - Nombre del sorteo
   - Fecha y hora
   - Cantidad de cartones (1-100)
   - Precio por cartón
   - Premios (tipo y descripción)
   - Reglas del juego
4. Click en "Crear Sorteo y Generar Cartones"
5. Los cartones se generarán automáticamente

#### 2. Gestionar Pagos
1. Ve a "Pagos Pendientes"
2. Revisa las solicitudes de los jugadores
3. Confirma o rechaza los pagos
4. Al confirmar, el cartón se activa automáticamente

#### 3. Iniciar un Sorteo
1. Ve a "Ver Sorteos"
2. Click en "Iniciar Sorteo" en el sorteo programado
3. Selecciona el modo de extracción:
   - **Manual**: Click en "Sacar Siguiente Número" o ingresa uno específico
   - **Automático**: Establece intervalo y click en "Iniciar Automático"
4. Activa/desactiva el narrador según prefieras
5. Revisa los reclamos de BINGO que lleguen
6. Valida o rechaza los reclamos
7. Finaliza el sorteo cuando termines

### Para Jugadores

#### 1. Registrarse
1. En la página de login, click en "Registrarse"
2. Completa tus datos:
   - Nombre completo
   - WhatsApp
   - Email
   - Contraseña
3. Click en "Crear Cuenta"

#### 2. Reservar un Cartón
1. Inicia sesión
2. Selecciona un sorteo disponible
3. Click en "Seleccionar Cartón"
4. Elige un cartón (click para ver preview)
5. Completa:
   - Método de pago
   - Notas (opcional)
6. Click en "Reservar Este Cartón"
7. Espera la confirmación del administrador

#### 3. Jugar
1. Cuando el sorteo esté en vivo y tu pago confirmado, verás "¡JUGAR AHORA!"
2. Click en "¡JUGAR AHORA!" en tu cartón
3. Los números que salen se marcarán automáticamente
4. También puedes hacer click en los números para marcarlos manualmente
5. Cuando completes una figura, click en "¡CANTAR BINGO!"
6. Selecciona el tipo de premio que reclamas
7. El sistema validará automáticamente
8. Si ganas, ¡verás confetti y un mensaje de felicitación! 🎉

## 🎮 Tipos de Premios

- **Línea Horizontal**: Completar cualquier fila horizontal
- **Línea Vertical**: Completar cualquier columna vertical
- **Diagonal**: Completar una diagonal completa
- **X**: Completar ambas diagonales
- **L**: Completar la primera columna y la última fila
- **Cuadro Central**: Completar el cuadro 3x3 del centro
- **Cuadro Externo**: Completar todo el borde externo
- **Cartón Lleno**: Completar todo el cartón

## 🔧 Configuración Avanzada

### Cambiar Zona Horaria

Edita `includes/config.php`:
```php
date_default_timezone_set('America/Bogota'); // Cambia según tu zona
```

### Ajustar Tiempo de Sesión

Edita `includes/config.php`:
```php
define('SESSION_TIMEOUT', 1800); // Tiempo en segundos (30 minutos)
```

### Personalizar Colores

Edita `css/styles.css` y modifica las variables CSS:
```css
:root {
    --color-primary: #FFD700;
    --color-secondary: #1a1a2e;
    --color-accent: #0f3460;
    /* ... más colores */
}
```

## 🌐 Estructura del Proyecto

```
bingo-virtual/
├── admin/                  # Panel de administración
│   ├── includes/          # Headers del admin
│   ├── index.php          # Dashboard
│   ├── crear-sorteo.php   # Crear sorteos
│   ├── sorteo-vivo.php    # Control de sorteo en vivo
│   └── ...
├── api/                   # APIs REST
│   ├── extraer-numero.php # Extraer números
│   ├── cantar-bingo.php   # Reclamos de BINGO
│   └── ...
├── assets/                # Recursos estáticos
│   ├── images/           # Imágenes
│   └── sounds/           # Sonidos
├── css/                   # Estilos
│   ├── styles.css        # Estilos principales
│   ├── admin.css         # Estilos admin
│   ├── jugador.css       # Estilos jugador
│   ├── sorteo-vivo.css   # Estilos sorteo en vivo
│   └── juego.css         # Estilos del juego
├── database/              # Base de datos
│   └── schema.sql        # Schema SQL
├── includes/              # Archivos compartidos
│   ├── config.php        # Configuración
│   ├── Database.php      # Clase de BD
│   └── funciones.php     # Funciones comunes
├── js/                    # JavaScript
│   ├── confetti.js       # Efecto confetti
│   ├── sorteo-vivo-admin.js
│   └── juego.js          # Lógica del juego
├── jugador/              # Panel de jugador
│   ├── index.php         # Dashboard jugador
│   ├── seleccionar-carton.php
│   ├── jugar.php         # Pantalla de juego
│   └── ...
├── index.php             # Página principal
├── login.php             # Login/Registro
├── logout.php            # Cerrar sesión
└── README.md             # Este archivo
```

## 🐛 Solución de Problemas

### Error de Conexión a la Base de Datos
- Verifica que MySQL esté en ejecución
- Revisa las credenciales en `includes/config.php`
- Asegúrate de haber importado el schema.sql

### El Narrador no Funciona
- Verifica que tu navegador soporte Web Speech API
- Chrome/Edge: ✅ Totalmente compatible
- Firefox: ⚠️ Soporte limitado
- Safari: ⚠️ Requiere configuración

### Los Estilos no se Cargan
- Verifica que la ruta del CSS sea correcta
- Limpia la caché del navegador (Ctrl + Shift + R)

### Las Actualizaciones en Tiempo Real no Funcionan
- Verifica que JavaScript esté habilitado
- Abre la consola del navegador (F12) para ver errores
- Asegúrate de que las rutas de las APIs sean correctas

## 🔒 Seguridad

- ✅ Protección CSRF en formularios
- ✅ Validación de entrada de datos
- ✅ Prepared statements para prevenir SQL injection
- ✅ Passwords hasheados con bcrypt
- ✅ Sesiones seguras con timeout

**Recomendaciones para Producción:**
1. Cambia todas las contraseñas por defecto
2. Configura HTTPS
3. Desactiva los errores de PHP en producción
4. Usa contraseñas fuertes para la base de datos
5. Implementa rate limiting para las APIs

## 📱 Compatibilidad

### Navegadores Compatibles
- ✅ Chrome 80+
- ✅ Firefox 75+
- ✅ Safari 13+
- ✅ Edge 80+
- ✅ Opera 67+

### Dispositivos
- ✅ Desktop (Windows, Mac, Linux)
- ✅ Tablets
- ✅ Móviles (iOS, Android)

## 📝 Licencia

Este proyecto es de código abierto y puede ser usado libremente para fines educativos y comerciales.

## 👨‍💻 Soporte

Para reportar bugs o solicitar funcionalidades:
1. Revisa la sección de "Solución de Problemas"
2. Verifica los logs en la carpeta `/logs`
3. Contacta al desarrollador

## 🎉 ¡Disfruta del Bingo Virtual!

¡Esperamos que disfrutes de este sistema completo de Bingo en línea! 🎰🎊
