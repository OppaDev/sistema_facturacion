# 📋 CONTEXTO COMPLETO DEL PROYECTO - SISTEMA DE FACTURACIÓN

## 📌 INFORMACIÓN GENERAL

### Identificación del Proyecto
- **Nombre**: Sistema de Inventario y Facturación Laravel 11+
- **Versión**: 3.3.0 (Sistema Web Completo - Estados Simplificados)
- **Framework**: Laravel 12.0 (basado en composer.json)
- **PHP**: ^8.2
- **Base de Datos**: PostgreSQL / MySQL (soporta ambas)
- **Propósito**: Sistema completo de gestión de inventario y facturación exclusivamente web

### Estado del Proyecto
✅ **COMPLETAMENTE FUNCIONAL Y EN PRODUCCIÓN**
- Sistema web funcional con interfaz AdminLTE 3
- ❌ **API REST eliminada** - El cliente no requiere servicios API
- Sistema de autenticación web con Laravel Breeze
- Sistema de auditoría completo
- Gestión de roles y permisos con Spatie
- Integración SRI (Sistema de Rentas Internas - Ecuador)
- Sistema de notificaciones y emails

### ⚠️ CAMBIOS IMPORTANTES - HISTORIAL DE VERSIONES
**Última Actualización**: Octubre 2, 2025

**VERSIÓN 3.3.0 - SIMPLIFICACIÓN DE ESTADOS DE FACTURA:**
Por alineación con la lógica de negocio (facturas se crean después del pago):
- ❌ Campo `estado` eliminado de tabla `facturas` (enum: pendiente/pagada/anulada)
- ❌ Métodos `isPendiente()` e `isPagada()` eliminados del modelo
- ✅ Anulación mediante soft deletes (`deleted_at`)
- ✅ Estados basados en flujo SRI: PENDIENTE → FIRMADA → EMITIDA → ANULADA
- ✅ Campos mantenidos: `estado_firma`, `estado_emision`, `deleted_at`
- ✅ Métodos: `isActiva()`, `isAnulada()`, `isFirmada()`, `isEmitida()`
- ✅ **Total**: 1 campo eliminado, 2 métodos removidos, 0 migraciones nuevas
- 📄 **Ver**: `SIMPLIFICACION_ESTADOS_V3.3.0.md` para detalles completos

**VERSIÓN 3.2.0 - ELIMINACIÓN COMPLETA DEL MÓDULO DE PAGOS:**
Por simplificación del sistema y falta de interfaz funcional para clientes:
- ❌ Modelo `Pago` eliminado
- ❌ Controlador `PagoController` eliminado (274 líneas)
- ❌ Request `StorePagoRequest` eliminado
- ❌ Policy `PagoPolicy` eliminado
- ❌ 3 Notificaciones de pagos eliminadas
- ❌ Vistas de pagos eliminadas (index, show, dashboard_pagos)
- ❌ Migración `create_pagos_table` eliminada
- ❌ Tabla `pagos` eliminada de base de datos
- ❌ Rutas de pagos eliminadas (4 rutas)
- ❌ Menú de pagos eliminado del sidebar
- ❌ Dashboard de pagos eliminado
- ❌ 4 Comandos de testing eliminados
- ✅ **Total**: 15 archivos eliminados, ~1,700 líneas de código
- ✅ **Alternativa**: Marcar facturas como "pagada" manualmente
- 📄 **Ver**: `ELIMINACION_MODULO_PAGOS.md` para detalles completos

**VERSIÓN 3.1.0 - ELIMINACIÓN DE LARAVEL TELESCOPE:**
Para simplificar el sistema y reducir dependencias, se ha eliminado Laravel Telescope:
- ❌ Paquete `laravel/telescope` removido
- ❌ Configuración `config/telescope.php` eliminada
- ❌ Service Provider `TelescopeServiceProvider` eliminado
- ❌ Migración de `telescope_entries` eliminada
- ❌ Ruta `/telescope` eliminada
- ✅ **Alternativas**: Laravel Pail, sistema de auditoría propio, logs tradicionales
- 📄 **Ver**: `ELIMINACION_TELESCOPE.md` para detalles completos

**VERSIÓN 3.0.0 - ELIMINACIÓN COMPLETA DE API REST:**
Por requerimiento del cliente, se eliminó completamente toda la funcionalidad de API REST del sistema:
- ❌ Laravel Sanctum (autenticación por tokens)
- ❌ Controladores API (`app/Http/Controllers/Api/`)
- ❌ API Resources (`app/Http/Resources/`)
- ❌ Rutas API (`routes/api.php`)
- ❌ Middleware API (ApiAuditLogger, ApiErrorHandler, SecurityValidator, etc.)
- ❌ Traits de API (HasObfuscatedId, HasDataSanitization)
- ❌ Configuración CORS
- ❌ Migración de personal_access_tokens
- 📄 **Ver**: `CAMBIOS_ELIMINACION_API.md` para detalles completos

**El sistema ahora es exclusivamente una aplicación web tradicional** con autenticación basada en sesiones de Laravel y gestión manual de pagos.

---

## 🏗️ ARQUITECTURA DEL SISTEMA

### Estructura del Proyecto

