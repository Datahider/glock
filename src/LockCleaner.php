<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\glock;

/**
 * Description of LockCleaner
 *
 * @author drweb
 */
class LockCleaner extends \losthost\DB\DBView {
    
    public function __construct() {
        
        $sql = <<<END
                SELECT name
                FROM [locks]
                WHERE until < ?
                END;
        parent::__construct($sql, time());
    }
    
    public function clean() {
        
        $sth = $this->prepare(<<<END
                DELETE FROM [locks]
                WHERE until < ?
                END);
        
        $sth->execute([time()]);
    }
}
