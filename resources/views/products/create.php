<div class="max-w-2xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Add New Product</h1>
            <p class="text-sm text-slate-500 mt-1">Laravel Form & Controller Validation</p>
        </div>
        <a href="<?= url('/products') ?>" class="text-sm text-slate-600 hover:text-slate-900 font-medium">
            &larr; Back to list
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 md:p-8">
        <form action="<?= url('/products') ?>" method="POST" class="space-y-6">
            <?= csrf_field() ?>

            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Product Name <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="<?= old('name') ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition" placeholder="e.g. Ultra HD Smart TV">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="sku" class="block text-sm font-medium text-slate-700 mb-1">SKU Code <span class="text-red-500">*</span></label>
                    <input type="text" id="sku" name="sku" value="<?= old('sku') ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition" placeholder="e.g. TV-55-4K">
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-slate-700 mb-1">Price ($) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" id="price" name="price" value="<?= old('price') ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition" placeholder="499.99">
                </div>
                <div>
                    <label for="stock" class="block text-sm font-medium text-slate-700 mb-1">Stock Quantity</label>
                    <input type="number" min="0" id="stock" name="stock" value="<?= old('stock', '0') ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="4" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition" placeholder="Product features, connectivity, warranty..."><?= old('description') ?></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                <a href="<?= url('/products') ?>" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                    Create Product
                </button>
            </div>
        </form>
    </div>
</div>