```
sistema_facturacion/
├── app/
│   ├── Console/Commands/Testing/        # Comandos de testing y pruebas
│   ├── Exports/                         # Exportación Excel (ProductosExport)
│   ├── Http/
│   │   ├── Controllers/                 # Controladores Web
│   │   │   ├── Auth/                    # Autenticación Breeze
│   │   │   ├── Api/                     # ❌ ELIMINADO (v3.0.0)
│   │   │   │   ├── AuthController       # ❌ ELIMINADO
│   │   │   │   ├── ClienteController    # ❌ ELIMINADO
│   │   │   │   ├── ProductoController   # ❌ ELIMINADO
│   │   │   │   ├── FacturaController    # ❌ ELIMINADO
│   │   │   │   └── PagoController       # ❌ ELIMINADO
│   │   │   ├── AuditoriaController      # Gestión de auditoría
│   │   │   ├── DashboardController      # Dashboard principal
│   │   │   ├── FacturasController       # Gestión web de facturas
│   │   │   ├── FacturaEstadoController  # Firma y emisión de facturas
│   │   │   ├── PagoController           # ❌ ELIMINADO (v3.2.0)
│   │   │   ├── ProductosController      # Gestión de productos
│   │   │   ├── ProfileController        # Perfil de usuario
│   │   │   ├── RolesController          # Gestión de roles
│   │   │   └── UserController           # Gestión de usuarios
│   │   ├── Middleware/                  # Middlewares personalizados
│   │   │   ├── ApiAuditLogger          # Logging de API
│   │   │   ├── ApiErrorHandler         # Manejo de errores API
│   │   │   ├── ApiRateLimiter          # Rate limiting API
│   │   │   ├── AuditTokenUsage         # Auditoría de tokens
│   │   │   ├── CheckUserStatus         # Verificación estado usuario
│   │   │   ├── FacturaPermissions      # Permisos de facturas
│   │   │   └── SecurityValidator       # Validación de seguridad
│   │   ├── Requests/                    # FormRequests de validación
│   │   │   ├── Api/                     # ❌ ELIMINADO (v3.0.0)
│   │   │   ├── StoreClienteRequest
│   │   │   ├── StoreProductoRequest
│   │   │   ├── StoreFacturaRequest
│   │   │   └── StorePagoRequest         # ❌ ELIMINADO (v3.2.0)
│   │   └── Resources/Api/               # ❌ ELIMINADO (v3.0.0)
│   │       ├── UserResource
│   │       ├── ClienteResource
│   │       ├── ProductoResource
│   │       ├── FacturaResource
│   │       ├── FacturaDetalleResource
│   │       └── PagoResource
│   ├── Models/                          # Modelos Eloquent
│   │   ├── User                         # Usuario (con roles)
│   │   ├── Producto                     # Productos
│   │   ├── Categoria                    # Categorías
│   │   ├── Factura                      # Facturas
│   │   ├── FacturaDetalle               # Detalles de factura
│   │   ├── Pago                         # ❌ ELIMINADO (v3.2.0)
│   │   └── Auditoria                    # Logs de auditoría
│   ├── Notifications/                   # Notificaciones
│   │   ├── PagoAprobadoNotification     # ❌ ELIMINADO (v3.2.0)
│   │   ├── PagoRechazadoNotification    # ❌ ELIMINADO (v3.2.0)
│   │   └── PagoRegistradoNotification   # ❌ ELIMINADO (v3.2.0)
│   ├── Observers/                       # Observers de modelos
│   │   └── UserObserver                # Observer de Usuario
│   ├── Policies/                        # Políticas de autorización
│   │   ├── FacturaPolicy
│   │   ├── PagoPolicy                   # ❌ ELIMINADO (v3.2.0)
│   │   ├── ProductoPolicy
│   │   ├── RolePolicy
│   │   └── UserPolicy
│   ├── Providers/
│   │   └── AppServiceProvider          # Rate limiters configurados
│   ├── Services/                        # Servicios de negocio
│   │   ├── EmailService                # Servicio de envío de emails
│   │   ├── FacturaSRIService           # Integración SRI Ecuador
│   │   └── MailerooService             # Servicio Maileroo
│   └── Traits/                          # Traits reutilizables
│       ├── HasDataSanitization         # ❌ ELIMINADO (v3.0.0)
│       └── HasObfuscatedId             # ❌ ELIMINADO (v3.0.0)
├── bootstrap/
│   └── app.php                          # Configuración middleware
├── config/                              # Archivos de configuración
│   ├── app.php
│   ├── auth.php
│   ├── cors.php                        # ❌ ELIMINADO (v3.0.0)
│   ├── database.php
│   ├── mail.php
│   ├── permission.php                  # Spatie Permission
│   ├── sanctum.php                     # ❌ ELIMINADO (v3.0.0)
│   └── security.php                    # ❌ ELIMINADO (v3.0.0)
├── database/
│   ├── migrations/                     # 11 migraciones activas
│   │   ├── users_table
│   │   ├── permission_tables
│   │   ├── categorias_table
│   │   ├── productos_table
│   │   ├── facturas_table
│   │   ├── factura_detalles_table
│   │   ├── auditorias_table
│   │   ├── sri_fields (facturas)
│   │   ├── firma_emision_fields
│   │   ├── personal_access_tokens      # ❌ ELIMINADO (v3.0.0)
│   │   └── pagos_table                 # ❌ ELIMINADO (v3.2.0)
│   └── seeders/                        # Seeders de datos
│       ├── RolesSeeder
│       ├── UsuariosSeeder
│       ├── CategoriasSeeder
│       ├── ClientesSeeder
│       └── ProductosSeeder
├── public/                             # Assets públicos
│   ├── build/                          # Assets compilados
│   ├── img/                            # Imágenes
│   ├── js/
│   └── sneat/                          # Template AdminLTE
├── resources/
│   ├── css/
│   ├── js/
│   ├── lang/                           # Traducciones
│   └── views/                          # Vistas Blade
│       ├── clientes/
│       ├── productos/
│       ├── facturas/
│       ├── users/
│       ├── roles/
│       ├── profile/
│       ├── emails/                     # Plantillas de email
│       └── layouts/
├── routes/
│   ├── api.php                         # ❌ ELIMINADO (v3.0.0)
│   ├── web.php                         # Rutas web (sin pagos desde v3.2.0)
│   ├── auth.php                        # Rutas autenticación Breeze
│   └── console.php                     # Comandos Artisan
├── storage/
│   ├── app/                            # Archivos de aplicación
│   ├── framework/                      # Cache y sesiones
│   └── logs/                           # Logs del sistema
├── tests/                              # Tests PHPUnit
│   ├── Feature/
│   └── Unit/
└── vendor/                             # Dependencias Composer
```

---

## 🔐 SISTEMA DE AUTENTICACIÓN Y AUTORIZACIÓN

### Autenticación

#### Autenticación Web (Laravel Breeze)
- **Sistema**: Laravel Breeze con Blade
- **Features**: Login, Registro, Recuperación de contraseña, Verificación de email
- **Middleware**: `auth`, `verified`, `check.user.status`
- **Sesiones**: Autenticación basada en sesiones de Laravel (sin tokens API)

### Sistema de Roles (Spatie Laravel Permission)

#### Roles Disponibles
1. **Administrador** - Acceso total al sistema
2. **Secretario** - Gestión de usuarios y reportes
3. **Bodega** - Gestión de productos e inventario
4. **Ventas** - Creación y gestión de facturas
5. **Pagos** - ⚠️ SIN FUNCIONALIDAD (v3.2.0) - Módulo eliminado
6. **Cliente** - Acceso limitado a sus propias facturas

#### Permisos por Módulo

**Usuarios**:
- Ver, crear, editar, eliminar usuarios
- Activar/desactivar cuentas
- Restaurar usuarios eliminados
- Solo Administrador y Secretario

