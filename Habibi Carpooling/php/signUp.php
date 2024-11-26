<!DOCTYPE html>
<html>
    <head>
        <title>Sign Up</title>
        <link rel="stylesheet" href="../css/habibiStylesV4.css">
        <link href="https://fonts.googleapis.com/css2?family=Sour+Gummy:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    </head>

    <body class="blurredBackground">
        <div id="signup">
            
            <!-- sign up from -->
            <form id="signupForm" action="signUp.php" method="POST">
                <fieldset>
                    <legend>Create Your Account!</legend>
                    <br>

                    <input id="text" type="text" name="user" placeholder="Enter your username" required>
                    <br><br>
                

                    <input id="text" type="password" name="password" placeholder="Enter your password"required>
                    <br><br>

                    
                    <input id="text2" type="email" name="email" placeholder="Email" required>
                    <br><br>
                   
                    <input id="text2" type="tel" name="telephone" placeholder="Phone Number" required pattern="[0-9]{10}"
                    title="Please enter a valid phone number with exactly 10 digits">

                    <br><br>

                    <input id="signupButton" type="submit" value="Sign Up">
                </fieldset>
            </form>
        </div>
        <p id = "loginMsg">Already have an account? <a href="login.php">Login here</a>.</p>
    </body>
</html>

<?php
    //check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        //sanitize user input 
        $user = htmlspecialchars($_POST['user']);
        $password = htmlspecialchars($_POST['password']);
        $email = htmlspecialchars($_POST['email']);
        $telephone = htmlspecialchars($_POST['telephone']);


    //db connection details
    $host = 'sql207.infinityfree.com';
    $dbname = 'if0_37721054_profiles';
    $myUsername = 'if0_37721054'; 
    $myPassword = 'XBy6Pc3xIhSzC'; 

        //create a MySQLi connection
        $conn = new mysqli($host, $myUsername, $myPassword, $dbname);

        //check for connection errors
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        //check if username already exists
        $sql_check_username = "SELECT COUNT(*) FROM users WHERE username = ?";
        $stmt_check_username = $conn->prepare($sql_check_username);
        $stmt_check_username->bind_param("s", $user);
        $stmt_check_username->execute();
        $stmt_check_username->bind_result($username_exists);
        $stmt_check_username->fetch();
        $stmt_check_username->close();

        //check if email already exists
        $sql_check_email = "SELECT COUNT(*) FROM users WHERE email = ?";
        $stmt_check_email = $conn->prepare($sql_check_email);
        $stmt_check_email->bind_param("s", $email);
        $stmt_check_email->execute();
        $stmt_check_email->bind_result($email_exists);
        $stmt_check_email->fetch();
        $stmt_check_email->close();

        //check if telephone already exists
        $sql_check_telephone = "SELECT COUNT(*) FROM users WHERE telephone = ?";
        $stmt_check_telephone = $conn->prepare($sql_check_telephone);
        $stmt_check_telephone->bind_param("s", $telephone);
        $stmt_check_telephone->execute();
        $stmt_check_telephone->bind_result($telephone_exists);
        $stmt_check_telephone->fetch();
        $stmt_check_telephone->close();

        //if any of the fields already exist, show an error message
        if ($username_exists > 0) {
            echo '<div class = "errorMsg"> Username already exists. Please choose a different one. </div>';
        } elseif ($email_exists > 0) {
            echo '<div class = "errorMsg"> Email already exists. Please choose a different one. </div>';
        } elseif ($telephone_exists > 0) {
            echo '<div class = "errorMsg"> Telephone already exists. Please choose a different one. </div>';
        }else{
            //SQL query to insert user data
            $sql = "INSERT INTO users (username, password, email, telephone) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            //bind params to the query
            $stmt->bind_param("ssss", $user, $password, $email, $telephone);

            //execute the query
            if ($stmt->execute()) {
                echo "Sign up successful!";
                header("Location: profile.php");
                exit;
            } else {
                echo "Something went wrong. Please try again.";
            }

            //close the statement and connection
            $stmt->close();
            $conn->close();
    }
        
    }
?>