<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\glock;
/**
 * Description of glock
 *
 * @author drweb
 */
class Glock {

    const GLOCK_DEFAULT_TIMEOUT = 60;
    const GLOCK_COMMAND_LOCK = 'lock';
    const GLOCK_COMMAND_UNLOCK = 'unlock';
    
    protected $unlock_secret;
    protected $lock_name;
    protected $timeout;

    public function __construct($object_name, $secret, $timeout=self::GLOCK_DEFAULT_TIMEOUT) {
        $this->lock_name = "$object_name:$secret";
        $this->timeout = $timeout;
    }
    
    public function lock() {
        for ($i=0; $i <= $this->timeout; $i++) {
            $this->unlock_secret = $this->lockAsync();
            if ($this->unlock_secret) {
                return true;
            }
            sleep(1);
        }
        throw new \Exception("Lock timeout reached.", );
    }
    
    public function lockAsync() {
        $result = $this->lockRequest(self::GLOCK_COMMAND_LOCK, $this->lock_name, $this->timeout);
        return $result;
    }
    
    public function unlock() {
        
    }
    
    protected function lockRequest($command, $lock_name, $param) {
        
    }

    public function __destruct() {
        ;
    }
    
}
