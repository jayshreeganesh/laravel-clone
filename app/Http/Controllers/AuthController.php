<?php
namespace App\Http\Controllers;
use App\Core\Request;
use App\Core\Database;

class AuthController extends Controller {
    public function showLogin() {
        return view('auth.login');
    }

    public function login(Request $request) {
        $email = $request->input('email');
        $password = $request->input('password');

        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            return redirect('/products');
        }
        return redirect('/login');
    }

    public function showRegister() {
        return view('auth.register');
    }

    public function register(Request $request) {
        $name = $request->input('name');
        $email = $request->input('email');
        $password = password_hash($request->input('password'), PASSWORD_BCRYPT);

        $pdo = Database::connect();
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $password]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            return redirect('/products');
        } catch (\PDOException $e) {
            return redirect('/register');
        }
    }

    public function logout() {
        session_destroy();
        return redirect('/products');
    }
}
