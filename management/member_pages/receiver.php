<?php
include '../config.php';

// Verify user is logged in
if (!isset($_SESSION['member_id'])) {
    echo '<script>window.location.href = "login_member.php";</script>';
    exit();
}

$member_id = $_SESSION['member_id'];

// Fetch member details
$stmt = $conn->prepare("SELECT first_name, last_name FROM members WHERE member_id = ?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch messages with sender details
$sql = "SELECT m.message_id, m.message, m.created_at, m.sender_name, m.is_read 
        FROM messages m 
        WHERE m.member_id = ? 
        ORDER BY m.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>My Messages</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <style>
    /* Custom scrollbar for message container */
    .scrollbar-thin::-webkit-scrollbar {
        width: 6px;
    }
    .scrollbar-thin::-webkit-scrollbar-track {
        background: transparent;
    }
    .scrollbar-thin::-webkit-scrollbar-thumb {
        background-color: #3b82f6;
        border-radius: 10px;
    }
    /* Full height layout */
    .full-height-container {
        height: calc(100vh - 8rem); /* Adjust based on your header/footer height */
    }
  </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800 min-h-screen flex flex-col items-center p-4">
  <div class="w-full max-w-4xl bg-white rounded-2xl shadow-lg p-6 sm:p-10 mt-4 flex flex-col full-height-container">
    <header class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
      <h1 class="text-3xl font-semibold text-blue-600 flex items-center gap-3">
        <i class="fas fa-envelope"></i>
        My Messages
      </h1>
    </header>
    
    <?php if ($result->num_rows > 0): ?>
    <div class="flex-1 flex flex-col space-y-4 overflow-y-auto scrollbar-thin">
      <?php while ($row = $result->fetch_assoc()): ?>
      <article class="relative bg-blue-50 rounded-xl border-l-4 border-blue-600 p-5 shadow-sm hover:shadow-md transition-transform transform hover:translate-x-1">
        <?php if (!$row['is_read']): ?>
        <span class="absolute top-3 right-3 bg-red-600 text-white text-xs font-semibold px-3 py-0.5 rounded-full select-none">
          New
        </span>
        <?php endif; ?>
        <div class="flex justify-between items-center mb-3">
          <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white font-bold text-lg select-none">
              <?php echo strtoupper(substr($row['sender_name'], 0, 1)); ?>
            </div>
            <p class="text-blue-900 font-semibold">
              From: <?php echo htmlspecialchars($row['sender_name']); ?>
            </p>
          </div>
          <time class="flex items-center gap-1 text-gray-500 text-sm" datetime="<?php echo htmlspecialchars($row['created_at']); ?>">
            <i class="far fa-clock"></i>
            <?php 
              $date = new DateTime($row['created_at']);
              echo $date->format('F j, Y g:i A');
            ?>
          </time>
        </div>
        <p class="text-blue-900 whitespace-pre-line leading-relaxed">
          <?php echo nl2br(htmlspecialchars($row['message'])); ?>
        </p>
      </article>
      <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="flex-1 flex flex-col items-center justify-center text-center text-gray-500 space-y-4">
      <img alt="Empty inbox illustration" class="mx-auto w-36 h-36 object-contain" src="https://storage.googleapis.com/a1aa/image/2a72ab21-dc0d-439f-c02f-307c501eb090.jpg"/>
      <h2 class="text-2xl font-semibold text-blue-600">
        No Messages Yet
      </h2>
      <p class="max-w-md">
        When you receive messages, they will appear here for you to read.
      </p>
    </div>
    <?php endif; ?>
    
    <footer class="mt-4 pt-4 border-t border-gray-200 text-center text-gray-400 text-xs select-none">
      <i class="fas fa-sync-alt animate-spin mr-1"></i>
      Page refreshes automatically every minute
    </footer>
  </div>
  
  <script>
    // Auto refresh messages every 60 seconds
    setInterval(() => {
        location.reload();
    }, 60000);
  </script>
</body>
</html>