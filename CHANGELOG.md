# 📝 Changelog - Media Orphan Cleaner

## [1.1.0-beta] - 2024-11-21

### 🎉 Nuevas Funcionalidades

#### Sistema de Testing
- ✅ **Modo Dry-Run**: Previsualizar resultados sin eliminar nada
- ✅ **Script generador de datos de prueba** (`test-data-generator.php`)
- ✅ **Sistema de logs detallado** para debugging

#### Sistema de Backup
- ✅ **Backup automático antes de eliminar**
- ✅ **Restaurar imágenes eliminadas** con un click
- ✅ Guarda metadata, URLs y fecha de eliminación

#### Mejoras de UI/UX
- ✅ **Estimación de espacio a liberar** (en MB)
- ✅ **Export a CSV** de resultados del escaneo
- ✅ **Checkbox "Seleccionar todas"** en tabla
- ✅ **Logs expandibles** del último escaneo
- ✅ Tamaño individual de cada imagen en KB
- ✅ Banner de advertencia en modo prueba
- ✅ Banner de backup disponible

#### Detección Mejorada
- ✅ **Widgets y Sidebars**: Detecta imágenes en widgets
- ✅ **Customizer**: Detecta theme_mods adicionales
- ✅ **ACF (Advanced Custom Fields)**: Detecta campos de ACF
- ✅ **Paginación de content query**: Query SQL optimizada

### ⚡ Optimizaciones

#### Performance
- ✅ **Query SQL paginada** para `post_content` (evita timeouts)
- ✅ **Limpieza automática de transients huérfanos**
- ✅ Batch processing mejorado (500 posts por batch)
- ✅ Cálculo de espacio en disco optimizado

#### Código
- ✅ Logging estructurado con timestamps
- ✅ Mejores mensajes de error
- ✅ Validación mejorada de dry-run
- ✅ Cleanup en `uninstall.php` ampliado

### 🐛 Correcciones

- ✅ Query REGEXP sin límite causaba timeouts
- ✅ Transients quedaban huérfanos en escaneos interrumpidos
- ✅ No se detectaban imágenes en algunos widgets
- ✅ CSS de botones mejora compatibilidad

### 🔧 Técnico

#### Nuevos Métodos en `MOC_Scanner`
```php
- log()                         // Sistema de logging
- cleanup_old_transients()      // Limpieza automática
- extract_ids_from_post_content() // Query SQL paginada
- extract_ids_from_widgets()    // Detección en widgets
- extract_ids_from_customizer() // Detección en customizer
- extract_ids_from_acf()        // Detección en ACF
- calculate_total_size()        // Cálculo de espacio
```

#### Nuevos Métodos en `MOC_Admin`
```php
- handle_export_csv()           // Export CSV
- handle_restore_backup()       // Restaurar backup
- render_dry_run_field()        // UI dry-run
- render_backup_field()         // UI backup
```

#### Nuevas Options
```php
- moc_backup                    // Datos de backup
- moc_last_logs                 // Logs del último escaneo
```

#### Settings Ampliados
```php
- dry_run (boolean)             // Modo prueba
- enable_backup (boolean)       // Activar backup
```

---

## [1.0.0] - 2024-11-XX

### 🎉 Release Inicial

#### Funcionalidades Core
- ✅ Escaneo de imágenes huérfanas
- ✅ Batch processing (200 img/batch)
- ✅ Detección en WooCommerce (productos, galerías, categorías)
- ✅ Detección en Elementor (`_elementor_data`)
- ✅ Detección en JetEngine (meta keys configurables)
- ✅ Detección en post_content (wp-image, Gutenberg)
- ✅ Detección en site options (logo, site_icon)
- ✅ UI con barra de progreso
- ✅ Preview de imágenes en tabla
- ✅ Eliminación masiva con confirmación

#### Arquitectura
- ✅ Clase `MOC_Scanner` para lógica de escaneo
- ✅ Clase `MOC_Admin` para UI y endpoints AJAX
- ✅ Sistema de transients para escaneos largos
- ✅ Extracción recursiva de IDs en JSON/arrays

---

## 📋 Roadmap

### [1.2.0] - Próximas Mejoras Planeadas

#### Funcionalidades
- [ ] **Papelera temporal** (30 días antes de eliminar)
- [ ] **Escaneo programado** (cron)
- [ ] **Notificaciones email** de resultados
- [ ] **Whitelist de IDs** (proteger ciertas imágenes)
- [ ] **Detección de duplicados** (mismo archivo, diferente ID)

#### Performance
- [ ] **Caché de resultados** (24h)
- [ ] **Índices en BD** para queries grandes
- [ ] **Lazy loading** de tabla de resultados

#### Integraciones
- [ ] **Beaver Builder** support
- [ ] **Divi Builder** support
- [ ] **Oxygen Builder** support
- [ ] **Meta Box** support
- [ ] **Toolset** support

---

## 🔗 Links

- **Repositorio:** https://github.com/tu-usuario/media-orphan-cleaner
- **Documentación:** Ver `TESTING.md`
- **Issues:** Reportar en GitHub

---

**Convenciones:**
- ✅ = Implementado
- ⚡ = Optimización
- 🐛 = Bug fix
- 🎉 = Nueva funcionalidad
- 🔧 = Cambio técnico
- 📝 = Documentación