**Productos**:
- CRUD completo de productos
- Control de stock
- Exportar a Excel/PDF
- Solo Administrador y Bodega

**Facturas**:
- Crear, ver, editar, anular facturas
- Generar PDF
- Enviar por email
- Firmar digitalmente (SRI)
- Emitir facturas
- Solo Administrador y Ventas

**Pagos**:
- ❌ MÓDULO ELIMINADO (v3.2.0)
- ✅ Alternativa: Marcar facturas como "pagada" manualmente
- Solo Administrador puede cambiar estado de facturas

**Auditoría**:
- Ver logs completos
- Exportar reportes
- Filtros avanzados
- Solo Administrador

**Roles**:
- Crear, ver, eliminar roles
- Gestión de permisos
- Solo Administrador

### Estados de Usuario

#### Estados Disponibles
1. **activo** - Usuario activo con acceso completo
2. **inactivo** - Usuario suspendido sin acceso
3. **pendiente_eliminacion** - Usuario con solicitud de eliminación
4. **eliminado** - Usuario eliminado (soft delete)

#### Middleware CheckUserStatus
- Valida estado del usuario en cada request
- Bloquea acceso a usuarios inactivos/eliminados
- Permite acceso limitado a usuarios pendientes de eliminación
- Redirección inteligente según estado
- Cálculo de tiempo restante para eliminación definitiva

---

## 📦 MÓDULOS DEL SISTEMA

### 1. Módulo de Usuarios

#### Características
- **CRUD completo** con validaciones
- **Estados avanzados**: activo, inactivo, pendiente eliminación
- **Soft deletes** con restauración
- **Sincronización con clientes** (usuarios con rol Cliente)
- **Gestión de contraseñas** con visibilidad toggle
- **Validación de contraseña** para acciones críticas
- **Periodo de gracia** de 3 días (configurable) para eliminación
- **Cancelación de eliminación** antes del plazo
- **Auditoría completa** de todas las acciones

#### Campos del Modelo User
```php
- id (primary key)
- name (string)
- email (string, unique)
- password (hashed)
- telefono (nullable)
- direccion (nullable)
- estado (enum: activo, inactivo)
- pending_delete_at (datetime, nullable)
- observacion (text, nullable)
- motivo_suspension (text, nullable)
- created_by (foreign key)
- updated_by (foreign key)
- email_verified_at
- remember_token
- timestamps
- soft deletes
```

#### Relaciones
- `roles()` - Roles asignados (Spatie)
- `permissions()` - Permisos directos
- `facturasComoCliente()` - Facturas donde es cliente
- `facturasCreadas()` - Facturas que creó como vendedor
- `pagos()` - Pagos realizados

### 2. Módulo de Productos

#### Características
- **CRUD completo** con imágenes
- **Control automático de stock**
- **Categorización** de productos
- **Validación de stock** en tiempo real
- **Soft deletes** con restauración
- **Exportación** a Excel y PDF
- **Filtros avanzados** por categoría, stock, precio
- **Auditoría completa**

#### Campos del Modelo Producto
```php
- id
- nombre (string)
- descripcion (text)
- imagen (path, nullable)
- categoria_id (foreign key)
- stock (integer)
- precio (decimal 10,2)
- created_by
- updated_by
- timestamps
- soft deletes
```

#### Relaciones
- `categoria()` - belongsTo Categoria
- `facturaDetalles()` - hasMany FacturaDetalle
- `creador()` - belongsTo User (created_by)
- `modificador()` - belongsTo User (updated_by)

### 3. Módulo de Facturas

#### Características
- **Creación dinámica** con múltiples productos
- **Validación de stock** en tiempo real
- **Transacciones seguras** con rollback automático
- **Generación de PDF** profesional
- **Envío por email** con plantilla personalizada
- **Firma digital** (integración SRI)
- **Emisión oficial** con CUA y número secuencial
- **Código QR** con datos de la factura
- **Estados**: PENDIENTE → FIRMADA → EMITIDA → ANULADA (soft delete)
- **Reversión automática** de stock al anular
- **Auditoría completa**

#### Campos del Modelo Factura
```php
- id
- cliente_id (foreign key a users)
- usuario_id (foreign key a users - vendedor)
- factura_original_id (para facturas modificadas)
- subtotal (decimal 10,2)
- iva (decimal 10,2)
- total (decimal 10,2)
- motivo_anulacion (text, nullable)
- created_by
- updated_by
- timestamps
- soft deletes (deleted_at para facturas anuladas)

# Campos SRI Ecuador
- numero_secuencial (string, unique)
- cua (string, unique - Código Único de Autorización)
- firma_digital (text)
- mensaje_autorizacion (string)
- fecha_emision (date)
- hora_emision (time)
- ambiente (enum: pruebas, produccion)
- tipo_emision (string)
- tipo_documento (string)
- contenido_qr (text)
- imagen_qr (path, nullable)
- estado_firma (enum: PENDIENTE, FIRMADA)
- fecha_firma (datetime)
- estado_emision (enum: PENDIENTE, EMITIDA)
- fecha_emision_email (datetime)
```

#### Relaciones
- `cliente()` - belongsTo User
- `usuario()` - belongsTo User (vendedor)
- `detalles()` - hasMany FacturaDetalle
- `pagos()` - hasMany Pago
- `facturaOriginal()` - belongsTo Factura
- `facturasModificadas()` - hasMany Factura

#### Métodos Importantes
- `generarDatosSRI()` - Genera datos SRI automáticamente
- `generarFirmaYQR()` - Genera firma digital y código QR
- `firmarDigitalmente()` - Firma la factura
- `emitir()` - Emite y envía por email
- `isAnulada()`, `isPendiente()`, `isPagada()` - Estados
- `isFirmada()`, `isEmitida()` - Estados SRI

### 4. ❌ Módulo de Pagos (ELIMINADO EN v3.2.0)

**ESTADO**: ❌ Completamente eliminado

**RAZÓN**: Módulo diseñado exclusivamente para API REST (eliminada en v3.0.0). Sin interfaz web funcional para que clientes registren pagos.

**CAMBIO EN v3.3.0**: 
- ❌ Campo `estado` (pendiente/pagada/anulada) eliminado
- ✅ Lógica simplificada: Factura se emite después del pago (todas están "pagadas")
- ✅ Anulación mediante soft delete (`deleted_at`)
- ✅ Estados basados en flujo SRI: estado_firma y estado_emision

