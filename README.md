# MWM Luvato Plugin

Plugin personalizado para WordPress desarrollado para Luvato con funcionalidades específicas.

## Descripción

Este plugin proporciona funcionalidades personalizadas para el sitio web de Luvato, incluyendo características específicas del negocio y mejoras en la experiencia del usuario.

## Características

- Estructura de plugin profesional y bien organizada
- Hooks de activación y desactivación
- Soporte para internacionalización
- Gestión de scripts y estilos
- Compatible con WordPress 5.0+

## Requisitos

- WordPress 5.0 o superior
- PHP 7.4 o superior

## Instalación

1. Sube la carpeta `mwm-luvato` al directorio `/wp-content/plugins/` de tu instalación de WordPress
2. Activa el plugin a través del menú 'Plugins' en WordPress
3. El plugin se activará automáticamente

## Uso

Una vez activado, el plugin funcionará automáticamente. Puedes personalizar las funcionalidades editando el archivo principal `mwm-luvato.php`.

## Estructura del Plugin

```
mwm-luvato/
├── mwm-luvato.php      # Archivo principal del plugin
├── README.md           # Este archivo
├── .git/               # Control de versiones
└── assets/             # Carpeta para CSS, JS e imágenes (opcional)
    ├── css/
    ├── js/
    └── images/
```

## Personalización

Para agregar nuevas funcionalidades:

1. Edita la clase `MWM_Luvato_Plugin` en `mwm-luvato.php`
2. Agrega nuevos hooks y métodos según sea necesario
3. Crea archivos adicionales para organizar mejor el código

## Soporte

Para soporte técnico, contacta al equipo de MWM.

## Licencia

Este plugin está licenciado bajo GPL v2 o posterior.

## Changelog

### Versión 1.0.0
- Lanzamiento inicial del plugin
- Estructura básica implementada
- Hooks de activación/desactivación
- Soporte para scripts y estilos

