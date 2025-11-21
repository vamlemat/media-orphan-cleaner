# ⚡ Instalación y Testing Rápido (5 minutos)

## 🎯 Para empezar YA

### 1️⃣ Subir archivos (1 min)
```bash
# Subir carpeta completa a:
/wp-content/plugins/media-orphan-cleaner/

# O por FTP/SFTP
```

### 2️⃣ Activar plugins (30 seg)
```
WordPress Admin > Plugins > Plugins instalados

✅ Activar "Media Orphan Cleaner"
✅ Activar "MOC Test Data Generator"
```

### 3️⃣ Configurar (1 min)
```
Herramientas > Media Orphan Cleaner

⚙️ Ajustes:
  ☑️ Modo prueba (Dry Run) <- IMPORTANTE para testing
  ☑️ Backup antes de eliminar
  
💾 Guardar ajustes
```

### 4️⃣ Generar datos de prueba (1 min)
```
Herramientas > MOC Test Generator

🚀 Click en "Generar Datos de Prueba"

Resultado: 21 imágenes creadas
- 11 "usadas" (en posts, WooCommerce, widgets, etc.)
- 10 "huérfanas" (no usadas)
```

### 5️⃣ Escanear (1 min)
```
Herramientas > Media Orphan Cleaner

▶️ Click en "Iniciar escaneo"

Esperar barra de progreso...

✅ Resultado esperado:
   "Escaneo completado. Encontradas 10 huérfanas."
   "Espacio a liberar: X.XX MB"
```

### 6️⃣ Verificar resultados (30 seg)
```
✅ Debe mostrar tabla con 10 imágenes
✅ Debe mostrar tamaño en MB
✅ Debe mostrar logs expandibles
✅ Export CSV debe estar disponible
✅ Botón borrar debe estar DESHABILITADO (dry-run activo)
```

### 7️⃣ Probar eliminación (1 min)
```
1. Desactivar "Modo prueba (Dry Run)"
2. Guardar ajustes
3. Volver a escanear
4. Seleccionar 2-3 imágenes
5. Click "Borrar seleccionadas"
6. Confirmar

✅ Debe aparecer banner:
   "📦 Backup disponible: Se eliminaron X imágenes..."
```

### 8️⃣ Restaurar backup (30 seg)
```
📦 En el banner azul:
   Click "Restaurar backup"

✅ Las imágenes vuelven a la biblioteca
```

### 9️⃣ Limpiar (30 seg)
```
Herramientas > MOC Test Generator

🗑️ Click "Limpiar Datos de Prueba"

✅ Todo eliminado y listo para usar con datos reales
```

---

## 🎉 ¡LISTO!

**Tiempo total:** ~7 minutos

### Próximos pasos:

#### Para seguir testeando:
👉 Ver **[TESTING.md](TESTING.md)** para plan completo

#### Para usar en producción:
1. ⚠️ Desactivar "MOC Test Data Generator"
2. ✅ Hacer backup completo del site
3. ✅ Activar "Modo prueba" primero
4. ✅ Escanear con datos reales
5. ✅ Verificar que no haya falsos positivos
6. ✅ Solo entonces desactivar modo prueba y eliminar

---

## 📚 Documentación Completa

| Archivo | Contenido |
|---------|-----------|
| **README.md** | Documentación completa del plugin |
| **TESTING.md** | Plan de testing de 8 fases |
| **CHANGELOG.md** | Historial de cambios versión por versión |
| **RESUMEN-ACTUALIZACION.md** | Detalles técnicos de todas las mejoras |
| **INSTALACION-RAPIDA.md** | Este archivo (inicio rápido) |

---

## ⚠️ IMPORTANTE - Antes de usar en PRODUCCIÓN

### ✅ SIEMPRE:
1. Hacer backup completo (BD + archivos)
2. Probar primero en modo dry-run
3. Activar backup antes de eliminar
4. Revisar logs del escaneo
5. Exportar CSV antes de eliminar

### ❌ NUNCA:
1. Eliminar sin revisar primero
2. Usar sin backup del site
3. Desactivar dry-run la primera vez
4. Eliminar todo de golpe (ir poco a poco)
5. Confiar 100% sin verificar manualmente algunos casos

---

## 🐛 ¿Problemas?

### Plugin no aparece en menú
```
Verificar:
- Archivos subidos correctamente
- Plugin activado
- Usuario tiene permisos "manage_options"
```

### Escaneo no encuentra nada
```
Verificar:
- Hay imágenes en biblioteca de medios
- Test data generator se ejecutó correctamente
```

### Escaneo se queda colgado
```
Solución:
- Aumentar max_execution_time en php.ini
- Aumentar memory_limit
- Revisar error_log de PHP
```

### Encuentra falsas huérfanas
```
Solución:
- Revisar logs del escaneo
- Ver dónde está usada realmente
- Añadir meta key en configuración si es custom field
```

---

## 📊 Checklist de Verificación

### Después del testing inicial:

- [ ] 10 huérfanas encontradas (con test data)
- [ ] Tamaño en MB se muestra
- [ ] Logs expandibles funcionan
- [ ] Export CSV descarga
- [ ] Dry-run previene eliminación
- [ ] Backup se crea al eliminar
- [ ] Restore funciona correctamente
- [ ] Select-all checkbox funciona
- [ ] Preview de imágenes se muestra
- [ ] No hay errores en consola

### Todo OK? ✅
👉 **Listo para probar con datos reales (modo dry-run)**

---

**¿Necesitas ayuda?**
- 📖 Lee README.md para info completa
- 🧪 Lee TESTING.md para casos de prueba
- 🔧 Lee RESUMEN-ACTUALIZACION.md para detalles técnicos

**¡A testear! 🚀**
