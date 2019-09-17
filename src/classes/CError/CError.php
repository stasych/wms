<?
class CError extends Exception
{
    public $data = false;
    
    public function __construct($message, $code = 0, $data = false, $previous = null)
    {
        if($data)
            $this->data = $data;
        
        parent::__construct( $message, $code, $previous );
    }
    
//    public function __toString()
//    {
//        return __CLASS__ . ": from function: [{$this->fn}] : [{$this->code}]: [{$this->message}]" . PHP_EOL;
//    }
    
}