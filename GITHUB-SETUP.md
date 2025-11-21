# 🚀 Guía de Configuración en GitHub

## 📦 Archivos para Subir a GitHub

### ✅ Archivos Principales (INCLUIR)
```
media-orphan-cleaner/
├── .gitignore                      ✅ Control de versiones
├── .htaccess                       ✅ Seguridad
├── LICENSE                         ✅ Licencia GPL v2
├── README.md                       ✅ Documentación principal
├── CHANGELOG.md                    ✅ Historial de cambios
├── TESTING.md                      ✅ Guía de testing
├── INSTALACION-RAPIDA.md           ✅ Inicio rápido
├── index.php                       ✅ Seguridad
├── media-orphan-cleaner.php        ✅ Plugin principal
├── uninstall.php                   ✅ Desinstalación
├── test-data-generator.php         ✅ Testing (opcional)
│
├── assets/
│   ├── index.php                   ✅ Seguridad
│   ├── admin.css                   ✅ Estilos
│   └── admin.js                    ✅ JavaScript
│
└── includes/
    ├── index.php                   ✅ Seguridad
    ├── class-moc-scanner.php       ✅ Scanner
    └── class-moc-admin.php         ✅ Admin
```

### ❌ Archivos a EXCLUIR
```
❌ RESUMEN-ACTUALIZACION.md         (documentación interna)
❌ ESTRUCTURA-FINAL.txt              (documentación interna)
❌ *.zip                             (releases, no código)
```

---

## 🔧 Pasos para Subir a GitHub

### 1️⃣ Crear Repositorio en GitHub

```bash
# Ir a https://github.com/new
Nombre: media-orphan-cleaner
Descripción: Plugin WordPress para limpiar imágenes huérfanas
Público ✅
NO inicializar con README (ya lo tenemos)
```

### 2️⃣ Inicializar Git Local

```bash
cd /ruta/a/media-orphan-cleaner/
git init
git add .
git commit -m "🎉 Initial commit - v1.1.0-beta"
```

### 3️⃣ Conectar con GitHub

```bash
git remote add origin https://github.com/vamlemat/media-orphan-cleaner.git
git branch -M main
git push -u origin main
```

### 4️⃣ Crear Release en GitHub

```bash
# Ir a: https://github.com/vamlemat/media-orphan-cleaner/releases/new

Tag: v1.1.0-beta
Release title: v1.1.0-beta - Testing, Backup y Performance
Description:
```

```markdown
## 🎉 Primera Beta Release

### ✨ Características Principales
- 🧪 Modo Dry-Run para testing seguro
- 📦 Sistema de Backup y Restore
- ⚡ Performance optimizado (query paginada)
- 📊 Logs detallados y export CSV
- 🔍 Detección ampliada (ACF, Widgets, Customizer)

### 📦 Archivos para Descargar
- **media-orphan-cleaner-1.1.0-beta.zip** - Plugin instalable en WordPress
- **moc-test-data-generator.zip** - Herramienta de testing (opcional)

### 📚 Documentación
- [README.md](README.md) - Documentación completa
- [TESTING.md](TESTING.md) - Guía de testing
- [CHANGELOG.md](CHANGELOG.md) - Historial de cambios

### ⚠️ Nota Beta
Esta es una versión beta. Recomendamos:
- ✅ Usar en staging primero
- ✅ Activar modo dry-run
- ✅ Hacer backup del site

Ver [CHANGELOG.md](CHANGELOG.md) para detalles completos.
```

**Adjuntar archivos:**
- `media-orphan-cleaner-1.1.0-beta.zip`
- `moc-test-data-generator.zip`

### 5️⃣ Configurar Topics en GitHub

```
Settings > Repository > Topics:

wordpress
wordpress-plugin
woocommerce
elementor
jetengine
acf
image-optimization
media-library
php
```

### 6️⃣ Configurar About (Sidebar derecho)

```
Description: 
🧹 Plugin WordPress para detectar y eliminar imágenes huérfanas. 
Compatible con WooCommerce, Elementor, JetEngine, ACF y más.

Website: https://github.com/vamlemat/media-orphan-cleaner

Topics: wordpress, wordpress-plugin, woocommerce, elementor, 
        jetengine, acf, media-library, php
```

### 7️⃣ Habilitar Issues y Discussions

```
Settings > General > Features:
✅ Issues
✅ Discussions
✅ Wiki (opcional)
```

---

## 📋 Configuración Adicional

### Crear Labels para Issues

```
bug 🐛           - Reportes de bugs
enhancement ✨    - Nuevas funcionalidades
help wanted 🙋   - Ayuda bienvenida
good first issue - Para nuevos contribuidores
question ❓      - Preguntas
documentation 📚 - Mejoras en docs
```

### Crear Plantilla de Issue

Archivo: `.github/ISSUE_TEMPLATE/bug_report.md`

