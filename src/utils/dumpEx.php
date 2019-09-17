<?
function dumpNewFix($variable)
{
    if(is_float($variable))
    {
        return "<span class='dumpNew_float'>" . htmlspecialchars($variable) . "</span>,";
    }
    elseif(is_numeric($variable)) // is_int
    {
        return "<span class='dumpNew_int'>" . htmlspecialchars($variable) . "</span>,";
    }
    elseif(is_string($variable))
    {
        $tmp = strtolower($variable);
        $res1 = substr($tmp, 0, 8);
        $res2 = substr($tmp, 0, 7);
        if(($res1 == "https://" || $res2 == "http://") && strlen($variable) > 15)
        {
            return "<span class='dumpNew_text'><a class='dumpNew_a' href=\"{$variable}\" target=\"_blank\">\"" . htmlspecialchars($variable) . "\"</a></span>,";
        }
        else
        {
            return "<span class='dumpNew_text'>\"" . htmlspecialchars($variable) . "\"</span>,";
        }
    }
    elseif($variable === true)
    {
        return "<span class='dumpNew_bool'>true</span>,";
    }
    elseif($variable === false)
    {
        return "<span class='dumpNew_bool'>false</span>,";
    }
    elseif(is_null($variable))
    {
        return "<span class='dumpNew_null'>null</span>,";
    }
    else
    {
        return htmlspecialchars($variable) . '<span class="dumpNew_sys_type">{TYPE = ?}</span>';
    }
}

function dumpNewPrint(&$variable, $hide, $dumpex_id, $level = 0)
{
    $ret = '';
    if(is_object($variable) && !is_a($variable,'Exception'))
        $variable = get_object_vars($variable);
    if(is_array($variable) && count($variable) > 0)
    {
        foreach($variable as $key => $item)
        {
            $is_int_key = is_int($key);
            if(is_object($item) && !is_a($variable,'Exception'))
                $item = get_object_vars($item);
            if(is_array($item))
            {
                
                if(count($item) > 0)
                {
                    $class = (($level == 0) ? 'dumpNew_sub_details_0' : 'dumpNew_sub_details');
                    //$arr_type = (is_array_assoc($item) ? ', type')
                    //$ret .= "<details class='{$class} {$dumpex_id}' {$hide}><summary class='dumpNew_sub_summary'>[" . (($is_int_key) ? ($key) : ("'" . htmlspecialchars($key) . "'")) . "] <span class='dumpNew_sys_type_arr'>{size=" . count($item) . ", level=" . $level . "}</span></summary>" . dumpNewPrint($item, $hide, $dumpex_id, $level + 1);
                    $ret .= "<details class='{$class} {$dumpex_id}' {$hide}><summary class='dumpNew_sub_summary' title='{size=" . count($item) . ", level=" . $level . "}'>'" . htmlspecialchars($key) . "' =&gt; [</summary>" . dumpNewPrint($item, $hide, $dumpex_id, $level + 1);
                    $ret .= "<span class='dumpNew_end_array'>],</span></details>";
                }
                else
                {
                    $class = (($level == 0) ? 'dumpNew_sub_details_0' : 'dumpNew_sub_details');
                    $ret .= "<details class='{$class} {$dumpex_id}' {$hide}><summary class='dumpNew_sub_summary' title='{size=" . count($item) . ", level=" . $level . "}'>'" . htmlspecialchars($key) . "' =&gt; [</summary><span class='dumpNew_comment'>// empty array</span><br>";
                    $ret .= "<span class='dumpNew_end_array'>],</span></details>";
                }
            }
            else
            {
                //$ret .= "[" . (($is_int_key) ? ($key) : ("'" . htmlspecialchars($key) . "'")) . "] =&gt; " . dumpNewFix($item) . "<br>";
                $ret .= (($is_int_key) ? ($key) : ("'" . htmlspecialchars($key) . "'")) . " =&gt; " . dumpNewFix($item) . "<br>";
            }
        }
    }
    else
    {
        $ret .= dumpNewFix($variable);
    }
    return $ret;
}

