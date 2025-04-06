<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel AJAX CRUD with Tailwind</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Product Management</h1>
        
        <button onclick="openAddModal()" class="bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-700 mb-4 cursor-pointer">
            Add Product
        </button>
        
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="products-table" class="bg-white divide-y divide-gray-200">
                    @foreach($products as $product)
                    <tr id="product-{{ $product->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">{{ $product->name }}</td>
                        <td class="px-6 py-4">{{ $product->description }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${{ number_format($product->price, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button onclick="editProduct({{ $product->id }})" class="bg-yellow-500 text-white py-2 px-4 rounded-lg hover:bg-yellow-600 mb-4 cursor-pointer">Edit</button>
                            <button onclick="deleteProduct({{ $product->id }})" class="bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600 mb-4 cursor-pointer">Delete</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="productModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 id="modalTitle" class="text-lg leading-6 font-medium text-gray-900">Add New Product</h3>
                <form id="productForm" class="mt-2">
                    @csrf
                    <input type="hidden" id="productId" name="id">
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                        <input type="text" id="name" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <span id="name-error" class="text-red-500 text-xs italic"></span>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
                        <textarea id="description" name="description" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                        <span id="description-error" class="text-red-500 text-xs italic"></span>
                    </div>
                    <div class="mb-4">
                        <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Price:</label>
                        <input type="number" step="0.01" id="price" name="price" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <span id="price-error" class="text-red-500 text-xs italic"></span>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-gray-300 mr-2 cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-cyan-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-cyan-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 id="confirmTitle" class="text-lg leading-6 font-medium text-gray-900 mt-2">Confirm Deletion</h3>
                <div class="mt-2 px-7 py-3">
                    <p id="confirmMessage" class="text-sm text-gray-500">Are you sure you want to delete this product?</p>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="confirmCancel" class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 mr-2">
                        Cancel
                    </button>
                    <button id="confirmOK" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="successAlert" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg hidden">
        <span id="successMessage"></span>
    </div>
    
</body>
</html>