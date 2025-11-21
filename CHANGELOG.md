# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---

## [1.1.5-beta] - 2024-11-21

### 🎨 Mejorado
- **Botón "Borrar seleccionadas" duplicado**
  - Ahora aparece arriba Y abajo de la tabla
  - Evita scroll en listados grandes (5,000+ imágenes)
  - Incluye tip visual sobre el checkbox "Seleccionar todas"
- Mejor UX para limpieza masiva de huérfanas

### 🔧 Técnico
- Mismo botón en dos ubicaciones (top y bottom)
- Mismo estado dry-run en ambos botones
- Mismo formulario, evita duplicación de código

---

## [1.1.4-beta] - 2024-11-21

### ✨ Añadido
- **Columna "Estado"** en tabla de huérfanas
  - ✅ OK: Archivo físico existe
  - ⚠️ Sin archivo físico: Solo registro en BD
- **Detección de attachments sin archivo físico**
  - Muestra "sin archivo físico" en lugar de `?attachment_id=X`
  - Resalta en rojo estos registros
  - Contador separado de archivos vs registros fantasma
- **Resumen mejorado** con estadísticas:
  - Espacio a liberar (MB)
  - Cantidad de registros sin archivo físico
  - Cantidad de archivos con datos físicos

### 🎨 Mejorado
- Mejor visualización de attachments corruptos/sin archivo
- Título del attachment si no hay nombre de archivo
- Resaltado visual de filas problemáticas
- Preview solo para archivos que existen físicamente

### 🔧 Técnico
- Verificación `file_exists()` antes de mostrar
- Contadores `$orphans_with_file` y `$orphans_no_file`
- Clases CSS `.moc-status-ok` y `.moc-status-no-file`
- Manejo robusto de URLs inválidas

---

## [1.1.3-beta] - 2024-11-21

### 🐛 Corregido
- **Error "Page not found"** al acceder a Testing
- Hook `admin_menu` con prioridad 20 para ejecutarse después del menú principal
- Fallback a menú Herramientas si plugin principal no está activo

### 🔧 Técnico
- Verificación de `class_exists('MOC_Admin')` antes de añadir submenú
- Hook ejecutado con `add_action('admin_menu', 'moc_test_add_menu', 20)`
- Mantiene compatibilidad si se usa solo el plugin de testing

---

## [1.1.2-beta] - 2024-11-21

### ✨ Añadido
- **Método alternativo sin GD library** en test-data-generator
  - Usa placeholders de internet si no hay GD
  - Fallback a imagen mínima válida (1x1 pixel)
  - Mensaje descriptivo del método usado
- **Plugin de testing en menú principal** "Orphan Cleaner > 🧪 Testing"
- **Botón "Limpiar Logs"** en panel de logs
- **Auto-limpieza de logs** mayores de 1 día
- **Edad del log** visible ("hace X horas/días")
- **Mensaje de confirmación** al limpiar logs

### 🎨 Mejorado
- Test-data-generator ahora funciona **sin GD library**
- Ubicación del testing integrada en mismo ecosistema
- UI del panel de logs con botón de limpieza
- Limpieza automática de logs antiguos (> 24h)

### 🔧 Técnico
- Método `cleanup_old_logs()` ejecutado diariamente
- Método `handle_clear_logs()` para limpieza manual
- Opción `moc_last_log_cleanup` para controlar frecuencia
- Uso de placeholders via.placeholder.com cuando no hay GD
- Fallback a base64 de imagen 1x1 si falla todo

---

## [1.1.1-beta] - 2024-11-21

### 🐛 Corregido
- **Error crítico en test-data-generator** al generar imágenes
- Validación de extensión GD de PHP antes de crear imágenes
- Manejo de errores con try-catch en escaneo
- Mensajes de error más descriptivos

### ✨ Añadido
- **Menú propio "Orphan Cleaner"** en barra lateral (después de Biblioteca)
- **Panel de Logs y Debug** con información del sistema
- **Panel de Configuración** separado
- Registro de errores del escaneo (últimos 10)
- Información del sistema (PHP, memoria, GD library, etc.)
- Icono dashicons-images-alt2 en menú

### 🎨 Mejorado
- Estructura del menú con submenús organizados:
  - Scanner (página principal)
  - Logs (debug y system info)
  - Configuración (settings)
- Links rápidos entre secciones
- CSS mejorado para panel de logs
- Mensajes de error más claros

### 🔧 Técnico
- Método `log_error()` para registrar errores
- Método `render_logs_page()` para panel de logs
- Método `render_settings_page()` para configuración
- Opción `moc_scan_errors` para errores persistentes
- Validación de GD library en test generator

---

## [1.1.0-beta] - 2024-11-21

### 🎉 Añadido

#### Sistema de Testing
- Modo Dry-Run para previsualizar sin eliminar
- Script generador de datos de prueba (`test-data-generator.php`)
- Sistema de logs detallado con timestamps
- Logs expandibles en la interfaz

