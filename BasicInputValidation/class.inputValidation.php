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
    public static function validate(string $input, string $regex, string $type): array
    {
        # Clean whitespaces and html symbols
        $cleanInput = trim($input);
        $cleanInput = stripslashes($cleanInput);
        $cleanInput = htmlspecialchars($cleanInput);
        # Validations
        if (empty($cleanInput)) {
            return ["valid" => false, "output" => "", "errorMsg" => "$type is empty"];
        }
        if (!preg_match($regex, $cleanInput)) {
            return ["valid" => false, "output" => "", "errorMsg" => "Invalid $type"];
        }

        return ["valid" => true, "output" =>  $cleanInput, "errorMsg" => ""];
    }
}
