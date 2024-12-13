<?php
session_start();
require_once('./Core/JobPost.php');

// Restrict access to HR role (RoleID = 2)
if ($_SESSION['role'] !== 2) {
    header('Location: index.php');
    exit;
}

// Check if user is authenticated; if not, redirect to login.php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$jobPost = new JobPost();
$errors = [];
$successMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $createdBy = $_SESSION['user_id'];

    $result = $jobPost->store($title, $description, $createdBy);

    if ($result['success']) {
        header('Location: index.php');
        exit;
    } else {
        $errors = $result['errors'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Job Post</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(to bottom, #e0f7fa, #b2dfdb); /* Lighter teal gradient */
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <!-- Header / Navigation -->
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <!-- Logo -->
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-teal-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-xl font-bold text-teal-600">FindHire</span>
            </div>
            
            <!-- Navigation Links -->
            <div class="flex items-center space-x-6">
                <?php if ($_SESSION['role'] == 1): ?>
                    <a href="messages_for_applicant.php" 
                    class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-md shadow-md transition duration-200">
                    Message HR
                    </a>
                <?php elseif ($_SESSION['role'] == 2): ?>
                    <a href="messages_for_hr.php" 
                    class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-md shadow-md transition duration-200">
                    View Messages
                    </a>
                <?php endif; ?>
                <span class="text-gray-700">Welcome, <?= htmlspecialchars($_SESSION['email']) ?></span>
                <form action="logout.php" method="POST">
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md shadow-md">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-6 py-8">
        <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-lg">
            
            <!-- Title Section -->
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold mb-6 text-teal-600">Create Job Post</h2>

                <!-- Back Button -->
                <div class="mb-6">
                    <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-full transition duration-200 ml-2">Back</a>
                </div>
            </div>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <ul class="mb-6 text-red-600">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <!-- Form -->
            <form action="" method="POST">
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700">Job Title</label>
                    <input type="text" name="title" id="title" class="w-full border rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500" rows="4" required></textarea>
                </div>

                <button type="submit" class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-full w-full shadow-md transition duration-200">Create Job Post</button>
            </form>
        </div>
    </main>
</body>
</html>
