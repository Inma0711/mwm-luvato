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
        

        
        // Hook adicional para capturar cualquier acción de añadir al carrito
        add_action('woocommerce_add_to_cart', array($this, 'debug_add_to_cart'), 1, 6);
        
        // Hook para capturar acciones AJAX de YITH
        add_action('wp_ajax_yith_wapo_add_to_cart', array($this, 'debug_yith_ajax'), 1);
        add_action('wp_ajax_nopriv_yith_wapo_add_to_cart', array($this, 'debug_yith_ajax'), 1);
        
        // Hook para capturar cualquier acción AJAX de WooCommerce
        add_action('wp_ajax_woocommerce_add_to_cart', array($this, 'debug_wc_ajax'), 1);
        add_action('wp_ajax_nopriv_woocommerce_add_to_cart', array($this, 'debug_wc_ajax'), 1);
        

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
        

        
        // Solo procesar si la validación ya pasó
        if (!$passed) {
            return $passed;
        }
        
        // Obtener la cantidad del selector general (pack)
        $pack_quantity = $this->get_pack_quantity();
        
        // NO multiplicar el producto principal, solo las opciones de YITH
        // La multiplicación se hará en modify_cart_item_quantity solo para productos YITH

        
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
            

            
        } else if ($pack_quantity > 1) {
            // Para productos principales, mostrar la cantidad del pack
            $original_quantity = $cart_item_data['quantity'] ?? 1;
            $cart_item_data['mwm_pack_quantity'] = $pack_quantity;
            $cart_item_data['mwm_original_quantity'] = $original_quantity;
            $cart_item_data['mwm_multiplied_quantity'] = $pack_quantity; // Mostrar la cantidad del pack
            
            // Modificar la cantidad para mostrar el pack
            $cart_item_data['quantity'] = $pack_quantity;
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








    }
    
    /**
     * Debug para acciones AJAX de YITH
     */
    public function debug_yith_ajax() {
        // Función de debug (logs eliminados para producción)
    }
    
    /**
     * Debug para acciones AJAX de WooCommerce
     */
    public function debug_wc_ajax() {
        // Función de debug (logs eliminados para producción)
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
        

        
        // Verificar si este item fue procesado por nuestro plugin
        if (isset($cart_item['mwm_multiplied_quantity']) && isset($cart_item['mwm_original_quantity'])) {
            
            $original_qty = $cart_item['mwm_original_quantity'];
            $pack_qty = $cart_item['mwm_pack_quantity'];
            $final_qty = $cart_item['mwm_multiplied_quantity'];
            
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
     * Mostrar cantidad multiplicada en el nombre del item del carrito
     */
    public function display_multiplied_quantity_in_name($name, $cart_item, $cart_item_key) {
        // Verificar si este item fue procesado por nuestro plugin
        if (isset($cart_item['mwm_multiplied_quantity']) && isset($cart_item['mwm_original_quantity'])) {
            $original_qty = $cart_item['mwm_original_quantity'];
            $pack_qty = $cart_item['mwm_pack_quantity'];
            $final_qty = $cart_item['mwm_multiplied_quantity'];
        }
        
        return $name;
    }
    
    /**
     * Mostrar cantidad multiplicada en el widget del carrito
     */
    public function display_multiplied_quantity_widget($quantity_html, $cart_item, $cart_item_key) {
        // Verificar si este item fue procesado por nuestro plugin
        if (isset($cart_item['mwm_multiplied_quantity']) && isset($cart_item['mwm_original_quantity'])) {
            $original_qty = $cart_item['mwm_original_quantity'];
            $pack_qty = $cart_item['mwm_pack_quantity'];
            $final_qty = $cart_item['mwm_multiplied_quantity'];
            
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
        
        // Función de debug (logs eliminados para producción)
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
                    
                    // Log para debugging (eliminado para producción)
                    
                } else {
                    // Para productos principales, mostrar la cantidad del pack
                    $cart->set_quantity($cart_item_key, $pack_quantity, false);
                    
                    // Guardar información adicional en los datos del item
                    $cart_item['mwm_original_quantity'] = $quantity;
                    $cart_item['mwm_pack_quantity'] = $pack_quantity;
                    $cart_item['mwm_multiplied_quantity'] = $pack_quantity; // Mostrar la cantidad del pack
                    
                    // Actualizar los datos del item en el carrito
                    $cart->cart_contents[$cart_item_key] = $cart_item;
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
        
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            // Verificar si este item fue procesado por nuestro plugin
            if (isset($cart_item['mwm_multiplied_quantity']) && isset($cart_item['mwm_original_quantity'])) {
                
                $original_qty = $cart_item['mwm_original_quantity'];
                $pack_qty = $cart_item['mwm_pack_quantity'];
                $final_qty = $cart_item['mwm_multiplied_quantity'];
                $current_qty = $cart_item['quantity'];
                
                // Forzar la cantidad multiplicada
                $cart->set_quantity($cart_item_key, $final_qty, false);
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
            
            // Asegurar que la cantidad correcta se mantenga
            $cart = WC()->cart;
            if ($cart) {
                $cart->set_quantity($cart_item_key, $final_qty, false);
            }
        }
    }
    
    /**
     * Obtener la cantidad del selector general (pack)
     * 
     * @return int
     */
    private function get_pack_quantity() {
        // Intentar obtener del POST data
        if (isset($_POST['pack_quantity'])) {
            $quantity = intval($_POST['pack_quantity']);
            return $quantity;
        }
        
        // Intentar obtener del GET data
        if (isset($_GET['pack_quantity'])) {
            $quantity = intval($_GET['pack_quantity']);
            return $quantity;
        }
        
        // Intentar obtener de la sesión de WooCommerce
        if (WC()->session && WC()->session->get('pack_quantity')) {
            $quantity = intval(WC()->session->get('pack_quantity'));
            return $quantity;
        }
        
        // Intentar obtener de la sesión PHP
        if (isset($_SESSION['pack_quantity'])) {
            $quantity = intval($_SESSION['pack_quantity']);
            return $quantity;
        }
        
        // Por defecto, no multiplicar
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
    

}


