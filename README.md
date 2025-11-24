# 🧹 Media Orphan Cleaner

![Version](https://img.shields.io/badge/version-1.3.0-blue)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)
![License](https://img.shields.io/badge/license-GPL%20v2%2B-green)
![Stable](https://img.shields.io/badge/status-stable-green)

Plugin de WordPress para detectar y eliminar imágenes huérfanas (no utilizadas) en la biblioteca de medios, optimizado para WooCommerce, Elementor, JetEngine, ACF y más.

---

## 🎯 Características Principales

### ✅ Detección Completa
Escanea imágenes en uso en múltiples fuentes:
- **WooCommerce** - Productos, galerías, categorías
- **Elementor** - Páginas, templates, popups
- **JetEngine** - Custom fields configurables
- **ACF** - Advanced Custom Fields
- **Gutenberg** - Bloques nativos
- **Widgets** - Sidebars y widgets
- **Customizer** - Theme mods
- **Site Options** - Logo, favicon, etc.

### 🛡️ Modo Prueba (Dry-Run)
- Previsualiza resultados SIN eliminar nada
- Ideal para testing y verificación
- Banner de advertencia visible
- Doble validación (frontend + backend)

### 📦 Sistema de Backup
- Backup automático antes de eliminar
- Restaurar con 1 click
- Guarda metadata completa
- Confirmación visual

### ⚡ Performance Optimizado
- Batch processing (200 imágenes/lote)
- Query SQL paginada (evita timeouts)
- Limpieza automática de transients
- Soporta sites con 10,000+ imágenes

### 📊 Reportes y Análisis
- **Logs detallados** con timestamps
- **Estimación de espacio** a liberar (MB)
- **Export CSV** de resultados
- Tamaño individual por imagen

---

## 📦 Instalación

### Método 1: Descarga desde GitHub Release (Recomendado)

1. **Descargar el ZIP**:
   - [media-orphan-cleaner-1.3.0.zip](https://github.com/vamlemat/media-orphan-cleaner/releases/download/v1.3.0/media-orphan-cleaner-1.3.0.zip)

2. **Instalar en WordPress**:
   - Ve a **Plugins → Añadir nuevo → Subir plugin**
   - Selecciona el archivo descargado
   - Click **Instalar ahora** → **Activar**

3. **Acceder al plugin**:
   - Menú lateral: **Orphan Cleaner** (debajo de Biblioteca de medios)

### Método 2: Manual (Git)
```bash
cd wp-content/plugins/
git clone https://github.com/vamlemat/media-orphan-cleaner.git
```

### Método 3: WP-CLI
```bash
wp plugin install https://github.com/vamlemat/media-orphan-cleaner/releases/download/v1.3.0/media-orphan-cleaner-1.3.0.zip --activate
```

---

## 🚀 Uso Rápido

### 1️⃣ Primer Escaneo (Modo Seguro)

1. Ve a **Orphan Cleaner → Configuración**
2. Activa **"Modo dry-run"** (recomendado la primera vez)
3. Activa **"Crear backup antes de eliminar"**
4. Si usas JetEngine: añade tus meta keys personalizados
5. Guarda cambios

### 2️⃣ Escanear Imágenes

1. Ve a **Orphan Cleaner → Scanner**
2. Click **"Iniciar escaneo"**
3. Espera unos segundos (ver barra de progreso)
4. Revisa los resultados:
   - ✅ **OK**: Archivo físico existe
   - ⚠️ **Sin archivo físico**: Solo registro en BD (100% seguro borrar)

### 3️⃣ Eliminar Huérfanas (Estrategia Recomendada)

**Fase 1 - Solo Registros Fantasma** (más seguro):
```
1. Click "⚠️ Solo fantasma"
2. Click "🗑️ Borrar seleccionadas"
3. Confirmar
```

**Fase 2 - Por Lotes** (para listados grandes):
```
1. Click "✅ Solo físicos"
2. Selecciona las primeras 100-200
3. Click "🗑️ Borrar seleccionadas"
4. Verifica que todo funcione OK
5. Repite con el siguiente lote
```

**Fase 3 - Todas a la Vez** (si tienes backup del servidor):
```
1. Click "☑️ Todas"
2. Click "🗑️ Borrar seleccionadas"
3. Confirmar
```

### 4️⃣ Restaurar Backup (Si es necesario)

Si borraste algo por error:
```
1. Ve a la parte superior de la página
2. Click "📦 Restaurar backup"
3. ¡Listo! Imágenes restauradas
```

---

## 🧪 Testing

### Plugin de Testing Incluido

El repositorio incluye un plugin generador de datos de prueba:

**Instalación**:
1. Descarga [moc-test-data-generator-1.2.0.zip](https://github.com/vamlemat/media-orphan-cleaner/releases/download/v1.3.0/moc-test-data-generator-1.2.0.zip)
2. Instálalo como cualquier plugin de WordPress
3. Actívalo

**Uso**:
1. Ve a **Orphan Cleaner → 🧪 Testing**
2. Click **"Generar datos de prueba"**
   - Crea 21 imágenes (11 usadas, 10 huérfanas esperadas)
3. Ve a **Scanner** y haz un escaneo
4. Verifica que detecta ~10 huérfanas
5. Vuelve a **Testing** y click **"Limpiar datos de prueba"**

**Notas**:
- Funciona sin GD Library (usa placeholders de internet)
- Si no hay conexión, usa imagen 1x1 como fallback
- Ideal para testear el plugin en staging antes de producción

---

## ⚙️ Requisitos

| Requisito | Versión |
|-----------|---------|
| WordPress | 5.0+ |
| PHP | 7.4+ |
| Memoria PHP | 256MB+ recomendado |
| Max Execution Time | 60s+ recomendado |

### Plugins Compatibles
- ✅ WooCommerce
- ✅ Elementor
- ✅ JetEngine
- ✅ JetFormBuilder
- ✅ ACF (Advanced Custom Fields)
- ✅ Gutenberg (nativo)

---

## 📚 Documentación

| Documento | Descripción |
|-----------|-------------|
| [CHANGELOG.md](CHANGELOG.md) | Historial completo de cambios por versión |
| [CONTRIBUTING.md](CONTRIBUTING.md) | Guía para contribuidores (Git Flow) |
| [LICENSE](LICENSE) | Licencia GPL v2+ |

---

## 🔧 Configuración Avanzada

### Meta Keys de JetEngine
Si usas JetEngine con campos de imagen personalizados:

```
Ajustes > Meta keys extra de JetEngine:

imagen_portada
galeria_proyecto
foto_principal
imagen_hero
```

### Modificar Batch Size
```php
// En functions.php
add_filter('moc_batch_size', function($size) {
    return 500; // Default: 200
});
```

---

## 🐛 Solución de Problemas

### El escaneo se queda colgado
- Aumentar `max_execution_time` en php.ini
- Aumentar `memory_limit`
- Revisar error_log de PHP

### No detecta mis imágenes personalizadas
- Añadir meta keys en configuración
- Verificar que uses attachment IDs (no URLs)
- Revisar logs del escaneo

### Falsos positivos
- Revisar logs detallados
- Ver dónde está usada la imagen
- Añadir meta key si está en custom field
- Reportar issue con detalles

---

## 🤝 Contribuir

¡Las contribuciones son bienvenidas! Este proyecto utiliza **Git Flow** para el desarrollo.

### Estructura de Branches

- **`main`** - Código en producción (estable)
- **`develop`** - Desarrollo activo
- **`feature/*`** - Nuevas funcionalidades
- **`bugfix/*`** - Corrección de bugs
- **`hotfix/*`** - Fixes urgentes en producción

### Flujo de Trabajo Rápido

```bash
# 1. Fork y clonar
git clone https://github.com/TU-USUARIO/media-orphan-cleaner.git
cd media-orphan-cleaner

# 2. Crear branch desde develop
git checkout develop
git checkout -b feature/mi-nueva-feature

# 3. Hacer cambios y commits
git add .
git commit -m "✨ feat: Añadir [descripción]"

# 4. Push y crear Pull Request a develop
git push origin feature/mi-nueva-feature
```

### Guía Completa

Ver [CONTRIBUTING.md](CONTRIBUTING.md) para:
- Convenciones de commits (Conventional Commits + emojis)
- Estándares de código (WordPress Coding Standards)
- Proceso de Pull Request
- Testing y releases

### Reportar Bugs

Crear un [issue](https://github.com/vamlemat/media-orphan-cleaner/issues) con:
- Versiones (WP, PHP, plugin)
- Pasos para reproducir
- Logs del scanner
- Screenshots si aplica

---

## 📊 Roadmap

### v1.3.0 - Próximas Mejoras
- [ ] Papelera temporal (30 días antes de borrado definitivo)
- [ ] Escaneo programado automático (cron jobs)
- [ ] Notificaciones por email con reportes
- [ ] Whitelist de IDs protegidos
- [ ] Detección de imágenes duplicadas
- [ ] Filtros por fecha, tamaño y tipo de archivo
- [ ] Estadísticas históricas con gráficos

### Integraciones Futuras
- [ ] Beaver Builder
- [ ] Divi Builder
- [ ] Oxygen Builder
- [ ] Meta Box
- [ ] Toolset
- [ ] Soporte Multisite
- [ ] API REST completa

---

## 📜 Licencia

Este plugin es software libre; puedes redistribuirlo y/o modificarlo bajo los términos de la GNU General Public License versión 2 o posterior publicada por la Free Software Foundation.

```
Copyright (C) 2024 vamlemat

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```

Ver [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) para más información.

---

## 👤 Autor

**vamlemat**
- GitHub: [@vamlemat](https://github.com/vamlemat)
- Plugin URI: [Media Orphan Cleaner](https://github.com/vamlemat/media-orphan-cleaner)

---

## ⭐ Agradecimientos

Si este plugin te resulta útil:
- ⭐ Dale una estrella en GitHub
- 🐦 Compártelo en redes sociales
- 🐛 Reporta bugs o sugiere mejoras
- 🤝 Contribuye con código

---

## 📈 Estadísticas

![GitHub stars](https://img.shields.io/github/stars/vamlemat/media-orphan-cleaner?style=social)
![GitHub forks](https://img.shields.io/github/forks/vamlemat/media-orphan-cleaner?style=social)
![GitHub issues](https://img.shields.io/github/issues/vamlemat/media-orphan-cleaner)
![GitHub last commit](https://img.shields.io/github/last-commit/vamlemat/media-orphan-cleaner)

---

**Hecho con ❤️ para la comunidad WordPress**

🔗 [Reportar Issue](https://github.com/vamlemat/media-orphan-cleaner/issues) | 📖 [Ver Releases](https://github.com/vamlemat/media-orphan-cleaner/releases) | ⭐ [GitHub](https://github.com/vamlemat/media-orphan-cleaner)