**ARCHIVOS ELIMINADOS**:
- ❌ `app/Models/Pago.php`
- ❌ `app/Http/Controllers/PagoController.php`
- ❌ `app/Http/Requests/StorePagoRequest.php`
- ❌ `app/Policies/PagoPolicy.php`
- ❌ 3 Notificaciones de pagos
- ❌ Vistas de pagos
- ❌ Tabla `pagos` en base de datos
- ❌ 4 Rutas web de pagos

**📄 Ver**: `ELIMINACION_MODULO_PAGOS.md` para detalles completos

### 5. Módulo de Auditoría

#### Características
- **Registro automático** de todas las acciones CRUD
- **Información completa**: old_values, new_values
- **Filtros avanzados** por acción, usuario, modelo, fecha
- **Paginación** de resultados
- **Exportación** a Excel
- **Búsqueda** por descripción
- **Observaciones** personalizadas para acciones críticas

#### Campos del Modelo Auditoria
```php
- id
- user_id (foreign key)
- action (string - created, updated, deleted, restored, etc.)
- model_type (string - nombre del modelo)
- model_id (integer)
- old_values (json, nullable)
- new_values (json, nullable)
- description (text, nullable)
- ip_address (string, nullable)
- user_agent (text, nullable)
- timestamps
```

#### Acciones Registradas
- `created` - Creación de registros
- `updated` - Actualización de registros
- `deleted` - Eliminación (soft delete)
- `restored` - Restauración de eliminados
- `force_deleted` - Eliminación definitiva
- `api_login` - Login por API
- `api_logout` - Logout por API
- `api_token_refresh` - Renovación de token
- Acciones personalizadas por módulo

---

## 🔒 SISTEMA DE SEGURIDAD

### Medidas de Seguridad Implementadas

#### 1. Protección contra SQL Injection
- **Eloquent ORM** exclusivamente
- **PreparedStatements** automáticos
- **Sanitización de inputs** (trait HasDataSanitization)
- **Detección de patrones** maliciosos con regex
- **Logging automático** de intentos

#### 2. Protección contra XSS (Cross-Site Scripting)
- **Sanitización HTML** automática
- **Escape de caracteres** peligrosos
- **Validación de contenido** con regex
- **API Resources** estructurados
- **strip_tags()** en inputs libres

#### 3. Protección contra CSRF
- **Headers CSRF** en configuración CORS
- **Validación de origen** de requests
- **Token-based auth** con Sanctum (protección CSRF inherente)
- **Verificación de hosts** permitidos

#### 4. Prevención de Exposición de Datos Sensibles
- **Campos $hidden** en modelos:
  - Passwords
  - Tokens
  - Datos de auditoría interna
  - Información administrativa
- **API Resources** con filtrado por rol
- **Logging seguro** (datos sensibles marcados como [REDACTED])
- **Ofuscación de IDs** con encriptación

#### 5. Anti-enumeración de IDs
- **Trait HasObfuscatedId**:
  - `obfuscateId($id)` - Encripta IDs
  - `deobfuscateId($obfuscatedId)` - Desencripta
  - `findByObfuscatedId($obfuscatedId)` - Búsqueda segura
- IDs encriptados en respuestas API
- Base64 + Laravel Crypt

#### 6. Rate Limiting Multinivel
Configurado en `AppServiceProvider`:
- **auth**: 5 requests/minuto (login)
- **sensitive**: 10 requests/minuto (operaciones críticas)
- **write**: 30 requests/minuto (escritura)
- **read**: 100 requests/minuto (lectura)
- **api**: 60 requests/minuto (general)

#### 7. Middleware de Seguridad

##### SecurityValidator
- Valida headers maliciosos
- Detecta user agents sospechosos
- Verifica tamaño de requests
- Detecta intentos de injection
- Implementa rate limiting adicional
- Bloquea IPs tras múltiples violaciones

##### ApiAuditLogger
- Logging completo de requests API
- Información contextual (IP, User Agent, timestamps)
- Datos sanitizados
- Niveles de log por criticidad

##### ApiErrorHandler
- Manejo estructurado de errores
- Formato JSON estándar
- Códigos de error consistentes
- No expone información sensible

##### AuditTokenUsage
- Auditoría de uso de tokens
- Detección de tokens sospechosos
- Registro de última actividad

### Configuración de Seguridad

#### config/security.php
```php
'allowed_hosts' => ['localhost', '127.0.0.1']
'api_max_request_size' => 1048576 // 1MB
'rate_limit' => 300 // requests por minuto
'rate_limit_decay' => 60 // segundos
```

#### config/cors.php
```php
'paths' => ['api/*', 'sanctum/csrf-cookie']
'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']
'allowed_origins' => ['localhost:3000', 'localhost:5173', '*']
'allowed_headers' => ['Authorization', 'Content-Type', 'X-CSRF-TOKEN']
'exposed_headers' => ['X-RateLimit-*', 'Retry-After']
'supports_credentials' => true
```

---

## 🌐 API REST COMPLETA

### Arquitectura API

#### Endpoints Disponibles (24 total)

##### Autenticación (4 endpoints)
```
POST   /api/login              - Login y generación de token
POST   /api/logout             - Logout y revocación de token
POST   /api/refresh-token      - Renovar token
GET    /api/me                 - Información del usuario autenticado
```

##### Clientes (5 endpoints)
```
GET    /api/clientes           - Listar clientes
POST   /api/clientes           - Crear cliente
GET    /api/clientes/{id}      - Ver cliente específico
PUT    /api/clientes/{id}      - Actualizar cliente (completo)
PATCH  /api/clientes/{id}      - Actualizar cliente (parcial)
DELETE /api/clientes/{id}      - Eliminar cliente
```

##### Productos (6 endpoints)
```
GET    /api/productos          - Listar productos
POST   /api/productos          - Crear producto
GET    /api/productos/{id}     - Ver producto específico
PUT    /api/productos/{id}     - Actualizar producto (completo)
PATCH  /api/productos/{id}     - Actualizar producto (parcial)
PATCH  /api/productos/{id}/stock - Actualizar solo stock
DELETE /api/productos/{id}     - Eliminar producto
```

##### Facturas (5 endpoints)
```
GET    /api/facturas           - Listar facturas
POST   /api/facturas           - Crear factura
GET    /api/facturas/{id}      - Ver factura específica
GET    /api/facturas-pendientes - Facturas pendientes de pago
POST   /api/facturas/{id}/cancel - Cancelar/anular factura
```

