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
class LockCleaner extends \losthost\DB\DBObject {
    
    const TABLE_NAME = 'locks';
    
    public function clean() {
        
        $sth = $this->prepare(<<<END
                DELETE FROM %TABLE_NAME%
                WHERE until < ?
                END);
        
        $sth->execute([time()]);
    }
}
