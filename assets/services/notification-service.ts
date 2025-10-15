/**
 * Notification Service
 * 
 * Provides methods for displaying toast notifications to the user
 * with a clean, injectable and testable design.
 */

/**
 * Types of notifications that can be displayed
 */
export enum NotificationType {
    SUCCESS = 'success',
    ERROR = 'error',
    WARNING = 'warning',
    INFO = 'info'
}

/**
 * Configuration options for a notification
 */
export interface NotificationOptions {
    /**
     * Type of notification (success, error, warning, info)
     */
    type: NotificationType;
    
    /**
     * Message to display
     */
    message: string;
    
    /**
     * Duration in milliseconds (0 for no auto-hide)
     * @default 5000
     */
    duration?: number;
    
    /**
     * Position of the notification
     * @default 'top-right'
     */
    position?: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left' | 'top-center' | 'bottom-center';
}

/**
 * Interface for notification handler implementations
 */
export interface NotificationHandler {
    /**
     * Show a notification with the given options
     */
    show(options: NotificationOptions): void;
    
    /**
     * Show a success notification
     */
    showSuccess(message: string, duration?: number): void;
    
    /**
     * Show an error notification
     */
    showError(message: string, duration?: number): void;
    
    /**
     * Show a warning notification
     */
    showWarning(message: string, duration?: number): void;
    
    /**
     * Show an info notification
     */
    showInfo(message: string, duration?: number): void;
}

/**
 * Default toast notification implementation using Toastr
 * Falls back to console if Toastr is not available
 */
class ToastrNotificationHandler implements NotificationHandler {
    /**
     * Show a notification with the given options
     */
    show(options: NotificationOptions): void {
        const { type, message, duration = 5000, position = 'top-right' } = options;
        
        // Check if toastr is available globally
        const toastr = (window as any).toastr;
        
        if (toastr) {
            // Configure toastr if needed
            toastr.options = {
                closeButton: true,
                newestOnTop: true,
                progressBar: true,
                positionClass: `toast-${position}`,
                preventDuplicates: false,
                timeOut: duration,
                extendedTimeOut: 1000,
                showEasing: 'swing',
                hideEasing: 'linear',
                showMethod: 'fadeIn',
                hideMethod: 'fadeOut'
            };
            
            // Show the notification
            switch (type) {
                case NotificationType.SUCCESS:
                    toastr.success(message);
                    break;
                case NotificationType.ERROR:
                    toastr.error(message);
                    break;
                case NotificationType.WARNING:
                    toastr.warning(message);
                    break;
                case NotificationType.INFO:
                    toastr.info(message);
                    break;
                default:
                    toastr.info(message);
            }
        } else {
            // Fallback to console
            switch (type) {
                case NotificationType.SUCCESS:
                    console.info(`✅ ${message}`);
                    break;
                case NotificationType.ERROR:
                    console.error(`❌ ${message}`);
                    break;
                case NotificationType.WARNING:
                    console.warn(`⚠️ ${message}`);
                    break;
                case NotificationType.INFO:
                    console.info(`ℹ️ ${message}`);
                    break;
                default:
                    console.log(message);
            }
        }
    }
    
    /**
     * Show a success notification
     */
    showSuccess(message: string, duration?: number): void {
        this.show({
            type: NotificationType.SUCCESS,
            message,
            duration
        });
    }
    
    /**
     * Show an error notification
     */
    showError(message: string, duration?: number): void {
        this.show({
            type: NotificationType.ERROR,
            message,
            duration
        });
    }
    
    /**
     * Show a warning notification
     */
    showWarning(message: string, duration?: number): void {
        this.show({
            type: NotificationType.WARNING,
            message,
            duration
        });
    }
    
    /**
     * Show an info notification
     */
    showInfo(message: string, duration?: number): void {
        this.show({
            type: NotificationType.INFO,
            message,
            duration
        });
    }
}

/**
 * Factory to create notification services
 * Makes it easier to test and replace the implementation
 */
export class NotificationServiceFactory {
    private static instance: NotificationHandler | null = null;
    
    /**
     * Get or create the notification service instance
     */
    static getInstance(): NotificationHandler {
        if (!NotificationServiceFactory.instance) {
            NotificationServiceFactory.instance = new ToastrNotificationHandler();
        }
        return NotificationServiceFactory.instance;
    }
    
    /**
     * Set a custom notification handler (useful for testing)
     */
    static setInstance(handler: NotificationHandler): void {
        NotificationServiceFactory.instance = handler;
    }
    
    /**
     * Reset to default handler
     */
    static reset(): void {
        NotificationServiceFactory.instance = null;
    }
}

/**
 * Default notification service instance
 */
export const notificationService = NotificationServiceFactory.getInstance();

export default notificationService;
