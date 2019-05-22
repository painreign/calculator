<?php

define('E', exp(1));
define('Pi', pi());
$file = 'definedVars.txt';

if (file_exists($file)) {
    $definedVarsFile = file_get_contents($file);
    $definedVars = json_decode($definedVarsFile, true);
    extract($definedVars, EXTR_SKIP);

    // unset so that file doesn't grow
    unset($definedVars);
    unset($definedVarsFile);
}

$definedVars = removeSystemVariables(get_defined_vars());
$varNames = array_keys($definedVars);

if(!isset($argv[1])) {
    die("Example: php calculate.php '".'$number'." = E*15/2+(7+12)*3' \n");
}

if (!validate($argv[1], $varNames)) {
    die ("Only math expression and '" . '$var' . " = expression' is allowed \n");
}
try {
    eval('$result = ' . $argv[1] . ';');
} catch (ParseError $e) {
    die ("Only math expression and '" . '$var' . " = expression' is allowed \n");
}

echo $result . "\n";

$definedVars = removeSystemVariables(get_defined_vars());
file_put_contents($file, json_encode($definedVars));

/**
 * Validate that code consists only of digits and letters for security
 * 
 * @param string $code
 * @param array $varNames
 * @return bool
 */
function validate(string $code, array $varNames): bool
{
    // Remove whitespaces and math operations
    $code = preg_replace('/[^A-Za-z0-9\-\$\{\};]/', '', $code);
    $regex = '/[^A-Za-z0-9\$]/';
    
    if (!preg_match($regex, $code, $matches)) {
        return true;
    }
    
    return false;
}

/**
 * Skip system variables so that they cannot be used in expressions
 * 
 * @param array $definedVars
 * @return array
 */
function removeSystemVariables(array $definedVars): array
{
    unset($definedVars['GLOBALS']);
    unset($definedVars['_GET']);
    unset($definedVars['_POST']);
    unset($definedVars['_REQUEST']);
    unset($definedVars['_SERVER']);
    unset($definedVars['_COOKIE']);
    unset($definedVars['_FILES']);
    unset($definedVars['argv']);
    unset($definedVars['argc']);
    unset($definedVars['_ENV']);
    unset($definedVars['file']);
    unset($definedVars['varNames']);
    
    return $definedVars;
} 