<?php
class Parser
{
    public static function parseEnv($path)
    {
        $envPath = trim($path);
        $env = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($env as $line) {
            if (strpos(trim($line), "#") === 0) {
                continue; # Comments skip
            }
            list($name, $value) = explode("=", $line, 2);
            $name = trim($name);
            $value = trim($value);
            $value = trim($value, "\"");
            $envArray[$name] = $value;
        }
        return $envArray;
    }
}
