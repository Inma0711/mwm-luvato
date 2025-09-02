/**
 * MWM Luvato Price Multiplier JavaScript
 * Captura la cantidad del selector general (pack) y la envía al añadir al carrito
 * 
 * @version 1.0.0
 */

(function($) {
    'use strict';
    
    // Objeto principal del multiplicador de precios
    var MWMPriceMultiplier = {
        
        // Inicialización
        init: function() {
            this.bindEvents();
            this.setupQuantityCapture();
        },
        
        // Vincular eventos
        bindEvents: function() {
            var self = this; // Guardar referencia al objeto
            
            // Interceptar clics en botones de añadir al carrito
            $(document).on('click', '.single_add_to_cart_button, .add_to_cart_button', function(e) {
                self.handleAddToCart.call(self, e);
            });
            
            // Interceptar envíos de formularios de añadir al carrito
            $(document).on('submit', '.cart, .variations_form', function(e) {
                self.handleFormSubmit.call(self, e);
            });
            
            // Interceptar TODOS los envíos de formularios (más agresivo)
            $(document).on('submit', 'form', function(e) {
                var $form = $(this);

                
                // Verificar si es un formulario de producto
                if ($form.find('input[name="add-to-cart"]').length > 0 || 
                    $form.find('input[name="yith_wapo_product_id"]').length > 0) {

                    var packQuantity = self.getPackQuantity();
                    if (packQuantity > 1) {
                        // Remover campo existente
                        $form.find('input[name="pack_quantity"]').remove();
                        // Agregar nuevo campo
                        $form.append('<input type="hidden" name="pack_quantity" value="' + packQuantity + '">');

                    }
                }
            });
            
            // Interceptar cambios en el selector general (pack)
            $(document).on('change', '.pack-quantity-selector, [data-pack-quantity]', function() {
                self.updatePackQuantity.call(self);
            });
            
            // Interceptar cambios en TODOS los selectores de cantidad para multiplicación visual
            $(document).on('change', 'input[type="number"]', function(e) {
                self.handleQuantityChange.call(self, e);
            });
            
            // Interceptar cambios en selectores de productos específicos
            $(document).on('change', 'input.wapo-product-qty', function(e) {
                self.handleProductQuantityChange.call(self, e);
            });
            
            // Interceptar cambios en el selector general (pack) específicamente
            $(document).on('change', 'input[type="number"]:not(.wapo-product-qty)', function() {
                var $input = $(this);
                // Si NO está dentro de option-add-to-cart, es el selector general
                if ($input.closest('.option-add-to-cart').length === 0) {

                    self.updatePackDisplay.call(self);
                    // NO actualizar multiplicación visual aquí - solo cuando se pulse Bestellen
                }
            });
        },
        
        // Configurar captura de cantidad
        setupQuantityCapture: function() {
            // Buscar selectores de cantidad de pack en la página
            var $packSelectors = $('.pack-quantity-selector, [data-pack-quantity]');
            
            if ($packSelectors.length > 0) {

                
                // Actualizar cantidad inicial
                this.updatePackQuantity();
            }
        },
        
        // Manejar clic en añadir al carrito
        handleAddToCart: function(e) {
            var $button = $(this);
            var packQuantity = MWMPriceMultiplier.getPackQuantity();
            

            
            // HACER LA MULTIPLICACIÓN VISUAL AQUÍ
            this.updateVisualMultiplication();
            
            if (packQuantity > 1) {

                
                // Agregar campo oculto con la cantidad de pack
                MWMPriceMultiplier.addPackQuantityField($button, packQuantity);
                

                

            } else {

            }
        },
        
        // Manejar envío de formulario
        handleFormSubmit: function(e) {
            var $form = $(this);
            var packQuantity = MWMPriceMultiplier.getPackQuantity();
            

            
            // HACER LA MULTIPLICACIÓN VISUAL AQUÍ
            this.updateVisualMultiplication();
            
            if (packQuantity > 1) {

                
                // Agregar campo oculto con la cantidad de pack
                MWMPriceMultiplier.addPackQuantityField($form, packQuantity);
            }
        },
        
        // Actualizar cantidad de pack
        updatePackQuantity: function() {
            var packQuantity = MWMPriceMultiplier.getPackQuantity();
            
            // Guardar en localStorage para persistencia
            localStorage.setItem('mwm_pack_quantity', packQuantity);
            

        },
        
        // Obtener cantidad de pack
        getPackQuantity: function() {
            // Buscar en diferentes lugares
            var packQuantity = 1;
            
            // 1. Buscar el selector general (pack) - NO tiene clase 'wapo-product-qty'
            var $packSelector = $('input[type="number"]:not(.wapo-product-qty)');
            if ($packSelector.length > 0) {
                // Filtrar para encontrar el que está fuera de option-add-to-cart
                $packSelector.each(function() {
                    var $input = $(this);
                    // Si NO está dentro de option-add-to-cart, es el selector general
                    if ($input.closest('.option-add-to-cart').length === 0) {
                        packQuantity = parseInt($input.val()) || 1;

                        return false; // Salir del bucle
                    }
                });
            }
            
            // 2. Buscar en localStorage como respaldo
            if (packQuantity === 1) {
                var storedQuantity = localStorage.getItem('mwm_pack_quantity');
                if (storedQuantity) {
                    packQuantity = parseInt(storedQuantity) || 1;

                }
            }
            

            return packQuantity;
        },
        

        

        
        // Agregar campo oculto con cantidad de pack
        addPackQuantityField: function($element, packQuantity) {
            
            // Remover cualquier campo pack_quantity existente
            $('input[name="pack_quantity"]').remove();
            
            // Crear campo oculto
            var $hiddenField = $('<input type="hidden" name="pack_quantity" value="' + packQuantity + '">');

            
            // Buscar el formulario más cercano
            var $form = $element.closest('form');

            
            if ($form.length > 0) {
                // Si es un formulario, agregar al formulario
                $form.append($hiddenField);

            } else {
                // Si no es un formulario, buscar en el body o agregar al elemento padre

                
                // Buscar cualquier formulario en la página
                var $anyForm = $('form').first();
                if ($anyForm.length > 0) {
                    $anyForm.append($hiddenField);

                } else {
                    // Como último recurso, agregar al body
                    $('body').append($hiddenField);

                }
            }
            

            

        },
        
        // Manejar cambios en cualquier selector de cantidad
        handleQuantityChange: function(e) {
            var $input = $(this);

        },
        
        // Manejar cambios específicos en selectores de productos
        handleProductQuantityChange: function(e) {
            var $input = $(this);

            
            // Actualizar multiplicación visual para este producto
            MWMPriceMultiplier.updateProductMultiplication($input);
        },
        
        // Actualizar multiplicación visual para todos los productos
        updateVisualMultiplication: function() {
            var packQuantity = this.getPackQuantity();

            
            // Actualizar cada selector de producto
            $('input.wapo-product-qty').each(function() {
                MWMPriceMultiplier.updateProductMultiplication($(this));
            });
        },
        
        // Actualizar multiplicación visual para un producto específico
        updateProductMultiplication: function($productInput) {
            var packQuantity = this.getPackQuantity();
            var productQuantity = parseInt($productInput.val()) || 1;
            var multipliedQuantity = productQuantity * packQuantity;
            

            
            // Buscar el contenedor del producto para mostrar la multiplicación
            // Actualizar el valor mostrado en el input (solo visual, no el valor real)
            $productInput.attr('data-original-value', productQuantity);
            $productInput.attr('data-multiplied-value', multipliedQuantity);
            

            
            // ACTUALIZAR EL WIDGET DEL CARRITO
            this.updateCartWidgetQuantity($productInput, productQuantity, packQuantity, multipliedQuantity);
            
            // ACTUALIZAR LA CANTIDAD VISIBLE EN LA PÁGINA DEL PRODUCTO
            this.updateProductPageQuantity($productInput, productQuantity, packQuantity, multipliedQuantity);
        },
        
        // Actualizar cantidad en el widget del carrito
        updateCartWidgetQuantity: function($productInput, productQuantity, packQuantity, multipliedQuantity) {
            // Buscar el widget del carrito
            var $cartWidget = $('.widget.woocommerce.widget_shopping_cart');
            
            if ($cartWidget.length > 0) {

                
                // Obtener información del producto para identificarlo
                var productName = this.getProductName($productInput);

                
                // Buscar el producto específico en el carrito por nombre
                var $cartItem = $cartWidget.find('.cart_list li').filter(function() {
                    var itemText = $(this).text();
                    return itemText.indexOf(productName) !== -1;
                });
                
                if ($cartItem.length > 0) {

                    
                    // Buscar el span con clase 'quantity' dentro de este item específico
                    var $quantitySpan = $cartItem.find('span.quantity');
                    
                    if ($quantitySpan.length > 0) {
                        // Obtener el texto actual (ej: "2 × €20,00")
                        var currentText = $quantitySpan.text();
                        
                        // Extraer el precio (ej: "€20,00")
                        var priceMatch = currentText.match(/€[\d,]+/);
                        var price = priceMatch ? priceMatch[0] : '';
                        
                        // Crear el nuevo texto con la cantidad multiplicada
                        var newText = multipliedQuantity + ' × ' + price;
                        
                        // Actualizar el span
                        $quantitySpan.text(newText);
                        

                    }
                }
            }
        },
        
        // Actualizar cantidad visible en la página del producto
        updateProductPageQuantity: function($productInput, productQuantity, packQuantity, multipliedQuantity) {
            var $container = $productInput.closest('.option-add-to-cart');
            
            if ($container.length > 0) {

                
                // Buscar elementos que contengan el patrón "número x" o similar
                var $quantityElements = $container.find('*').filter(function() {
                    var text = $(this).text();
                    return /\d+\s*x/.test(text) || /\d+\s*×/.test(text);
                });
                
                if ($quantityElements.length > 0) {
                    $quantityElements.each(function() {
                        var $element = $(this);
                        var currentText = $element.text();
                        
                        // Buscar el patrón "número x" y reemplazarlo CORRECTAMENTE
                        var newText = currentText.replace(/(\d+)\s*[x×]/, multipliedQuantity + ' x');
                        
                        if (newText !== currentText) {
                            $element.text(newText);
                        }
                    });
                }
            }
        },
        
        // Obtener el nombre del producto desde el input
        getProductName: function($productInput) {
            // Buscar el nombre del producto en el contenedor
            var $container = $productInput.closest('.option-add-to-cart');
            if ($container.length > 0) {
                // Buscar texto que parezca nombre de producto
                var $productName = $container.find('label, .product-name, h3, h4, .title');
                if ($productName.length > 0) {
                    return $productName.first().text().trim();
                }
                
                // Si no hay label específico, buscar en todo el contenedor
                var containerText = $container.text();
                // Extraer el primer texto que parezca nombre de producto
                var lines = containerText.split('\n').filter(function(line) {
                    return line.trim().length > 0 && line.trim().length < 100;
                });
                if (lines.length > 0) {
                    return lines[0].trim();
                }
            }
            
            // Fallback: usar el ID del input
            var inputId = $productInput.attr('id') || '';
            if (inputId.indexOf('yith_wapo_product_qty') !== -1) {
                return 'Producto YITH WAPO';
            }
            
            return 'Producto';
        },
        
        // Debug: mostrar información actual
        debug: function() {
            var info = {
                packQuantity: this.getPackQuantity(),
                localStorage: localStorage.getItem('mwm_pack_quantity'),
                selectors: {
                    totalInputs: $('input[type="number"]').length,
                    productInputs: $('input[type="number"].wapo-product-qty').length,
                    generalInputs: $('input[type="number"]:not(.wapo-product-qty)').length,
                    outsideOptionCart: $('input[type="number"]:not(.wapo-product-qty)').filter(function() {
                        return $(this).closest('.option-add-to-cart').length === 0;
                    }).length
                }
            };
            

            return info;
        }
    };
    
    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        MWMPriceMultiplier.init();
        
        // Hacer disponible globalmente para debugging
        window.MWMPriceMultiplier = MWMPriceMultiplier;
        

    });
    
})(jQuery);
