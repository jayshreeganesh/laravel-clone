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
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR sku LIKE ? OR description LIKE ? ORDER BY $sort $dir");
                $stmt->execute(["%$q%", "%$q%", "%$q%"]);
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

    public function export() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $q = $_GET['q'] ?? '';
        $pdo = \App\Core\Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR sku LIKE ? OR description LIKE ? ORDER BY id DESC");
        $stmt->execute(["%$q%", "%$q%", "%$q%"]);
        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $format = $_GET['format'] ?? 'csv';

        if ($format === 'json') {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="products.json"');
            echo json_encode($products, JSON_PRETTY_PRINT);
            exit;
        }

        if ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="products.csv"');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Name', 'SKU', 'Description', 'Price', 'Stock']);
            foreach ($products as $row) {
                fputcsv($output, [$row['id'], $row['name'], $row['sku'], $row['description'], $row['price'], $row['stock']]);
            }
            fclose($output);
            exit;
        }
    }
}