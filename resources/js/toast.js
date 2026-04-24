// resources/js/toast.js

class ToastNotification {
    constructor() {
        this.container = null;
        this.createContainer();
    }

    createContainer() {
        // Crear el contenedor si no existe
        if (!document.getElementById('toast-container')) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
                pointer-events: none;
            `;
            document.body.appendChild(container);
        }
        this.container = document.getElementById('toast-container');
    }

    show(message, type = 'success', duration = 2000) {
        const toast = this.createToast(message, type);
        this.container.appendChild(toast);
        
        // Animación de entrada (de derecha a izquierda)
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.opacity = '1';
        }, 10);
        
        // Auto-cerrar después de la duración
        setTimeout(() => {
            this.close(toast);
        }, duration);
        
        return toast;
    }

    createToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        
        // Colores según tipo
        const colors = {
            success: { bg: '#28a745', icon: 'fa-check-circle' },
            error: { bg: '#dc3545', icon: 'fa-exclamation-circle' },
            warning: { bg: '#ffc107', icon: 'fa-exclamation-triangle' },
            info: { bg: '#17a2b8', icon: 'fa-info-circle' }
        };
        
        const color = colors[type] || colors.success;
        
        toast.style.cssText = `
            background: white;
            border-left: 4px solid ${color.bg};
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            padding: 12px 16px;
            min-width: 280px;
            max-width: 350px;
            display: flex;
            align-items: center;
            gap: 12px;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease;
            pointer-events: auto;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
        `;
        
        toast.innerHTML = `
            <i class="fas ${color.icon}" style="color: ${color.bg}; font-size: 18px;"></i>
            <span style="flex: 1; color: #333;">${message}</span>
            <button class="toast-close" style="
                background: none;
                border: none;
                cursor: pointer;
                color: #999;
                font-size: 14px;
                padding: 0;
                margin-left: 8px;
            ">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Botón cerrar
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => this.close(toast));
        
        return toast;
    }

    close(toast) {
        // Animación de salida (de izquierda a derecha)
        toast.style.transform = 'translateX(100%)';
        toast.style.opacity = '0';
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }
    
    // Métodos de conveniencia
    success(message, duration = 2000) {
        return this.show(message, 'success', duration);
    }
    
    error(message, duration = 2500) {
        return this.show(message, 'error', duration);
    }
    
    warning(message, duration = 2000) {
        return this.show(message, 'warning', duration);
    }
    
    info(message, duration = 2000) {
        return this.show(message, 'info', duration);
    }
}

// Instancia global
window.toast = new ToastNotification();