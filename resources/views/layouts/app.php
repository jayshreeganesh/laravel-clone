<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Laravel Clone') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col font-sans">
    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-red-600 to-rose-600 text-white shadow-md">
        <div class="max-w-6xl mx-auto px-4 py-3.5 flex justify-between items-center">
            <a href="<?= url('/products') ?>" class="text-xl font-bold tracking-tight flex items-center space-x-2">
                <i class="fa-brands fa-laravel text-2xl"></i>
                <span>Laravel <span>Clone</span></span>
                <span class="text-xs bg-white/20 text-white px-2 py-0.5 rounded-full ml-2">Low Inode</span>
            </a>
            <div class="flex items-center space-x-4">
                <a href="<?= url('/products') ?>" class="text-sm font-medium hover:text-red-100 transition">Products</a>
                <a href="<?= url('/products/create') ?>" class="text-sm bg-white text-red-600 hover:bg-red-50 font-semibold px-3 py-1.5 rounded-md shadow-sm transition">
                    <i class="fa-solid fa-plus mr-1"></i> New Product
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="max-w-6xl mx-auto px-4 py-8 flex-1 w-full">
        <!-- Flash Messages -->
        <?php if (!empty($flash_success)): ?>
            <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded shadow-sm flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                    <span><?= htmlspecialchars($flash_success) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($flash_error)): ?>
            <div class="mb-6 bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 rounded shadow-sm flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>
                    <span><?= htmlspecialchars($flash_error) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="mb-6 bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 rounded shadow-sm">
                <div class="font-semibold mb-1 flex items-center space-x-1">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Please fix the following errors:</span>
                </div>
                <ul class="list-disc list-inside text-sm space-y-0.5">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Content Slot -->
        <?= $slot ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 mt-12 text-center text-xs text-slate-500">
        <div class="max-w-6xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center space-y-2 md:space-y-0">
            <p>Laravel Clone (Ultra-Low Inode MVC) &copy; <?= date('Y') ?></p>
            <p>Eloquent-style ORM &bull; Total Project Files: <strong>~16</strong> &bull; Zero Composer Bloat</p>
        </div>
    </footer>
</body>
</html>