##### Pagos (5 endpoints)
```
GET    /api/pagos              - Listar pagos
POST   /api/pagos              - Crear pago
GET    /api/pagos/{id}         - Ver pago específico
POST   /api/pagos/{id}/approve - Aprobar pago
POST   /api/pagos/{id}/reject  - Rechazar pago
```

### Formato de Respuestas API

#### Respuesta Exitosa
```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": {
    // Datos del recurso
  }
}
```

#### Respuesta de Error
```json
{
  "success": false,
  "message": "Descripción del error",
  "error": "ERROR_CODE",
  "errors": {
    // Errores de validación (si aplica)
  }
}
```

### API Resources Implementados

#### UserResource
- Información de usuario filtrada por rol
- Oculta datos sensibles según contexto
- Incluye roles y permisos

#### ProductoResource
- Información de producto
- Estadísticas de ventas (solo usuarios autorizados)
- Datos de categoría incluidos

#### FacturaResource
- Información completa de factura
- Incluye detalles de productos
- Datos SRI (solo admins)
- Estado de pagos

#### PagoResource
- Información de pago
- Estado visual
- Datos del validador

### Validación y Sanitización

#### FormRequests Implementados
1. **StoreClienteRequest** - Validar creación de clientes
2. **UpdateClienteRequest** - Validar actualización de clientes
3. **StoreProductoRequest** - Validar productos
4. **StoreFacturaRequest** - Validar facturas
5. **StorePagoRequest** - Validar pagos

#### Trait HasDataSanitization
Métodos disponibles:
- `sanitizeString()` - Limpieza general
- `sanitizeEmail()` - Emails
- `sanitizePhone()` - Teléfonos
- `sanitizeAlphanumeric()` - Códigos
- `sanitizeDecimal()` - Precios/montos
- `sanitizeInteger()` - Números enteros
- `sanitizeFilename()` - Nombres de archivo
- `sanitizeUrl()` - URLs
- `sanitizeFreeText()` - Texto libre (descripciones)
- `detectInjectionAttempt()` - Detecta ataques
- `logInjectionAttempt()` - Registra intentos

---

## 🛠️ SERVICIOS Y UTILIDADES

### Servicios de Negocio

#### EmailService
**Ubicación**: `app/Services/EmailService.php`
**Propósito**: Gestión unificada de envío de emails

**Métodos**:
- `enviarFactura($factura, $email, $asunto, $mensaje)` - Enviar factura por email
- Genera PDF automáticamente
- Adjunta PDF al email
- Usa plantilla personalizada
- Registra en logs

#### FacturaSRIService
**Ubicación**: `app/Services/FacturaSRIService.php`
**Propósito**: Integración con SRI Ecuador

**Métodos**:
- `prepararDatosSRI($subtotal)` - Genera datos SRI básicos
- `generarFirmaYQR($factura)` - Genera firma digital y QR
- `verificarFirmaDigital($factura, $firma)` - Verifica firma
- `generarQRParaFactura($factura)` - Genera código QR
- `getDatosEmisor()` - Obtiene datos del emisor

**Campos Generados**:
- Número secuencial único
- CUA (Código Único de Autorización)
- Firma digital SHA-256
- Código QR con datos de factura
- Fecha y hora de emisión
- Ambiente (pruebas/producción)

#### MailerooService
**Ubicación**: `app/Services/MailerooService.php`
**Propósito**: Servicio alternativo de email (Maileroo)

### Traits Reutilizables

#### HasObfuscatedId
**Ubicación**: `app/Traits/HasObfuscatedId.php`
**Propósito**: Ofuscación de IDs para seguridad

**Uso**:
```php
use HasObfuscatedId;

// En el modelo
$obfuscatedId = $model->obfuscated_id;
$id = Model::deobfuscateId($obfuscatedId);
$model = Model::findByObfuscatedId($obfuscatedId);
```

#### HasDataSanitization
**Ubicación**: `app/Traits/HasDataSanitization.php`
**Propósito**: Sanitización automática de datos

**Uso**:
```php
use HasDataSanitization;

// En el FormRequest o Controller
$email = $this->sanitizeEmail($request->email);
$phone = $this->sanitizePhone($request->telefono);
$precio = $this->sanitizeDecimal($request->precio);

if ($this->detectInjectionAttempt($value)) {
    $this->logInjectionAttempt($value, 'context');
}
```

### Exports

#### ProductosExport
**Ubicación**: `app/Exports/ProductosExport.php`
**Propósito**: Exportación de productos a Excel
**Librería**: Maatwebsite/Excel

**Features**:
- Exporta todos los productos
- Incluye categoría
- Formato profesional
- Headers personalizados

---

## 💾 BASE DE DATOS

### Migraciones (13 total)

1. **create_users_table** - Tabla de usuarios base
2. **create_cache_table** - Cache del sistema
3. **create_jobs_table** - Cola de trabajos
4. **create_permission_tables** - Spatie Permission (roles y permisos)
5. **create_categorias_table** - Categorías de productos
6. **create_productos_table** - Productos
7. **create_facturas_table** - Facturas base
8. **create_factura_detalles_table** - Detalles de facturas
9. **create_auditorias_table** - Logs de auditoría
10. **add_sri_fields_to_facturas_table** - Campos SRI Ecuador
11. **add_firma_emision_fields_to_facturas_table** - Estados de firma/emisión
12. **create_personal_access_tokens_table** - Tokens Sanctum
13. **create_pagos_table** - Módulo de pagos

### Seeders

1. **RolesSeeder** - Crea roles iniciales (Administrador, Secretario, Bodega, Ventas, Pagos, Cliente)
2. **UsuariosSeeder** - Crea 4 usuarios de prueba con roles (sin usuario Pagos desde v3.2.0)
3. **CategoriasSeeder** - Categorías de productos
4. **ClientesSeeder** - Clientes de ejemplo
5. **ProductosSeeder** - Productos de ejemplo

### Relaciones Importantes

```
User (Usuario)
├── hasMany(Factura) como cliente_id
├── hasMany(Factura) como usuario_id (vendedor)
├── hasMany(Pago) como pagado_por
└── belongsToMany(Role) - Spatie

Producto
├── belongsTo(Categoria)
├── hasMany(FacturaDetalle)
├── belongsTo(User) como created_by
└── belongsTo(User) como updated_by

Factura
├── belongsTo(User) como cliente_id
├── belongsTo(User) como usuario_id
├── hasMany(FacturaDetalle)
├── hasMany(Pago)                      # ❌ ELIMINADO (v3.2.0)
├── belongsTo(Factura) como factura_original_id
└── hasMany(Factura) como facturasModificadas

Pago                                   # ❌ MODELO ELIMINADO (v3.2.0)
├── belongsTo(Factura)
├── belongsTo(User) como pagado_por
└── belongsTo(User) como validado_por

Auditoria
├── belongsTo(User)
└── morphTo() - model_type y model_id (relación polimórfica)
```

