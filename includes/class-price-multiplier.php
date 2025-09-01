<?php
/**
 * Clase para multiplicar cantidades de productos por el selector general (pack)
 * 
 * @package MWM_Luvato
 * @version 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase MWM_Price_Multiplier
 */
class MWM_Price_Multiplier {
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        error_log("MWM Luvato: Constructor de Price_Multiplier ejecutado");
        
        // Hook para interceptar antes de añadir al carrito
        add_filter('woocommerce_add_to_cart_validation', array($this, 'multiply_quantity_before_cart'), 60, 4);
        
        // Hook para modificar la cantidad en el carrito
        add_filter('woocommerce_add_cart_item_data', array($this, 'modify_cart_item_quantity'), 10, 3);
        
        // Hook para procesar complementos de YITH
        add_filter('woocommerce_add_cart_item_data', array($this, 'process_yith_addons_data'), 5, 2);
        
        // Hook para mostrar la cantidad multiplicada en el carrito
        add_filter('woocommerce_cart_item_quantity', array($this, 'display_multiplied_quantity'), 10, 3);
        
        // Hook adicional para el carrito
        add_filter('woocommerce_cart_item_name', array($this, 'display_multiplied_quantity_in_name'), 10, 3);
        
        // Hook para el widget del carrito
        add_filter('woocommerce_widget_cart_item_quantity', array($this, 'display_multiplied_quantity_widget'), 10, 3);
        
        // Hook para rastrear el problema del precio
        add_action('woocommerce_cart_calculate_fees', array($this, 'debug_cart_prices'), 10);
        
        // Hook para forzar la cantidad ANTES de calcular precios
        add_action('woocommerce_before_calculate_totals', array($this, 'force_quantities_before_calculation'), 10);
        
        // Hook para procesar complementos de YITH
        add_action('woocommerce_add_to_cart', array($this, 'process_yith_addons'), 10, 6);
        
        // Hook para modificar la cantidad después de añadir al carrito
        add_action('woocommerce_add_to_cart', array($this, 'multiply_quantity_after_cart'), 20, 6);
        
        // Hook para modificar la cantidad DURANTE el cálculo de precios
        add_filter('woocommerce_cart_item_quantity', array($this, 'force_multiplied_quantity'), 1, 3);
        
        // Hook adicional para asegurar que la cantidad se aplique correctamente
        add_action('woocommerce_cart_item_restored', array($this, 'modify_cart_item_quantity_restored'), 10, 2);
        
        // Hook para incluir JavaScript en el frontend
        add_action('wp_enqueue_scripts', array($this, 'enqueue_popup_script'));
        
        // Hook adicional para capturar cualquier acción de añadir al carrito
        add_action('woocommerce_add_to_cart', array($this, 'debug_add_to_cart'), 1, 6);
        
        // Hook para capturar acciones AJAX de YITH
        add_action('wp_ajax_yith_wapo_add_to_cart', array($this, 'debug_yith_ajax'), 1);
        add_action('wp_ajax_nopriv_yith_wapo_add_to_cart', array($this, 'debug_yith_ajax'), 1);
        
        // Hook para capturar cualquier acción AJAX de WooCommerce
        add_action('wp_ajax_woocommerce_add_to_cart', array($this, 'debug_wc_ajax'), 1);
        add_action('wp_ajax_nopriv_woocommerce_add_to_cart', array($this, 'debug_wc_ajax'), 1);
        
