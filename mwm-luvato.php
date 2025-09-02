<?php
/**
 * Plugin Name: MWM Luvato
 * Plugin URI: https://luvato.com
 * Description: Plugin personalizado para Luvato con funcionalidades específicas
 * Version: 1.0.0
 * Author: MWM Team
 * Author URI: https://mwm.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mwm-luvato
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('MWM_LUVATO_VERSION', '1.0.0');
define('MWM_LUVATO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MWM_LUVATO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MWM_LUVATO_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Clase principal del plugin
 */
class MWM_Luvato_Plugin {
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'admin_init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Inicialización del plugin
     */
    public function init() {
        // Cargar traducciones
        load_plugin_textdomain('mwm-luvato', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Cargar funcionalidades del plugin
        $this->load_includes();
        
        // Aquí puedes agregar tus hooks y funcionalidades
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Cargar archivos incluidos
     */
    private function load_includes() {
        // Cargar multiplicador de precios
        require_once MWM_LUVATO_PLUGIN_DIR . 'includes/class-price-multiplier.php';
        
        // Inicializar multiplicador de precios
        new MWM_Price_Multiplier();
    }
    
    /**
     * Inicialización del admin
     */
    public function admin_init() {
        // Configuraciones del admin
    }
    
    /**
     * Activación del plugin
     */
    public function activate() {
        // Crear tablas de base de datos si es necesario
        // Configurar opciones por defecto
        add_option('mwm-luvato-version', MWM_LUVATO_VERSION);
        
        // Limpiar cache si es necesario
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }
    
    /**
     * Desactivación del plugin
     */
    public function deactivate() {
        // Limpiar cache si es necesario
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }
    
    /**
     * Cargar scripts y estilos
     */
    public function enqueue_scripts() {
        // Cargar estilos CSS del multiplicador de precios
        wp_enqueue_style('mwm-luvato-price-multiplier', MWM_LUVATO_PLUGIN_URL . 'assets/css/price-multiplier.css', array(), MWM_LUVATO_VERSION);
        
        // Cargar JavaScript del multiplicador de precios
        wp_enqueue_script('mwm-luvato-price-multiplier', MWM_LUVATO_PLUGIN_URL . 'assets/js/price-multiplier.js', array('jquery'), MWM_LUVATO_VERSION, true);
    }
}

// Inicializar el plugin
new MWM_Luvato_Plugin();

/**
 * Función de activación global (hook de activación)
 */
function mwm_luvato_activate() {
    // Código de activación global si es necesario
}

/**
 * Función de desactivación global (hook de desactivación)
 */
function mwm_luvato_deactivate() {
    // Código de desactivación global si es necesario
}

// Registrar hooks globales
register_activation_hook(__FILE__, 'mwm_luvato_activate');
register_deactivation_hook(__FILE__, 'mwm_luvato_deactivate');
