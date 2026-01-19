<?php
session_start();

/* SECURITY CHECK – student must be logged in */
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

$sql = "SELECT full_name, reg_number, email FROM users WHERE id = '$user_id' LIMIT 1";
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
    <title>Mount Kenya University : Information Update</title>
    <style>
        /* Base Portal Styles */
        body { font-family: Verdana, sans-serif; font-size: 12px; background-color: #f2f2f2; margin: 0; }
        #content { width: 1000px; margin: 10px auto; background: #fff; border: 1px solid #aaa; border-radius: 20px;  }
        
        /* Header & Logo */
        #top_info { padding: 15px; border-bottom: 3px solid #0056b3; }
        .logoimg { max-height: 70px; }
        h1 { margin: 0; font-size: 22px; }
        h1 a { color: #0056b3; text-decoration: none; }

        /* Navigation */
        #navigation { background: #0062b3; margin-top:-36px;}
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

        /* Form & Table Styles */
        .left_articles { padding: 20px; }
        fieldset { border: 1px solid #0056b3; border-radius: 4px; padding: 15px; margin-bottom: 25px; }
        legend { color: #0056b3; font-weight: bold; padding: 0 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        td { border: 1px solid #ffffff; padding: 6px; font-size: 11px; }
        .dentry_label { background: #f2f2f2; font-weight: bold; width: 150px; }
        .colhead { background: #eee; font-weight: bold; text-align: center; }
        
        input[type="text"], select { width: 95%; padding: 4px; border: 1px solid #ccc; }
        .btn-submit { background: #0056b3; color: white; padding: 8px 15px; border: none; cursor: pointer; font-weight: bold; margin: 10px 0; }
        .required { color: red; }
        .note-box { background: #ffffcc; padding: 10px; border: 1px solid #e6db55; font-size: 10px; margin-top: 10px; }

        #footer { text-align: right; padding: 15px; font-size: 11px; border-top: 1px solid #ccc; background: #f8f8f8; }

        /* --- CHATBOT INTEGRATION --- */
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
        <table border="0">
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
            <li class="active"><a href="#">Information Update</a></li>
            <li><a href="#">Fees</a></li>
            <li><a href="teaching_timetable.php">Timetables</a></li>
            <li><a href="regisration.php">Course Registration</a></li>
            <li><a href="#">Results</a></li>
            <li><a href="#">My Requests</a></li>
            <li><a href="#">Sign Out</a></li>
        </ul>
        <ul class="ult-section secondary">
            <li class="active"><a href="#">Personal Information</a></li>
        </ul>
    </div>

    <div class="left_articles">
        <div class="student-bar">
            <?php echo htmlspecialchars($student['reg_number']); ?> | <?php echo htmlspecialchars($student['full_name']); ?> | Thika (Day) , Main Campus (Thika)
        </div>

        <fieldset>
            <legend>Personal Information Update</legend>
            <form action="#" method="post">
                <table>
                    <tr>
                        <td class="dentry_label">Current :</td>
                        <td><?php echo htmlspecialchars($fname); ?></td>
                        <td><?php echo htmlspecialchars($mname); ?></td>
                        <td><?php echo htmlspecialchars($lname); ?></td>
                    </tr>
                    <tr>
                        <td class="dentry_label">Change : <span class="required">*</span></td>
                        <td align="center"><?php echo htmlspecialchars($fname); ?></td>
                        <td align="center"><?php echo htmlspecialchars($mname); ?></td>
                        <td align="center"><?php echo htmlspecialchars($lname); ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" align="center" style="background:#f9f9f9;"><i>Preferred Order of Name as it appears in other Certificates</i></td>
                    </tr>
                    <tr>
                        <td class="dentry_label">Address : <span class="required">*</span></td>
                        <td colspan="3">119</td>
                    </tr>
                    <tr>
                        <td class="dentry_label">Post Code : <span class="required">*</span></td>
                        <td>30401</td>
                        <td class="dentry_label">City / Town : <span class="required">*</span></td>
                        <td>Kabartonjo</td>
                    </tr>
                    <tr>
                        <td class="dentry_label">Home County : <span class="required">*</span></td>
                        <td>Baringo</td>
                        <td class="dentry_label">Sub-County :</td>
                        <td>Baringo North</td>
                    </tr>
                    <tr>
                        <td class="dentry_label">Main Mobile : <span class="required">*</span></td>
                        <td>0759768770</td>
                        <td class="dentry_label">Alt. Mobile :</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td class="dentry_label">Online Email : <span class="required">*</span></td>
                        <td colspan="3"><?php echo htmlspecialchars($student['email']); ?></td>
                    </tr>
                </table>
                <div class="note-box">
                    <b>Note:</b> Successfully saved Personal Details can ONLY be changed by the admissions office.
                </div>
            </form>
        </fieldset>

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
                if(val.includes("graduation")) {
                    botMsg.textContent = "To apply for graduation, go to the bottom section 'Graduation Application', select your ceremony and gown collection center, then save.";
                } else if(val.includes("email")) {
                    botMsg.textContent = "You can reset your official MKU email password in the second section of this page. You will receive a code via SMS or your personal email.";
                } else {
                    botMsg.textContent = "I can guide you through the Information Update form. Is there a specific section you're stuck on?";
                }
                body.appendChild(botMsg);
                body.scrollTop = body.scrollHeight;
            }, 600);
            input.value = "";
            body.scrollTop = body.scrollHeight;
        }
    }
    document.getElementById('chat-input').addEventListener('keypress', function (e) { if (e.key === 'Enter') sendMessage(); });
</script>

</body>
</html>
