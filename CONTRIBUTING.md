# 🤝 Guía de Contribución

¡Gracias por tu interés en contribuir a **Media Orphan Cleaner**! Este documento explica el flujo de trabajo y las mejores prácticas.

---

## 📋 Tabla de Contenidos

- [Estructura de Branches](#estructura-de-branches)
- [Flujo de Trabajo (Git Flow)](#flujo-de-trabajo-git-flow)
- [Convenciones de Commits](#convenciones-de-commits)
- [Proceso de Pull Request](#proceso-de-pull-request)
- [Estándares de Código](#estándares-de-código)
- [Testing](#testing)

---

## 🌳 Estructura de Branches

El proyecto utiliza **Git Flow** con dos branches principales:

### Branches Principales

| Branch | Propósito | Protección |
|--------|-----------|------------|
| `main` | **Producción** - Solo código estable y testeado | ✅ Protegido |
| `develop` | **Desarrollo** - Integración de nuevas features | 📝 Abierto |

### Branches Temporales

| Tipo | Nomenclatura | Base | Merge a | Ejemplo |
|------|--------------|------|---------|---------|
| **Feature** | `feature/nombre-descriptivo` | `develop` | `develop` | `feature/multisite-support` |
| **Bugfix** | `bugfix/descripcion-bug` | `develop` | `develop` | `bugfix/scan-timeout` |
| **Hotfix** | `hotfix/v1.2.1` | `main` | `main` + `develop` | `hotfix/v1.2.1` |
| **Release** | `release/v1.3.0` | `develop` | `main` + `develop` | `release/v1.3.0` |

---

## 🔄 Flujo de Trabajo (Git Flow)

### 1️⃣ Desarrollar una Nueva Feature

```bash
# 1. Actualizar develop
git checkout develop
git pull origin develop

# 2. Crear branch de feature
git checkout -b feature/nombre-de-tu-feature

# 3. Desarrollar y hacer commits
git add .
git commit -m "✨ feat: Añadir [descripción]"

# 4. Push del branch
git push origin feature/nombre-de-tu-feature

# 5. Crear Pull Request a develop en GitHub
# (desde la UI de GitHub)
```

### 2️⃣ Corregir un Bug

```bash
# 1. Desde develop
git checkout develop
git pull origin develop

# 2. Crear branch de bugfix
git checkout -b bugfix/descripcion-del-bug

# 3. Corregir y hacer commits
git add .
git commit -m "🐛 fix: Corregir [descripción]"

# 4. Push y PR a develop
git push origin bugfix/descripcion-del-bug
```

### 3️⃣ Hotfix Urgente en Producción

```bash
# 1. Desde main
git checkout main
git pull origin main

# 2. Crear branch hotfix
git checkout -b hotfix/v1.2.1

# 3. Corregir el bug crítico
git add .
git commit -m "🚑 hotfix: Corregir [bug crítico]"

# 4. Actualizar versión
# Editar media-orphan-cleaner.php (Version: 1.2.1)
# Añadir entrada en CHANGELOG.md

# 5. Push
git push origin hotfix/v1.2.1

# 6. Crear PR a main Y develop
# (se debe mergear a ambos branches)
```

### 4️⃣ Preparar Release

```bash
# 1. Desde develop (cuando esté listo para release)
git checkout develop
git pull origin develop

# 2. Crear branch release
git checkout -b release/v1.3.0

# 3. Actualizar versión y CHANGELOG
# - media-orphan-cleaner.php (Version: 1.3.0)
# - CHANGELOG.md (añadir sección [1.3.0])
# - README.md (actualizar badges si es necesario)

git add .
git commit -m "🔖 chore: Preparar release v1.3.0"

# 4. Push
git push origin release/v1.3.0

# 5. Crear PR a main
# 6. Después del merge a main, también mergear a develop
# 7. Crear tag y GitHub Release desde main
git checkout main
git pull origin main
git tag -a v1.3.0 -m "Release v1.3.0"
git push origin v1.3.0
```

---

## 📝 Convenciones de Commits

Usamos **Conventional Commits** con emojis para claridad:

### Formato

```
<emoji> <tipo>: <descripción>

[cuerpo opcional]

[footer opcional]
```

### Tipos de Commits

| Emoji | Tipo | Uso | Ejemplo |
|-------|------|-----|---------|
| ✨ | `feat` | Nueva funcionalidad | `✨ feat: Añadir soporte multisite` |
| 🐛 | `fix` | Corrección de bug | `🐛 fix: Corregir timeout en escaneo` |
| 🚑 | `hotfix` | Fix crítico urgente | `🚑 hotfix: Corregir SQL injection` |
| 🎨 | `style` | Cambios de estilo/UX | `🎨 style: Mejorar diseño de tabla` |
| ♻️ | `refactor` | Refactorización | `♻️ refactor: Optimizar query SQL` |
| ⚡ | `perf` | Mejora de performance | `⚡ perf: Reducir uso de memoria` |
| 📝 | `docs` | Documentación | `📝 docs: Actualizar README` |
| ✅ | `test` | Añadir/modificar tests | `✅ test: Añadir tests de scanner` |
| 🔧 | `chore` | Tareas de mantenimiento | `🔧 chore: Actualizar dependencias` |
| 🔖 | `release` | Preparar release | `🔖 release: v1.3.0` |
| 🧹 | `cleanup` | Limpieza de código | `🧹 cleanup: Eliminar código muerto` |

### Ejemplos Buenos

```bash
✨ feat: Añadir filtros por fecha y tamaño

Permite al usuario filtrar huérfanas por:
- Rango de fechas
- Tamaño mínimo/máximo
- Tipo de archivo

Closes #15
```

```bash
🐛 fix: Resolver timeout en sites con +10k imágenes

El escaneo fallaba en sites grandes debido a:
- Query SQL sin paginación
- Memoria insuficiente

Solución:
- Implementar batch processing de 500 items
- Añadir límite de memoria dinámico

Fixes #23
```

### Ejemplos Malos

```bash
❌ update code
❌ fix bug
❌ changes
❌ wip
```

---

## 🔍 Proceso de Pull Request

### Checklist Antes de Crear PR

- [ ] El código sigue los estándares de WordPress
- [ ] Todos los cambios están commiteados
- [ ] Los commits siguen las convenciones
- [ ] El código está testeado localmente
- [ ] No hay errores de linter
- [ ] CHANGELOG.md está actualizado (si aplica)
- [ ] README.md está actualizado (si aplica)
- [ ] El branch está actualizado con develop/main

### Crear Pull Request

1. **Push tu branch** a GitHub
2. **Ir a GitHub** → Pestaña "Pull requests"
3. **Click "New pull request"**
4. **Base branch**: `develop` (o `main` para hotfix/release)
5. **Compare branch**: Tu branch (feature/bugfix/etc.)
6. **Título**: Usar convención de commits
7. **Descripción**: Usar template

### Template de PR

```markdown
## 📋 Descripción

[Descripción clara de los cambios]

## 🎯 Tipo de Cambio

- [ ] 🐛 Bugfix (cambio que corrige un issue)
- [ ] ✨ Feature (nueva funcionalidad)
- [ ] 💥 Breaking change (cambio que rompe compatibilidad)
- [ ] 📝 Documentación
- [ ] 🎨 Estilo/UX
- [ ] ♻️ Refactorización
- [ ] ⚡ Performance

## ✅ Testing

- [ ] Testeado localmente en WP 6.4
- [ ] Testeado con PHP 7.4 y 8.2
- [ ] Testeado con WooCommerce activo
- [ ] Testeado con Elementor activo
- [ ] No genera errores de PHP
- [ ] No genera errores de JS en consola

## 📸 Screenshots (si aplica)

[Capturas de pantalla]

## 📝 Notas Adicionales

[Información adicional para el reviewer]

## 🔗 Issues Relacionados

Closes #[número]
Fixes #[número]
Related to #[número]
```

---

## 💻 Estándares de Código

### PHP (WordPress Coding Standards)

```php
// ✅ BUENO
class MOC_Scanner {
    private $batch_size = 200;
    
    public function scan_attachments() {
        global $wpdb;
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = %s 
                LIMIT %d",
                'attachment',
                $this->batch_size
            )
        );
        
        return $results;
    }
}

// ❌ MALO
class moc_scanner {
    var $batchSize=200;
    function scanAttachments(){
        global $wpdb;
        $results=$wpdb->get_results("SELECT ID FROM {$wpdb->posts} WHERE post_type='attachment'");
        return $results;
    }
}
```

### JavaScript

```javascript
// ✅ BUENO
(function($) {
    'use strict';
    
    function initScanner() {
        const $button = $('#moc-scan-button');
        
        $button.on('click', function(e) {
            e.preventDefault();
            startScan();
        });
    }
    
    $(document).ready(function() {
        initScanner();
    });
})(jQuery);

// ❌ MALO
$(document).ready(function(){
    $('#moc-scan-button').click(function(){
        startScan()
    })
})
```

### Reglas Generales

1. **Indentación**: 4 espacios (no tabs)
2. **Nombres de variables**: `$snake_case` (PHP), `camelCase` (JS)
3. **Nombres de clases**: `PascalCase`
4. **Nombres de funciones**: `snake_case` (PHP), `camelCase` (JS)
5. **Strings**: Comillas simples en PHP, dobles en JS
6. **Seguridad**: Siempre usar `wp_prepare()`, `esc_html()`, `sanitize_text_field()`
7. **I18n**: Usar `__()`, `_e()`, `esc_html__()` para todos los textos

---

## 🧪 Testing

### Testing Manual

Antes de cada PR, testear:

1. **Instalación limpia**:
   ```bash
   wp plugin install media-orphan-cleaner.zip --activate
   ```

2. **Escaneo básico**:
   - Site con 100+ imágenes
   - Site con 5,000+ imágenes
   - Site con 0 imágenes

3. **Compatibilidad**:
   - WordPress 5.0, 6.0, 6.4+
   - PHP 7.4, 8.0, 8.1, 8.2
   - Con/sin WooCommerce
   - Con/sin Elementor
   - Con/sin JetEngine

4. **Funcionalidades**:
   - Escaneo completo
   - Selección inteligente (Todas/Físicos/Fantasma)
   - Borrado con backup
   - Restauración de backup
   - Export CSV
   - Modo dry-run
   - Logs

### Testing con Plugin de Testing

```bash
# 1. Instalar plugin de testing
wp plugin install moc-test-data-generator.zip --activate

# 2. Generar datos
# Orphan Cleaner → Testing → Generar datos

# 3. Escanear y verificar ~10 huérfanas

# 4. Limpiar
# Testing → Limpiar datos
```

---

## 🚀 Release Checklist

Cuando estés listo para crear un release:

- [ ] Todos los PRs mergeados a `develop`
- [ ] Crear branch `release/vX.Y.Z`
- [ ] Actualizar `Version:` en `media-orphan-cleaner.php`
- [ ] Actualizar `MOC_VERSION` constant
- [ ] Añadir entrada en `CHANGELOG.md`
- [ ] Actualizar badges en `README.md`
- [ ] Testing completo en staging
- [ ] PR a `main`
- [ ] Merge a `main`
- [ ] Crear tag `vX.Y.Z`
- [ ] Generar ZIPs
- [ ] Crear GitHub Release
- [ ] Mergear `release/vX.Y.Z` de vuelta a `develop`
- [ ] Eliminar branch `release/vX.Y.Z`

---

## 🤝 Código de Conducta

- 🙏 Sé respetuoso con otros contribuidores
- 💬 Comenta tu código cuando sea necesario
- 📝 Documenta cambios complejos
- 🐛 Reporta bugs con detalles
- ✅ Testea antes de hacer PR
- 📖 Lee la documentación existente

---

## 💡 ¿Tienes Dudas?

- 📖 Lee el [README.md](README.md)
- 📝 Revisa el [CHANGELOG.md](CHANGELOG.md)
- 🐛 Abre un [Issue](https://github.com/vamlemat/media-orphan-cleaner/issues)
- 💬 Pregunta en tu Pull Request

---

## 🎉 ¡Gracias por Contribuir!

Tu ayuda hace que **Media Orphan Cleaner** sea mejor para toda la comunidad WordPress.

---

**Mantenedor**: [@vamlemat](https://github.com/vamlemat)  
**Licencia**: GPL v2+