---

## 🎨 INTERFAZ DE USUARIO (WEB)

### Frontend Stack
- **Template**: AdminLTE 3
- **CSS Framework**: Tailwind CSS 3.x + Bootstrap (AdminLTE)
- **JavaScript**: Alpine.js 3.4, Axios 1.8
- **Icons**: Bootstrap Icons
- **Build Tool**: Vite 6.2

### Layout Principal

#### components principales
1. **layouts/app.blade.php** - Layout principal
2. **layouts/navigation.blade.php** - Navegación
3. **layouts/partials/header.blade.php** - Header con menú usuario
4. **layouts/partials/sidebar.blade.php** - Sidebar con menú principal
5. **layouts/partials/footer.blade.php** - Footer

### Vistas por Módulo

#### Usuarios (`resources/views/users/`)
- `index.blade.php` - Lista con estados y filtros
- `create.blade.php` - Formulario de creación
- `edit.blade.php` - Formulario de edición
- `show.blade.php` - Vista detallada

#### Productos (`resources/views/productos/`)
- `index.blade.php` - Lista con filtros y exportación
- `create.blade.php` - Formulario con imagen
- `edit.blade.php` - Edición con preview de imagen
- `show.blade.php` - Vista detallada
- `reporte.blade.php` - Reporte de inventario

#### Facturas (`resources/views/facturas/`)
- `index.blade.php` - Lista con estados SRI
- `create.blade.php` - Creación dinámica multi-producto
- `show.blade.php` - Vista detallada con QR
- `pdf.blade.php` - Plantilla PDF profesional

#### Perfil (`resources/views/profile/`)
- `edit.blade.php` - Perfil completo
- `partials/update-profile-information-form.blade.php`
- `partials/update-password-form.blade.php` - Con visibilidad toggle
- `partials/delete-user-form.blade.php` - Con modal profesional

#### Emails (`resources/views/emails/`)
- `factura.blade.php` - Plantilla profesional de email

### Features de UI

#### Modales Profesionales
- Confirmación de eliminación
- Validación de contraseña integrada
- Manejo de errores sin cerrar
- Reapertura automática en errores

#### Formularios
- Validación en tiempo real
- Feedback visual
- Ojos para ver contraseñas
- Preservación de datos en errores

#### Notificaciones
- Auto-cierre configurable
- Tipos: success, error, warning, info
- Diseño consistente
- Responsive

#### Tablas
- Paginación
- Ordenamiento
- Filtros avanzados
- Búsqueda en tiempo real
- Exportación

---

## 🧪 TESTING Y VALIDACIÓN

### Comandos de Testing

#### Comando Principal: test:api-security
**Ubicación**: `app/Console/Commands/Testing/TestApiSecurity.php`

**Pruebas que ejecuta**:
1. ✅ Protección de endpoints sin autenticación
2. ✅ Prevención de SQL injection
3. ✅ Prevención de XSS
4. ✅ Funcionamiento de rate limiting
5. ✅ Rechazo de headers maliciosos
6. ✅ Límites de tamaño de request

**Uso**:
```bash
php artisan test:api-security --host=localhost:8000
```

#### Otros Comandos de Testing
- `CrearFacturaPrueba` - Crear facturas de prueba
- `TestEmail` - Probar envío de emails
- `TestEmailConfig` - Validar configuración email
- `TestFirmaDigital` - Probar firma SRI
- `TestMaileroo` - Probar servicio Maileroo
- `TestResend` - Probar servicio Resend
- `TestLoginEndpoint` - Probar login API
- `TestRateLimiterEndpoints` - Probar rate limiting

### Tests PHPUnit
**Ubicación**: `tests/`
- `tests/Feature/` - Tests de integración
- `tests/Unit/` - Tests unitarios

### Herramientas de Testing

#### Postman Collection
**Archivo**: `postman_collection.json` (43KB, 25+ endpoints)
**Contenido**:
- Todos los endpoints API documentados
- Ejemplos de requests/responses
- Tests automatizados
- Variables de entorno

---

## 📊 ANÁLISIS ESTÁTICO Y CALIDAD

### Herramientas Configuradas

#### Larastan (PHPStan)
**Archivos de configuración**:
- `phpstan.neon` - Configuración principal
- `phpstan-baseline.neon` - Baseline de errores

**Nivel**: Configurable
**Uso**:
```bash
./vendor/bin/phpstan analyse
```

#### Laravel Pint
**Propósito**: Code styling automático
**Uso**:
```bash
./vendor/bin/pint
```

#### Laravel IDE Helper
**Propósito**: Autocompletado en IDE
**Uso**:
```bash
php artisan ide-helper:generate
php artisan ide-helper:models
```

---

## 📦 DEPENDENCIAS

### Dependencias de Producción (composer.json)

```json
{
  "php": "^8.2",
  "barryvdh/laravel-dompdf": "^3.1",          // Generación PDF
  "endroid/qr-code": "^6.0",                   // Códigos QR
  "guzzlehttp/guzzle": "^7.9",                 // HTTP Client
  "laravel/framework": "^12.0",                // Framework
  "laravel/sanctum": "^4.2",                   // API Auth
  "laravel/tinker": "^2.10.1",                 // REPL
  "maatwebsite/excel": "^3.1",                 // Exportar Excel
  "resend/resend-laravel": "^0.19.0",          // Email (Resend)
  "s-ichikawa/laravel-sendgrid-driver": "^4.0", // Email (SendGrid)
  "sendgrid/sendgrid": "^8.1",                 // Email (SendGrid)
  "spatie/laravel-permission": "^6.20"         // Roles y permisos
}
```

### Dependencias de Desarrollo

```json
{
  "barryvdh/laravel-ide-helper": "^3.6",       // IDE Support
  "fakerphp/faker": "^1.23",                   // Datos fake
  "larastan/larastan": "^3.6",                 // Análisis estático
  "laravel/breeze": "^2.3",                    // Auth scaffolding
  "laravel/pail": "^1.2.2",                    // Log viewer
  "laravel/pint": "^1.13",                     // Code styling
  "laravel/sail": "^1.41",                     // Docker env
  "mockery/mockery": "^1.6",                   // Mocking
  "nunomaduro/collision": "^8.6",              // Error handler
  "phpunit/phpunit": "^11.5.3"                 // Testing
}
```

