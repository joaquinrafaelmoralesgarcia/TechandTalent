# Portal Tech & Talent Services - Guía de Instalación en Hostinger

## 📦 Contenido del Paquete

Este ZIP contiene todo lo necesario para tu portal web:

```
tech-talent-services/
├── index.html              (Página principal)
├── servicios.html          (Catálogo de servicios)
├── sectores.html           (Industrias)
├── insights.html           (Casos de estudio)
├── casos.html              (Casos de éxito)
├── nosotros.html           (About)
├── contacto.html           (Contacto)
├── assets/                 (Imágenes, logo, fotos)
│   ├── logo-mark.png
│   ├── photos/            (Tus fotos reales)
│   └── img/               (Imágenes generadas)
└── README.md

```

## 🚀 Instalación en Hostinger (cPanel)

### Paso 1: Accede a tu cPanel
1. Ve a **https://techandtalentservices.com.mx/cpanel**
2. Inicia sesión con tu usuario de Hostinger

### Paso 2: Abre el Administrador de Archivos
1. Busca **"File Manager"** (Administrador de Archivos)
2. Haz clic para abrirlo

### Paso 3: Navega a la carpeta `public_html`
- Esta es la carpeta raíz de tu dominio
- Aquí es donde irán todos tus archivos web

### Paso 4: Sube los archivos
**Opción A (Recomendado):**
1. En el Administrador de Archivos, busca un botón **"Upload"** (Subir)
2. Sube el archivo `tech-talent-services.zip`
3. Haz clic derecho en el ZIP → **"Extract"** (Extraer)
4. Si pregunta dónde extraer, elige la carpeta actual (`public_html`)
5. Una vez extraído, **elimina el ZIP**

**Opción B (Manual):**
1. Extrae el ZIP en tu computadora
2. Copia todos los archivos y carpetas dentro de `tech-talent-services/`
3. Sube cada archivo a `public_html` usando **Drag & Drop** en el Administrador

### Paso 5: Verifica que funciona
1. Abre tu navegador
2. Ve a **https://techandtalentservices.com.mx**
3. Deberías ver la página principal del portal

## 🔧 Notas Técnicas

- **Lenguaje:** HTML + CSS inline + JavaScript vanilla
- **No requiere:** Base de datos, Node.js, ni herramientas externas
- **Compatible con:** Todos los navegadores modernos
- **Responsive:** Optimizado para desktop, tablet y mobile

## 📧 Formulario de Contacto

El formulario de contacto muestra un mensaje de confirmación, pero **no envía emails automáticamente**. 

Para que funcione el envío de emails real, necesitas:
1. Un script PHP en tu servidor de Hostinger
2. Configurar el formulario para enviar datos a ese script

Contacta a soporte de Hostinger si necesitas ayuda con PHP/envío de emails.

## ❓ Troubleshooting

**P: ¿Qué hago si los archivos no aparecen?**
- Espera 5-10 minutos para que el DNS se propague
- Borra la caché del navegador (Ctrl+Shift+Delete)

**P: ¿Las imágenes no carga?**
- Verifica que la carpeta `assets/` esté en `public_html` con todos sus contenidos
- Los permisos deben ser 644 para archivos y 755 para carpetas (automático en cPanel)

**P: ¿Puedo editar el contenido después?**
- Sí, todos los textos están en los archivos `.html`
- Usa un editor de texto (Notepad, VS Code) para editar y sube nuevamente

---

**¿Preguntas?** Contacta a tu proveedor de hosting o lee la documentación de Hostinger.
