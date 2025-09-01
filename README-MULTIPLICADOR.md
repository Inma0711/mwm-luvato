# 🧮 Multiplicador de Cantidades - MWM Luvato

## 📋 Descripción

Este plugin implementa un sistema de multiplicación automática de cantidades en WooCommerce, especialmente diseñado para trabajar con complementos de YITH. Cuando se añade un producto al carrito, la cantidad se multiplica automáticamente por el valor del selector "pack_quantity".

## 🔧 Funcionalidades

### ✅ Multiplicación Automática
- **Detección automática** del valor `pack_quantity` desde el formulario
- **Multiplicación en tiempo real** cuando se añade al carrito
- **Compatibilidad con YITH** WooCommerce Product Add-ons
- **Soporte para complementos** y productos variables

### 🎯 Hooks de WooCommerce Utilizados

1. **`woocommerce_add_to_cart_validation`** - Validación antes de añadir al carrito
2. **`woocommerce_add_cart_item_data`** - Modificación de datos del item
3. **`woocommerce_add_to_cart`** - Procesamiento después de añadir al carrito
4. **`woocommerce_cart_item_quantity`** - Visualización de cantidades en el carrito

### 🔍 Detección de Complementos YITH

El plugin detecta automáticamente si:
- El plugin YITH WooCommerce Product Add-ons está activo
- El producto tiene complementos configurados
- Hay complementos globales aplicables al producto

## 📁 Estructura de Archivos

```
mwm-luvato/
├── includes/
│   ├── class-price-multiplier.php    # Lógica principal de multiplicación
│   └── class-debug-helper.php        # Herramientas de debugging
├── assets/
│   ├── js/
│   │   └── price-multiplier.js       # JavaScript del frontend
│   └── css/
│       └── price-multiplier.css      # Estilos CSS
└── mwm-luvato.php                    # Archivo principal del plugin
```

## 🚀 Cómo Funciona

### 1. **Detección del Pack Quantity**
```php
private function get_pack_quantity() {
    // Busca en POST data
    if (isset($_POST['pack_quantity'])) {
        return intval($_POST['pack_quantity']);
    }
    
    // Busca en GET data
    if (isset($_GET['pack_quantity'])) {
        return intval($_GET['pack_quantity']);
    }
    
    // Busca en sesión de WooCommerce
    if (WC()->session && WC()->session->get('pack_quantity')) {
        return intval(WC()->session->get('pack_quantity'));
    }
    
    return 1; // Por defecto, no multiplicar
}
```

### 2. **Procesamiento de Complementos YITH**
```php
private function has_yith_addons($product_id) {
    // Verifica si YITH está activo
    if (!class_exists('YITH_WAPO')) {
        return false;
    }
    
    // Verifica complementos del producto
    $addons = get_post_meta($product_id, '_yith_wapo_product_addon', true);
    
    // Verifica complementos globales
    $global_addons = get_posts(array(
        'post_type' => 'yith_wapo_group',
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => '_yith_wapo_products',
                'value' => $product_id,
                'compare' => 'LIKE'
            )
        )
    ));
    
    return !empty($addons) || !empty($global_addons);
}
```

### 3. **Multiplicación en el Carrito**
```php
public function multiply_quantity_after_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    $pack_quantity = WC()->session->get('mwm_pack_quantity', 1);
    $original_quantity = WC()->session->get('mwm_original_quantity', $quantity);
    
    if ($pack_quantity > 1) {
        $cart = WC()->cart;
        $cart_item = $cart->get_cart_item($cart_item_key);
        
        if ($cart_item) {
            $multiplied_quantity = $original_quantity * $pack_quantity;
            $cart->set_quantity($cart_item_key, $multiplied_quantity, false);
            
            // Guardar información adicional
            $cart_item['mwm_original_quantity'] = $original_quantity;
            $cart_item['mwm_pack_quantity'] = $pack_quantity;
            $cart_item['mwm_multiplied_quantity'] = $multiplied_quantity;
            
            $cart->cart_contents[$cart_item_key] = $cart_item;
        }
    }
}
```

