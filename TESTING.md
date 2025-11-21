# 🧪 Guía de Testing - Media Orphan Cleaner v1.1.0-beta

## 📋 Requisitos Previos

- WordPress 5.0+
- PHP 7.4+
- Entorno de TESTING (no producción)
- WooCommerce (opcional, para probar detección en productos)
- ACF / JetEngine (opcional, para probar detección en custom fields)

---

## 🚀 Instalación para Testing

### 1. Instalar el Plugin Principal
```bash
# Activar plugin en WordPress
wp plugin activate media-orphan-cleaner
```

### 2. Instalar el Generador de Datos de Prueba
```bash
# Activar plugin de testing
wp plugin activate test-data-generator
```

---

## 📝 Plan de Pruebas

### **FASE 1: Generación de Datos**

1. **Ir a:** `Herramientas > MOC Test Generator`
2. **Hacer clic en:** "🚀 Generar Datos de Prueba"
3. **Verificar mensaje:** Debe confirmar que se crearon 21 imágenes

**Resultado esperado:**
- ✅ 11 imágenes "usadas" en diferentes contextos
- ❌ 10 imágenes "huérfanas"

---

### **FASE 2: Configuración del Scanner**

1. **Ir a:** `Herramientas > Media Orphan Cleaner`
2. **Configurar:**
   - ✅ Activar "Modo prueba (Dry Run)"
   - ✅ Activar "Backup antes de eliminar"
   - Si usas JetEngine, añadir: `imagen_portada` (una por línea)
3. **Guardar ajustes**

---

### **FASE 3: Escaneo de Prueba**

1. **Hacer clic en:** "Iniciar escaneo"
2. **Observar:**
   - Barra de progreso
   - Mensaje de completado con tamaño en MB
   - Log de escaneo expandible

**Resultado esperado:**
```
✅ Escaneo completado. Encontradas 10 huérfanas.
💾 Espacio a liberar: X.XX MB
```

3. **Verificar logs:**
   - Debe mostrar fuentes escaneadas (postmeta, content, widgets, etc.)
   - Total de IDs en uso
   - Cantidad de huérfanas encontradas

---

### **FASE 4: Verificar Resultados**

1. **Revisar la tabla de huérfanas:**
   - ✅ Debe mostrar 10 imágenes
   - ✅ Checkbox "Seleccionar todas" funciona
   - ✅ Preview de imágenes se muestra
   - ✅ Tamaño en KB visible
   - ✅ Nombre de archivo es enlace clickeable

2. **Exportar CSV:**
   - Hacer clic en "📄 Exportar CSV"
   - Verificar que el archivo contiene: ID, Archivo, URL, Tamaño, Fecha

---

### **FASE 5: Modo Dry-Run (Sin Eliminar)**

1. **Intentar eliminar:**
   - Seleccionar algunas imágenes
   - Hacer clic en "🔒 Borrar deshabilitado (modo prueba activo)"

**Resultado esperado:**
- ❌ Botón debe estar deshabilitado
- ⚠️ Banner amarillo arriba: "MODO PRUEBA ACTIVADO"

---

### **FASE 6: Eliminación Real (Con Backup)**

1. **Desactivar Dry Run:**
   - Quitar checkbox "Modo prueba (Dry Run)"
   - Guardar ajustes

2. **Eliminar 3-5 imágenes:**
   - Seleccionar algunas (no todas)
   - Hacer clic en "🗑️ Borrar seleccionadas"
   - Confirmar

**Resultado esperado:**
- ✅ Redirige a la misma página
- ✅ Banner azul: "📦 Backup disponible: Se eliminaron X imágenes..."
- ✅ Las imágenes desaparecen de la biblioteca de medios

---

### **FASE 7: Restaurar Backup**

1. **Hacer clic en:** "Restaurar backup"
2. **Confirmar**

**Resultado esperado:**
- ✅ Las imágenes vuelven a la biblioteca
- ℹ️ Los IDs pueden cambiar (se crean nuevos attachments)

---

### **FASE 8: Limpieza**

1. **Ir a:** `Herramientas > MOC Test Generator`
2. **Hacer clic en:** "🗑️ Limpiar Datos de Prueba"
3. **Confirmar**

**Resultado esperado:**
- ✅ Todas las imágenes de prueba eliminadas
- ✅ Posts de prueba eliminados

---

## 🐛 Casos de Prueba Críticos

### ✅ Test 1: Detección en Post Content
```
Verificar que detecta:
- <img class="wp-image-123">
- <!-- wp:image {"id":123} -->
- "mediaId":123 (Gutenberg)
```

### ✅ Test 2: Detección en WooCommerce
```
Verificar:
- _thumbnail_id (imagen destacada)
- _product_image_gallery (galería)
- thumbnail_id en termmeta (categorías)
```

### ✅ Test 3: Detección en Elementor
```
Verificar:
- _elementor_data con IDs de imagen
```

### ✅ Test 4: Detección en Widgets
```
Verificar:
- widget_media_image
- Otros widgets con attachment_id
```

### ✅ Test 5: Detección en ACF
```
Verificar:
- Meta keys con "_field_" 
- Meta keys con "acf_"
```

### ✅ Test 6: Performance en Sites Grandes
```
Simular 5000+ imágenes:
- El escaneo debe completarse sin timeout
- Progreso debe ser fluido
- No debe consumir toda la memoria
```

---

## 📊 Métricas de Éxito

| Métrica | Esperado | ✅/❌ |
|---------|----------|------|
| Detección correcta (11 usadas) | 100% | |
| Detección correcta (10 huérfanas) | 100% | |
| Sin falsos positivos | 0 | |
| Sin falsos negativos | 0 | |
| Tiempo escaneo (100 img) | < 10s | |
| Backup funcional | 100% | |
| Export CSV completo | 100% | |

---

## 🚨 Problemas Conocidos

### 1. **Imágenes en Custom Post Types externos**
- Puede no detectar CPT de plugins third-party
- **Solución:** Añadir meta keys manualmente

### 2. **Shortcodes personalizados**
- No detecta shortcodes no estándar
- **Solución:** Usar regex o añadir hook personalizado

### 3. **Backup no restaura archivos físicos**
- Solo restaura attachments en BD
- Los archivos deben existir en disco
- **Limitación conocida**

---

## 📞 Reportar Bugs

Al reportar un bug, incluir:

1. **WordPress version:** X.X.X
2. **PHP version:** X.X.X
3. **Tema activo:** Nombre
4. **Plugins activos:** Lista
5. **Pasos para reproducir:** Detallados
6. **Logs del scanner:** Copiar JSON del log
7. **Falsos positivos/negativos:** IDs específicos

---

## ✅ Checklist Final Pre-Release

- [ ] Todos los tests pasados
- [ ] No hay falsos positivos
- [ ] No hay falsos negativos
- [ ] Performance < 10s para 100 imágenes
- [ ] Backup funciona correctamente
- [ ] CSV se exporta sin errores
- [ ] Dry-run previene eliminación
- [ ] Logs son informativos
- [ ] No hay errores PHP
- [ ] No hay errores JavaScript en consola

---

**🎉 ¡Listo para testing! Si todos los checks pasan, el plugin está listo para BETA release.**
