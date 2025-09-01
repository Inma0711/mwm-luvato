# MWM Luvato - Multiplicador de Precios

## 🎯 **Descripción de la Funcionalidad**

Este plugin implementa un sistema de multiplicación de cantidades que funciona de la siguiente manera:

### **Lógica de Multiplicación:**
```
Cantidad Final = Cantidad del Producto × Cantidad del Pack (Selector General)
```

### **Ejemplo:**
- **Selector del Producto:** "Deurgreep antraciet" = 3 unidades
- **Selector General (Pack):** = 2 
- **Resultado Final:** 3 × 2 = **6 unidades** en el carrito

## 🚀 **Cómo Funciona**

### **1. Interceptación del Proceso:**
- El plugin se activa **antes** de añadir productos al carrito
- Utiliza el hook `woocommerce_add_to_cart_validation` con prioridad 60
- Intercepta tanto botones como formularios de añadir al carrito

### **2. Captura de Cantidades:**
- **JavaScript:** Captura la cantidad del selector general (pack)
- **PHP:** Recibe esta cantidad y la multiplica por la cantidad del producto
- **Resultado:** Modifica la cantidad antes de procesar el carrito

### **3. Visualización en el Carrito:**
- Muestra la cantidad multiplicada en el carrito
- Incluye tooltip que muestra la multiplicación (ej: "3 × 2 = 6")
- Estilos visuales para identificar productos multiplicados

## 📁 **Estructura de Archivos**

```
mwm-luvato/
├── includes/
│   ├── class-price-multiplier.php    # Lógica principal PHP
│   └── index.php                     # Protección de directorio
├── assets/
│   ├── css/
│   │   ├── style.css                 # Estilos generales
│   │   └── price-multiplier.css      # Estilos del multiplicador
│   └── js/
│       ├── script.js                 # JavaScript general
│       └── price-multiplier.js      # JavaScript del multiplicador
└── mwm-luvato.php                    # Archivo principal del plugin
```

## 🔧 **Hooks Implementados**

### **PHP Hooks:**
- `woocommerce_add_to_cart_validation` (prioridad 60)
- `woocommerce_add_cart_item_data` (prioridad 10)
- `woocommerce_cart_item_quantity` (prioridad 10)

### **JavaScript Events:**
- Clic en botones de añadir al carrito
- Envío de formularios de carrito
- Cambios en selectores de cantidad de pack

## 🎨 **Clases CSS Utilizadas**

### **Para Cantidades Multiplicadas:**
- `.mwm-quantity-display` - Cantidad final mostrada
- `.mwm-multiplier-active` - Indicador visual de multiplicación activa

### **Para Debugging:**
- `.mwm-debug-info` - Información de debugging
- `.mwm-debug-item` - Elementos individuales de debug

## 🧪 **Testing y Debugging**

### **Console JavaScript:**
```javascript
// Ver información de debugging
MWMPriceMultiplier.debug();

// Ver cantidad de pack actual
console.log('Pack Quantity:', MWMPriceMultiplier.getPackQuantity());
```

### **Logs PHP:**
- Los logs se escriben en `error_log` de WordPress
- Formato: `MWM Luvato: Cantidad multiplicada - Original: X, Pack: Y, Resultado: Z`

## 📋 **Requisitos del Sistema**

### **WordPress:**
- Versión mínima: 5.0
- WooCommerce activo

### **Plugins Compatibles:**
- YITH WooCommerce Advanced Product Options Premium
- Cualquier plugin que use hooks estándar de WooCommerce

### **PHP:**
- Versión mínima: 7.4
- Extensión `session` habilitada (opcional)

## 🔄 **Flujo de Funcionamiento**

1. **Usuario selecciona cantidades:**
   - Cantidad del producto específico
   - Cantidad del pack (selector general)

2. **Usuario hace clic en "Añadir al carrito":**
   - JavaScript captura la cantidad del pack
   - Se envía como campo oculto `pack_quantity`

3. **Plugin intercepta la acción:**
   - Hook `woocommerce_add_to_cart_validation`
   - Calcula: cantidad_producto × cantidad_pack
   - Modifica la cantidad antes del procesamiento

4. **Resultado en el carrito:**
   - Se muestra la cantidad multiplicada
   - Tooltip muestra la operación matemática
   - Estilos visuales identifican la multiplicación

## 🚨 **Consideraciones Importantes**

### **Compatibilidad:**
- Funciona con cualquier plugin que respete los hooks de WooCommerce
- No interfiere con la funcionalidad existente de YITH WAPO
- Prioridad 60 asegura que se ejecute después de la validación principal

### **Rendimiento:**
- Solo se activa cuando hay cantidad de pack > 1
- No afecta productos sin multiplicación
- Logs de debugging se pueden deshabilitar en producción

### **Seguridad:**
- Validación de tipos de datos
- Sanitización de entradas
- Prevención de acceso directo a archivos

## 🔮 **Futuras Mejoras**

- [ ] Panel de administración para configuraciones
- [ ] Historial de multiplicaciones
- [ ] Reglas personalizadas de multiplicación
- [ ] Integración con sistemas de inventario
- [ ] Reportes de ventas con multiplicaciones


