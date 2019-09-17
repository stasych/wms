<?
function rus2translit($object, $purge = false)
{
    $converter = [
        'а' => 'a', 'б' => 'b', 'в' => 'v',
        'г' => 'g', 'д' => 'd', 'е' => 'e',
        'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
        'и' => 'i', 'й' => 'y', 'к' => 'k',
        'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'c',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ь' => '\'', 'ы' => 'y', 'ъ' => '\'',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        
        'А' => 'A', 'Б' => 'B', 'В' => 'V',
        'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
        'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
        'И' => 'I', 'Й' => 'Y', 'К' => 'K',
        'Л' => 'L', 'М' => 'M', 'Н' => 'N',
        'О' => 'O', 'П' => 'P', 'Р' => 'R',
        'С' => 'S', 'Т' => 'T', 'У' => 'U',
        'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
        'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
        'Ь' => '\'', 'Ы' => 'Y', 'Ъ' => '\'',
        'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
    ];
    if($purge)
    {
        $converter['ь'] = '';
        $converter['Ь'] = '';
        $converter['ъ'] = '';
        $converter['Ъ'] = '';
    }
    if(is_string($object))
        return strtr($object, $converter);
    if(is_array($object))
    {
        foreach($object as &$item)
        {
            $item = strtr($item, $converter);
        }
        return $object;
    }
    return $object;
}

function edebug($all = false, $silient = false)
{
    $debugTrac = debug_backtrace();
    $caller = @array_shift($debugTrac); // php trace of function call
    $text = "EDEBUG called from line $caller[line] of file $caller[file]";
    if(!$silient)
    {
        ?>
        <script>
            $(document).ready(function ()
            {
                console.log("<?=$text?>");
            });
        </script><?
    }
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    if($all)
    {
        error_reporting(-1);
    }
    else
    {
        error_reporting(E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
    }
}
