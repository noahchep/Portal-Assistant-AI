<?php
session_start();

/* --- SECURITY CHECK --- */
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

/* --- DB CONNECTION --- */
$conn = mysqli_connect("localhost", "root", "", "Portal-Asisstant-AI");
if (!$conn) {
    die("Database connection failed");
}

/* --- FETCH STUDENT DATA --- */
$user_id = $_SESSION['user_id'];
$sql = "SELECT full_name, reg_number FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) !== 1) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

$student = mysqli_fetch_assoc($result);
$name_parts = explode(" ", $student['full_name']);
$fname = $name_parts[0] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Student Support Agent</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --bg: #f8fafc;
            --white: #ffffff;
            --text-main: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --accent: #e0e7ff;
        }

        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text-main); margin: 0; line-height: 1.5; }

        /* HEADER & BRANDING */
        header { background: var(--white); border-bottom: 1px solid var(--border); padding: 1rem 5%; display: flex; align-items: center; justify-content: space-between; }
        .branding { display: flex; align-items: center; gap: 15px; }
        .logoimg { height: 50px; border-radius: 8px; }
        .branding h1 { margin: 0; font-size: 1.4rem; color: var(--primary); font-weight: 800; }
        .branding small { color: var(--text-light); display: block; font-size: 0.85rem; }

        /* NAVIGATION */
        nav { background: var(--primary); padding: 0 5%; display: flex; gap: 10px; }
        nav a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 14px 20px; font-size: 0.9rem; font-weight: 600; transition: 0.3s; border-bottom: 3px solid transparent; }
        nav a:hover { color: white; background: rgba(255,255,255,0.1); }
        nav a.active { color: white; border-bottom: 3px solid white; background: rgba(255,255,255,0.15); }

        /* CONTAINER */
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        
        .student-strip { background: var(--accent); padding: 15px 25px; border-radius: 12px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; color: var(--primary-dark); font-weight: 700; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }

        /* NOTICES AREA */
        .section-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; color: var(--text-main); display: flex; align-items: center; gap: 10px; }
        
        .notice-card { background: var(--white); border-radius: 12px; border: 1px solid var(--border); padding: 20px; margin-bottom: 15px; transition: transform 0.2s, box-shadow 0.2s; display: flex; gap: 20px; align-items: flex-start; }
        .notice-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
        
        .date-badge { background: #f1f5f9; padding: 10px; border-radius: 8px; text-align: center; min-width: 80px; }
        .date-badge .day { display: block; font-size: 1.2rem; font-weight: 800; color: var(--primary); }
        .date-badge .month { font-size: 0.7rem; text-transform: uppercase; font-weight: 700; color: var(--text-light); }

        .notice-content h4 { margin: 0 0 5px 0; color: var(--primary-dark); font-size: 1rem; }
        .notice-content p { margin: 0; font-size: 0.9rem; color: var(--text-light); line-height: 1.6; }
        .notice-link { color: var(--primary); font-weight: 600; text-decoration: none; display: inline-block; margin-top: 10px; }
        .notice-link:hover { text-decoration: underline; }

        /* CHATBOT */
        #chat-trigger { position: fixed; bottom: 30px; right: 30px; background: var(--primary); color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 10px 25px rgba(79, 70, 229, 0.4); z-index: 100; font-size: 1.5rem; }
        #chat-window { position: fixed; bottom: 100px; right: 30px; width: 340px; height: 480px; background: white; border-radius: 20px; display: none; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border: 1px solid var(--border); z-index: 101; overflow: hidden; }
        .chat-header { background: var(--primary); color: white; padding: 15px; font-weight: bold; display: flex; justify-content: space-between; }
        #chat-body { flex: 1; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; }
        .msg { padding: 10px 14px; border-radius: 12px; font-size: 0.85rem; max-width: 80%; }
        .msg.bot { background: #f1f5f9; align-self: flex-start; }
        .msg.user { background: var(--primary); color: white; align-self: flex-end; }

        footer { text-align: center; padding: 40px; color: var(--text-light); font-size: 0.85rem; }
    </style>
</head>
<body>

<header>
    <div class="branding">
        <img src="../Images/logo.jpg" class="logoimg" alt="Logo">
        <div>
            <h1>Student Support Agent</h1>
            <small>Infinite support for infinite possibilities.</small>
        </div>
    </div>
</header>

<nav>
    <a href="#" class="active">Home</a>
    <a href="personal_information.php">Information Update</a>
    <a href="#">Fees</a>
    <a href="teaching_timetable.php">Timetables</a>
    <a href="regisration.php">Course Registration</a>
    <a href="../logout.php">Sign Out</a>
</nav>

<div class="container">
    <div class="student-strip">
        <span>Welcome back, <?php echo htmlspecialchars($fname); ?></span>
        <span><?php echo htmlspecialchars($student['reg_number']); ?> | Thika Main Campus</span>
    </div>

    <div class="section-title">📢 Latest Notices & Events</div>

    <div class="notice-card">
        <div class="date-badge">
            <span class="day">05</span>
            <span class="month">May 21</span>
        </div>
        <div class="notice-content">
            <h4>Hostel Booking Process</h4>
            <p>To ensure a smooth accommodation experience, please follow the updated booking guidelines for the upcoming semester. You can download the full video tutorial below.</p>
            <a href="https://shorturl.at/86iS3" class="notice-link" target="_blank">Download Booking Tutorial →</a>
        </div>
    </div>

    <div class="notice-card">
        <div class="date-badge">
            <span class="day">02</span>
            <span class="month">Feb 26</span>
        </div>
        <div class="notice-content">
            <h4>Jan-Apr Semester Registration</h4>
            <p>Ensure all units are confirmed before the deadline to avoid late registration penalties. Contact your department head for unit code clarifications.</p>
            <a href="regisration.php" class="notice-link">Go to Registration →</a>
        </div>
    </div>
</div>

<div id="chat-trigger" onclick="toggleChat()">💬</div>
<div id="chat-window">
    <div class="chat-header">
        <span>Support Assistant</span>
        <span style="cursor:pointer" onclick="toggleChat()">×</span>
    </div>
    <div id="chat-body">
        <div class="msg bot">Welcome back, <?php echo htmlspecialchars($fname); ?>! How can I help you today?</div>
    </div>
    <div style="padding: 15px; border-top: 1px solid var(--border); display: flex; gap: 8px;">
        <input type="text" id="chat-in" placeholder="Ask about hostels or fees..." style="flex:1; border: 1px solid #ddd; padding: 8px; border-radius: 20px; outline:none;">
        <button onclick="sendMsg()" style="background: var(--primary); border: none; color: white; border-radius: 50%; width: 35px; height: 35px; cursor: pointer;">></button>
    </div>
</div>

<footer>
    &copy; 2026 Mount Kenya University | Portal Assistant AI
</footer>

<script>
    function toggleChat() {
        const win = document.getElementById('chat-window');
        win.style.display = (win.style.display === 'flex') ? 'none' : 'flex';
    }

    function sendMsg() {
        const input = document.getElementById('chat-in');
        const body = document.getElementById('chat-body');
        if(!input.value.trim()) return;

        const uMsg = document.createElement('div');
        uMsg.className = 'msg user';
        uMsg.textContent = input.value;
        body.appendChild(uMsg);

        const text = input.value.toLowerCase();
        input.value = '';

        setTimeout(() => {
            const bMsg = document.createElement('div');
            bMsg.className = 'msg bot';
            if(text.includes('hostel')) {
                bMsg.textContent = "The hostel booking tutorial link is available on your dashboard notice card!";
            } else {
                bMsg.textContent = "I'm here to help with navigation. Check the 'Timetables' or 'Fees' tab for more info.";
            }
            body.appendChild(bMsg);
            body.scrollTop = body.scrollHeight;
        }, 600);
    }
    document.getElementById('chat-in').addEventListener('keypress', (e) => { if(e.key === 'Enter') sendMsg(); });
</script>

</body>
</html>