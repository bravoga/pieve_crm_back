# Laravel Telescope - Herramienta de Debugging y Monitoreo

Laravel Telescope ha sido instalado y configurado correctamente en este proyecto.

## 📋 ¿Qué es Telescope?

Telescope es una herramienta de debugging elegante para Laravel que proporciona información sobre:
- 🔍 Requests HTTP
- 🗄️ Queries a la base de datos
- 📧 Emails enviados
- 🚨 Excepciones y errores
- 📝 Logs
- ⚡ Jobs y colas
- 🔔 Notificaciones
- 🔐 Gates y policies
- 📦 Cache operations
- Y mucho más...

## 🚀 Acceso a Telescope

### URL Local
```
http://127.0.0.1:8000/telescope
```

### URL Producción
```
https://tu-dominio.com/telescope
```

## 🔐 Control de Acceso

El acceso a Telescope está restringido según el archivo `app/Providers/TelescopeServiceProvider.php`:

```php
protected function gate(): void
{
    Gate::define('viewTelescope', function ($user) {
        // Permitir acceso solo a administradores
        return $user && in_array($user->role, ['admin', 'superadmin']);
    });
}
```

**Solo usuarios con rol `admin` o `superadmin` pueden acceder a Telescope.**

## ⚙️ Configuración

### Variables de Entorno (.env)

```env
# Habilitar/Deshabilitar Telescope
TELESCOPE_ENABLED=true

# Watchers específicos
TELESCOPE_QUERY_WATCHER=true
TELESCOPE_MODEL_WATCHER=true
TELESCOPE_RESPONSE_SIZE_LIMIT=64
```

### Configuración Completa

El archivo de configuración principal está en:
```
config/telescope.php
```

## 🛠️ Comandos Útiles

### Instalar Telescope (ya realizado)
```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

### Limpiar entradas antiguas
```bash
# Eliminar entradas de más de 24 horas
php artisan telescope:prune

# Eliminar entradas de más de 48 horas
php artisan telescope:prune --hours=48
```

### Pausar el registro
```bash
# Pausar
php artisan telescope:pause

# Reanudar
php artisan telescope:resume
```

### Limpiar caché después de cambios
```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize
```

## 📊 Características Principales

### 1. Requests
Monitorea todas las peticiones HTTP:
- URL, método, status code
- Headers y parámetros
- Sesión y usuario autenticado
- Middleware aplicados
- Tiempo de respuesta

### 2. Queries
Visualiza todas las consultas SQL:
- Query completa con bindings
- Tiempo de ejecución
- Conexión utilizada
- Stack trace

### 3. Exceptions
Captura todas las excepciones:
- Tipo de excepción
- Mensaje y stack trace
- Request que causó la excepción
- Puede marcar como "resueltas"

### 4. Logs
Todos los logs de la aplicación:
- Nivel (debug, info, warning, error)
- Mensaje
- Contexto adicional
- Stack trace

### 5. Jobs
Monitorea trabajos en cola:
- Nombre del job
- Payload
- Estado (pending, processing, completed, failed)
- Intentos y tiempo de ejecución

## 🎯 Uso en Desarrollo

1. **Iniciar el servidor**
   ```bash
   cd back
   php artisan serve
   ```

2. **Acceder a Telescope**
   - Abrir navegador en: `http://127.0.0.1:8000/telescope`
   - Iniciar sesión con usuario admin

3. **Realizar requests**
   - Las peticiones aparecerán automáticamente en Telescope
   - Explorar las diferentes pestañas (Requests, Queries, Exceptions, etc.)

## 🔧 Filtrado y Búsqueda

Telescope permite filtrar por:
- **Tags**: Agregar tags personalizados a entradas
- **Tipo**: Filtrar por tipo de entrada
- **Status**: Filtrar por código de respuesta HTTP
- **Fecha**: Rango de fechas

### Agregar Tags Personalizados

```php
// En cualquier parte del código
Telescope::tag(function () {
    return ['user:' . auth()->id()];
});
```

## 📝 Watchers Disponibles

Los watchers pueden habilitarse/deshabilitarse en `config/telescope.php`:

- ✅ RequestWatcher - Peticiones HTTP
- ✅ CommandWatcher - Comandos Artisan
- ✅ ScheduleWatcher - Tareas programadas
- ✅ JobWatcher - Jobs en cola
- ✅ ExceptionWatcher - Excepciones
- ✅ LogWatcher - Logs
- ✅ DumpWatcher - Dumps (dd, dump)
- ✅ QueryWatcher - Consultas SQL
- ✅ ModelWatcher - Eventos de Eloquent
- ✅ EventWatcher - Eventos
- ✅ MailWatcher - Emails
- ✅ NotificationWatcher - Notificaciones
- ✅ GateWatcher - Gates y Policies
- ✅ CacheWatcher - Operaciones de caché
- ✅ RedisWatcher - Comandos Redis
- ✅ ViewWatcher - Vistas renderizadas

## ⚠️ Consideraciones de Producción

### Modo Producción

En producción, Telescope solo registra:
- Excepciones reportables
- Failed requests
- Failed jobs
- Scheduled tasks
- Entries con monitored tags

### Rendimiento

Telescope puede impactar el rendimiento:
- ✅ Usar `TELESCOPE_ENABLED=false` en producción si no es necesario
- ✅ Configurar `prune` automático para limpiar entradas antiguas
- ✅ Limitar el tamaño de respuestas con `TELESCOPE_RESPONSE_SIZE_LIMIT`
- ✅ Deshabilitar watchers innecesarios

### Seguridad

- 🔒 Siempre proteger el acceso con Gate
- 🔒 No exponer información sensible
- 🔒 Configurar `hideSensitiveRequestDetails()` en el ServiceProvider
- 🔒 Considerar usar un dominio diferente en producción

## 🗃️ Mantenimiento de Base de Datos

Telescope guarda todas las entradas en la base de datos. Para evitar que crezca indefinidamente:

### Configurar Cron (Producción)
```bash
# Agregar al crontab
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Configurar en Kernel (app/Console/Kernel.php)
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('telescope:prune --hours=48')->daily();
}
```

## 📚 Recursos Adicionales

- [Documentación Oficial](https://laravel.com/docs/telescope)
- [GitHub Repository](https://github.com/laravel/telescope)
- [Tutorial en Video](https://laracasts.com/series/laravel-telescope)

## ✅ Estado Actual

- ✅ Telescope instalado (v5.15.0)
- ✅ Migraciones ejecutadas
- ✅ Configuración publicada
- ✅ Variables de entorno configuradas
- ✅ Control de acceso implementado (solo admin/superadmin)
- ✅ Watchers habilitados

**Telescope está listo para usar en desarrollo! 🎉**
