<?php
/**
 * Simple parser for env file 
 */
class Parser
{
    /**
     * Parse through env file using absolute path
     * @param string $path
     * @throws \Exception
     * @return string[]
     */
    public static function parseEnv(string $path): array
    {
        # Clear whitespaces
        $envPath = trim($path);

        # Check if file exsits
        if (!file_exists($envPath)) {
            throw new Exception("Env file not found");
        }

        # Open file and write lines in variable
        $env = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        # Read line by line
        foreach ($env as $line) {

            # Comments skip
            if (strpos(trim($line), "#") === 0) {
                continue;
            }

            # Separate name and values
            [$name, $value] = explode("=", $line, 2);

            # Clear whitespaces and quotes
            $name = trim($name);
            $value = trim($value);
            $value = trim($value, "\"");

            # Fill array with name and value
            $envArray[$name] = $value;
        }
        return $envArray;
    }
}
