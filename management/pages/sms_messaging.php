<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>SMS Messaging System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <style>
    /* Scrollbar styles */
    .scrollbar-thin::-webkit-scrollbar {
      width: 6px;
    }
    .scrollbar-thin::-webkit-scrollbar-track {
      background: transparent;
    }
    .scrollbar-thin::-webkit-scrollbar-thumb {
      background-color: #a0aec0;
      border-radius: 3px;
    }
    /* Bubble tail effect */
    .bubble-left::after {
      content: "";
      position: absolute;
      left: -6px;
      top: 10px;
      width: 0;
      height: 0;
      border-top: 8px solid transparent;
      border-right: 8px solid #e5e7eb;
      border-bottom: 8px solid transparent;
    }
    .bubble-right::after {
      content: "";
      position: absolute;
      right: -6px;
      top: 10px;
      width: 0;
      height: 0;
      border-top: 8px solid transparent;
      border-left: 8px solid #2563eb;
      border-bottom: 8px solid transparent;
    }
    /* Container height adjustments */
    .member-list-container {
      height: calc(100vh - 8rem); /* Adjust based on your header height */
    }
    .message-container {
      height: calc(100vh - 16rem); /* Adjust based on your header and form height */
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
  <?php
  require 'C:/xampp/htdocs/www.sangguniang_kabataan.com/sms/vendor/autoload.php';

  use AndroidSmsGateway\Client;
  use AndroidSmsGateway\Domain\Message;

  $response = '';
  $members = [];
  $selectedMemberId = isset($_GET['member_id']) ? $_GET['member_id'] : null;
  $memberMessages = [];
  $selectedMember = null;

  // Create database connection
  $db = new mysqli('localhost', 'root', '', 'youth_sk');
  if ($db->connect_error) {
      die("Connection failed: " . $db->connect_error);
  }

  // Handle SMS sending
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'], $_POST['member_id'], $_POST['username'], $_POST['password'])) {
      $login = $_POST['username'];
      $password = $_POST['password'];
      $messageContent = $_POST['message'];
      $memberId = $db->real_escape_string($_POST['member_id']);

      // Get member's contact number
      $query = "SELECT contact_number FROM members WHERE member_id = '$memberId'";
      $result = $db->query($query);
      if ($result && $result->num_rows > 0) {
          $member = $result->fetch_assoc();
          $result->free();

          $numberInput = $member['contact_number'];
          $number = "+63" . ltrim($numberInput, '0');

          if (!preg_match('/^\+639\d{9}$/', $number)) {
              $response = '❌ Error: Philippine mobile number must start with 9 (format: +639XXXXXXXXX) and be 10 digits long after +63.';
          } else {
              $client = new Client($login, $password);
              $message = new Message($messageContent, [$number]);

              try {
                  $messageState = $client->Send($message);
                  $response = "✅ Message sent! ID: " . $messageState->ID();

                  // Save the sent message to the database
                  $senderName = "Admin";
                  $messageContentEscaped = $db->real_escape_string($messageContent);
                  $query = "
                      INSERT INTO messages (member_id, sender_name, message, is_read, created_at)
                      VALUES ('$memberId', '$senderName', '$messageContentEscaped', 0, NOW())
                  ";
                  $db->query($query);

                  // Optionally check message state
                  $messageState = $client->GetState($messageState->ID());
                  $response .= "<br/>📬 Message state: " . $messageState->State();

                  // Redirect to refresh and show updated messages
                  echo '<script>window.location.href = "?page=sms_messaging&member_id='.$memberId.'";</script>';
                  exit();
              } catch (Exception $e) {
                  $response = "❌ Error: " . $e->getMessage();
              }
          }
      } else {
          $response = "❌ Error: Member not found.";
      }
  }

  // Fetch all members from the database
  $query = "
      SELECT 
          m.member_id, 
          m.first_name,
          m.last_name,
          m.contact_number,
          m.id_photo
      FROM members m
      ORDER BY m.last_name, m.first_name
  ";

  $result = $db->query($query);
  if ($result) {
      $members = $result->fetch_all(MYSQLI_ASSOC);
      $result->free();
  }

  // If a member is selected, get their message history
  if ($selectedMemberId !== null) {
      // Get member details
      $query = "
          SELECT 
              m.member_id, 
              CONCAT(m.first_name, ' ', COALESCE(m.middle_name, ''), ' ', m.last_name) AS full_name,
              m.contact_number,
              m.id_photo
          FROM members m
          WHERE m.member_id = '$selectedMemberId'
      ";
      $result = $db->query($query);
      if ($result && $result->num_rows > 0) {
          $selectedMember = $result->fetch_assoc();
          $result->free();
      }

      // Get message history for this member
      $query = "
          SELECT 
              message_id,
              sender_name,
              message,
              is_read,
              created_at
          FROM messages
          WHERE member_id = '$selectedMemberId'
          ORDER BY created_at DESC
      ";
      $result = $db->query($query);
      if ($result) {
          $memberMessages = $result->fetch_all(MYSQLI_ASSOC);
          $result->free();
      }
  }

  $db->close();
  ?>
  
  <header class="bg-blue-600 text-white p-4 shadow-md">
    <h1 class="text-2xl font-semibold text-center">SMS Messaging System</h1>
  </header>
  
