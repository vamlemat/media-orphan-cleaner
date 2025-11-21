# 🧹 Media Orphan Cleaner

![Version](https://img.shields.io/badge/version-1.2.0-blue)
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

### Desde ZIP
1. Descargar el archivo `media-orphan-cleaner.zip`
2. En WordPress: **Plugins > Añadir nuevo > Subir plugin**
3. Seleccionar el archivo ZIP
4. Click en **Instalar ahora**
5. Activar el plugin

### Manual
```bash
cd wp-content/plugins/
git clone https://github.com/vamlemat/media-orphan-cleaner.git
```

### WP-CLI
```bash
wp plugin install media-orphan-cleaner.zip --activate
```

---

## 🚀 Uso

### 1️⃣ Configuración Inicial
```
Herramientas > Media Orphan Cleaner

⚙️ Ajustes recomendados:
  ☑️ Modo prueba (Dry Run) - Primera vez
  ☑️ Backup antes de eliminar - Siempre
  📝 Meta keys de JetEngine - Si usas JetEngine
```

### 2️⃣ Escanear
```
▶️ Click "Iniciar escaneo"
📊 Ver progreso en tiempo real
📝 Revisar logs detallados
```

### 3️⃣ Revisar Resultados
```
✅ Total de huérfanas encontradas
💾 Espacio a liberar (MB)
📄 Exportar CSV (opcional)
```

### 4️⃣ Eliminar (Opcional)
```
1. Desactivar "Modo prueba"
2. Seleccionar imágenes
3. Click "Borrar seleccionadas"
4. Confirmar acción
```

### 5️⃣ Restaurar (Si es necesario)
```
📦 Click "Restaurar backup"
✅ Imágenes restauradas
```

---

## 🧪 Testing

El plugin incluye un script generador de datos de prueba:

```
1. Activar "MOC Test Data Generator"
2. Ir a: Herramientas > MOC Test Generator
3. Generar datos de prueba (21 imágenes)
4. Hacer escaneo
5. Verificar 10 huérfanas encontradas
6. Limpiar datos de prueba
```

Ver [`TESTING.md`](TESTING.md) para plan completo.

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
| [TESTING.md](TESTING.md) | Guía de testing completa (8 fases) |
| [CHANGELOG.md](CHANGELOG.md) | Historial de cambios |
| [INSTALACION-RAPIDA.md](INSTALACION-RAPIDA.md) | Inicio rápido (5 minutos) |

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

¡Las contribuciones son bienvenidas!

1. Fork del repositorio
2. Crear rama feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit cambios (`git commit -m 'Añadir nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abrir Pull Request

### Reportar Bugs
Crear un [issue](https://github.com/vamlemat/media-orphan-cleaner/issues) con:
- Versiones (WP, PHP, plugin)
- Pasos para reproducir
- Logs del scanner
- IDs específicos si aplica

---

## 📊 Roadmap

### v1.2.0 - Próximas Mejoras
- [ ] Papelera temporal (30 días)
- [ ] Escaneo programado (cron)
- [ ] Notificaciones por email
- [ ] Whitelist de IDs protegidos
- [ ] Detección de duplicados

### Integraciones Futuras
- [ ] Beaver Builder
- [ ] Divi Builder
- [ ] Oxygen Builder
- [ ] Meta Box
- [ ] Toolset

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

🔗 [Reportar Issue](https://github.com/vamlemat/media-orphan-cleaner/issues) | 📖 [Documentación](https://github.com/vamlemat/media-orphan-cleaner/wiki) | 💬 [Discusiones](https://github.com/vamlemat/media-orphan-cleaner/discussions)
