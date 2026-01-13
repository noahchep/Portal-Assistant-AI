<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mount Kenya University : Students Online Portal</title>
    <style>
        /* Base Portal Styles - Keeping original MKU look */
        body { font-family: Verdana, sans-serif; font-size: 12px; background-color: #f2f2f2; margin: 0; }
        #content { width: 1000px; margin: 10px auto; background: #fff; border: 1px solid #aaa; border-radius: 20px; }
        
        /* Header & Logo */
        #top_info { padding: 15px; border-bottom: 3px solid #0056b3; }
        .logoimg { max-height: 70px; }
        h1 { margin: 0; font-size: 22px; }
        h1 a { color: #0056b3; text-decoration: none; }

        /* Navigation */
        #navigation { background: #0056b3; margin-top:-27px; }
        .ult-section { list-style: none; margin: 0; padding: 0; display: flex; }
        .primary { background: #004080; }
        .secondary { background: #e9e9e9; border-bottom: 1px solid #ccc; }
        
        .ult-section li a { display: block; padding: 10px 15px; text-decoration: none; font-weight: bold; }
        .primary li a { color: #fff; border-right: 1px solid #003366; }
        .secondary li a { color: #333; font-size: 11px; border-right: 1px solid #ccc; }
        .active { background: #fff !important; }
        .active a { color: #0056b3 !important; }

        /* Student Info Bar */
        .student-bar { background: #f9f9f9; padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold; color: #444; text-align: center; }

        /* Table Styles for Notices */
        .left_articles { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ffffff; padding: 8px; font-size: 11px; }
        .colhead { background: #eeeeee; font-weight: bold; text-align: center; }
        .notice-title { background: #f2f2f2; font-weight: bold; padding: 10px; text-align: center; }
        
        a { color: #0056b3; text-decoration: none; }
        a:hover { text-decoration: underline; }

        #footer { text-align: right; padding: 10px; font-size: 11px; border-top: 1px solid #ccc; background: #f8f8f8; }

        /* --- CHATBOT INTEGRATION STYLES --- */
        #chat-widget { position: fixed; bottom: 20px; right: 20px; z-index: 1000; font-family: Arial, sans-serif; }
        #chat-button { background: #0056b3; color: white; border: none; padding: 12px 20px; border-radius: 50px; cursor: pointer; font-weight: bold; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        #chat-window { width: 300px; height: 400px; background: white; border: 1px solid #0056b3; border-radius: 10px; display: none; flex-direction: column; box-shadow: 0 5px 15px rgba(0,0,0,0.3); overflow: hidden; }
        #chat-header { background: #0056b3; color: white; padding: 10px; font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
        #chat-body { flex-grow: 1; padding: 10px; overflow-y: auto; background: #fdfdfd; display: flex; flex-direction: column; gap: 8px; }
        .chat-msg { padding: 8px 12px; border-radius: 15px; max-width: 80%; font-size: 11px; }
        .bot { background: #e9e9e9; align-self: flex-start; color: #333; }
        .user { background: #0056b3; align-self: flex-end; color: white; }
        #chat-input-area { border-top: 1px solid #ccc; padding: 10px; display: flex; }
        #chat-input { flex-grow: 1; border: 1px solid #ccc; padding: 5px; border-radius: 3px; outline: none; }
        #chat-send { background: #0056b3; color: white; border: none; margin-left: 5px; padding: 5px 10px; border-radius: 3px; cursor: pointer; }
    </style>
</head>
<body>

<div id="content">
    <div id="top_info">
        <table style="border: 1px solid white; border-collapse: collapse; width: 100%;">
            <tr>
                <td width="80"><img src="../Images/logo.jpg" class="logoimg" alt="MKU Logo" /></td>
                <td>
                    <h1>Student Support Agent</h1>
                    <small>Infinite support for infinite possibilities.</small>
                </td>
            </tr>
        </table>
    </div>

    <div id="navigation">
        <ul class="ult-section primary">
            <li class="active"><a href="Home.php">Home</a></li>
            <li><a href="personal_information.php">Information Update</a></li>
            <li><a href="#">Fees</a></li>
            <li><a href="teaching_timetable.php">Timetables</a></li>
            <li><a href="regisration.php">Course Registration</a></li>
            <li><a href="#">Results</a></li>
            <li><a href="#">My Requests</a></li>
            <li><a href="#">Sign Out</a></li>
        </ul>
        <ul class="ult-section secondary">
            <li class="active"><a href="#">Change Password</a></li>
            
        </ul>
    </div>

    <div class="left_articles">
        <div class="student-bar">
            BIT/2024/43255 | CHEPKONGA CHEPCHIENG NOAH | Thika (Day) , Main Campus (Thika)
        </div>

        <table border="1">
            <tr>
                <td class="notice-title" colspan="6">Current Notices / Events</td>
            </tr>
            <tr class="colhead">
                <td width="30">#</td>
                <td>Subject</td>
                <td>Flag</td>
                <td>Date</td>
                <td>Notice / Event</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td align="right">1.</td>
                <td align="left">Hostel booking process</td>
                <td align="left"></td>
                <td align="left">05-May-2021</td>
                <td align="left">
                    <a href="#">
                        kindly download hostel booking tutorial use the below link <br>
                        https://shorturl.at/86iS3 <br>
                        Tha . . .
                    </a>
                </td>
                <td align="left">1</td>
            </tr>
        </table>
    </div>

    <div id="footer">
        <p>&copy; 2026 Mount Kenya University</p>
    </div>
</div>

<div id="chat-widget">
    <button id="chat-button" onclick="toggleChat()">Chat with Assistant</button>
    <div id="chat-window">
        <div id="chat-header">
            Portal Assistant
            <span style="cursor:pointer" onclick="toggleChat()">×</span>
        </div>
        <div id="chat-body">
            <div class="chat-msg bot">Welcome back, Noah! Check the notices for the hostel booking tutorial. How else can I help you today?</div>
        </div>
        <div id="chat-input-area">
            <input type="text" id="chat-input" placeholder="Ask about fees, courses...">
            <button id="chat-send" onclick="sendMessage()">Send</button>
        </div>
    </div>
</div>

<script>
    function toggleChat() {
        var win = document.getElementById('chat-window');
        var btn = document.getElementById('chat-button');
        if (win.style.display === 'none' || win.style.display === '') {
            win.style.display = 'flex';
            btn.style.display = 'none';
        } else {
            win.style.display = 'none';
            btn.style.display = 'block';
        }
    }

    function sendMessage() {
        var input = document.getElementById('chat-input');
        var body = document.getElementById('chat-body');
        if (input.value.trim() !== "") {
            var userMsg = document.createElement('div');
            userMsg.className = 'chat-msg user';
            userMsg.textContent = input.value;
            body.appendChild(userMsg);
            
            var val = input.value.toLowerCase();
            setTimeout(() => {
                var botMsg = document.createElement('div');
                botMsg.className = 'chat-msg bot';
                if(val.includes("hostel")) {
                    botMsg.textContent = "You can find the hostel booking link in the notices section of this page. The tutorial link is: https://shorturl.at/86iS3";
                } else if(val.includes("result")) {
                    botMsg.textContent = "Your results are available under the 'Results' tab in the top menu.";
                } else {
                    botMsg.textContent = "I can help with navigation. Try asking about 'hostels', 'fees', or 'registration'.";
                }
                body.appendChild(botMsg);
                body.scrollTop = body.scrollHeight;
            }, 600);

            input.value = "";
            body.scrollTop = body.scrollHeight;
        }
    }
    document.getElementById('chat-input').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') sendMessage();
    });
</script>

</body>
</html>