<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <title>Practice</title>
    <style>
        .error {
            color: red;
            white-space: nowrap;
        }

        .warn {
            color: yellow;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .input-container {
            width: 100%;
            max-width: 400px;
            display: flex;
            gap: 5px;
            flex-direction: row;
        }

        .message-container {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }

        .credentials {
            display: flex;
            flex-direction: row;
            gap: 10px;
            width: 100%;
            margin-bottom: 20px;
        }

        .credentials-container {
            display: flex;
            flex-direction: column;
        }

        .message-form-container {
            width: 1000px;
            margin: 50px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 14px;
            color: #333;
            white-space: nowrap;
        }

        input[type="text"] {
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            margin-bottom: 5px;
            font-size: 16px;
        }

        textarea {
            width: 100%;
            height: 500px;
            resize: vertical;
            min-height: 24px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            margin-bottom: 5px;
            font-size: 16px;
        }


        input[type="file"] {
            margin-bottom: 5px;
        }


        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 5px;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        input[type="submit"]:active {
            background-color: #004494;
        }
    </style>

</head>

<body>
    <?php

    # imports
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require "PHPMailer/src/Exception.php";
    require "PHPMailer/src/PHPMailer.php";
    require "PHPMailer/src/SMTP.php";
    require_once "ENVParser/class.parser.php";
    require_once "BasicInputValidation/class.inputValidation.php";

    # Read env file
    $envArray = Parser::parseEnv(__DIR__ . '/.env');

    # Status vars
    $isNameValid = $isMailVaild = $isMessageValid = $isSubjectValid = $isSend = false;

    # Error vars
    $emailError = $nameError = $messageError = $subjectError = $sendError = "";

    # Values
    $email = $name = $message = $subject = "";

    # Check if form was send
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        # Message validation 
        [
            "valid" => $isMessageValid,
            "output" => $message,
            "errorMsg" => $messageError
        ] = InputValidation::validate($_POST["message"], "/\S+/", "message");

        # Subject validation
        [
            "valid" => $isSubjectValid,
            "output" => $subject,
            "errorMsg" => $subjectError
        ] = InputValidation::validate($_POST["subject"], "/^\S+$/", "subject");

        # Email validation
        [
            "valid" => $isMailVaild,
            "output" => $email,
            "errorMsg" => $emailError
        ] = InputValidation::validate($_POST["email"], "/^\S+@\S+\.\S+$/", "email");

        # Name validation
        [
            "valid" => $isNameValid,
            "output" => $name,
            "errorMsg" => $nameError
        ] = InputValidation::validate($_POST["name"], "/^\S+$/", "name");

        # Email domain validation
        if ($isMailVaild) {
            $emailSplit = explode("@", $email);
            $hostname = $emailSplit[array_key_last($emailSplit)];

            if (!getmxrr($hostname, $mxHosts)) {
                $emailError = "Mail doesn`t exsist";
                $isMailVaild = false;
            }
        }

        # Send message if everything valid
        if ($isMailVaild && $isNameValid && $isMessageValid && $isSubjectValid) {
            $isSend = sendEmail($email, $name, $message, $subject, $_FILES["userfile"]);
            if (!$isSend) {
                $sendError = "Something went wrong";
            }
        }
    }

    /**
     * Send email function
     * @param mixed $emailAddres
     * @param mixed $name
     * @param mixed $message
     * @param mixed $subject
     * @param mixed $attachment
     * @return string containing alert script
     */
    function sendEmail($emailAddres, $name, $message, $subject, $attachment)
    {
        # Define env variables
        global $envArray;

        # Define mailer class
        $mail = new PHPMailer(true);

        # Try to send message 
        try {
            $mail->isSMTP(); # Use SMTP 
            $mail->isHTML(true);
            $mail->CharSet = "UTF-8"; # Set to utf-8 bc of russian symbols
            $mail->SMTPAuth = true; # Auth required

            # Set connections
            $mail->Host = $envArray["Host"];
            $mail->Port = $envArray["Port"];
            $mail->Username = $envArray["Username"];
            $mail->Password = $envArray["Password"];
            $mail->SMTPSecure = $envArray["Security"];

            # Set addresses
            $mail->setFrom("example@sandbox-7625958-221cd3.unigosendbox.com", "example");
            $mail->addAddress($emailAddres, $name);

            # Set message data
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AltBody = $message;

            # Check if file attached 
            if ($attachment["error"][0] == 0) {
                # Attach
                $mail->addAttachment($attachment['tmp_name'][0], $attachment['name'][0]);
            }

            # Trying to send
            $mail->send();
            return true;
        } catch (Exception $e) {
            $err = json_encode($mail->ErrorInfo);
            echo "<script>console.log('$err');</script>";
            return false;
        }
    }
    ?>

    <?php
    if ($isSend) {
        echo "<script>";
        echo "alert('Message Send');";
        echo 'window.location.href = "card-form.php"';
        echo "</script>";
    }
    ?>

    <div class="message-form-container">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">

            <div class="credentials">
                <div class="credentials-container">
                    <div class="input-container">
                        <label for="email">Email to:</label>
                        <input type="text" name="email" value="<?php echo $email ?>" placeholder="email@example.com">
                        <span class="error">*</span>
                    </div>
                    <span class="error"> <?php echo $emailError ?></span>
                </div>

                <div class="credentials-container">
                    <div class="input-container">
                        <label for="name">Name:</label>
                        <input type="text" name="name" value="<?php echo $name ?>" placeholder="Name">
                        <span class="error">*</span>
                    </div>
                    <span class="error"> <?php echo $nameError ?> </span>
                </div>
            </div>

            <div class="input-container">
                <label for="subject">Subject:</label>
                <input type="text" name="subject" value="<?php echo $subject ?>" placeholder="Subject">
                <span class="error">* <?php echo $subjectError ?> </span>
            </div>

            <div class="message-container">
                <label for="message">Message
                    <span class="error">* <?php echo $messageError ?> </span>
                </label>
                <textarea name="message"><?php echo $message ?></textarea>
            </div>

            <input type="file" name="userfile[]" multiple="multiple">
            <input type="submit" name="send" value="Send">
        </form>
        <span class="error"><?php echo $sendError ?></span>
    </div>
</body>

</html>