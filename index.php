<!DOCTYPE html>
<html lang="en">

<head>
    <title>Practice</title>
    <style>
        .error {
            color: red
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .form-container {
            width: 400px;
            margin: 50px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .input-container {
            margin-bottom: 20px;
        }

        .backend-output {
            text-align: center;
            margin-top: 250px;
            font-size: 26px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            margin-bottom: 5px;
            font-size: 16px;
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

    # Error output var
    $ccnError = "";
    $cvcError = "";
    # Card type output var like Visa, Mastercard etc.
    $cardType = "";
    # Card number value
    $ccn = "";
    # CVC/CVV number
    $cvc = "";

    # Check if something posted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $ccnPost = $_POST["ccn"];
        $cvcPost = $_POST["cvc"];
        $ccnClean = cleanInputVar($ccnPost);
        $cvcClean = cleanInputVar($cvcPost);

        # Card Validation
        if (empty($ccnPost)) {
            $ccnError = "No card number found";
        } else if ($ccnClean == "") {
            $ccnError = "Invalid card number";
        } else if (checkCardSum($ccnClean)) {
            $ccnError = "Invalid card checksum";
        } else if (checkCardType($ccnClean) == "") {
            $ccnError = "Invalid card number";
        } else {
            $cardType = checkCardType($ccnClean);
            $ccn = $ccnClean;
        }

        # CVC/CVV validation
        if (empty($cvcPost)) {
            $cvcError = "No CVC/CVV found";
        } else if ($cvcClean == "") {
            $cvcError = "Invalid CVC/CVV";
        } else if (!preg_match("/^[0-9]{3}$/", $cvcClean)) {
            $cvcError = "Invalid CVC/CVV";
        } else {
            $cvc = $cvcClean;
        }
    }

    # Validate input string from xss and make it only contain number
    function cleanInputVar($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = preg_replace("/\D/", "", $data);
        return $data;
    }

    # Check final sum of card number (Luhn Algorithm)
    function checkCardSum($data)
    {
        $sum = 0;
        $dataLen = strlen($data);
        $parity = $dataLen % 2;
        for ($i = 0; $i < $dataLen - 1; $i++) {
            $digit = $data[$i];
            if ($i % 2 == $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }
        return $sum % 10 == 0;
    }

    # Define and check card type
    function checkCardType($data)
    {
        $pattrens = [
            "Visa" => "/^4[0-9]{12}(?:[0-9]{3})?$/",
            "MasterCard" => "/^(?:5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$/",
            "Maestro" => "/^5018|5020|5038|5893|6304|6759|676[1-3][0-9]{8,15}$/",
            "Daron` Credit" => "/^14|81|99[0-9]{14}/",
        ];

        $cardName = "";

        foreach ($pattrens as $name => $pattern) {
            if (preg_match($pattern, $data)) {
                $cardName = $name;
            }
        }
        return $cardName;
    }
    ?>


    <div class="form-container">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="input-container">
                <label for="ccn">Card number</label>
                <span class="error">*</span>
                <input type="text" name="ccn" autocomplete="cc-number"
                    placeholder="0123 4567 8901 2345" value="<?php echo $ccn ?>">
                <span> <?php echo $cardType; ?></span>
                <span class="error"><?php echo $ccnError ?></span>
            </div>
            <div class="input-container">
                <label for="cvc">CVV/CVC</label>
                <span class="error">*</span>
                <input type="text" name="cvc" placeholder="123" value="<?php echo $cvc ?>">
                <span class="error"><?php echo $cvcError ?></span>
            </div>
            <div class="input-container">
                <label for="expDate">Expiration date</label>
                <span class="error">*</span>
                <input type="text" name="expDate" placeholder="MM/YY" value="<?php echo $expDate ?>">
            </div>
            <input type="submit" value="Send">
        </form>
    </div>

    <div class="backend-output">
        <p> ccn: <?php echo $ccn ?></p>
    </div>
</body>

</html>