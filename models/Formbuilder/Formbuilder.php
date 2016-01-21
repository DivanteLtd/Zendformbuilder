<?php

/**
 * Class Formbuilder_Formbuilder
 * Modified by Maciej Koprek (mkoprek@divante.pl)
*/

/*

Copyright (c) 2011, alexandre delattre
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

 * Redistributions of source code must retain the above copyright notice,
   this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright
   notice, this list of conditions and the following disclaimer in the
   documentation and/or other materials provided with the distribution.
 * Neither the name of grafyweb.com nor the names of its contributors may
   be used to endorse or promote products derived from this software
   without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.

*/

class Formbuilder_Formbuilder
{
    protected $table;

    public function __construct()
    {
        $pimDb = Pimcore_Resource_Mysql::get();
        $rev = Pimcore_Version::$revision;
        if ($rev > 1350) {
            Zend_Db_Table::setDefaultAdapter($pimDb->getResource());
        } else {
            Zend_Db_Table::setDefaultAdapter($pimDb);
        }

        $this->table = new Formbuilder_DbTable_Formbuilder();
    }

    public function create($name)
    {
        $name = addslashes($name);
        $id = $this->table->insert(array('name' => $name, 'date' => time()));

        return $id;
    }

    public function delete($id)
    {
        $id = (int)$id;
        $ret = $this->table->delete(array('id=?' => $id));
        if ($ret > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function read()
    {
        $rows = $this->table->fetchAll();
        return $rows;
    }

    public function rename($id, $name)
    {
        $id = (int)$id;
        $this->table->update(array('name' => $name), array('id=?' => $id));
        return true;
    }

    public function getName($id)
    {
        $id = (int)$id;
        $row = $this->table->fetchRow(array('id=?' => $id));
        return $row->name;
    }

    public function getIdByName($name)
    {
        $row = $this->table->fetchRow(array('name=?' => $name));
        return $row->id;
    }

}