        error_log("MWM Luvato: Todos los hooks registrados correctamente");
    }
    
    /**
     * Multiplicar cantidad antes de añadir al carrito
     * 
     * @param bool $passed Validación pasada
     * @param int $product_id ID del producto
     * @param int $quantity Cantidad
     * @param int $variation_id ID de la variación
     * @return bool
     */
    public function multiply_quantity_before_cart($passed, $product_id, $quantity, $variation_id = null) {
        
        error_log("MWM Luvato: multiply_quantity_before_cart ejecutado - Product ID: {$product_id}, Quantity: {$quantity}, Passed: " . ($passed ? 'true' : 'false'));
        
        // Solo procesar si la validación ya pasó
        if (!$passed) {
            error_log("MWM Luvato: Validación falló, no procesando multiplicación");
            return $passed;
        }
        
        // Obtener la cantidad del selector general (pack)
        $pack_quantity = $this->get_pack_quantity();
        
        // NO multiplicar el producto principal, solo las opciones de YITH
        // La multiplicación se hará en modify_cart_item_quantity solo para productos YITH
        if ($pack_quantity > 1) {
            error_log("MWM Luvato: Pack quantity detectado: {$pack_quantity}, pero NO multiplicando producto principal");
        } else {
            error_log("MWM Luvato: No se necesita multiplicación (pack = 1)");
        }
        
        return $passed;
    }
    
    /**
     * Modificar datos del item del carrito
     * 
     * @param array $cart_item_data Datos del item
     * @param int $product_id ID del producto
     * @param int $variation_id ID de la variación
     * @return array
     */
    public function modify_cart_item_quantity($cart_item_data, $product_id, $variation_id) {
        
        error_log("MWM Luvato: modify_cart_item_quantity ejecutado - Product ID: {$product_id}, Cart data: " . print_r($cart_item_data, true));
        
        // Obtener la cantidad del selector general (pack)
        $pack_quantity = $this->get_pack_quantity();
        
        // Solo multiplicar si es un producto YITH (tiene yith_wapo_product_as_item)
        if ($pack_quantity > 1 && isset($cart_item_data['yith_wapo_product_as_item']) && $cart_item_data['yith_wapo_product_as_item'] == 1) {
            
            // Guardar la cantidad original para referencia
            $original_quantity = $cart_item_data['quantity'] ?? 1;
            $cart_item_data['mwm_original_quantity'] = $original_quantity;
            $cart_item_data['mwm_pack_quantity'] = $pack_quantity;
            $cart_item_data['mwm_multiplied_quantity'] = $original_quantity * $pack_quantity;
            
            // Modificar la cantidad final SOLO para productos YITH
            $cart_item_data['quantity'] = $cart_item_data['mwm_multiplied_quantity'];
            
            error_log("MWM Luvato: Cantidad YITH modificada - Original: {$original_quantity}, Pack: {$pack_quantity}, Resultado: " . $cart_item_data['mwm_multiplied_quantity']);
            
        } else if ($pack_quantity > 1) {
            // Para productos principales, mostrar la cantidad del pack
            $original_quantity = $cart_item_data['quantity'] ?? 1;
            $cart_item_data['mwm_pack_quantity'] = $pack_quantity;
            $cart_item_data['mwm_original_quantity'] = $original_quantity;
            $cart_item_data['mwm_multiplied_quantity'] = $pack_quantity; // Mostrar la cantidad del pack
            
            // Modificar la cantidad para mostrar el pack
            $cart_item_data['quantity'] = $pack_quantity;
            
            error_log("MWM Luvato: Producto principal mostrando pack - Original: {$original_quantity}, Pack: {$pack_quantity}, Mostrando: {$pack_quantity}");
        } else {
            error_log("MWM Luvato: No se modificó cantidad (pack = 1)");
        }
        
        return $cart_item_data;
    }
    
    /**
     * Función de debug para capturar cualquier acción de añadir al carrito
     * 
     * @param string $cart_item_key Clave del item del carrito
     * @param int $product_id ID del producto
     * @param int $quantity Cantidad
     * @param int $variation_id ID de la variación
     * @param array $variation Datos de la variación
     * @param array $cart_item_data Datos del item del carrito
     */
    public function debug_add_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
        error_log("MWM Luvato: DEBUG - woocommerce_add_to_cart ejecutado");
        error_log("MWM Luvato: DEBUG - Cart Item Key: {$cart_item_key}");
        error_log("MWM Luvato: DEBUG - Product ID: {$product_id}");
        error_log("MWM Luvato: DEBUG - Quantity: {$quantity}");
        error_log("MWM Luvato: DEBUG - Variation ID: " . ($variation_id ? $variation_id : 'null'));
        error_log("MWM Luvato: DEBUG - Cart Item Data: " . print_r($cart_item_data, true));
        error_log("MWM Luvato: DEBUG - POST Data: " . print_r($_POST, true));
        error_log("MWM Luvato: DEBUG - GET Data: " . print_r($_GET, true));
    }
    
    /**
     * Debug para acciones AJAX de YITH
     */
    public function debug_yith_ajax() {
        error_log("MWM Luvato: DEBUG - YITH AJAX add_to_cart ejecutado");
        error_log("MWM Luvato: DEBUG - YITH POST Data: " . print_r($_POST, true));
        error_log("MWM Luvato: DEBUG - YITH GET Data: " . print_r($_GET, true));
    }
    
    /**
     * Debug para acciones AJAX de WooCommerce
     */
    public function debug_wc_ajax() {
        error_log("MWM Luvato: DEBUG - WooCommerce AJAX add_to_cart ejecutado");
        error_log("MWM Luvato: DEBUG - WC AJAX POST Data: " . print_r($_POST, true));
        error_log("MWM Luvato: DEBUG - WC AJAX GET Data: " . print_r($_GET, true));
    }
    
    /**
     * Mostrar cantidad multiplicada en el carrito
     * 
     * @param string $quantity_html HTML de la cantidad
     * @param string $cart_item_key Clave del item del carrito
     * @param array $cart_item Datos del item
     * @return string
     */
    public function display_multiplied_quantity($quantity_html, $cart_item_key, $cart_item) {
        
        error_log("MWM Luvato: display_multiplied_quantity ejecutado - Cart Item Key: {$cart_item_key}");
        error_log("MWM Luvato: Cart Item Data: " . print_r($cart_item, true));
        
        // Verificar si este item fue procesado por nuestro plugin
        if (isset($cart_item['mwm_multiplied_quantity']) && isset($cart_item['mwm_original_quantity'])) {
            
            $original_qty = $cart_item['mwm_original_quantity'];
            $pack_qty = $cart_item['mwm_pack_quantity'];
            $final_qty = $cart_item['mwm_multiplied_quantity'];
            
            error_log("MWM Luvato: Mostrando cantidad multiplicada - Original: {$original_qty}, Pack: {$pack_qty}, Final: {$final_qty}");
            
            // Crear HTML personalizado que muestre la multiplicación
            $quantity_html = sprintf(
                '<span class="mwm-quantity-display" title="%s × %s = %s">%s</span>',
                esc_attr($original_qty),
                esc_attr($pack_qty),
                esc_attr($final_qty),
                esc_html($final_qty)
            );
            
            error_log("MWM Luvato: HTML generado: " . $quantity_html);
        } else {
            error_log("MWM Luvato: Item no procesado por nuestro plugin");
        }
        
        return $quantity_html;
    }
    
    /**
     * Mostrar cantidad multiplicada en el nombre del item del carrito
     */
    public function display_multiplied_quantity_in_name($name, $cart_item, $cart_item_key) {
        
        error_log("MWM Luvato: display_multiplied_quantity_in_name ejecutado - Cart Item Key: {$cart_item_key}");
        
        // Verificar si este item fue procesado por nuestro plugin
        if (isset($cart_item['mwm_multiplied_quantity']) && isset($cart_item['mwm_original_quantity'])) {
            
            $original_qty = $cart_item['mwm_original_quantity'];
            $pack_qty = $cart_item['mwm_pack_quantity'];
            $final_qty = $cart_item['mwm_multiplied_quantity'];
            
            error_log("MWM Luvato: Mostrando cantidad multiplicada en nombre - Original: {$original_qty}, Pack: {$pack_qty}, Final: {$final_qty}");
            
            // Agregar información de multiplicación al nombre
            $name .= sprintf(
                ' <span class="mwm-multiplication-info" style="color: #ff6b6b; font-weight: bold;">(%s × %s = %s)</span>',
                esc_html($original_qty),
                esc_html($pack_qty),
                esc_html($final_qty)
            );
        }
        
        return $name;
    }
    
    /**
     * Mostrar cantidad multiplicada en el widget del carrito
     */
    public function display_multiplied_quantity_widget($quantity_html, $cart_item, $cart_item_key) {
        
        error_log("MWM Luvato: display_multiplied_quantity_widget ejecutado - Cart Item Key: {$cart_item_key}");
        
        // Verificar si este item fue procesado por nuestro plugin
        if (isset($cart_item['mwm_multiplied_quantity']) && isset($cart_item['mwm_original_quantity'])) {
            
            $original_qty = $cart_item['mwm_original_quantity'];
            $pack_qty = $cart_item['mwm_pack_quantity'];
            $final_qty = $cart_item['mwm_multiplied_quantity'];
            
            error_log("MWM Luvato: Mostrando cantidad multiplicada en widget - Original: {$original_qty}, Pack: {$pack_qty}, Final: {$final_qty}");
            
            // Crear HTML personalizado que muestre la multiplicación
            $quantity_html = sprintf(
                '<span class="mwm-quantity-display" title="%s × %s = %s">%s</span>',
                esc_attr($original_qty),
                esc_attr($pack_qty),
                esc_attr($final_qty),
                esc_html($final_qty)
            );
        }
        
        return $quantity_html;
    }
    

    
    /**
     * Debug: rastrear precios del carrito
     */
    public function debug_cart_prices() {
        $cart = WC()->cart;
        if (!$cart) return;
        
        error_log("MWM Luvato: === PRECIOS DEL CARRITO ===");
        error_log("MWM Luvato: Subtotal: " . $cart->get_subtotal());
        error_log("MWM Luvato: Total: " . $cart->get_total('edit'));
        
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $line_total = $cart_item['line_total'];
            $line_tax = $cart_item['line_tax'];
            
            error_log("MWM Luvato: Item - " . $product->get_name());
            error_log("MWM Luvato: - Cantidad: " . $cart_item['quantity']);
            error_log("MWM Luvato: - Precio unitario: " . $product->get_price());
            error_log("MWM Luvato: - Line total: " . $line_total);
            error_log("MWM Luvato: - Line tax: " . $line_tax);
            error_log("MWM Luvato: - Total con impuestos: " . ($line_total + $line_tax));
            
            // Debug adicional: mostrar todos los datos del item
            error_log("MWM Luvato: - Datos completos del item: " . print_r($cart_item, true));
        }
    }
    

    
    /**
     * Procesar complementos de YITH cuando se añade al carrito
     * 
     * @param string $cart_item_key Clave del item del carrito
     * @param int $product_id ID del producto
     * @param int $quantity Cantidad
     * @param int $variation_id ID de la variación
     * @param array $variation Datos de la variación
     * @param array $cart_item_data Datos del item del carrito
     */
    public function process_yith_addons($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
        
        // Obtener la cantidad del selector general (pack)
        $pack_quantity = $this->get_pack_quantity();
        
        if ($pack_quantity > 1) {
            // Guardar información de multiplicación en la sesión para usar después
            WC()->session->set('mwm_pack_quantity', $pack_quantity);
            WC()->session->set('mwm_original_quantity', $quantity);
            
            error_log("MWM Luvato: Procesando complementos YITH - Pack: {$pack_quantity}, Cantidad original: {$quantity}");
        }
    }
    
    /**
     * Multiplicar cantidad después de añadir al carrito
     * 
     * @param string $cart_item_key Clave del item del carrito
     * @param int $product_id ID del producto
     * @param int $quantity Cantidad
     * @param int $variation_id ID de la variación
     * @param array $variation Datos de la variación
     * @param array $cart_item_data Datos del item del carrito
     */
    public function multiply_quantity_after_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
        
        // Obtener la cantidad del pack de la sesión
        $pack_quantity = WC()->session->get('mwm_pack_quantity', 1);
        $original_quantity = WC()->session->get('mwm_original_quantity', $quantity);
        
        if ($pack_quantity > 1) {
            // Obtener el carrito
            $cart = WC()->cart;
            
            // Obtener el item del carrito
            $cart_item = $cart->get_cart_item($cart_item_key);
            
            if ($cart_item) {
                // SOLO multiplicar si es un producto YITH (tiene yith_wapo_product_as_item)
                if (isset($cart_item['yith_wapo_product_as_item']) && $cart_item['yith_wapo_product_as_item'] == 1) {
                    
                    // Calcular la nueva cantidad multiplicada
                    $multiplied_quantity = $original_quantity * $pack_quantity;
                    
                    // Actualizar la cantidad en el carrito
                    $cart->set_quantity($cart_item_key, $multiplied_quantity, false);
                    
                    // Guardar información adicional en los datos del item
                    $cart_item['mwm_original_quantity'] = $original_quantity;
                    $cart_item['mwm_pack_quantity'] = $pack_quantity;
                    $cart_item['mwm_multiplied_quantity'] = $multiplied_quantity;
                    
                    // Actualizar los datos del item en el carrito
                    $cart->cart_contents[$cart_item_key] = $cart_item;
                    
                    // Log para debugging
                    error_log("MWM Luvato: Cantidad YITH multiplicada en carrito - Original: {$original_quantity}, Pack: {$pack_quantity}, Resultado: {$multiplied_quantity}");
                    
                } else {
                    // Para productos principales, mostrar la cantidad del pack
                    $cart->set_quantity($cart_item_key, $pack_quantity, false);
                    
                    // Guardar información adicional en los datos del item
                    $cart_item['mwm_original_quantity'] = $quantity;
                    $cart_item['mwm_pack_quantity'] = $pack_quantity;
                    $cart_item['mwm_multiplied_quantity'] = $pack_quantity; // Mostrar la cantidad del pack
                    
                    // Actualizar los datos del item en el carrito
                    $cart->cart_contents[$cart_item_key] = $cart_item;
                    
                    error_log("MWM Luvato: Producto principal mostrando pack en carrito - Original: {$quantity}, Pack: {$pack_quantity}, Mostrando: {$pack_quantity}");
                }
                
                // Limpiar la sesión
                WC()->session->set('mwm_pack_quantity', null);
                WC()->session->set('mwm_original_quantity', null);
            }
        }
    }
    
    /**
     * Forzar cantidades ANTES de calcular totales
     */
    public function force_quantities_before_calculation() {
        $cart = WC()->cart;
        if (!$cart) return;
        
        error_log("MWM Luvato: === FORZANDO CANTIDADES ANTES DE CÁLCULO ===");
        
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            // Verificar si este item fue procesado por nuestro plugin
            if (isset($cart_item['mwm_multiplied_quantity']) && isset($cart_item['mwm_original_quantity'])) {
                
                $original_qty = $cart_item['mwm_original_quantity'];
                $pack_qty = $cart_item['mwm_pack_quantity'];
                $final_qty = $cart_item['mwm_multiplied_quantity'];
                $current_qty = $cart_item['quantity'];
                
                error_log("MWM Luvato: Item - " . $cart_item['data']->get_name());
                error_log("MWM Luvato: - Cantidad actual: {$current_qty}");
                error_log("MWM Luvato: - Cantidad que debería ser: {$final_qty}");
                
                // Forzar la cantidad multiplicada
                $cart->set_quantity($cart_item_key, $final_qty, false);
                error_log("MWM Luvato: - Cantidad forzada a: {$final_qty}");
            }
        }
    }
    
    /**
     * Forzar cantidad multiplicada durante el cálculo de precios
     */
    public function force_multiplied_quantity($quantity, $cart_item_key, $cart_item) {
        // Verificar si este item fue procesado por nuestro plugin
        if (isset($cart_item['mwm_multiplied_quantity']) && isset($cart_item['mwm_original_quantity'])) {
            
            $original_qty = $cart_item['mwm_original_quantity'];
            $pack_qty = $cart_item['mwm_pack_quantity'];
            $final_qty = $cart_item['mwm_multiplied_quantity'];
            
            error_log("MWM Luvato: FORZANDO cantidad - Original: {$original_qty}, Pack: {$pack_qty}, Final: {$final_qty}, Cantidad actual: {$quantity}");
            
            // Forzar la cantidad multiplicada
            return $final_qty;
        }
        
        return $quantity;
    }
    
    /**
     * Modificar cantidad cuando se restaura un item del carrito
     */
    public function modify_cart_item_quantity_restored($cart_item_key, $cart_item) {
        // Verificar si este item fue procesado por nuestro plugin
        if (isset($cart_item['mwm_multiplied_quantity']) && isset($cart_item['mwm_original_quantity'])) {
            
            $original_qty = $cart_item['mwm_original_quantity'];
            $pack_qty = $cart_item['mwm_pack_quantity'];
            $final_qty = $cart_item['mwm_multiplied_quantity'];
            
            error_log("MWM Luvato: Item restaurado - Original: {$original_qty}, Pack: {$pack_qty}, Final: {$final_qty}");
            
            // Asegurar que la cantidad correcta se mantenga
            $cart = WC()->cart;
            if ($cart) {
                $cart->set_quantity($cart_item_key, $final_qty, false);
                error_log("MWM Luvato: Cantidad restaurada a: {$final_qty}");
            }
        }
    }
    
    /**
     * Obtener la cantidad del selector general (pack)
     * 
     * @return int
     */
    private function get_pack_quantity() {
        
        // Log para debugging
        error_log("MWM Luvato: Verificando pack_quantity - POST: " . (isset($_POST['pack_quantity']) ? $_POST['pack_quantity'] : 'no existe') . 
                 ", GET: " . (isset($_GET['pack_quantity']) ? $_GET['pack_quantity'] : 'no existe'));
        
        // Intentar obtener del POST data
        if (isset($_POST['pack_quantity'])) {
            $quantity = intval($_POST['pack_quantity']);
            error_log("MWM Luvato: Pack quantity obtenido de POST: " . $quantity);
            return $quantity;
        }
        
        // Intentar obtener del GET data
        if (isset($_GET['pack_quantity'])) {
            $quantity = intval($_GET['pack_quantity']);
            error_log("MWM Luvato: Pack quantity obtenido de GET: " . $quantity);
            return $quantity;
        }
        
        // Intentar obtener de la sesión de WooCommerce
        if (WC()->session && WC()->session->get('pack_quantity')) {
            $quantity = intval(WC()->session->get('pack_quantity'));
            error_log("MWM Luvato: Pack quantity obtenido de WC session: " . $quantity);
            return $quantity;
        }
        
        // Intentar obtener de la sesión PHP
        if (isset($_SESSION['pack_quantity'])) {
            $quantity = intval($_SESSION['pack_quantity']);
            error_log("MWM Luvato: Pack quantity obtenido de PHP session: " . $quantity);
            return $quantity;
        }
        
        // Por defecto, no multiplicar
        error_log("MWM Luvato: Pack quantity no encontrado, usando valor por defecto: 1");
        return 1;
    }
    
    /**
     * Detectar si hay complementos de YITH en el producto
     * 
     * @param int $product_id ID del producto
     * @return bool
     */
    private function has_yith_addons($product_id) {
        
        // Verificar si el plugin YITH WooCommerce Product Add-ons está activo
        if (!class_exists('YITH_WAPO')) {
            return false;
        }
        
        // Verificar si el producto tiene complementos
        $addons = get_post_meta($product_id, '_yith_wapo_product_addon', true);
        
        if (!empty($addons) && is_array($addons)) {
            return true;
        }
        
        // Verificar complementos globales
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
        
        return !empty($global_addons);
    }
    
    /**
     * Procesar complementos de YITH específicamente
     * 
     * @param array $cart_item_data Datos del item del carrito
     * @param int $product_id ID del producto
     * @return array
     */
    public function process_yith_addons_data($cart_item_data, $product_id) {
        
        // Verificar si hay complementos de YITH
        if ($this->has_yith_addons($product_id)) {
            
            // Obtener la cantidad del pack
            $pack_quantity = $this->get_pack_quantity();
            
            if ($pack_quantity > 1) {
                // Marcar que este producto tiene complementos YITH
                $cart_item_data['mwm_has_yith_addons'] = true;
                $cart_item_data['mwm_pack_quantity'] = $pack_quantity;
                
                error_log("MWM Luvato: Producto con complementos YITH detectado - ID: {$product_id}, Pack: {$pack_quantity}");
            }
        }
        
        return $cart_item_data;
    }
    
    /**
     * Obtener información de debugging
     * 
     * @return array
     */
    public function get_debug_info() {
        return array(
            'pack_quantity' => $this->get_pack_quantity(),
            'post_data' => $_POST,
            'get_data' => $_GET,
            'session_data' => $_SESSION ?? array(),
            'yith_addons_active' => class_exists('YITH_WAPO'),
            'cart_contents' => WC()->cart ? WC()->cart->get_cart_contents() : array()
        );
    }
    
    /**
     * Enqueue popup script for frontend
     */
    public function enqueue_popup_script() {
        // Agregar JavaScript inline para mostrar popup amarillo
        add_action('wp_footer', array($this, 'add_popup_script'));
    }
    
    /**
     * Agregar script para mostrar popup amarillo al pulsar Bestellen
     */
    public function add_popup_script() {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Función para mostrar popup
            function showMultiplicationPopup(productQuantity, packQuantity, multipliedQuantity) {
                // Mostrar popup amarillo
                var popupHtml = '<div id="mwm-popup-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">';
                popupHtml += '<div id="mwm-popup" style="background: #fff3cd; border: 3px solid #ffc107; border-radius: 10px; padding: 25px; text-align: center; max-width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">';
                popupHtml += '<h2 style="color: #856404; margin: 0 0 20px 0; font-size: 24px;">🧮 MULTIPLICACIÓN APLICADA</h2>';
                popupHtml += '<div style="background: white; padding: 20px; border-radius: 8px; margin: 15px 0; border: 2px solid #ffc107;">';
                popupHtml += '<div style="font-size: 18px; color: #856404; margin-bottom: 15px;">Cantidad del producto: <strong>' + productQuantity + '</strong></div>';
                popupHtml += '<div style="font-size: 18px; color: #856404; margin-bottom: 15px;">Pack quantity: <strong>' + packQuantity + '</strong></div>';
                popupHtml += '<div style="font-size: 24px; font-weight: bold; color: #856404; margin: 15px 0;">';
                popupHtml += '<span style="background: #ffc107; color: white; padding: 8px 12px; border-radius: 6px; margin: 0 5px;">' + productQuantity + '</span>';
                popupHtml += '<span style="font-size: 28px; margin: 0 10px;">×</span>';
                popupHtml += '<span style="background: #ffc107; color: white; padding: 8px 12px; border-radius: 6px; margin: 0 5px;">' + packQuantity + '</span>';
                popupHtml += '<span style="font-size: 28px; margin: 0 10px;">=</span>';
                popupHtml += '<span style="background: #28a745; color: white; padding: 10px 15px; border-radius: 8px; font-size: 26px; margin: 0 5px;">' + multipliedQuantity + '</span>';
                popupHtml += '</div>';
                popupHtml += '</div>';
                popupHtml += '<button id="mwm-popup-close" style="background: #ffc107; color: white; border: none; padding: 12px 25px; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer;">CERRAR</button>';
                popupHtml += '</div>';
                popupHtml += '</div>';
                
                // Agregar popup al body
                $('body').append(popupHtml);
                
                // Cerrar popup al hacer clic en el botón
                $(document).on('click', '#mwm-popup-close', function() {
                    $('#mwm-popup-overlay').remove();
                });
                
                // Cerrar popup al hacer clic fuera
                $(document).on('click', '#mwm-popup-overlay', function(e) {
                    if (e.target.id === 'mwm-popup-overlay') {
                        $('#mwm-popup-overlay').remove();
                    }
                });
            }
            
            // Hacer la función disponible globalmente para que tu JavaScript la pueda llamar
            window.showMultiplicationPopup = showMultiplicationPopup;
            
            // Interceptar clic en botón "Bestellen" como respaldo
            $(document).on('click', 'button:contains("Bestellen"), .bestellen, [class*="bestellen"]', function(e) {
                console.log('MWM Luvato: Botón Bestellen clickeado');
            });
        });
        </script>
        <?php
    }
}


