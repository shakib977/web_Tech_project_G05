<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP Form Validation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 50px;
        }
        .error {
            color: red;
            font-size: 0.9em;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        input {
            margin: 8px 0;
            padding: 8px;
            width: 320px;
        }
        label {
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h2>Registration Form - PHP Validation</h2>

    <?php
    // variables
    $name = $age = $email = $password = $phone = "";
    $nameErr = $ageErr = $emailErr = $passwordErr = $phoneErr = "";
    $success = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // name
        if (empty($_POST["name"])) {
            $nameErr = "Name is required";
        } else {
            $name = test_input($_POST["name"]);
            if (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
                $nameErr = "Only letters and white space allowed";
            }
        }

        // age
        if (empty($_POST["age"])) {
            $ageErr = "Age is required";
        } else {
            $age = test_input($_POST["age"]);
            if (!is_numeric($age)) {
                $ageErr = "Age must be a number";
            } elseif ($age < 10 || $age > 100) {
                $ageErr = "Age must be between 10 and 100";
            }
        }

        // email
        if (empty($_POST["email"])) {
            $emailErr = "Email is required";
        } else {
            $email = test_input($_POST["email"]);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailErr = "Invalid email format (must contain @)";
            }
        }

        // password
        if (empty($_POST["password"])) {
            $passwordErr = "Password is required";
        } else {
            $password = $_POST["password"];
            if (strlen($password) < 8) {
                $passwordErr = "Password must be at least 8 characters";
            }
        }

        // phone
        if (empty($_POST["phone"])) {
            $phoneErr = "Phone number is required";
        } else {
            $phone = test_input($_POST["phone"]);
            if (!preg_match("/^[0-9]{11}$/", $phone)) {
                $phoneErr = "Phone number must be exactly 11 digits";
            }
        }

        if (empty($nameErr) && empty($ageErr) && empty($emailErr) && empty($passwordErr) && empty($phoneErr)) {
            $success = "Form submitted successfully! All fields are valid.";
            // Clear fields after success if desired
            $name = $age = $email = $phone = "";
        }
    }

    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    ?>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label>Name:</label><br>
        <input type="text" name="name" value="<?php echo $name; ?>" required>
        <span class="error">* <?php echo $nameErr; ?></span><br><br>

        <label>Age:</label><br>
        <input type="number" name="age" value="<?php echo $age; ?>" min="10" max="100" required>
        <span class="error">* <?php echo $ageErr; ?></span><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?php echo $email; ?>" required>
        <span class="error">* <?php echo $emailErr; ?></span><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required>
        <span class="error">* <?php echo $passwordErr; ?></span><br><br>

        <label>Phone Number (11 digits):</label><br>
        <input type="text" name="phone" maxlength="11" value="<?php echo $phone; ?>" required>
        <span class="error">* <?php echo $phoneErr; ?></span><br><br>

        <input type="submit" value="Submit" style="padding: 12px 25px; font-size: 16px; width: auto; cursor: pointer;">
    </form>

    <?php if ($success) echo "<p class='success'>$success</p>"; ?>

<!-- Code injected by live-server -->
<script>
	// <![CDATA[  <-- For SVG support
	if ('WebSocket' in window) {
		(function () {
			function refreshCSS() {
				var sheets = [].slice.call(document.getElementsByTagName("link"));
				var head = document.getElementsByTagName("head")[0];
				for (var i = 0; i < sheets.length; ++i) {
					var elem = sheets[i];
					var parent = elem.parentElement || head;
					parent.removeChild(elem);
					var rel = elem.rel;
					if (elem.href && typeof rel != "string" || rel.length == 0 || rel.toLowerCase() == "stylesheet") {
						var url = elem.href.replace(/(&|\?)_cacheOverride=\d+/, '');
						elem.href = url + (url.indexOf('?') >= 0 ? '&' : '?') + '_cacheOverride=' + (new Date().valueOf());
					}
					parent.appendChild(elem);
				}
			}
			var protocol = window.location.protocol === 'http:' ? 'ws://' : 'wss://';
			var address = protocol + window.location.host + window.location.pathname + '/ws';
			var socket = new WebSocket(address);
			socket.onmessage = function (msg) {
				if (msg.data == 'reload') window.location.reload();
				else if (msg.data == 'refreshcss') refreshCSS();
			};
			if (sessionStorage && !sessionStorage.getItem('IsThisFirstTime_Log_From_LiveServer')) {
				console.log('Live reload enabled.');
				sessionStorage.setItem('IsThisFirstTime_Log_From_LiveServer', true);
			}
		})();
	}
	else {
		console.error('Upgrade your browser. This Browser is NOT supported WebSocket for Live-Reloading.');
	}
	// ]]>
</script>
</body>
</html>