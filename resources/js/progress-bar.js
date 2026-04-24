// resources/js/progress-bar.js

class ProgressBar {
    constructor() {
        this.container = null;
        this.totalCount = 0;
        this.completedCount = 0;
        this.onCompleteCallback = null;
        this.onUpdateCallback = null;
        this.selector = '.nota-select, .valoracion-select, .cantidad-input'; // Selector genérico
    }
    
    // Inicializar el Progress Bar
    init(containerId = 'progressContainer', selector = '.nota-select, .valoracion-select, .cantidad-input') {
        this.container = document.getElementById(containerId);
        this.selector = selector;
        return this;
    }
    
    // Mostrar el progress bar
    show() {
        if (this.container) {
            this.container.style.display = 'block';
        }
        return this;
    }
    
    // Ocultar el progress bar
    hide() {
        if (this.container) {
            this.container.style.display = 'none';
        }
        return this;
    }
    
    // Calcular y actualizar el progreso
    update() {
        if (!this.container) return this;
        
        let total = 0;
        let completados = 0;
        
        $(this.selector).each(function() {
            total++;
            let valor = $(this).text();
            if (valor !== 'Seleccionar' && valor !== '' && valor !== '0') {
                completados++;
            }
        });
        
        this.totalCount = total;
        this.completedCount = completados;
        let porcentaje = total > 0 ? Math.round((completados / total) * 100) : 0;
        
        $('#totalCount').text(total);
        $('#completedCount').text(completados);
        $('#pendingCount').text(total - completados);
        $('#progressPercentage').text(porcentaje + '%');
        $('#progressBarFill').css('width', porcentaje + '%');
        
        // Cambiar color según porcentaje
        if (porcentaje === 100) {
            $('#progressBarFill').css('background-color', '#28a745');
        } else {
            $('#progressBarFill').css('background-color', 'var(--primary-color)');
        }
        
        // Ejecutar callback de actualización
        if (this.onUpdateCallback) {
            this.onUpdateCallback(porcentaje, completados, total);
        }
        
        // Ejecutar callback de completado
        if (porcentaje === 100 && this.onCompleteCallback) {
            this.onCompleteCallback();
        }
        
        return this;
    }
    
    // Verificar si está completo
    isComplete() {
        return this.totalCount > 0 && this.completedCount === this.totalCount;
    }
    
    // Obtener porcentaje
    getPercentage() {
        return this.totalCount > 0 ? Math.round((this.completedCount / this.totalCount) * 100) : 0;
    }
    
    // Resetear contadores
    reset() {
        this.totalCount = 0;
        this.completedCount = 0;
        $('#totalCount').text('0');
        $('#completedCount').text('0');
        $('#pendingCount').text('0');
        $('#progressPercentage').text('0%');
        $('#progressBarFill').css('width', '0%');
        return this;
    }
    
    // Configurar callback cuando se completa
    onComplete(callback) {
        this.onCompleteCallback = callback;
        return this;
    }
    
    // Configurar callback en cada actualización
    onUpdate(callback) {
        this.onUpdateCallback = callback;
        return this;
    }
    
    // Vincular a los elementos que cambian el progreso
    bindToChanges() {
        $(document).on('click', '.dropdown-menu .dropdown-item', () => {
            setTimeout(() => this.update(), 50);
        });
        
        $(document).on('input', '.cantidad-input', () => {
            setTimeout(() => this.update(), 50);
        });
        
        return this;
    }
    
    // Vincular específicamente al botón de selección (para dropdowns)
    bindToSelectButton() {
        $(document).on('click', '.nota-select, .valoracion-select', function() {
            setTimeout(() => {
                if (window.progressBar) window.progressBar.update();
            }, 100);
        });
        return this;
    }
}

// Instancia global
window.progressBar = new ProgressBar();