/**
 * Gestion des images des packages
 */
class PackageImageManager {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.initImagePreview();
        this.initSortable();
    }

    bindEvents() {
        // Gestion de la suppression d'images
        document.addEventListener('click', (e) => {
            if (e.target.matches('.remove-image-btn')) {
                e.preventDefault();
                this.removeImage(e.target);
            }
        });

        // Gestion du changement d'image principale
        document.addEventListener('click', (e) => {
            if (e.target.matches('.set-main-image-btn')) {
                e.preventDefault();
                this.setMainImage(e.target);
            }
        });

        // Prévisualisation des images uploadées
        const imageInput = document.querySelector('input[type="file"][accept*="image"]');
        if (imageInput) {
            imageInput.addEventListener('change', (e) => {
                this.handleImageUpload(e);
            });
        }
    }

    async removeImage(button) {
        const imagePath = button.dataset.imagePath;
        const packageId = button.dataset.packageId;
        
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette image ?')) {
            return;
        }

        try {
            const response = await fetch(`/admin/packages/${packageId}/images/remove`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `image_path=${encodeURIComponent(imagePath)}`
            });

            const data = await response.json();
            
            if (data.success) {
                // Supprimer l'élément de l'interface
                const imageContainer = button.closest('.image-item');
                if (imageContainer) {
                    imageContainer.remove();
                }
                
                this.showNotification('Image supprimée avec succès', 'success');
                this.updateImageCount();
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Erreur lors de la suppression:', error);
            this.showNotification('Erreur lors de la suppression', 'error');
        }
    }

    async setMainImage(button) {
        const imagePath = button.dataset.imagePath;
        const packageId = button.dataset.packageId;
        
        try {
            const response = await fetch(`/admin/packages/${packageId}/images/reorder`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `image_order[]=${encodeURIComponent(imagePath)}`
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Image principale mise à jour', 'success');
                // Recharger la page pour voir les changements
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Erreur lors du changement d\'image principale:', error);
            this.showNotification('Erreur lors du changement', 'error');
        }
    }

    handleImageUpload(event) {
        const files = event.target.files;
        const previewContainer = document.querySelector('.image-preview-container');
        
        if (!previewContainer) return;

        previewContainer.innerHTML = '';

        Array.from(files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                const preview = document.createElement('div');
                preview.className = 'image-preview-item';
                
                reader.onload = (e) => {
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Prévisualisation">
                        <span class="image-name">${file.name}</span>
                    `;
                };
                
                reader.readAsDataURL(file);
                previewContainer.appendChild(preview);
            }
        });
    }

    initImagePreview() {
        // Ajouter des boutons de suppression aux images existantes
        const imageItems = document.querySelectorAll('.image-item');
        imageItems.forEach(item => {
            if (!item.querySelector('.remove-image-btn')) {
                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-image-btn btn btn-sm btn-danger';
                removeBtn.innerHTML = '🗑️';
                removeBtn.title = 'Supprimer cette image';
                removeBtn.dataset.imagePath = item.dataset.imagePath;
                removeBtn.dataset.packageId = item.dataset.packageId;
                
                item.appendChild(removeBtn);
            }
        });
    }

    initSortable() {
        // Initialiser le tri des images si Sortable.js est disponible
        const imageContainer = document.querySelector('.images-container');
        if (imageContainer && typeof Sortable !== 'undefined') {
            new Sortable(imageContainer, {
                animation: 150,
                onEnd: (evt) => {
                    this.updateImageOrder();
                }
            });
        }
    }

    updateImageOrder() {
        const imageItems = document.querySelectorAll('.image-item');
        const order = Array.from(imageItems).map(item => item.dataset.imagePath);
        
        // Envoyer le nouvel ordre au serveur
        this.saveImageOrder(order);
    }

    async saveImageOrder(order) {
        const packageId = document.querySelector('[data-package-id]')?.dataset.packageId;
        if (!packageId) return;

        try {
            const response = await fetch(`/admin/packages/${packageId}/images/reorder`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `image_order[]=${order.map(encodeURIComponent).join('&image_order[]=')}`
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Ordre des images mis à jour', 'success');
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Erreur lors de la mise à jour de l\'ordre:', error);
            this.showNotification('Erreur lors de la mise à jour', 'error');
        }
    }

    updateImageCount() {
        const imageItems = document.querySelectorAll('.image-item');
        const countElement = document.querySelector('.image-count');
        if (countElement) {
            countElement.textContent = imageItems.length;
        }
    }

    showNotification(message, type = 'info') {
        // Créer une notification simple
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Supprimer après 3 secondes
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// Initialiser quand le DOM est prêt
document.addEventListener('DOMContentLoaded', () => {
    new PackageImageManager();
});