function dumpEx($data, $title = false, $hidden = true, $force = false, $custom = false)
{
    if(isset($_REQUEST['AJAX']) && $_REQUEST['AJAX'] == 'Y')
        return;
    $dumpex_id = uniqid();
    $haha = "";
    $base_text_color = "#EEE";
    $dumpNewStyle = "
<style type='text/css'>
.dumpNew_base_block {
    height: auto;
    overflow: hidden;
    border: none;
    min-width: 100%;
    width: 100%;
    box-sizing: border-box;
    padding: 0px;
    margin: 0px auto;
    position: relative;
}
.dumpNew_sys_type {
    color: #777;
    font-weight: normal;
    text-decoration: none;
    font-family: 'Consolas', 'Monaco', 'Courier New', Courier, monospace;
    font-size: 10px;
    float: right;
    max-width: 150px;
    min-width: 150px;
}
.dumpNew_sys_type_arr {
    color: #777;
    font-weight: normal;
    text-decoration: none;
    font-family: 'Consolas', 'Monaco', 'Courier New', Courier, monospace;
    font-size: 10px;
    margin-left: 0px;
}
.dumpNew_text {
    color: #6A8759;
}
.dumpNew_int {
    color: #6897BB;
}
.dumpNew_float {
    color: #ABABAB;
}
.dumpNew_comment {
    color: #808080;
}
.dumpNew_bool {
    color: #CC7832;
}
.dumpNew_null {
    color: #CC7832;
}
.dumpNew_a {
    color: #66ff6c;
}
.dumpNew_type_str {
    font-weight: normal;
}
.dumpNew_base_details {
    background-color: #232525;
    color: {$base_text_color};
    border: 1px solid #000000;
    font-family: 'Lucida Console', 'DejaVu Sans Mono', Monaco, monospace;
    font-size: 12px;
    width: 100%;
    font-weight: normal;
    white-space: normal;
    padding-bottom: 0px;
}
.dumpNew_base_top_summary {
    color: #FFFFFF;
    text-align: center;
    font-weight: bold;
    margin: 3px;
    padding: 1px;
    background-color: #0C4797;
    border: 1px solid #000000;
    display: block;
}
.dumpNew_base_bottom_summary {
    text-align: center;
    margin: 3px;
    padding: 1px;
    color: #FFFFFF;
    border: 1px solid #000000;
    background-color: #0C4797;
}
.dumpNew_sub_details {
    padding-left: 40px;
}
.dumpNew_sub_details_0 {
    padding-left: 40px;
    margin: 0px 0px 0px 20px
}
.dumpNew_sub_summary {
    margin: 0 0 0 -53px;
    font-weight: normal;
    color: {$base_text_color};
}
.dumpNew_end_array {
    color: {$base_text_color};
    margin: 0px 0px 0px -40px;
}
.dumpNew_input {
    float: right;
    margin-right: 10px;
    max-height: 18px;
    background-color: #1C1C1C;
    border: 1px solid lightgrey;
    color: lightgrey;
}
{
    $haha
}
</style><script type='text/javascript'>function dumpNew(did,stat){document.getElementById('did_i_'+did).value=((stat=='Show all')?((stat=true)?'Hide all':false):((stat=false)?true:'Show all'));var dido=document.getElementsByClassName(did);for(var i=0;i<dido.length;i++)dido.item(i).open=stat}</script>";
    //    global $USER;
    //    if(!cval($USER) && !$force)
    //    {
    //        return;
    //    }
    $debugTrac = debug_backtrace();
    $caller = @array_shift($debugTrac); // php trace of function call
    $prev_caller = count($debugTrac) > 1 ? @next($debugTrac)['function'] : $caller['file']; // php trace of function call
    $count = ' [elements count: ' . scount($data) . ']';
    
    if(strlen($title) == 36 && $title[8] == '-' && $title[13] == '-' && $title[18] == '-' && $title[23])
    {
        $title = '<b style="color: #FFF;">UID: <u>' . strtoupper($title) . '</u></b>';
    }
    
    if($title === false)
    {
        if(is_string($data))
        {
            $title = $data;//$caller['file'] . '[' . $caller['line'] . ']';
        }
        else
        {
            $title = $caller['file'] . '[' . $caller['line'] . ']';
        }
    }
    
    if($custom != 'test')
    {
        $title = $title ? $title . $count : $prev_caller;
    }
    
    $calltext = 'dump called from line ' . $caller['line'] . ' of file ' . $caller['file']; // get line and file
    
    /*
     * if(!$force && !$USER->IsAdmin())
        return;
    */
    
    if(defined('NO_GUI') && NO_GUI === true)
        $hide = 'open="open"';
    else
        $hide = '';
    
    // USER PARAM
    $hide = (!$hidden) ? 'open="open"' : $hide;
    
    global $dump_new_style_load;
    $final_data = ((!$dump_new_style_load) ? ($dumpNewStyle) : (''));
    $dump_new_style_load = true;
    
    $did_i_val = !$hide ? 'Show all' : 'Hide all';
    
    $data_h = cval($data);
    
    $hide_button = "<input class='dumpNew_input' type=\"button\" id=\"did_i_{$dumpex_id}\" value=\"{$did_i_val}\" onclick=\"dumpNew('{$dumpex_id}', this.value);\">";
    
    if(!$data_h) $hide_button = '';
    
    $final_data .= "<div class='dumpNew_base_block'><details id='{$dumpex_id}' class='dumpNew_base_details' {$hide}><summary class='dumpNew_base_top_summary'>{$title}</summary>{$hide_button}<div style='padding-left: 5px;'>";
    $final_data .= $data_h == false ? dumpNewFix($data) : dumpNewPrint($data, $hide, $dumpex_id, 0);
    $final_data .= "</div><summary class='dumpNew_base_bottom_summary'>{$calltext}</summary></details></div>";
    
    echo $final_data;
}