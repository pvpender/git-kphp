<?php
namespace pvpender\GitKphp;
class Systemc{
    public static function load(){
        \FFI::load(__DIR__ . "/c.h");
    }
    public function __construct(){

        $this->c = \FFI::scope('c');
    }
    public function system(string $command): int
    {
        return $this->c->system($command);
    }

    /** @var ffi_scope<c> */
    private $c;
}