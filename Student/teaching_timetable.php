<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mount Kenya University : Students Online Portal</title>
    <style>
        body { font-family: Verdana, sans-serif; font-size: 12px; background-color: #f2f2f2; margin: 0; }
        #content { width: 1000px; margin: 10px auto; background: #fff; border: 1px solid #aaa;border-radius: 20px;  }
        
        #top_info { padding: 15px; border-bottom: 3px solid #0056b3; }
        .logoimg { max-height: 70px; }
        h1 { margin: 0; font-size: 22px; }
        h1 a { color: #0056b3; text-decoration: none; }

        #navigation { background: #0056b3; margin-top:-20px;}
        .ult-section { list-style: none; margin: 0; padding: 0; display: flex; }
        .primary { background: #004080; }
        .secondary { background: #e9e9e9; border-bottom: 1px solid #ccc; }
        
        .ult-section li a { display: block; padding: 10px 15px; text-decoration: none; font-weight: bold; }
        .primary li a { color: #fff; border-right: 1px solid #003366; }
        .secondary li a { color: #333; font-size: 11px; border-right: 1px solid #ccc; }
        .active { background: #fff !important; }
        .active a { color: #0056b3 !important; }

        .student-bar { background: #f9f9f9; padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold; color: #444; text-align: center; }

        .left_articles { padding: 20px; }
        .timetable-search { background: #f9f9f9; border: 1px solid #ccc; padding: 15px; text-align: center; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ffffff; padding: 4px 6px; font-size: 10px; }
        .colhead { background: #eeeeee; font-weight: bold; text-align: center; color: #333; }
        .notice-title { background: #f2f2f2; font-weight: bold; padding: 10px; text-align: center; color: #0056b3; font-size: 13px; }
        .day-header { background: #f8f8f8; font-weight: bold; padding: 8px; text-align: left; border-left: 5px solid #0056b3; }
        
        .inputbutton { background: #0056b3; color: white; border: none; padding: 4px 10px; cursor: pointer; font-size: 11px; font-weight: bold; }
        input[type="text"] { border: 1px solid #ccc; padding: 3px; }
        
        #footer { text-align: right; padding: 15px; font-size: 11px; border-top: 1px solid #ccc; background: #f8f8f8; color: #666; }
        #footer a { color: #0056b3; text-decoration: none; }

        /* --- CHATBOT STYLES --- */
        #chat-widget { position: fixed; bottom: 20px; right: 20px; z-index: 2000; }
        #chat-button { background: #0056b3; color: white; border: none; padding: 12px 20px; border-radius: 50px; cursor: pointer; font-weight: bold; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        
        #chat-container {
            position: absolute; bottom: 60px; right: 0;
            width: 300px; height: 380px; background: white;
            border: 1px solid #0056b3; border-radius: 8px;
            display: none; flex-direction: column;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        #chat-header { background: #0056b3; color: white; padding: 10px; font-weight: bold; display: flex; justify-content: space-between; border-top-left-radius: 7px; border-top-right-radius: 7px; }
        #chat-messages { flex: 1; padding: 10px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; background: #fdfdfd; }
        .bubble { padding: 7px 10px; border-radius: 12px; max-width: 85%; font-size: 11px; word-wrap: break-word; }
        .bot { background: #e3f2fd; align-self: flex-start; color: #222; border: 1px solid #bbdefb; }
        .user { background: #0056b3; align-self: flex-end; color: white; }
        #chat-input-area { border-top: 1px solid #ddd; padding: 8px; display: flex; gap: 4px; }
        #chat-input-area input { flex: 1; padding: 5px; border: 1px solid #ccc; border-radius: 3px; font-size: 11px; }
    </style>
</head>
<body>

<div id="content">
    <div id="top_info">
        <table border="0" width="100%">
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
            <li><a href="Home.php">Home</a></li>
            <li><a href="personal_information.php">Information Update</a></li>
            <li><a href="#">Fees</a></li>
            <li class="active"><a href="#">Timetables</a></li>
            <li><a href="regisration.php">Course Registration</a></li>
            <li><a href="#">Results</a></li>
            <li><a href="#">My Requests</a></li>
            <li><a href="#">Sign Out</a></li>
        </ul>
        <ul class="ult-section secondary">
            <li class="active"><a href="#">Teaching Timetable</a></li>
            <li><a href="#">My Timetable</a></li>
        </ul>
    </div>

    <div class="left_articles">
        <div class="student-bar">
            BIT/2024/43255 | CHEPKONGA CHEPCHIENG NOAH | Thika (Day) , Main Campus (Thika)
        </div>

        <div class="timetable-search">
            Course Code: <input type="text" size="8"> or <input type="text" size="8"> or <input type="text" size="8">
            <input type="submit" class="inputbutton" value="Show Timetable for Selected Courses">
            <p style="font-size: 10px; margin-top: 8px;">Type first two letters (e.g. BIT) or leave blank to <input type="submit" class="inputbutton" value="show all courses" style="padding: 2px 5px; font-size: 9px;"></p>
        </div>

        <div style="text-align:center; margin-bottom:15px;">
            <b style="font-size: 13px;">BIT Bachelor of Science in Information Technology</b><br>
            <span style="font-size: 11px;">2025/2026 (Jan/Apr) : Jan 5th 2026 - Apr 30th 2026</span>
        </div>

        <table border="1">
            <tr><td class="notice-title" colspan="9">WEEKLY TEACHING SCHEDULE</td></tr>
            <tr class="day-header"><td colspan="9">Saturday</td></tr>
            <tr class="colhead">
                <td>#</td><td>Code</td><td>Course Title</td><td>From</td><td>To</td><td>Venue</td><td>Group</td><td>Lecturer</td><td>Exam Date</td>
            </tr>
            <tr><td>1</td><td>ABCU001</td><td>Research Methodology</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Dr. GIKANDI</td><td>-</td></tr>
            <tr><td>2</td><td>BAF1101</td><td>Financial Accounting I</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. KITHINJI</td><td>-</td></tr>
            <tr><td>3</td><td>BBM1101</td><td>Introduction to business studies</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mrs. MWIRIGI</td><td>-</td></tr>
            <tr><td>4</td><td>BBM1201</td><td>Principles of Management</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Dr. MURIUKI</td><td>-</td></tr>
            <tr><td>5</td><td>BBM1202</td><td>Principles of Marketing</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Ms. NYOKABI</td><td>-</td></tr>
            <tr><td>6</td><td>BBM2103</td><td>Organization Behavior</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Ms. NDEGE</td><td>-</td></tr>
            <tr><td>7</td><td>BBM3107</td><td>Human Resource Management</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Dr. SHITIABAI</td><td>-</td></tr>
            <tr><td>8</td><td>BEG2112</td><td>Digital Electronics and Devices</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. MULEI</td><td>-</td></tr>
            <tr><td>9</td><td>BIT1101</td><td>Computer Architecture</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. NYAGA</td><td>-</td></tr>
            <tr><td>10</td><td>BIT1106</td><td>Introduction to Computer Application Packages</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Ms. KIARIE</td><td>-</td></tr>
            <tr><td>11</td><td>BIT1112</td><td>Introduction to Computer Systems</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Ms. KIARIE</td><td>-</td></tr>
            <tr><td>12</td><td>BIT1201</td><td>Database systems</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mrs. MWINJI</td><td>-</td></tr>
            <tr><td>13</td><td>BIT2102</td><td>Fundamentals of Internet</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mrs. NYANSIABOKA</td><td>-</td></tr>
            <tr><td>14</td><td>BIT2201</td><td>Computer Programming Methodology</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. MUIRURI</td><td>-</td></tr>
            <tr><td>15</td><td>BIT2203</td><td>Data Structure and Algorithms</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Dr. KABURU</td><td>-</td></tr>
            <tr><td>16</td><td>BIT3101</td><td>Software Engineering</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mrs. MWINJI</td><td>-</td></tr>
            <tr><td>17</td><td>BIT3102</td><td>Event Driven Programming</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. WAMBUI</td><td>-</td></tr>
            <tr><td>18</td><td>BIT3106</td><td>Object Oriented Programming</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mrs. NYANSIABOKA</td><td>-</td></tr>
            <tr><td>19</td><td>BIT3107</td><td>Database systems II</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. NYAGA</td><td>-</td></tr>
            <tr><td>20</td><td>BIT3205</td><td>Business systems simulation and modeling</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. NYAGA</td><td>-</td></tr>
            <tr><td>21</td><td>BIT3206</td><td>ICT project management</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Ms. KIARIE</td><td>-</td></tr>
            <tr><td>22</td><td>BIT3209</td><td>Design and analysis of algorithm</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. KODHEK</td><td>-</td></tr>
            <tr><td>23</td><td>BIT4102</td><td>Computer Graphics</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. OKELLO</td><td>-</td></tr>
            <tr><td>24</td><td>BIT4104</td><td>Security and Cryptography</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. MUIRURI</td><td>-</td></tr>
            <tr><td>25</td><td>BIT4108</td><td>Information Systems Audit</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mrs. MWINJI</td><td>-</td></tr>
            <tr><td>26</td><td>BIT4202</td><td>Artificial Intelligence</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Ms. MWAI</td><td>-</td></tr>
            <tr><td>27</td><td>BIT4203</td><td>Distributed Multimedia Systems</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mrs. NDINDA</td><td>-</td></tr>
            <tr><td>28</td><td>BIT4204</td><td>E - Commerce</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. NYAGA</td><td>-</td></tr>
            <tr><td>29</td><td>BIT4205</td><td>Network Programming</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. KODHEK</td><td>-</td></tr>
            <tr><td>30</td><td>BIT4209</td><td>Distributed Systems</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mrs. NDINDA</td><td>-</td></tr>
            <tr><td>31</td><td>BMA1104</td><td>Probability and Statistics I</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. KABUE</td><td>-</td></tr>
            <tr><td>32</td><td>BMA1202</td><td>Discrete Mathematics</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. CHEGE</td><td>-</td></tr>
            <tr><td>33</td><td>BMA3102</td><td>Business statistics II</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. CHEGE</td><td>-</td></tr>
            <tr><td>34</td><td>BMA3201</td><td>Operation research I</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. CHEGE</td><td>-</td></tr>
            <tr><td>35</td><td>BPY1101</td><td>Basic Electricity and Optics</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. MUINUKI</td><td>-</td></tr>
            <tr><td>36</td><td>BUCU007</td><td>Communication Skills and Academic Writing</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Ms. NABEA</td><td>-</td></tr>
            <tr><td>37</td><td>BUCU009</td><td>Climate Change and Development</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Ms. WONGE</td><td>-</td></tr>
            <tr><td>38</td><td>BUCU011</td><td>Health Literacy</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. OTIENO</td><td>-</td></tr>
            <tr><td>39</td><td>BIT2117</td><td>Accounting Information System</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mrs. NDINDA</td><td>-</td></tr>
            <tr><td>40</td><td>BUCU008</td><td>Fundamentals of Digital and Information Literacy Skills</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mr. NYAGA</td><td>-</td></tr>
            <tr><td>41</td><td>BIT3105</td><td>Management Information Systems</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mrs. NDINDA</td><td>-</td></tr>
            <tr><td>42</td><td>BIT3224</td><td>Computing Projects Development Approaches</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Mrs. NYANSIABOKA</td><td>-</td></tr>
            <tr><td>43</td><td>BUCU010</td><td>Entrepreneurial Mindset and Financial Literacy</td><td>07:00</td><td>08:00</td><td>1</td><td>Class I</td><td>Dr. MURIUKI</td><td>-</td></tr>
        </table>
    </div>

    <div id="footer">
        <p>&copy; 2026 <a href="#">Mount Kenya University</a>. Hosted by <a href="#">Fountain ICT Services</a></p>
    </div>
</div>

<div id="chat-widget">
    <button id="chat-button" onclick="toggleChat()">💬 Chat with Assistant</button>
    
    <div id="chat-container">
        <div id="chat-header">
            <span>Student Help Desk</span>
            <span style="cursor:pointer" onclick="toggleChat()">×</span>
        </div>
        <div id="chat-messages">
            <div class="bubble bot">Hello Noah! I am your portal assistant. How can I help you with your 43 units today?</div>
        </div>
        <div id="chat-input-area">
            <input type="text" id="chatInput" placeholder="Ask about units or exams..." onkeypress="if(event.key==='Enter') sendChat()">
            <button class="inputbutton" onclick="sendChat()">Send</button>
        </div>
    </div>
</div>

<script>
    function toggleChat() {
        const chat = document.getElementById('chat-container');
        // Toggle between flex and none
        if (chat.style.display === 'flex') {
            chat.style.display = 'none';
        } else {
            chat.style.display = 'flex';
        }
    }

    function sendChat() {
        const input = document.getElementById('chatInput');
        const box = document.getElementById('chat-messages');
        const val = input.value.trim();
        
        if(!val) return;
        
        // Add User Bubble
        const userMsg = document.createElement('div');
        userMsg.className = 'bubble user';
        userMsg.textContent = val;
        box.appendChild(userMsg);
        
        const text = val.toLowerCase();
        input.value = "";

        // Bot Logic
        setTimeout(() => {
            const botMsg = document.createElement('div');
            botMsg.className = 'bubble bot';
            
            if(text.includes("unit") || text.includes("how many")) {
                botMsg.textContent = "You are currently registered for 43 course units in this semester.";
            } else if(text.includes("saturday")) {
                botMsg.textContent = "All your 43 units are currently scheduled for Saturday sessions starting from 07:00.";
            } else {
                botMsg.textContent = "For more details on specific units like BIT4209, please refer to the timetable rows.";
            }
            
            box.appendChild(botMsg);
            box.scrollTop = box.scrollHeight;
        }, 500);
        
        box.scrollTop = box.scrollHeight;
    }
</script>

</body>
</html>