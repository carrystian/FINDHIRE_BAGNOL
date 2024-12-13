<?php
session_start();
require_once('./Core/JobPost.php');

// Check if user is authenticated; if not, redirect to login.php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$roleID = $_SESSION['role'];
$jobPost = new JobPost();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_job_post_id'])) {
    $jobPostId = $_POST['delete_job_post_id'];
    if ($jobPost->deleteJobPost($jobPostId)) {
        $_SESSION['message'] = ['type' => 'success', 'content' => 'Job post deleted successfully.'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'content' => 'Failed to delete the job post.'];
    }
    header("Location: index.php");
    exit;
}

$jobPosts = $jobPost->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage - Job Posts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(to bottom, #e0f7fa, #b2dfdb);
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
                <?php if ($roleID == 1): ?>
                    <a href="messages_for_applicant.php" 
                    class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-md shadow-md transition duration-200">
                    Message HR
                    </a>
                <?php elseif ($roleID == 2): ?>
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
        <h1 class="text-3xl font-bold text-teal-600 mb-6 flex items-center justify-between">
            Job Posts
            <!-- Button to navigate to Create Job Post page -->
            
            <?php if ($roleID == 2): ?>
                <a href="create_job.php" 
            class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-md shadow-md transition duration-200">
            Create Job Post
            </a>
                <?php endif; ?>
        </h1>

        <!-- Flash Message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="mb-6 p-4 rounded <?= $_SESSION['message']['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= htmlspecialchars($_SESSION['message']['content']) ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- Job Posts List -->
        <?php if ($jobPosts): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <?php foreach ($jobPosts as $post): ?>
                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <h2 class="text-lg font-semibold text-purple-700"><?= htmlspecialchars($post['Title']) ?></h2>
                        <p class="text-gray-600 mb-2"><?= htmlspecialchars($post['Description']) ?></p>
                        <p class="text-xs text-gray-500 mb-4">Created by <?= htmlspecialchars($post['CreatedBy']) ?> on <?= htmlspecialchars($post['CreatedAt']) ?></p>
                        <div class="flex space-x-4">
                            <?php if ($roleID == 2): ?>
                                <a href="applications.php?job_post_id=<?= htmlspecialchars($post['JobPostID']) ?>" 
                                   class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-md">View Applications</a>
                                <form action="index.php" method="POST">
                                    <input type="hidden" name="delete_job_post_id" value="<?= htmlspecialchars($post['JobPostID']) ?>">
                                    <button type="submit" 
                                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">Delete</button>
                                </form>
                            <?php elseif ($roleID == 1): ?>
                                <a href="apply_for_a_job.php?job_post_id=<?= htmlspecialchars($post['JobPostID']) ?>" 
                                   class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-md">Apply</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-gray-500">No job posts available.</p>
        <?php endif; ?>
    </main>
</body>
</html>
