<?php
namespace App\Http\Controllers;

use App\Core\Request;
use App\Models\Product;

class ProductController extends Controller {

    public function index() {
        $products = Product::all();
        return view('products.index', compact('products'));
    }

    public function show(int $id) {
        $product = Product::findOrFail($id);
        return view('products.show', compact('product'));
    }

    public function create() {
        return view('products.create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name'  => 'required',
            'sku'   => 'required',
            'price' => 'required|numeric',
        ]);

        Product::create([
            'name'        => $validated['name'],
            'sku'         => $validated['sku'],
            'price'       => (float)$validated['price'],
            'stock'       => (int)$request->input('stock', 0),
            'description' => $request->input('description', ''),
        ]);

        return redirect('/products')->with('success', 'Product created successfully!');
    }

    public function edit(int $id) {
        $product = Product::findOrFail($id);
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, int $id) {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name'  => 'required',
            'sku'   => 'required',
            'price' => 'required|numeric',
        ]);

        $product->update([
            'name'        => $validated['name'],
            'sku'         => $validated['sku'],
            'price'       => (float)$validated['price'],
            'stock'       => (int)$request->input('stock', 0),
            'description' => $request->input('description', ''),
        ]);

        return redirect('/products')->with('success', 'Product updated successfully!');
    }

    public function destroy(int $id) {
        $product = Product::find($id);
        if ($product) {
            $product->delete();
            return redirect('/products')->with('success', 'Product deleted successfully!');
        }
        return redirect('/products')->with('error', 'Product not found.');
    }
}
