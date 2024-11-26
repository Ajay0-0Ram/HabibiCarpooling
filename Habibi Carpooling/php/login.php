<?php
    //start the session to handle user login status
    session_start();

    //db connection details
    $host = 'sql207.infinityfree.com';
    $dbname = 'if0_37721054_profiles';
    $myUsername = 'if0_37721054';
    $myPassword = 'XBy6Pc3xIhSzC';

    //create a MySQLi connection
    $conn = new mysqli($host, $myUsername, $myPassword, $dbname);

    //check for connection errors
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    //initialize error message
    $errorMessage = "";

    //handle the form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        //sanitize user input
        $username = htmlspecialchars($_POST['username']);
        $password = htmlspecialchars($_POST['password']);

        //get the database for the user by username
        $sql = "SELECT username, password FROM users WHERE username = ?";

        //prepare the SQL statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind the username parameter to the query
            $stmt->bind_param("s", $username);

            //execute the query
            $stmt->execute();

            //bind result variables
            $stmt->bind_result($db_username, $db_password);

            //check if the user exists and fetch the result
            if ($stmt->fetch()) {
                // Verify the password
                if ($password == $db_password) {
                    //password is correct, start the session and log the user in
                    $_SESSION['username'] = $db_username;

                    //redirect to the profile
                    header('Location: profile.php');
                    exit;
                } else {
                    $errorMessage = '<div class = "errorMsg"> Invalid username or password. </div>';
                }
            } else {
                $errorMessage = '<div class = "errorMsg"> Invalid username or password. </div>';
            }

            //close the statement
            $stmt->close();
        } else {
            $errorMessage = "Error preparing statement: " . $conn->error;
        }
        
    }

    //close the database connection
    $conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../css/habibiStylesV4.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Sour+Gummy:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body class="blurredBackground">
    <!-- display the error message if there is one -->
    <?php if (!empty($errorMessage)): ?>
        <div id="warning" style="color: red;">
            <?php echo $errorMessage; ?>
        </div>
    <?php endif; ?>

    <div id="loginbox">
        <div id="login">
        

            <!-- login Form-->
            <form id="loginForm" action="login.php" method="POST">
                <fieldset>
                    <legend>Enter Your Login Details</legend>

                    <input type ="text" id="text" name="username" placeholder="Username" required><br><br>

                    <input type="password" id="text" name="password" placeholder="Password" required><br><br>

                    <input type="submit" id="loginButton" value="Login">
             </fieldset>
            </form>

            <p>Don't have an account? <a href="signUp.php">Sign up here</a>.</p>
        </div>
    </div>
</body>
</html>