#### Sistema de Backup
- Backup automático antes de eliminar
- Función de restauración con 1 click
- Guarda metadata, URLs y fecha de eliminación
- Banner visual de confirmación

#### Mejoras de UI/UX
- Estimación de espacio a liberar (en MB)
- Export a CSV de resultados del escaneo
- Checkbox "Seleccionar todas" en tabla
- Tamaño individual de cada imagen (KB)
- Banner de advertencia en modo prueba
- Banner informativo de backup disponible
- Preview mejorado de imágenes

#### Detección Mejorada
- **Widgets y Sidebars**: Detecta imágenes en todos los widgets
- **Customizer**: Detecta theme_mods de todos los temas
- **ACF**: Detecta campos de Advanced Custom Fields
- **Paginación**: Query SQL optimizada para post_content

#### Archivos de Seguridad
- Archivos `index.php` en todas las carpetas
- `.htaccess` para proteger archivos sensibles
- `.gitignore` configurado apropiadamente

### ⚡ Mejorado

#### Performance
- Query SQL paginada para `post_content` (500 posts/batch)
- Limpieza automática de transients huérfanos al iniciar escaneo
- Batch processing optimizado
- Cálculo eficiente de espacio en disco

#### Código
- Logging estructurado con datos JSON
- Mejores mensajes de error y validación
- Sanitización mejorada en todos los inputs
- Validación reforzada de dry-run
- Cleanup ampliado en `uninstall.php`

#### Documentación
- README.md completo con badges
- TESTING.md con plan de 8 fases
- INSTALACION-RAPIDA.md para inicio en 5 minutos
- Comentarios inline mejorados

### 🔧 Cambiado
- Header del plugin con información completa (URI, License, etc.)
- Versión actualizada a 1.1.0-beta
- Author actualizado a vamlemat
- Text Domain definido correctamente

### 🐛 Corregido
- Query REGEXP sin límite causaba timeouts en sites grandes
- Transients quedaban huérfanos en escaneos interrumpidos
- No se detectaban imágenes en algunos widgets
- CSS de botones para mejor compatibilidad con temas

### 🔒 Seguridad
- Archivos index.php en todas las carpetas (previene directory listing)
- .htaccess para proteger archivos .md y .txt
- Nonces verificados en todos los formularios
- Capabilities `manage_options` verificados en todos los endpoints

---

## [1.0.0] - 2024-11-XX

### 🎉 Release Inicial

#### Funcionalidades Core
- Escaneo de imágenes huérfanas en biblioteca de medios
- Batch processing (200 imágenes por lote)
- Barra de progreso en tiempo real
- Eliminación masiva con confirmación

#### Detección
- **WooCommerce**: Productos (destacadas, galerías), categorías
- **Elementor**: Páginas, templates, `_elementor_data`
- **JetEngine**: Meta keys configurables por usuario
- **Gutenberg**: Bloques wp-image, mediaId, media_id
- **Post Content**: Regex para detectar imágenes en contenido
- **Site Options**: Logo, site_icon, custom_logo

#### Arquitectura
- Clase `MOC_Scanner` para lógica de escaneo
- Clase `MOC_Admin` para UI y endpoints AJAX
- Sistema de transients para escaneos largos
- Extracción recursiva de IDs en JSON/arrays/objetos

#### UI
- Interfaz en Herramientas > Media Orphan Cleaner
- Tabla con preview de imágenes
- Configuración de meta keys de JetEngine
- Sistema de settings con WordPress Settings API

---

## [Unreleased]

### 🔮 Planeado para v1.2.0
- Papelera temporal (30 días antes de eliminar permanentemente)
- Escaneo programado con WP-Cron
- Notificaciones por email de resultados
- Whitelist para proteger IDs específicos
- Detección de imágenes duplicadas

### 🔌 Integraciones Planeadas
- Beaver Builder
- Divi Builder  
- Oxygen Builder
- Meta Box
- Toolset

---

## Tipos de Cambios

- `Añadido` - Para nuevas funcionalidades
- `Mejorado` - Para mejoras en funcionalidades existentes
- `Obsoleto` - Para funcionalidades que serán removidas
- `Eliminado` - Para funcionalidades eliminadas
- `Corregido` - Para corrección de bugs
- `Seguridad` - Para vulnerabilidades

---

## Versionado

Este proyecto sigue [Semantic Versioning](https://semver.org/lang/es/):

- **MAJOR** (1.x.x): Cambios incompatibles con versiones anteriores
- **MINOR** (x.1.x): Nuevas funcionalidades compatibles con versiones anteriores
- **PATCH** (x.x.1): Corrección de bugs compatibles

---

## Links

- [Repositorio GitHub](https://github.com/vamlemat/media-orphan-cleaner)
- [Issues](https://github.com/vamlemat/media-orphan-cleaner/issues)
- [Releases](https://github.com/vamlemat/media-orphan-cleaner/releases)

---

**Formato basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/)**
