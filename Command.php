<?php

namespace filsh\yii2\orientdb;

class Command extends \yii\base\Component
{
    /**
     * @var Connection the OrientDB connection that this command is associated with
     */
    public $db;
    
    /**
     * @var string the SQL statement that this command represents
     */
    private $_sql;
    
    /**
     * Returns the SQL statement for this command.
     * @return string the SQL statement to be executed
     */
    public function getSql()
    {
        return $this->_sql;
    }

    /**
     * Specifies the SQL statement to be executed.
     * The previous SQL execution (if any) will be cancelled, and [[params]] will be cleared as well.
     * @param string $sql the SQL statement to be set.
     * @return static this command instance
     */
    public function setSql($sql)
    {var_dump($sql);exit;
        if ($sql !== $this->_sql) {
            $this->cancel();
            $this->_sql = $this->db->quoteSql($sql);
            $this->_pendingParams = [];
            $this->params = [];
        }

        return $this;
    }
}