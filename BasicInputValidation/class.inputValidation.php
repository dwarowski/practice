<?php
/**
 * Class to validate input values from html form 
 */
class InputValidation
{
    public static function validate($input, $regex, $type)
    {
        $cleanInput = trim($input);
        $cleanInput = stripslashes($input);
        $cleanInput = htmlspecialchars($input);

        if (empty($cleanInput)) {
            return ["valid" => false, "output" => "", "errorMsg" => "$type is empty"];
        } else if ($cleanInput == "") {
            return ["valid" => false, "output" => "", "errorMsg" => "Invalid $type"];
        } else if (!preg_match($regex, $cleanInput)) {
            return ["valid" => false, "output" => "", "errorMsg" => "Invalid $type"];
        } else {
            return ["valid" => true, "output" =>  $cleanInput, "errorMsg" => ""];
        }
    }
}
