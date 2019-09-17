<?
define('SUCCESS',200);
define('DEF_ERROR',404);
class CRes
{
    public $code = 200;
    public $msg = 'Functions complete successfully';
    public $data = [];
    
    public function __construct($code = false, $msg = false, $data = false)
    {
        if($code)
            $this->code = $code;
        if($msg)
            $this->msg = $msg;
        if($data)
            $this->data = $data;
    }
    
    public function __toString()
    {
        return "[{$this->code}] : [{$this->msg}]";
    }
    
    public function success() {return $this->code === SUCCESS;}
}