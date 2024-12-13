<?php
session_start();
require_once('./Core/Application.php');
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


// Fetch job post title
$jobPostModel = new JobPost();
$jobPostId = $_GET['job_post_id'] ?? 0;
$jobPostDetails = $jobPostModel->getJobPostById($jobPostId);
$jobPostTitle = $jobPostDetails['Title'] ?? "Unknown Job Post";
$roleID = $_SESSION['role'];

$applicationModel = new Application();
$applications = $applicationModel->getApplicationsByJobPostId($jobPostId);

// Handle form submission for accept/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $applicationId = $_POST['application_id'] ?? 0;

    if ($action === 'accept') {
        $applicationModel->acceptApplication($applicationId);
    } elseif ($action === 'reject') {
        $applicationModel->rejectApplication($applicationId);
    }

    header("Location: applications.php?job_post_id=" . $jobPostId);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applications</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(to bottom, #e0f7fa, #b2dfdb);
        }
    </style>
</head>
<body class="min-h-screen">

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


    <!-- Main Container -->
    <main class="container mx-auto px-6 py-12">
        <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-6xl mx-auto">
            <!-- Title and Back Button -->
            <div class="flex justify-between items-center mb-8">
                <a href="index.php" 
                   class="bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded-full shadow-md transition">
                    Back
                </a>
                <h2 class="text-2xl font-bold text-gray-700">Applications for: <?= htmlspecialchars($jobPostTitle) ?></h2>
            </div>

            <!-- Applications Section -->
            <div>
                <?php if ($applications && count($applications) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full border-collapse border border-gray-300 bg-white">
                            <thead>
                                <tr>
                                    <th class="border border-gray-300 px-4 py-2 bg-gray-100 text-gray-700">Applicant</th>
                                    <th class="border border-gray-300 px-4 py-2 bg-gray-100 text-gray-700">Cover Letter</th>
                                    <th class="border border-gray-300 px-4 py-2 bg-gray-100 text-gray-700">Resume</th>
                                    <th class="border border-gray-300 px-4 py-2 bg-gray-100 text-gray-700">Status</th>
                                    <th class="border border-gray-300 px-4 py-2 bg-gray-100 text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $application): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($application['Username']) ?></td>
                                        <td class="border border-gray-300 px-4 py-2"><?= nl2br(htmlspecialchars($application['CoverLetter'])) ?></td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            <a href="<?= htmlspecialchars($application['ResumePath']) ?>" 
                                               target="_blank" class="text-teal-500 hover:text-teal-700 underline">
                                                View Resume
                                            </a>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($application['Status']) ?></td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            <form method="POST" class="flex space-x-2">
                                                <input type="hidden" name="application_id" value="<?= htmlspecialchars($application['ApplicationID']) ?>">
                                                <button type="submit" name="action" value="accept" 
                                                        class="bg-teal-500 hover:bg-teal-600 text-white px-3 py-1 rounded shadow-md transition">
                                                    Accept
                                                </button>
                                                <button type="submit" name="action" value="reject" 
                                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded shadow-md transition">
                                                    Reject
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-600">No applications found for this job post.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>