### Dependencias Frontend (package.json)

```json
{
  "@tailwindcss/forms": "^0.5.2",
  "@tailwindcss/vite": "^4.0.0",
  "alpinejs": "^3.4.2",
  "autoprefixer": "^10.4.2",
  "axios": "^1.8.2",
  "concurrently": "^9.0.1",
  "laravel-vite-plugin": "^1.2.0",
  "postcss": "^8.4.31",
  "tailwindcss": "^3.1.0",
  "vite": "^6.2.4"
}
```

---

## ⚙️ CONFIGURACIÓN Y DEPLOYMENT

### Variables de Entorno Clave

```env
# Aplicación
APP_NAME="Sistema de Facturación"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=America/Guayaquil

# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventario_laravel
DB_USERNAME=root
DB_PASSWORD=

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=contraseña-de-aplicación
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000

# Seguridad
ALLOWED_HOSTS=localhost,127.0.0.1
API_MAX_REQUEST_SIZE=1048576
SECURITY_RATE_LIMIT=300

# CORS
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:5173
```

### Comandos de Instalación

```bash
# 1. Clonar repositorio
git clone <repo-url>
cd sistema_facturacion

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env

# 5. Migrar y sembrar
php artisan migrate
php artisan db:seed

# 6. Crear enlace simbólico
php artisan storage:link

# 7. Compilar assets
npm run dev   # Desarrollo
npm run build # Producción

# 8. Iniciar servidor
php artisan serve
```

### Comandos de Deployment

```bash
# Optimizar para producción
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permisos
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Requisitos del Servidor

- **PHP**: >= 8.2
- **Extensiones PHP**:
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath
  - GD (para imágenes)
- **Base de datos**: MySQL 5.7+ / PostgreSQL 10+
- **Composer**: 2.x
- **Node.js**: 18.x+ y npm

---

## 📝 DOCUMENTACIÓN ADICIONAL

### Archivos de Documentación

1. **README.md** - Documentación principal del proyecto
2. **API_SECURITY_IMPLEMENTATION.md** - Implementación de seguridad API
3. **VALIDATION_CHECKLIST.md** - Checklist de validación completo
4. **postman_collection.json** - Colección Postman con todos los endpoints
5. **reporte_pruebas_estaticas.tex** - Reporte de pruebas estáticas (LaTeX)

### Configuración de Días de Eliminación

Por defecto: **3 días** (configurable)

**Archivos a modificar**:
1. `app/Http/Middleware/CheckUserStatus.php` - Línea 26
2. `app/Http/Controllers/Auth/AuthenticatedSessionController.php` - Línea 34
3. `resources/views/profile/edit.blade.php` - Línea 109 y textos

**Cambio**:
```php
// De:
$fechaEliminacion = \Carbon\Carbon::parse($user->pending_delete_at)->addDays(3);

// A (ejemplo 7 días):
$fechaEliminacion = \Carbon\Carbon::parse($user->pending_delete_at)->addDays(7);
```

---

## 🚀 CARACTERÍSTICAS DESTACADAS

### Innovaciones del Sistema

1. **Sistema de Estados Avanzado para Usuarios**
   - Estados: activo, inactivo, pendiente eliminación, eliminado
   - Periodo de gracia configurable
   - Cancelación de eliminación
   - Sincronización con clientes

2. **Integración Completa con SRI Ecuador**
   - Firma digital automática
   - Generación de CUA
   - Códigos QR con datos de factura
   - Estados de firma y emisión
   - Cumplimiento normativo

3. **API REST Segura de Nivel Empresarial**
   - 24 endpoints completamente documentados
   - 5 niveles de rate limiting
   - Protección contra 5 tipos de ataques
   - Middleware de seguridad multinivel
   - API Resources estructurados

4. **Sistema de Auditoría Integral**
   - Logging automático de todas las acciones
   - Información old/new values
   - Filtros y búsqueda avanzada
   - Exportación a Excel
   - Observaciones personalizadas

5. **Gestión Dinámica de Facturas**
   - Creación con múltiples productos
   - Validación de stock en tiempo real
   - Transacciones seguras con rollback
   - Generación de PDF profesional
   - Envío automático por email

6. ❌ **Módulo de Pagos** (ELIMINADO EN v3.2.0)
   - Múltiples tipos de pago
   - Aprobación/rechazo con validación
   - Notificaciones automáticas
   - Actualización de estado de factura
   - Auditoría completa

7. **Interfaz de Usuario Moderna**
   - AdminLTE 3 + Tailwind CSS
   - Modales profesionales
   - Formularios con validación en tiempo real
   - Notificaciones con auto-cierre
   - Responsive design completo

8. **Seguridad Multinivel**
   - SQL Injection prevention
   - XSS protection
   - CSRF protection
   - Data exposure prevention
   - ID obfuscation
   - Rate limiting
   - Request validation
   - IP blocking

---

## 🎯 CASOS DE USO PRINCIPALES

### 1. Flujo de Venta Completo

1. **Vendedor** crea factura:
   - Selecciona cliente
   - Agrega productos dinámicamente
   - Sistema valida stock en tiempo real
   - Calcula subtotal, IVA, total automáticamente
   - Genera datos SRI (CUA, número secuencial)

2. **Sistema** procesa factura:
   - Descuenta stock automáticamente
   - Registra en auditoría
   - Genera firma digital
   - Crea código QR
   - Guarda en base de datos con transacción

3. **Vendedor** firma y emite:
   - Firma digitalmente (estado: FIRMADA)
   - Emite factura (estado: EMITIDA)
   - Sistema envía PDF por email al cliente
   - Registra fecha de emisión

4. **Cliente** recibe:
   - Email con PDF adjunto
   - Factura con firma digital válida
   - Código QR con datos verificables
   - Acceso web para consultar

### 2. ❌ Flujo de Pago (ELIMINADO EN v3.2.0)

**FUNCIONALIDAD ELIMINADA**: El módulo completo de pagos fue removido.

**ALTERNATIVA ACTUAL**:
1. **Administrador** marca factura como pagada manualmente:
   - Accede al módulo de facturas
   - Selecciona la factura
   - Cambia el estado a "pagada"
   - El cambio queda registrado en auditoría

**RAZÓN**: Sin API REST, no había interfaz web funcional para que clientes registren pagos.

### 3. Flujo de Gestión de Usuarios

1. **Administrador** crea usuario:
   - Completa formulario con validación
   - Asigna rol(es)
   - Usuario creado en estado ACTIVO
   - Se registra en auditoría

2. **Usuario** solicita eliminación:
   - Accede a su perfil
   - Ingresa contraseña
   - Confirma eliminación
   - Estado cambia a PENDIENTE ELIMINACIÓN
   - Inicia periodo de gracia (3 días)

3. **Durante periodo de gracia**:
   - Usuario solo puede acceder a perfil
   - Ve tiempo restante
   - Puede cancelar eliminación
   - Middleware bloquea otras rutas

4. **Al cumplirse el plazo**:
   - Sistema elimina automáticamente (soft delete)
   - Si es cliente, se sincroniza
   - Se registra en auditoría

---

## 🔍 TROUBLESHOOTING

### Problemas Comunes

#### 1. Error 403 en API
**Causa**: Token inválido o expirado
**Solución**:
```bash
# Regenerar token
POST /api/refresh-token
# O hacer login nuevamente
POST /api/login
```

#### 2. Error de CORS
**Causa**: Origen no permitido
**Solución**:
- Agregar origen en `config/cors.php`
- Reiniciar servidor: `php artisan serve`

#### 3. Rate Limiting
**Causa**: Demasiados requests
**Solución**:
- Esperar el tiempo indicado en header `Retry-After`
- Revisar configuración en `AppServiceProvider`

#### 4. Error al generar PDF
**Causa**: Extensión GD no instalada
**Solución**:
```bash
# Ubuntu/Debian
sudo apt-get install php8.2-gd
# Restart Apache/Nginx
```

#### 5. Emails no se envían
**Causa**: Configuración incorrecta
**Solución**:
```bash
# Verificar configuración
php artisan test:email-config
# Revisar logs
tail -f storage/logs/laravel.log
```

### Logs Útiles

```bash
# Ver logs en tiempo real
php artisan pail

