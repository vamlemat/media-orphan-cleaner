# 🧹 Media Orphan Cleaner v1.1.0-beta

Plugin de WordPress para detectar y eliminar imágenes huérfanas (no utilizadas) en la biblioteca de medios.

---

## 🎯 Características

### ✅ Detección Completa
Escanea imágenes en uso en:
- **WooCommerce** (productos, galerías, categorías)
- **Elementor** (páginas, templates, popups)
- **JetEngine** (custom fields configurables)
- **JetFormBuilder / Gutenberg** (bloques)
- **ACF** (Advanced Custom Fields)
- **Widgets** (sidebars)
- **Customizer** (theme mods)
- **Opciones del sitio** (logo, site icon)
- **Contenido de posts** (wp-image, mediaId, JSON)

### 🛡️ Modo Prueba (Dry-Run)
- Previsualiza resultados SIN eliminar nada
- Ideal para testing y verificación
- Banner de advertencia visible

### 📦 Sistema de Backup
- Backup automático antes de eliminar
- Restaurar imágenes eliminadas con 1 click
- Guarda metadata completa

### ⚡ Performance Optimizado
- Batch processing (200 imágenes/lote)
- Query SQL paginada (evita timeouts)
- Limpieza automática de transients
- Barra de progreso en tiempo real

### 📊 Reportes y Análisis
- **Logs detallados** del escaneo
- **Estimación de espacio** a liberar (MB)
- **Export CSV** de resultados
- Tamaño individual de cada imagen

---

## 📦 Instalación

### Manual
1. Descargar el plugin
2. Subir a `/wp-content/plugins/media-orphan-cleaner/`
3. Activar desde **Plugins > Plugins instalados**
4. Ir a **Herramientas > Media Orphan Cleaner**

### WP-CLI
```bash
wp plugin install media-orphan-cleaner --activate
```

---

## 🚀 Uso Rápido

### 1️⃣ Configuración Inicial
```
Herramientas > Media Orphan Cleaner

✅ Activar "Modo prueba (Dry Run)" (primera vez)
✅ Activar "Backup antes de eliminar" (recomendado)
📝 Añadir meta keys de JetEngine (si usas JetEngine)
   Ejemplo: imagen_portada, galeria_proyecto
```

### 2️⃣ Escanear
```
Click en "Iniciar escaneo"
→ Observa la barra de progreso
→ Revisa los logs expandibles
```

### 3️⃣ Revisar Resultados
```
📊 Total de huérfanas encontradas
💾 Espacio a liberar en MB
📄 Exportar CSV (opcional)
```

### 4️⃣ Eliminar (Opcional)
```
☑️ Seleccionar imágenes a eliminar
🗑️ Click en "Borrar seleccionadas"
✅ Confirmar acción
```

### 5️⃣ Restaurar (Si es necesario)
```
📦 Si aparece banner "Backup disponible"
→ Click en "Restaurar backup"
```

---

## 🧪 Testing

### Plugin de Pruebas Incluido
El repositorio incluye `test-data-generator.php`:

```
Herramientas > MOC Test Generator

🚀 Generar Datos de Prueba
   → Crea 21 imágenes (11 usadas, 10 huérfanas)

🗑️ Limpiar Datos de Prueba
   → Elimina todo lo generado
```

### Guía Completa
Ver **[TESTING.md](TESTING.md)** para plan de pruebas detallado.

---

## ⚙️ Configuración Avanzada

### Meta Keys de JetEngine
Si usas JetEngine con campos de imagen personalizados:

```
Ajustes > Meta keys extra de JetEngine:

imagen_portada
galeria_proyecto
foto_principal
imagen_hero
```

**Formato:** Un meta key por línea.

### Modificar Batch Size
```php
// En tu functions.php
add_filter('moc_batch_size', function($size) {
    return 500; // Default: 200
});
```

### Modificar Content Batch
```php
add_filter('moc_content_batch_size', function($size) {
    return 1000; // Default: 500
});
```

---

## 🔧 Requisitos

- **WordPress:** 5.0+
- **PHP:** 7.4+
- **Memoria:** 256MB+ recomendado
- **Max Execution Time:** 60s+ recomendado

### Plugins Compatibles
- ✅ WooCommerce
- ✅ Elementor
- ✅ JetEngine
- ✅ JetFormBuilder
- ✅ ACF (Advanced Custom Fields)
- ✅ Gutenberg (nativo)

---

## 📊 Estructura de Archivos

```
media-orphan-cleaner/
├── assets/
│   ├── admin.css              # Estilos del admin
│   └── admin.js               # JavaScript del scanner
├── includes/
│   ├── class-moc-scanner.php  # Lógica de escaneo
│   └── class-moc-admin.php    # UI y endpoints AJAX
├── media-orphan-cleaner.php   # Archivo principal
├── uninstall.php              # Limpieza al desinstalar
├── test-data-generator.php    # Script de testing
├── README.md                  # Este archivo
├── TESTING.md                 # Guía de testing
└── CHANGELOG.md               # Historial de cambios
```

---

## 🐛 Solución de Problemas

### El escaneo se queda colgado
```
1. Aumentar max_execution_time en php.ini
2. Reducir batch_size con el filter
3. Verificar que no hay errores PHP
```

### No detecta mis imágenes personalizadas
```
1. Añadir los meta keys en configuración
2. Verificar que uses attachment IDs (no URLs)
3. Revisar los logs para ver qué detecta
```

### Falsos positivos (marca como huérfana algo usado)
```
1. Verificar logs del escaneo
2. Revisar dónde está usada la imagen
3. Añadir meta key si está en custom field
4. Reportar bug con detalles
```

### Error "scan_id inválido"
```
Los transients expiraron (1 hora límite)
→ Iniciar nuevo escaneo
```

---

## 🤝 Contribuir

### Reportar Bugs
1. Revisar issues existentes
2. Crear nuevo issue con:
   - Versiones (WP, PHP, plugin)
   - Pasos para reproducir
   - Logs del scanner
   - IDs específicos si aplica

### Pull Requests
1. Fork del repositorio
2. Crear rama feature/bugfix
3. Seguir estándares WordPress
4. Incluir tests si aplica
5. Actualizar CHANGELOG.md

---

## 📜 Licencia

GPL v2 or later

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

---

## 📞 Soporte

- **Documentación:** Ver archivos .md incluidos
- **Issues:** GitHub Issues
- **Email:** tu-email@ejemplo.com

---

## 🙏 Créditos

Desarrollado con ❤️ para la comunidad WordPress.

### Herramientas Utilizadas
- WordPress API
- jQuery
- WP_Query
- Transients API

---

## 📈 Changelog

Ver **[CHANGELOG.md](CHANGELOG.md)** para historial completo.

### Última Versión: 1.1.0-beta

**Principales cambios:**
- ✅ Modo Dry-Run
- ✅ Sistema de Backup
- ✅ Export CSV
- ✅ Logs detallados
- ✅ Detección de ACF/Widgets/Customizer
- ⚡ Query SQL optimizada

---

**🎉 ¡Gracias por usar Media Orphan Cleaner!**

Si te resulta útil, considera:
- ⭐ Dar una estrella en GitHub
- 🐦 Compartir en redes sociales
- 💬 Dejar una reseña
- ☕ [Invitarme un café](https://tu-link-donacion.com)
