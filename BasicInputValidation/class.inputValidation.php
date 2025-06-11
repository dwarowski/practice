<?php
/**
 * Class to validate input values from html form 
 */
class InputValidation
{
    /**
     * Summary of validate
     * @param string $input
     * @param string $regex
     * @param string $type
     * @return array{errorMsg: string, output: string, valid: bool}
     */
    public static function validate(string $input, string $regex, string $type)
    {
        # Clean whitespaces and html symbols
        $cleanInput = trim($input);
        $cleanInput = stripslashes($input);
        $cleanInput = htmlspecialchars($input);

        # Validations
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
