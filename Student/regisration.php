<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mount Kenya University : Course Registration</title>
    <style>
        /* Base Portal Styles */
        body { font-family: Verdana, sans-serif; font-size: 12px; background-color: #f2f2f2; margin: 0; }
        #content { width: 1000px; margin: 10px auto; background: #fff; border: 1px solid #aaa; border-radius: 20px; }
        
        /* Header & Logo */
        #top_info { padding: 15px; border-bottom: 3px solid #0056b3; }
        .logoimg { max-height: 70px; }
        h1 { margin: 0; font-size: 22px; }
        h1 a { color: #0056b3; text-decoration: none; }

        /* Navigation */

        #navigation { background: #0056b3; margin-top:-48px;}
        .ult-section { list-style: none; margin: 0; padding: 0; display: flex; }
        .primary { background: #004080; }
        .secondary { background: #e9e9e9; border-bottom: 1px solid #ccc; }
        
        .ult-section li a { display: block; padding: 10px 15px; text-decoration: none; font-weight: bold; }
        .primary li a { color: #fff; border-right: 1px solid #003366; }
        .secondary li a { color: #333; font-size: 11px; border-right: 1px solid #ccc; }
        .active { background: #fff !important; }
        .active a { color: #0056b3 !important; }

        /* Student Info Bar */
        .student-bar { background: #f9f9f9; padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold; color: #444; }

        /* Tables & Forms */
        .left_articles { padding: 20px; }
        .module-header { background: #eee; padding: 10px; text-align: center; font-weight: bold; border: 1px solid #ccc; margin-top: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid hsl(0, 0%, 100%); padding: 8px; font-size: 11px; }
        .dentry_label { background: #0056b3; color: #fff; font-weight: bold; text-align: center; }
        
        fieldset { border: 1px solid #0056b3; border-radius: 4px; padding: 15px; margin-bottom: 20px; }
        legend { color: #0056b3; font-weight: bold; padding: 0 10px; }

        /* Input Styles */
        input[type="text"] { width: 90%; border: 1px solid #999; padding: 3px; }
        select { width: 95%; padding: 2px; }
        .btn-register { background: #0056b3; color: white; padding: 10px 20px; border: none; cursor: pointer; font-weight: bold; margin-top: 10px; }
        .btn-register:hover { background: #003d80; }

        /* Guide Notes */
        .guide-notes { background: #fffae6; padding: 15px; border: 1px solid #ffe58f; line-height: 1.6; }

        #footer { text-align: right; padding: 10px; font-size: 11px; border-top: 1px solid #ccc; background: #f8f8f8; }

        /* --- CHATBOT ENHANCED STYLES --- */
        #chat-widget { position: fixed; bottom: 20px; right: 20px; z-index: 1000; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        #chat-button { background: #0056b3; color: white; border: none; padding: 12px 24px; border-radius: 50px; cursor: pointer; font-weight: bold; box-shadow: 0 4px 15px rgba(0,0,0,0.3); transition: transform 0.2s; }
        #chat-button:hover { transform: scale(1.05); }
        #chat-window { width: 320px; height: 450px; background: white; border: 1px solid #0056b3; border-radius: 12px; display: none; flex-direction: column; box-shadow: 0 10px 25px rgba(0,0,0,0.2); overflow: hidden; }
        #chat-header { background: #0056b3; color: white; padding: 15px; font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
        #chat-body { flex-grow: 1; padding: 15px; overflow-y: auto; background: #f9f9f9; display: flex; flex-direction: column; gap: 10px; scroll-behavior: smooth; }
        .chat-msg { padding: 10px 14px; border-radius: 18px; max-width: 85%; font-size: 12px; line-height: 1.4; }
        .bot { background: #eef2f7; align-self: flex-start; color: #333; border-bottom-left-radius: 2px; }
        .user { background: #0056b3; align-self: flex-end; color: white; border-bottom-right-radius: 2px; }
        #chat-input-area { border-top: 1px solid #eee; padding: 12px; display: flex; background: white; }
        #chat-input { flex-grow: 1; border: 1px solid #ddd; padding: 8px 12px; border-radius: 20px; outline: none; font-size: 12px; }
        #chat-send { background: #0056b3; color: white; border: none; margin-left: 8px; padding: 5px 15px; border-radius: 20px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<div id="content">
    <div id="top_info">
        <table border="0">
            <tr>
                <td width="80"><img src="../Images/ogo.jpg" class="logoimg" alt="MKU Logo" /></td>
                <td>
                    <h1>Student Support Agent</h1>
                    <small>Infinite support for infinite possibilities.</small>
                </td>
            </tr>
        </table>
    </div>

    <div id="navigation">
        <ul class="ult-section primary">
            <li><a href="Home.php">Home</a></li>
            <li><a href="personal_information.php">Information Update</a></li>
            <li><a href="#">Fees</a></li>
            <li><a href="teaching_timetable.php">Timetables</a></li>
            <li class="active"><a href="#">Course Registration</a></li>
            <li><a href="#">Results</a></li>
            <li><a href="#">Sign Out</a></li>
        </ul>
        <ul class="ult-section secondary">
            <li class="active"><a href="#">Course Registration</a></li>
        </ul>
    </div>

    <div class="left_articles">
        <div class="student-bar">
            BIT/2024/43255 | CHEPKONGA CHEPCHIENG NOAH | Thika (Day) - Main Campus
        </div>

        <div class="module-header">
            BIT Bachelor of Science in Information Technology<br>
            2025/2026 Jan/Apr Semester
        </div>

        <fieldset>
            <legend>Confirmed Courses</legend>
            <table>
                <tr style="background:#f2f2f2; font-weight:bold;">
                    <td>Module</td><td>Unit Code and Title</td><td>Exam Type</td><td>Group</td><td>Status</td><td>Fee</td>
                </tr>
                <tr><td colspan="6" align="center" style="padding: 20px; color: #666;">No Confirmed Courses yet.</td></tr>
            </table>
        </fieldset>

        <form action="#" method="post">
            <table>
                <tr style="background:#eee; font-weight:bold; text-align:center;">
                    <td width="30">#</td><td>Course Code</td><td>Exam Type</td><td>Class/Group</td>
                </tr>
                <script>
                    for(let i=1; i<=8; i++) {
                        document.write(`<tr><td align="center">${i}</td><td><input type="text" name="courseCode${i}"></td><td><select name="examType${i}"><option>First Attempt</option><option>Project</option><option>Retake</option></select></td><td><select name="classCode${i}"><option>Class I</option><option>Class II</option><option>Class III</option></select></td></tr>`);
                    }
                </script>
                <tr><td colspan="4" align="center"><input type="submit" class="btn-register" value="Register Courses"></td></tr>
            </table>
        </form>

        <div class="guide-notes">
            <strong>Guide Notes:</strong>
            <ol>
                <li>Pay the Required Amount for the units you want to register.</li>
                <li>Identify the COURSE CODE from the Class Timetable.</li>
                <li>Enter course code, select exam type and group.</li>
            </ol>
        </div>
    </div>

    <div id="footer"> &copy; 2026 Mount Kenya University </div>
</div>

<div id="chat-widget">
    <button id="chat-button" onclick="toggleChat()">💬 chat with assistant</button>
    <div id="chat-window">
        <div id="chat-header">
            <span>Portal Assistant</span>
            <span style="cursor:pointer" onclick="toggleChat()">×</span>
        </div>
        <div id="chat-body">
            <div class="chat-msg bot">Hello Noah! I can help you with registration. You can ask about "fee balance", "how to register", or "deadlines".</div>
        </div>
        <div id="chat-input-area">
            <input type="text" id="chat-input" placeholder="Ask a question...">
            <button id="chat-send" onclick="sendMessage()">Send</button>
        </div>
    </div>
</div>

<script>
    function toggleChat() {
        const win = document.getElementById('chat-window');
        const btn = document.getElementById('chat-button');
        const isHidden = win.style.display === 'none' || win.style.display === '';
        win.style.display = isHidden ? 'flex' : 'none';
        btn.style.display = isHidden ? 'none' : 'block';
    }

    function sendMessage() {
        const input = document.getElementById('chat-input');
        const body = document.getElementById('chat-body');
        const userText = input.value.trim();

        if (userText !== "") {
            // Append User Message
            appendMessage(userText, 'user');
            input.value = "";

            // Bot Response Logic
            setTimeout(() => {
                let response = "";
                const val = userText.toLowerCase();

                if (val.includes("fee") || val.includes("balance")) {
                    response = "Based on your portal, your current balance is 0. You are cleared to register for units!";
                } else if (val.includes("register") || val.includes("how")) {
                    response = "To register: 1. Check your timetable for Unit Codes. 2. Enter the codes in the boxes (1-8). 3. Choose 'First Attempt' and 'Class I'. 4. Click 'Register Courses'.";
                } else if (val.includes("deadline") || val.includes("when")) {
                    response = "Registration for the Jan/Apr 2026 semester usually closes at the end of the second week of the semester. Please check the 'Downloads' section for the official academic calendar.";
                } else if (val.includes("thank")) {
                    response = "You're welcome, Noah! Happy studying.";
                } else {
                    response = "I'm not sure about that. Try asking about 'unit codes', 'fee balance', or 'registration steps'.";
                }

                appendMessage(response, 'bot');
            }, 800);
        }
    }

    function appendMessage(text, sender) {
        const body = document.getElementById('chat-body');
        const msgDiv = document.createElement('div');
        msgDiv.className = `chat-msg ${sender}`;
        msgDiv.textContent = text;
        body.appendChild(msgDiv);
        body.scrollTop = body.scrollHeight; // Auto-scroll to bottom
    }

    // Enter key support
    document.getElementById('chat-input').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });
</script>

</body>
</html>