## 🐛 Debugging

### Información de Debug Disponible
- **Pack Quantity** detectado
- **Estado de YITH** (activo/inactivo)
- **Contenido del carrito** actual
- **Datos de sesión** de WooCommerce
- **Logs del servidor** (error_log)

### Herramientas de Debug
- **Panel de debug** en el frontend (solo para administradores)
- **Endpoint AJAX** para obtener información detallada
- **Logs automáticos** en el servidor

## 📝 Ejemplo de Uso

### Formulario HTML
```html
<form class="cart" action="" method="post" enctype="multipart/form-data">
    <!-- Selector de cantidad del producto -->
    <input type="number" name="quantity" value="1" min="1" />
    
    <!-- Selector de pack quantity -->
    <select name="pack_quantity">
        <option value="1">1 Pack</option>
        <option value="2">2 Packs</option>
        <option value="3">3 Packs</option>
    </select>
    
    <!-- Botón de añadir al carrito -->
    <button type="submit" name="add-to-cart" value="123">Añadir al carrito</button>
</form>
```

### Resultado en el Carrito
- **Cantidad original**: 1
- **Pack quantity**: 3
- **Cantidad final**: 3 (1 × 3)

## ⚙️ Configuración

### Requisitos
- **WordPress**: 5.0+
- **WooCommerce**: 3.0+
- **PHP**: 7.4+
- **YITH WooCommerce Product Add-ons**: Opcional

### Instalación
1. Subir el plugin a `/wp-content/plugins/mwm-luvato/`
2. Activar el plugin desde el admin de WordPress
3. Configurar productos con complementos YITH (opcional)

## 🔧 Personalización

### Modificar el Selector de Pack
```php
// En class-price-multiplier.php, método get_pack_quantity()
// Cambiar el nombre del campo:
if (isset($_POST['mi_campo_personalizado'])) {
    return intval($_POST['mi_campo_personalizado']);
}
```

### Agregar Validaciones Adicionales
```php
// En multiply_quantity_before_cart()
if ($pack_quantity > 10) {
    wc_add_notice('La cantidad máxima de packs es 10', 'error');
    return false;
}
```

## 📊 Logs y Monitoreo

### Logs del Servidor
```php
error_log("MWM Luvato: Cantidad multiplicada - Original: {$quantity}, Pack: {$pack_quantity}, Resultado: " . ($quantity * $pack_quantity));
```

### Información de Debug
```php
$debug_info = array(
    'pack_quantity' => $this->get_pack_quantity(),
    'yith_addons_active' => class_exists('YITH_WAPO'),
    'cart_contents' => WC()->cart->get_cart_contents(),
    'cart_count' => WC()->cart->get_cart_contents_count()
);
```

## 🚨 Solución de Problemas

### La multiplicación no funciona
1. Verificar que el campo `pack_quantity` esté en el formulario
2. Comprobar los logs del servidor
3. Verificar que WooCommerce esté activo
4. Revisar la consola del navegador para errores JavaScript

### Complementos YITH no se detectan
1. Verificar que YITH WooCommerce Product Add-ons esté activo
2. Comprobar que el producto tenga complementos configurados
3. Revisar la configuración de complementos globales

### Cantidades incorrectas en el carrito
1. Verificar la lógica de multiplicación en `multiply_quantity_after_cart()`
2. Comprobar que los datos de sesión se guarden correctamente
3. Revisar los hooks de WooCommerce

## 📞 Soporte

Para soporte técnico o preguntas sobre la implementación, contactar al equipo de desarrollo MWM.

---

**Versión**: 1.0.0  
**Última actualización**: Diciembre 2024  
**Compatibilidad**: WordPress 5.0+, WooCommerce 3.0+, PHP 7.4+
