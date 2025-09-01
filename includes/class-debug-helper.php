<?php
/**
 * Clase de ayuda para debugging del multiplicador de precios
 * 
 * @package MWM_Luvato
 * @version 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase MWM_Debug_Helper
 */
class MWM_Debug_Helper {
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        // Hook para mostrar información de debugging en el admin
        add_action('wp_ajax_mwm_debug_info', array($this, 'get_debug_info_ajax'));
        add_action('wp_ajax_nopriv_mwm_debug_info', array($this, 'get_debug_info_ajax'));
        
        // Hook para mostrar información en el frontend (solo para administradores)
        if (current_user_can('manage_options')) {
            add_action('wp_footer', array($this, 'show_debug_info'));
        }
        
        // Test de funcionamiento - log al cargar la página
        add_action('wp_loaded', array($this, 'test_plugin_loading'));
    }
    
    /**
     * Obtener información de debugging via AJAX
     */
    public function get_debug_info_ajax() {
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die('No tienes permisos para acceder a esta información');
        }
        
        $debug_info = array(
            'timestamp' => current_time('mysql'),
            'pack_quantity' => $this->get_pack_quantity(),
            'post_data' => $_POST,
            'get_data' => $_GET,
            'session_data' => $_SESSION ?? array(),
            'yith_addons_active' => class_exists('YITH_WAPO'),
            'cart_contents' => WC()->cart ? WC()->cart->get_cart_contents() : array(),
            'cart_total' => WC()->cart ? WC()->cart->get_cart_total() : 'N/A',
            'cart_count' => WC()->cart ? WC()->cart->get_cart_contents_count() : 0
        );
        
        wp_send_json_success($debug_info);
    }
    
    /**
     * Mostrar información de debugging en el frontend
     */
    public function show_debug_info() {
        
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $pack_quantity = $this->get_pack_quantity();
        $cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
        $yith_active = class_exists('YITH_WAPO');
        
        ?>
        <div id="mwm-debug-info" style="position: fixed; bottom: 10px; right: 10px; background: #000; color: #fff; padding: 10px; border-radius: 5px; font-size: 12px; z-index: 9999; max-width: 300px;">
            <h4 style="margin: 0 0 10px 0; color: #ffc107;">🔧 MWM Debug Info</h4>
            <div><strong>Pack Quantity:</strong> <?php echo esc_html($pack_quantity); ?></div>
            <div><strong>Cart Count:</strong> <?php echo esc_html($cart_count); ?></div>
            <div><strong>YITH Addons:</strong> <?php echo $yith_active ? '✅ Activo' : '❌ Inactivo'; ?></div>
            <div><strong>Time:</strong> <?php echo current_time('H:i:s'); ?></div>
            <button onclick="document.getElementById('mwm-debug-info').style.display='none'" style="background: #ffc107; color: #000; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-top: 5px;">Cerrar</button>
        </div>
        <?php
    }
    
    /**
     * Obtener la cantidad del selector general (pack)
     * 
     * @return int
     */
    private function get_pack_quantity() {
        
        // Intentar obtener del POST data
        if (isset($_POST['pack_quantity'])) {
            return intval($_POST['pack_quantity']);
        }
        
        // Intentar obtener del GET data
        if (isset($_GET['pack_quantity'])) {
            return intval($_GET['pack_quantity']);
        }
        
        // Intentar obtener de la sesión de WooCommerce
        if (WC()->session && WC()->session->get('pack_quantity')) {
            return intval(WC()->session->get('pack_quantity'));
        }
        
        // Intentar obtener de la sesión PHP
        if (isset($_SESSION['pack_quantity'])) {
            return intval($_SESSION['pack_quantity']);
        }
        
        // Por defecto, no multiplicar
        return 1;
    }
    
    /**
     * Test de funcionamiento del plugin
     */
    public function test_plugin_loading() {
        // Log de test para verificar que el plugin se está cargando
        error_log("MWM Luvato: Plugin cargado correctamente - " . current_time('Y-m-d H:i:s'));
        
        // Log de información del entorno
        error_log("MWM Luvato: WooCommerce activo: " . (class_exists('WooCommerce') ? 'Sí' : 'No'));
        error_log("MWM Luvato: YITH Addons activo: " . (class_exists('YITH_WAPO') ? 'Sí' : 'No'));
        error_log("MWM Luvato: Usuario actual: " . (is_user_logged_in() ? wp_get_current_user()->user_login : 'No logueado'));
    }
}