<main class="flex flex-col md:flex-row flex-1 p-4 max-w-full mx-auto w-full h-[calc(100vh-8rem)]">
    <!-- Members List Section -->
<section class="md:w-1/3 bg-white overflow-hidden flex flex-col h-full max-w-full">
      <div class="p-4 border-b border-gray-200 bg-blue-50">
        <h2 class="text-xl font-semibold">Members</h2>
        <div class="mt-2 relative">
          <input type="text" placeholder="Search members..." class="w-full px-4 py-2 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
        </div>
      </div>
      
      <div class="overflow-y-auto flex-1 scrollbar-thin">
        <div class="grid grid-cols-1 gap-3 p-4">
          <?php foreach ($members as $member): ?>
            <div onclick="window.location.href='?page=sms_messaging&member_id=<?= $member['member_id'] ?>'" 
                 class="flex items-center gap-3 p-3 rounded-lg cursor-pointer hover:bg-blue-50 transition-colors <?= $selectedMemberId == $member['member_id'] ? 'bg-blue-100 border border-blue-200' : '' ?>">
              <img src="<?= htmlspecialchars($member['id_photo'] ?? 'https://storage.googleapis.com/a1aa/image/e8f2d3cf-7c3f-46af-f0bd-8e4e2a3d1f26.jpg') ?>" 
                   alt="Member photo" 
                   class="w-12 h-12 rounded-full object-cover border-2 border-white shadow" />
              <div class="flex-1 min-w-0">
                <h3 class="font-medium text-gray-900 truncate">
                  <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                </h3>
                <p class="text-sm text-gray-500 flex items-center gap-1 truncate">
                  <i class="fas fa-phone-alt text-xs"></i>
                  <?= htmlspecialchars($member['contact_number']) ?>
                </p>
              </div>
              <?php if ($selectedMemberId == $member['member_id']): ?>
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Message Section -->
    <section class="md:w-2/3 bg-white flex flex-col h-full max-w-full">
      <?php if ($response): ?>
        <div class="m-4 p-3 rounded-md text-sm <?= strpos($response, '✅') === 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> border <?= strpos($response, '✅') === 0 ? 'border-green-300' : 'border-red-300' ?>">
          <i class="fas <?= strpos($response, '✅') === 0 ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
          <span><?= $response ?></span>
        </div>
      <?php endif; ?>
      
      <?php if ($selectedMember): ?>
        <div class="flex flex-col h-full">
          <header class="flex items-center justify-between p-6 border-b border-gray-200">
            <div>
              <h2 class="text-2xl font-semibold">
                <?= htmlspecialchars($selectedMember['full_name']) ?>
              </h2>
              <p class="text-gray-600 flex items-center gap-2 mt-1">
                <i class="fas fa-phone-alt"></i>
                <?= htmlspecialchars($selectedMember['contact_number']) ?>
              </p>
            </div>
            <img src="<?= htmlspecialchars($selectedMember['id_photo'] ?? 'https://storage.googleapis.com/a1aa/image/e8f2d3cf-7c3f-46af-f0bd-8e4e2a3d1f26.jpg') ?>" 
                 alt="Profile picture for <?= htmlspecialchars($selectedMember['full_name']) ?>" 
                 class="w-12 h-12 rounded-full object-cover" />
          </header>
          
          <div class="flex-1 overflow-y-auto scrollbar-thin p-6 bg-gray-50" id="messages" style="max-height: calc(100vh - 24rem);">
            <?php if (!empty($memberMessages)): ?>
              <div class="flex flex-col space-y-4 max-w-3xl mx-auto">
                <?php
                // Sort messages ascending by created_at for chat style
                usort($memberMessages, function ($a, $b) {
                    return strtotime($a['created_at']) - strtotime($b['created_at']);
                });
                foreach ($memberMessages as $msg):
                  $isSystem = strtolower($msg['sender_name']) === 'admin';
                  $isUnread = !$msg['is_read'];
                ?>
                <div class="relative max-w-xs md:max-w-md px-4 py-2 rounded-lg shadow-sm <?= $isSystem ? 'ml-auto bg-blue-600 text-white bubble-right' : 'mr-auto bubble-left' ?> <?= $isUnread ? 'ring-2 ring-blue-400' : '' ?>">
                  <div class="text-xs font-semibold mb-1 flex justify-between items-left select-none">
                    <span><?= htmlspecialchars($msg['sender_name']) ?></span>
                    <time class="text-gray-300 text-xs ml-2" datetime="<?= htmlspecialchars($msg['created_at']) ?>">
                      <?= date('M d, Y h:i A', strtotime($msg['created_at'])) ?>
                    </time>
                  </div>
                  <p class="whitespace-pre-wrap break-words"></p>
                    <?= htmlspecialchars($msg['message']) ?>
                  </p>
                </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="text-center text-gray-500 italic mt-10">
                No messages yet with this member.
              </p>
            <?php endif; ?>
          </div>
          
          <form action="" class="border-t border-gray-200 p-4 flex items-center gap-4 max-w-3xl mx-auto" method="post">
            <input name="username" type="hidden" value="KWBUN-"/>
            <input name="password" type="hidden" value="2342Gldekirby@21"/>
            <input name="member_id" type="hidden" value="<?= $selectedMemberId ?>"/>
            <textarea class="flex-grow resize-none w-fit rounded-full border border-gray-300 px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 scrollbar-thin" id="message" name="message" oninput="autoGrow(this)" placeholder="Type a message" required="" rows="1"></textarea>
            <button aria-label="Send message" class="inline-flex items-center justify-center p-3 bg-blue-600 text-white rounded-full shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" type="submit">
              <i class="fas fa-paper-plane"></i>
            </button>
          </form>
        </div>
        
        <script>
          // Auto grow textarea like chat apps
          function autoGrow(element) {
            element.style.height = "5px";
            element.style.height = (element.scrollHeight) + "px";
          }
          
          // Scroll to bottom on page load
          window.onload = function() {
            const messages = document.getElementById("messages");
            if (messages) {
              messages.scrollTop = messages.scrollHeight;
            }
            
            // Focus on message input
            const messageInput = document.getElementById("message");
            if (messageInput) {
              messageInput.focus();
            }
          };
        </script>
      <?php else: ?>
        <div class="flex flex-col items-center justify-center h-full p-6 text-gray-500 text-center">
          <i class="fas fa-comments text-4xl mb-4 text-gray-300"></i>
          <p class="text-lg italic">Select a member from the list to view messages and send SMS.</p>
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>