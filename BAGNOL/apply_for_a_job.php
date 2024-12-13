<?php
session_start();
require_once('./Core/JobPost.php');

// Only allow applicants (roleID == 1) to access this page
if ($_SESSION['role'] !== 1) {
    header('Location: index.php');
    exit;
}

// Check if user is authenticated; if not, redirect to login.php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$jobPostId = $_GET['job_post_id'] ?? null;
$jobPost = new JobPost();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {
    // Handle the application submission
    $coverLetter = $_POST['cover_letter'];
    
    // Handle file upload (resume)
    $resume = $_FILES['resume'];
    $resumePath = null;
    
    // Check if a file was uploaded and is a valid PDF
    if ($resume && $resume['error'] === UPLOAD_ERR_OK) {
        $fileTmpName = $resume['tmp_name'];
        $fileName = basename($resume['name']);
        $filePath = 'uploads/resumes/' . $fileName;
        
        // Move uploaded file to the 'uploads/resumes/' directory
        if (move_uploaded_file($fileTmpName, $filePath)) {
            $resumePath = $filePath;
        } else {
            $_SESSION['message'] = ['type' => 'error', 'content' => 'Failed to upload resume.'];
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'content' => 'Please upload a valid PDF resume.'];
    }

    // If file upload succeeded, proceed to apply
    if ($resumePath && $jobPostId) {
        $applicantId = $_SESSION['user_id'];
        if ($jobPost->applyJob($jobPostId, $applicantId, $coverLetter, $resumePath)) {
            $_SESSION['message'] = ['type' => 'success', 'content' => 'Application submitted successfully.'];
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['message'] = ['type' => 'error', 'content' => 'Failed to apply for the job.'];
        }
    }
}

// Fetch job post details for the selected job post
if ($jobPostId) {
    $jobDetails = $jobPost->getJobPostDetails($jobPostId);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Job</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(to bottom, #e0f7fa, #b2dfdb); /* Lighter teal */
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
            <!-- Flash Message Section -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="mb-6 p-4 rounded <?= $_SESSION['message']['type'] === 'success' ? 'bg-green-200 text-green-700' : 'bg-red-200 text-red-700' ?>">
                    <?= htmlspecialchars($_SESSION['message']['content']) ?>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <!-- Job Application Form -->
            <h2 class="text-3xl font-bold mb-6 text-center text-teal-600">Apply for Job</h2>

            <?php if ($jobDetails): ?>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-6">
                        <label for="cover_letter" class="block text-gray-800 font-medium mb-2">Cover Letter</label>
                        <textarea name="cover_letter" id="cover_letter" rows="5" 
                                  class="w-full px-4 py-2 border rounded focus:outline-none focus:ring focus:ring-teal-300" 
                                  required></textarea>
                    </div>

                    <div class="mb-6">
                        <label for="resume" class="block text-gray-800 font-medium mb-2">Resume (PDF)</label>
                        <input type="file" name="resume" id="resume" accept="application/pdf" 
                               class="w-full px-4 py-2 border rounded focus:outline-none focus:ring focus:ring-teal-300" 
                               required>
                    </div>

                    <div class="flex justify-between items-center">
                        <!-- Back Button -->
                        <a href="index.php" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-full shadow transition duration-200">
                            Back
                        </a>
                        <!-- Submit Button -->
                        <button type="submit" name="apply" 
                                class="bg-teal-500 hover:bg-teal-600 text-white px-6 py-2 rounded-full transition duration-200">
                            Submit Application
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <p class="text-center text-gray-600">Job post not found.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
