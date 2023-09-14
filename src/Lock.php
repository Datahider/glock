<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\glock;
use Ramsey\Uuid\Uuid;

/**
 * Description of lock
 *
 * @author drweb
 */
class Lock extends \losthost\DB\DBObject {
    
    const TABLE_NAME = 'locks';
    
    const SQL_CREATE_TABLE = <<<END
            CREATE TABLE IF NOT EXISTS %TABLE_NAME% (
                name varchar(256) NOT NULL COMMENT 'Имя_Объекта:Секрет', 
                unlock_secret varchar(36) COMMENT 'Код разблокировки (UUID)',
                until int(11) COMMENT 'Время удаления по таймауту',
                PRIMARY KEY (name)
            ) COMMENT = 'v1.0.0' 
            END;
    
    const EXCEPTION_LOCKED_MSG = 'The object is already locked.';
    const EXCEPTION_LOCKED_CODE = -10010;
    const EXCEPTION_NOT_LOCKED_MSG = 'The object is not locked.';
    const EXCEPTION_NOT_LOCKED_CODE = -10011;
    const EXCEPTION_WRONG_SECRET_MSG = 'Can not unlock the object. Wrong secret given.';
    const EXCEPTION_WRONG_SECRET_CODE = -10008;
    const EXCEPTION_LOCK_TIMEOUT_MSG = 'Can not lock the object. Timeout reached.';
    const EXCEPTION_LOCK_TIMEOUT_CODE = -10012;
    
    
    const STATUS_OK = 0;
    const STATUS_LOCKED = 1;
    const STATUS_ALREADY_LOCKED = 2;
    const STATUS_WAS_NOT_LOCKED = 3;
    const STATUS_UNLOCKED = 4;
    
    protected $status;
    
    public function __construct($name) {
        
        if (empty($name)) {
            throw new \Exception('Name cannot be empty!');
        }
        
        $where = 'name = ?';
        $params = [$name];
        
        try {
            parent::__construct($where, $params);
            $this->status = self::STATUS_LOCKED;
        } catch (\Exception $exc) {
            if ($exc->getMessage() != 'Not found') {
                throw $exc;
            }
            $this->name = $name;
            $this->status = self::STATUS_UNLOCKED;
        }
    }

    public function getStatus() {
        $this->checkUntil();
        return $this->status;
    }
    
    public function lock($timeout) {
        
        if (empty($timeout)) {
            throw new \Exception('Timeout cannot be empty!');
        }        

        for ($i = 0; $i<$timeout; $i++) {
            if ($this->lockAsync($timeout)) {
                return $this->unlock_secret;
            }
            sleep(1);
        }
        
        throw new \Exception(self::EXCEPTION_LOCK_TIMEOUT_MSG, self::EXCEPTION_LOCK_TIMEOUT_CODE);
    }
    
    public function lockAsync($timeout) {

        if (empty($timeout)) {
            throw new \Exception('Timeout cannot be empty!');
        }        

        $this->checkUntil();

        if ($this->status == self::STATUS_LOCKED) {
            return false;
        }    

        $this->until = time() + $timeout;
        $this->unlock_secret = Uuid::uuid4();

        try {
            $this->write();
            $this->status = self::STATUS_LOCKED;
            return $this->unlock_secret;
        } catch (\Exception $exc) {
            $this->__immutable = false;
            return false;
        }
        
    }


    public function unlock($unlock_secret) {

        if (empty($unlock_secret)) {
            throw new \Exception('Unlock secret cannot be empty!');
        }        

        $this->checkUntil();

        if ($this->status == self::STATUS_UNLOCKED) {
            throw new \Exception(self::EXCEPTION_NOT_LOCKED_MSG, self::EXCEPTION_NOT_LOCKED_CODE);
        }
        
        if ($unlock_secret != $this->unlock_secret ) {
            throw new \Exception(self::EXCEPTION_WRONG_SECRET_MSG, self::EXCEPTION_WRONG_SECRET_CODE);
        }
        
        $this->_delete();
        $this->status = self::STATUS_UNLOCKED;
    }

    public function getReadableStatus($prefix = '') {
        switch ($this->getStatus()) {
            case \losthost\glock\lock::STATUS_LOCKED:
                return trim($prefix. ' LOCKED');

            case \losthost\glock\lock::STATUS_UNLOCKED:
                return trim($prefix. ' UNLOCKED');

            default:
                return trim($prefix. ' UNKNOWN');
        }
    }
    
    protected function checkUntil() {
        if ($this->until < time()) {
            $this->_delete();
        }
    }
    
    protected function _delete() {
        $sth = $this->prepare(<<<END
                DELETE FROM %TABLE_NAME%
                WHERE name = ? AND unlock_secret = ?
                END);
        
        $sth->execute([$this->name, $this->unlock_secret]);
        $this->status = self::STATUS_UNLOCKED;
        $this->__is_new = true;
    }
    
}
