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
            font-size: 26px;
        }

        .form-header {
            font-size: 28px;
            text-align: center;
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
            margin-bottom: 5px;
        }

        input[type="button"] {
            width: 100%;
            padding: 10px;
            background-color: rgb(255, 255, 255);
            border-color: #007bff;
            color: #007bff;
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

    # Imports 
    require_once "BasicInputValidation/class.inputValidation.php";

    # Error output var
    $ccnError = $cvcError = $expDateError = "";

    # Validation vars
    $isCardValid = $isCVCValid = $isDateValid = false;

    # Card number/type, cvc and expiration date vars
    $cardType = $ccn = $cvc = $expDate = "";

    # Check if something posted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        # Card Validation
        [
            "errorMsg"  => $ccnError,
            "output" => $ccn,
            "valid" => $isCardValid
        ] = InputValidation::validate($_POST["ccn"], "/^\d+$/", "Card Number");

        # Check card by Luhn algorithm
        if (!checkCardSum($ccn)) {
            $ccnError = "Invalid card";
            $isCardValid = false;
        }

        # Set card type 
        if ($isCardValid) {
            $cardType = checkCardType($ccn);
        }

        # CVC/CVV validation
        [
            "errorMsg"  => $cvcError,
            "output" => $cvc,
            "valid" => $isCVCValid
        ] = InputValidation::validate($_POST["cvc"], "/^[0-9]{3}$/", "CVC/CVV");

        # Expiration date validation
        [
            "errorMsg" => $expDateError,
            "output" => $expDate,
            "valid"  => $isDateValid
        ] = InputValidation::validate($_POST["expDate"], "/^(0[1-9]|1[0-2])\/?([0-9]{4}|[0-9]{2})$/", "Expiration date");

        # Pretty output for date
        if ($isDateValid) {
            $expMonth = substr($expDate, 0, 2);
            $expDateYear = substr($expDate, -2, 2);
            $expDate =  "$expMonth/$expDateYear";
        }
    }

    /**
     * Check final sum of card number (Luhn Algorithm)
     * @param string $data
     * @return bool
     */
    function checkCardSum(string $data): bool
    {
        $sum = 0;
        $data = strrev($data);
        for ($i = 0; $i < strlen($data); $i++) {
            $digit = $data[$i];
            if ($i % 2 != 0) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }
        return $sum % 10 == 0;
    }

    /**
     * Check card type by regex pattern
     * @param string $data
     * @return string
     */
    function checkCardType(string $data): string
    {
        $pattrens = [
            "Visa" => "/^4[0-9]{12}(?:[0-9]{3})?$/",
            "MasterCard" => "/^(?:5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$/",
            "Maestro" => "/^5018|5020|5038|5893|6304|6759|676[1-3][0-9]{8,15}$/",
            "Daron` Credit" => "/^(14|81|99)[0-9]{14}$/",
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
        <p class="form-header">Donate to our mail service</p>
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
                <span class="error"><?php echo $expDateError ?></span>
            </div>
            <input type="submit" value="Pay">
            <input type="button" value="No, Thanks:(" onclick="location.href='index.php'">
        </form>
    </div>

    <div class="backend-output">
        <p> CCN: <?php if ($isCardValid && $isCVCValid && $isDateValid) echo $ccn ?></p>
        <p> CVC/CVV: <?php if ($isCardValid && $isCVCValid && $isDateValid) echo $cvc ?></p>
        <p> Expiration date: <?php if ($isCardValid && $isCVCValid && $isDateValid) echo $expDate ?></p>
    </div>
</body>

</html>