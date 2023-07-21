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
    const GLOCK_COMMAND_LOCK_ASYNC = 'lockAsync';
    const GLOCK_COMMAND_UNLOCK = 'unlock';
    
    protected $unlock_secret;
    protected $lock_name;
    protected $timeout;

    public function __construct($object_name, $secret, $timeout=self::GLOCK_DEFAULT_TIMEOUT) {
        
        if (empty($object_name) || empty($secret) || empty($timeout)) {
            throw new \Exception('Parameters must not be empty!');
        }
        
        $this->lock_name = "$object_name:$secret";
        $this->timeout = $timeout;
    }
    
    public function lock() {
        $result = $this->lockRequest(self::GLOCK_COMMAND_LOCK, $this->lock_name, $this->timeout);
       
        if (isset($result->error)){
            throw new \Exception($result->error);
        }
        
        $this->unlock_secret = $result->result;
        return true;
    }
    
    public function lockAsync() {
        $result = $this->lockRequest(self::GLOCK_COMMAND_LOCK_ASYNC, $this->lock_name, $this->timeout);
        if ($result->result == false) {
            return false;
        }
        $this->unlock_secret = $result->result;
        return true;
    }
    
    public function unlock() {
        $result = $this->lockRequest(self::GLOCK_COMMAND_UNLOCK, $this->lock_name, $this->unlock_secret);

        if (isset($result->error)){
            throw new \Exception($result->error);
        }
        return true;
    }
    
    protected function lockRequest($command, $lock_name, $param) {
        if ($command == self::GLOCK_COMMAND_UNLOCK){
            $url = "https://glock.pio.su/api/$command?name=$lock_name&unlock_secret=$param";
            $result = file_get_contents($url);
        } else {
            $url = "https://glock.pio.su/api/$command?name=$lock_name&timeout=$param";
            $result = file_get_contents($url);
        }
        
        return json_decode($result);
    }

    public function __destruct() {
        ;
    }
    
}
