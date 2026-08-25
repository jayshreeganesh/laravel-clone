<?php
namespace App\Http\Controllers;

use App\Core\Request;
use App\Models\Product;
use App\Core\Database;

class ProductController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        // Protect routes: only index and show are public
        if (!isset($_SESSION['user_id']) && (strpos($uri, '/create') !== false || strpos($uri, '/edit') !== false || $_SERVER['REQUEST_METHOD'] === 'POST')) {
            if ($uri !== '/login' && $uri !== '/register') {
                header('Location: /login');
                exit;
            }
        }
    }

    public function index() {
        $q = $_GET['q'] ?? '';
        $sort = $_GET['sort'] ?? 'id';
        $dir = $_GET['dir'] ?? 'desc';
        
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY $sort $dir");
                $stmt->execute(["%$q%"]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $products = [];
        foreach ($rows as $row) {
            $products[] = new \App\Models\Product($row);
        }
        
        return view('products.index', compact('products', 'q', 'sort', 'dir'));
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

