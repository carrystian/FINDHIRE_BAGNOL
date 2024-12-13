<?php
session_start();
require_once('./Core/Message.php');
require_once('./Core/User.php');

// Check if user is authenticated; if not, redirect to login.php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Restrict access only to Applicants (role 1)
if ($_SESSION['role'] !== 1) {
    header('Location: index.php');
    exit;
}

$messageModel = new Message();
$userModel = new User();

// Fetch all HR users to show in dropdown
$hrs = $userModel->getHRs();
$sendSuccess = false;
$deleteSuccess = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle message sending
    if (isset($_POST['send_message'])) {
        $receiverId = $_POST['receiver_id'] ?? 0;
        $content = $_POST['content'] ?? '';
        if ($receiverId && $content) {
            $sendSuccess = $messageModel->sendMessage($_SESSION['user_id'], $receiverId, $content);
            header('Location: messages_for_applicant.php');
            exit;
        }
    }

    // Handle delete all messages
    if (isset($_POST['delete_messages'])) {
        $hrId = $_POST['hr_id'] ?? 0;
        if ($hrId) {
            $deleteSuccess = $messageModel->deleteAllMessages($_SESSION['user_id'], $hrId);
            header('Location: messages_for_applicant.php');
            exit;
        }
    }
}

// Fetch all conversations grouped by HR (ReceiverID)
$conversations = [];
foreach ($hrs as $hr) {
    $conversation = $messageModel->getConversationBetweenUserAndApplicant($_SESSION['user_id'], $hr['UserID']);
    
    // Only add HR to the conversations array if there are messages
    if (count($conversation) > 0) {
        $conversations[$hr['UserID']] = $conversation;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversations with HR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(to bottom, #e0f7fa, #b2dfdb); /* Light teal gradient */
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <!-- Header -->
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
                <a href="index.php" class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-md shadow-md">Dashboard</a>
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
            Conversations with HR
        </h1>

        <!-- Success Messages -->
        <?php if ($sendSuccess): ?>
            <div class="mb-6 p-4 rounded bg-green-100 text-green-700">
                Your message has been sent!
            </div>
        <?php endif; ?>
        <?php if ($deleteSuccess): ?>
            <div class="mb-6 p-4 rounded bg-red-100 text-red-700">
                All messages with this HR have been deleted.
            </div>
        <?php endif; ?>

        <!-- Conversations Section -->
        <div class="message-section">
            <h2 class="text-2xl font-semibold text-teal-600 mb-4">Previous Messages</h2>

            <?php if (count($conversations) > 0): ?>
                <?php foreach ($conversations as $hrId => $conversation): ?>
                    <div class="message-list mb-6 p-4 bg-white rounded-lg shadow-md">
                        <div class="message-header flex justify-between items-center mb-4">
                            <h4 class="text-lg font-semibold text-teal-600"><?= htmlspecialchars($hrs[array_search($hrId, array_column($hrs, 'UserID'))]['Username']) ?></h4>
                            <!-- Delete Conversation Button -->
                            <form method="POST" class="inline-block">
                                <input type="hidden" name="hr_id" value="<?= htmlspecialchars($hrId) ?>">
                                <button type="submit" name="delete_messages" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md shadow-md">Delete Conversation</button>
                            </form>
                        </div>

                        <?php foreach ($conversation as $message): ?>
                            <div class="message mb-2">
                                <div class="sender text-teal-600 font-medium"><?= $message['SenderID'] == $_SESSION['user_id'] ? 'You' : 'HR' ?>:</div>
                                <div class="message-content text-gray-700 ml-6"><?= nl2br(htmlspecialchars($message['Content'])) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-gray-500">No conversations yet. Start a message with an HR!</p>
            <?php endif; ?>
        </div>

        <!-- Start New Conversation Section -->
        <div class="form-section mt-8">
            <h3 class="text-2xl font-semibold text-teal-600 mb-4">Start a New Conversation</h3>
            <form method="POST" class="bg-white p-6 rounded-lg shadow-md">
                <label for="receiver_id" class="text-teal-600 font-medium">Select HR:</label>
                <select name="receiver_id" id="receiver_id" class="block w-full bg-teal-50 border border-teal-200 text-teal-600 rounded-md p-2 mb-4" required>
                    <option value="" disabled selected>Select an HR</option>
                    <?php foreach ($hrs as $hr): ?>
                        <option value="<?= htmlspecialchars($hr['UserID']) ?>"><?= htmlspecialchars($hr['Username']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="content" class="text-teal-600 font-medium">Message Content:</label>
                <textarea name="content" id="content" rows="4" class="block w-full bg-teal-50 border border-teal-200 text-teal-600 rounded-md p-2 mb-4" required></textarea>

                <button type="submit" name="send_message" class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-md shadow-md">Send Message</button>
            </form>
        </div>
    </main>
</body>
</html>
