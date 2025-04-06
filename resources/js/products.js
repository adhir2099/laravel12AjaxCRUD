const productsTable = document.getElementById('products-table');
const productModal = document.getElementById('productModal');
const productForm = document.getElementById('productForm');
const modalTitle = document.getElementById('modalTitle');
const productIdInput = document.getElementById('productId');
const successAlert = document.getElementById('successAlert');
const successMessage = document.getElementById('successMessage');

// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Modal Functions
export function openAddModal() {
    modalTitle.innerText = 'Add New Product';
    productIdInput.value = '';
    productForm.reset();
    clearErrors();
    productModal.classList.remove('hidden');
}

export function closeModal() {
    productModal.classList.add('hidden');
}

function clearErrors() {
    document.querySelectorAll('[id$="-error"]').forEach(el => {
        el.innerText = '';
    });
}

function showSuccess(message) {
    successMessage.innerText = message;
    successAlert.classList.remove('hidden');
    setTimeout(() => {
        successAlert.classList.add('hidden');
    }, 3000);
}

// CRUD Operations
export async function saveProduct(e) {
    e.preventDefault();
    
    const formData = new FormData(productForm);
    const productId = productIdInput.value;
    const url = productId ? `/products/${productId}` : '/products';
    const method = productId ? 'PUT' : 'POST';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });

        const data = await response.json();

        if (!response.ok) throw data;

        if (data.success) {
            closeModal();
            if (productId) {
                updateProductRow(data.data);
            } else {
                addProductRow(data.data);
            }
            showSuccess(productId ? 'Product updated successfully!' : 'Product added successfully!');
        }
    } catch (error) {
        handleErrors(error);
    }
}

function addProductRow(product) {
    const row = document.createElement('tr');
    row.id = `product-${product.id}`;
    row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">${product.name}</td>
        <td class="px-6 py-4">${product.description}</td>
        <td class="px-6 py-4 whitespace-nowrap">$${parseFloat(product.price).toFixed(2)}</td>
        <td class="px-6 py-4 whitespace-nowrap">
            <button onclick="editProduct(${product.id})" class="bg-yellow-500 text-white py-2 px-4 rounded-lg hover:bg-yellow-600 mb-4 cursor-pointer">Edit</button>
            <button onclick="deleteProduct(${product.id})" class="bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600 mb-4 cursor-pointer">Delete</button>
        </td>
    `;
    productsTable.appendChild(row);
}

function updateProductRow(product) {
    const row = document.getElementById(`product-${product.id}`);
    if (row) {
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">${product.name}</td>
            <td class="px-6 py-4">${product.description}</td>
            <td class="px-6 py-4 whitespace-nowrap">$${parseFloat(product.price).toFixed(2)}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <button onclick="editProduct(${product.id})" class="bg-yellow-500 text-white py-2 px-4 rounded-lg hover:bg-yellow-600 mb-4 cursor-pointer">Edit</button>
                <button onclick="deleteProduct(${product.id})" class="bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600 mb-4 cursor-pointer">Delete</button>
            </td>
        `;
    }
}

export async function editProduct(id) {
    try {
        const response = await fetch(`/products/${id}`);
        const product = await response.json();
        
        modalTitle.innerText = 'Edit Product';
        productIdInput.value = product.id;
        document.getElementById('name').value = product.name;
        document.getElementById('description').value = product.description;
        document.getElementById('price').value = product.price;
        clearErrors();
        productModal.classList.remove('hidden');
    } catch (error) {
        console.error('Error fetching product:', error);
    }
}

export async function deleteProduct(id) {
    const confirmModal = document.getElementById('confirmModal');
    const confirmOK = document.getElementById('confirmOK');
    const confirmCancel = document.getElementById('confirmCancel');
    
    confirmModal.classList.remove('hidden');
    
    return new Promise((resolve) => {
        const confirmHandler = () => {
            cleanup();
            resolve(true);
        };
        
        const cancelHandler = () => {
            cleanup();
            resolve(false);
        };
        
        const cleanup = () => {
            confirmOK.removeEventListener('click', confirmHandler);
            confirmCancel.removeEventListener('click', cancelHandler);
            confirmModal.classList.add('hidden');
        };
        
        confirmOK.addEventListener('click', confirmHandler);
        confirmCancel.addEventListener('click', cancelHandler);
    }).then(async (confirmed) => {
        if (confirmed) {
            try {
                const response = await fetch(`/products/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById(`product-${id}`)?.remove();
                    showSuccess('Product deleted successfully!');
                }
            } catch (error) {
                console.error('Error deleting product:', error);
            }
        }
    });
}

function handleErrors(error) {
    if (error.errors) {
        for (const [field, messages] of Object.entries(error.errors)) {
            const errorElement = document.getElementById(`${field}-error`);
            if (errorElement) {
                errorElement.innerText = messages[0];
            }
        }
    } else {
        console.error('Error:', error);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    productForm.addEventListener('submit', saveProduct);
});