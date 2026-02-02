<?php
session_start();

/* SECURITY CHECK */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

/* DB CONNECTION */
$conn = mysqli_connect("localhost", "root", "", "Portal-Asisstant-AI");
if (!$conn) {
    die("Database connection failed");
}

/* FETCH STUDENT DATA */
$user_id = $_SESSION['user_id'];
$sql = "SELECT full_name, reg_number, email, department FROM users WHERE id = '$user_id' LIMIT 1";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) !== 1) {
    die("Student record not found");
}

$student = mysqli_fetch_assoc($result);

/* SPLIT FULL NAME */
$name_parts = explode(" ", $student['full_name']);
$fname = $name_parts[0] ?? '';
$mname = $name_parts[1] ?? '';
$lname = $name_parts[2] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Information Update | Student Support Agent</title>
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
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        
        .student-strip { background: var(--accent); padding: 12px 20px; border-radius: 10px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; color: var(--primary-dark); font-weight: 700; font-size: 0.85rem; }

        /* CARD STYLES */
        .card { background: var(--white); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 30px; border: 1px solid var(--border); }
        .card-title { font-size: 1.2rem; font-weight: 700; margin-bottom: 25px; color: var(--primary); display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--border); padding-bottom: 15px; }

        /* FORM GRID */
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        label { font-size: 0.75rem; font-weight: 700; color: var(--text-light); text-transform: uppercase; }
        .read-only-val { padding: 10px; background: #f1f5f9; border-radius: 6px; font-weight: 600; font-size: 0.9rem; border: 1px solid #e2e8f0; }
        
        input[type="text"] { padding: 10px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.9rem; }
        input[type="text"]:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

        .note-box { background: #fffbeb; border: 1px solid #fef3c7; color: #92400e; padding: 15px; border-radius: 8px; font-size: 0.85rem; margin-top: 25px; display: flex; gap: 10px; align-items: center; }

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
    <a href="Home.php">Home</a>
    <a href="#" class="active">Information Update</a>
    <a href="#">Fees</a>
    <a href="teaching_timetable.php">Timetables</a>
    <a href="regisration.php">Course Registration</a>
    <a href="#">Sign Out</a>
</nav>

<div class="container">
    <div class="student-strip">
        <span><?php echo htmlspecialchars($student['reg_number']); ?> | <?php echo htmlspecialchars($student['full_name']); ?></span>
        <span>Main Campus (Thika) | Day Student</span>
    </div>

    <div class="card">
        <div class="card-title">👤 Personal Information Update</div>
        
        <form action="#" method="post">
            <label style="margin-bottom: 10px; display: block;">Official Names (As per Certificates)</label>
            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <div class="read-only-val"><?php echo htmlspecialchars($fname); ?></div>
                </div>
                <div class="form-group">
                    <label>Middle Name</label>
                    <div class="read-only-val"><?php echo htmlspecialchars($mname); ?></div>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <div class="read-only-val"><?php echo htmlspecialchars($lname); ?></div>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 30px 0;">

            <div class="form-row">
                <div class="form-group">
                    <label>Address <span style="color:red">*</span></label>
                    <input type="text" value="119" placeholder="P.O Box">
                </div>
                <div class="form-group">
                    <label>Post Code <span style="color:red">*</span></label>
                    <input type="text" value="30401">
                </div>
                <div class="form-group">
                    <label>City / Town <span style="color:red">*</span></label>
                    <input type="text" value="Kabartonjo">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Home County</label>
                    <input type="text" value="Baringo">
                </div>
                <div class="form-group">
                    <label>Sub-County</label>
                    <input type="text" value="Baringo North">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Main Mobile <span style="color:red">*</span></label>
                    <input type="text" value="0759768770">
                </div>
                <div class="form-group">
                    <label>Official Email</label>
                    <div class="read-only-val" style="background: #f8fafc; color: var(--primary);"><?php echo htmlspecialchars($student['email']); ?></div>
                </div>
            </div>

            <div class="note-box">
                <span style="font-size: 1.2rem;">⚠️</span>
                <div>
                    <strong>Important Note:</strong> Successfully saved Personal Details can <u>only</u> be modified by the Admissions Office to ensure record integrity.
                </div>
            </div>
            
            <div style="margin-top: 30px; text-align: right;">
                <button type="button" class="btn-submit" style="background: var(--primary); color: white; padding: 12px 30px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">Update Information</button>
            </div>
        </form>
    </div>
</div>

<div id="chat-trigger" onclick="toggleChat()">💬</div>
<div id="chat-window">
    <div class="chat-header">
        <span>Support Assistant</span>
        <span style="cursor:pointer" onclick="toggleChat()">×</span>
    </div>
    <div id="chat-body">
        <div class="msg bot">Hi Noah! Need help updating your address or contact details?</div>
    </div>
    <div style="padding: 15px; border-top: 1px solid var(--border); display: flex; gap: 8px;">
        <input type="text" id="chat-in" placeholder="Ask a question..." style="flex:1; border: 1px solid #ddd; padding: 8px; border-radius: 20px;">
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
            bMsg.textContent = text.includes('name') ? "Names are locked for security. Please visit the Registrar with your ID for name changes." : "I've noted that! Anything else you need help with?";
            body.appendChild(bMsg);
            body.scrollTop = body.scrollHeight;
        }, 700);
    }
</script>

</body>
</html>