# Ver logs de Laravel
tail -f storage/logs/laravel.log

# Ver logs de seguridad
grep "Security violation" storage/logs/laravel.log

# Ver logs de API
grep "API" storage/logs/laravel.log
```

---

## 📈 MÉTRICAS DEL PROYECTO

### Estadísticas de Código

- **Total de archivos PHP**: ~135+ (reducido en v3.2.0)
- **Total de controladores**: 10+ (sin API, sin Pagos)
- **Total de modelos**: 6 (sin Pago)
- **Total de migraciones**: 11 (sin tokens, sin pagos)
- **Total de seeders**: 5
- **Total de middleware**: 7 (sin API middleware)
- **Total de policies**: 4 (sin PagoPolicy)
- **Total de requests**: 7+ (sin API, sin Pagos)
- **Total de resources**: 0 (API eliminada)
- **Total de traits**: 0 (API traits eliminados)
- **Total de servicios**: 3
- **Total de vistas Blade**: 40+ (sin vistas de pagos)
- **Total de clases autoload**: 7,874

### Cobertura de Funcionalidades (v3.2.0)

- ✅ Autenticación web: 100%
- ❌ Autenticación API: ELIMINADA (v3.0.0)
- ✅ CRUD Usuarios: 100%
- ✅ CRUD Productos: 100%
- ✅ CRUD Facturas: 100%
- ❌ CRUD Pagos: ELIMINADO (v3.2.0)
- ✅ Sistema de roles: 100%
- ✅ Auditoría: 100%
- ❌ Seguridad API: ELIMINADA (v3.0.0)
- ✅ Integración SRI: 100%
- ✅ Emails: 100%
- ✅ Exportaciones: 80% (Excel implementado, PDF en facturas)

---

## 🎓 TECNOLOGÍAS Y PATRONES

### Patrones de Diseño Implementados

1. **Repository Pattern** (implícito con Eloquent)
2. **Service Layer Pattern** (EmailService, FacturaSRIService)
3. **Observer Pattern** (UserObserver)
4. **Policy Pattern** (Laravel Policies)
5. **Middleware Pattern** (7 middleware personalizados)
6. ❌ **Resource Pattern** (API Resources - ELIMINADO v3.0.0)
7. **Request Pattern** (FormRequests)
8. ❌ **Trait Pattern** (HasDataSanitization, HasObfuscatedId - ELIMINADO v3.0.0)

### Principios SOLID

- ✅ **Single Responsibility**: Cada clase tiene una responsabilidad única
- ✅ **Open/Closed**: Extensible mediante traits y servicios
- ✅ **Liskov Substitution**: Interfaces y contratos respetados
- ✅ **Interface Segregation**: Interfaces específicas por funcionalidad
- ✅ **Dependency Inversion**: Inyección de dependencias en controllers

### Best Practices

1. **Code Organization**: Estructura clara y organizada
2. **Naming Conventions**: Nomenclatura consistente
3. **Documentation**: Código documentado con PHPDoc
4. **Error Handling**: Manejo estructurado de errores
5. **Logging**: Logging completo y contextual
6. **Security**: Múltiples capas de seguridad
7. **Testing**: Comandos de testing automatizados
8. **Version Control**: Git con commits descriptivos

---

## 🌟 CONCLUSIÓN

Este es un **sistema completo de facturación e inventario web de nivel empresarial** que implementa:

- ✅ Arquitectura MVC robusta
- ❌ API REST (ELIMINADA - v3.0.0) - Sistema exclusivamente web
- ✅ Sistema de autenticación web (Laravel Breeze)
- ✅ Gestión avanzada de roles y permisos
- ✅ Integración con SRI Ecuador
- ✅ Múltiples capas de seguridad
- ✅ Auditoría integral
- ✅ Interfaz moderna y responsive
- ✅ Documentación completa
- ✅ Sistema simplificado y optimizado (v3.2.0)

**EVOLUCIÓN DEL PROYECTO**:
- v2.0.0: Sistema completo con API REST + Telescope + Pagos
- v3.0.0: Eliminación de API REST y Laravel Sanctum
- v3.1.0: Eliminación de Laravel Telescope
- v3.2.0: Eliminación del módulo de Pagos

El proyecto está **100% funcional**, **simplificado**, **totalmente documentado** y **listo para producción** como aplicación web tradicional.

---

**Fecha de documentación**: Octubre 2, 2025  
**Versión del sistema**: 3.2.0 (Web-Only - Simplified)  
**Laravel**: 12.0  
**PHP**: 8.2+  
**Migraciones Activas**: 11  
**Clases Autoload**: 7,874  
**Estado**: ✅ Producción Ready