```markdown
---
name: Bug Report
about: Reportar un bug
title: "[BUG] "
labels: bug
---

## 🐛 Descripción del Bug
Una descripción clara del problema.

## 📋 Para Reproducir
1. Ir a '...'
2. Click en '...'
3. Ver error

## ✅ Comportamiento Esperado
Qué debería suceder.

## 📸 Screenshots
Si aplica, añadir screenshots.

## 🔧 Información del Sistema
- WordPress: [ej. 6.4]
- PHP: [ej. 8.1]
- Plugin: [ej. 1.1.0-beta]
- Tema: [ej. Astra]
- Plugins activos: [lista]

## 📝 Logs del Scanner
Copiar logs del escaneo si están disponibles.

## 📎 Información Adicional
Cualquier otra información relevante.
```

### Crear Plantilla de Pull Request

Archivo: `.github/PULL_REQUEST_TEMPLATE.md`

```markdown
## 📝 Descripción
Descripción clara de los cambios.

## 🔗 Issue Relacionado
Fixes #(issue)

## 🔄 Tipo de Cambio
- [ ] Bug fix
- [ ] Nueva funcionalidad
- [ ] Breaking change
- [ ] Documentación

## ✅ Checklist
- [ ] He testeado los cambios
- [ ] Sigo el estilo de código del proyecto
- [ ] He actualizado la documentación
- [ ] He añadido tests (si aplica)
- [ ] Todos los tests pasan
- [ ] He actualizado CHANGELOG.md
```

---

## 🎨 README Badges Recomendados

Ya incluidos en el README.md:

```markdown
![Version](https://img.shields.io/badge/version-1.1.0--beta-blue)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)
![License](https://img.shields.io/badge/license-GPL%20v2%2B-green)
```

Adicionales (añadir cuando aplique):

```markdown
![GitHub stars](https://img.shields.io/github/stars/vamlemat/media-orphan-cleaner?style=social)
![GitHub forks](https://img.shields.io/github/forks/vamlemat/media-orphan-cleaner?style=social)
![GitHub issues](https://img.shields.io/github/issues/vamlemat/media-orphan-cleaner)
![GitHub last commit](https://img.shields.io/github/last-commit/vamlemat/media-orphan-cleaner)
```

---

## 📊 Estructura Final en GitHub

```
vamlemat/media-orphan-cleaner
├── 📄 README.md (con badges y documentación)
├── 📄 CHANGELOG.md
├── 📄 LICENSE
├── 📄 TESTING.md
├── 📄 INSTALACION-RAPIDA.md
├── 📁 .github/
│   ├── ISSUE_TEMPLATE/
│   └── PULL_REQUEST_TEMPLATE.md
├── 📁 assets/
├── 📁 includes/
└── 📦 Releases/
    ├── v1.1.0-beta
    │   ├── media-orphan-cleaner-1.1.0-beta.zip
    │   └── moc-test-data-generator.zip
    └── ...
```

---

## 🚀 Comandos Git Útiles

### Crear nueva versión

```bash
# 1. Actualizar versión en archivos
# 2. Actualizar CHANGELOG.md
# 3. Commit cambios
git add .
git commit -m "🔖 Release v1.1.0"

# 4. Crear tag
git tag -a v1.1.0 -m "Release v1.1.0"

# 5. Push con tags
git push origin main --tags
```

### Crear branch para desarrollo

```bash
git checkout -b develop
git push -u origin develop
```

### Proteger rama main

```
Settings > Branches > Add rule
Branch name: main
✅ Require pull request reviews before merging
✅ Require status checks to pass before merging
```

---

## 📢 Promoción

### En tu perfil de GitHub

Añadir al README de perfil:

```markdown
### 🧹 Media Orphan Cleaner
Plugin WordPress para limpiar imágenes huérfanas. 
[Ver proyecto →](https://github.com/vamlemat/media-orphan-cleaner)
```

### Social Media

```
🎉 Nuevo proyecto open source!

🧹 Media Orphan Cleaner - Plugin WordPress para 
detectar y eliminar imágenes huérfanas.

✨ Características:
- Modo dry-run
- Sistema de backup
- Performance optimizado
- Compatible con WooCommerce, Elementor, ACF...

⭐ Dale una estrella en GitHub!
https://github.com/vamlemat/media-orphan-cleaner
```

---

## ✅ Checklist Final

Antes de hacer público:

- [ ] README.md completo y claro
- [ ] CHANGELOG.md actualizado
- [ ] LICENSE incluido
- [ ] .gitignore configurado
- [ ] Archivos de seguridad (index.php, .htaccess)
- [ ] Version actualizada en plugin principal
- [ ] Author correcto (vamlemat)
- [ ] Release creada en GitHub
- [ ] ZIPs adjuntos a release
- [ ] Topics configurados
- [ ] Issues habilitados
- [ ] Descripción del repo configurada

---

**¡Listo para compartir con el mundo! 🌍**
