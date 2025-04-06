import './bootstrap';

import { openAddModal, closeModal, editProduct, deleteProduct } from './products.js';

// Make functions available globally for onclick attributes
window.openAddModal = openAddModal;
window.closeModal = closeModal;
window.editProduct = editProduct;
window.deleteProduct = deleteProduct;