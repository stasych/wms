<?
/**
 * <p>Return variable value if exist and not empty</p>
 * @param $var : variable to check
 * @return mixed: false if empty/!isset or $var else
 */
function cval($var)
{
    return (!isset($var) || empty($var) || is_array($var) && count($var) < 1 || $var === 'false' || $var === false) ? false : $var;
}

/**
 * <p>Return via ternal operator cval value or $set arg</p>
 * @param $val : variable to check
 * @param $set : set value to set if cval = false
 * @return mixed
 */
function sval($val, $set = false)
{
    return cval($val) ?: $set;
}

function scount($ar)
{
    if(!isset($ar))
        return 0;
    if(!is_array($ar))
        return 0;
    return count($ar);
}

function wrap($string, $item = '"')
{
    return $item . $string . $item;
}

function wrapsql($string)
{
    return "'" . $string . "'";
}

function wrapsqlfield($string)
{
    return "`" . $string . "`";
}


/**
 * Used in UpsertJson for SKU like doubles
 */
function is_num_ex($val)
{
    $val = mb_strtoupper($val);
    return strpos($val,'E') !== false || (strpos($val,'0') === 0) ? false : is_numeric($val);
}

function recursive_array_replace($find, $replace, $array)
{
    if (!is_array($array))
    {
        return str_replace($find, $replace, $array);
    }
    $newArray = [];
    foreach ($array as $key => $value)
    {
        $newArray[$key] = recursive_array_replace($find, $replace, $value);
    }
    return $newArray;
}

function explod3($delimeter, $string)
{
    if(is_array($string))
        return $string;
    
    $result = explode($delimeter, $string);
    if(scount($result) <= 1 && !cval($result[0]) && $result[0] !== false)
        return [];
    return $result;
}