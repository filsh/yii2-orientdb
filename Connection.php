<?php

namespace filsh\yii2\orientdb;

use Yii;
use yii\base\InvalidConfigException;
use Doctrine\OrientDB\Binding\BindingParameters;
use Doctrine\OrientDB\Binding\HttpBinding;

class Connection extends \yii\base\Component
{
    /**
     * @event Event an event that is triggered after a HttpBinding connection instance is established
     */
    const EVENT_AFTER_OPEN = 'afterOpen';
    
    /**
     * Connection uri string, e.g. http://127.0.0.1:2480/demo
     * 
     * @var string
     */
    public $uri;
    
    /**
     * @var string the username for establishing HttpBinding connection instance. Defaults to `null` meaning no username to use.
     */
    public $username;
    
    /**
     * @var string the password for establishing HttpBinding connection instance. Defaults to `null` meaning no password to use.
     */
    public $password;
    
    /**
     * @var HttpBinding the HttpBinding instance associated with this HttpBinding connection instance.
     * This property is mainly managed by [[open()]] and [[close()]] methods.
     * When a HttpBinding connection instance is active, this property will represent a HttpBinding instance;
     * otherwise, it will be null.
     */
    public $httpBinding;
    
    public function init()
    {
        parent::init();
        
        $this->open();
    }
    
    /**
     * Returns a value indicating whether the HttpBinding connection instance is established.
     * @return boolean whether the HttpBinding connection instance is established
     */
    public function getIsActive()
    {
        return $this->httpBinding !== null;
    }
    
    /**
     * Establishes a HttpBinding connection instance.
     * It does nothing if a HttpBinding connection instance has already been established.
     * @throws Exception if connection fails
     */
    public function open()
    {
        if ($this->httpBinding !== null) {
            return;
        }
        
        if (empty($this->uri)) {
            throw new InvalidConfigException('Connection::uri cannot be empty.');
        }
        $token = 'Creating HttpBinding connection instance: ' . $this->uri;
        try {
            Yii::info($token, __METHOD__);
            Yii::beginProfile($token, __METHOD__);
            $this->httpBinding = $this->createHttpBindingInstance();
            $this->trigger(self::EVENT_AFTER_OPEN);
            Yii::endProfile($token, __METHOD__);
        } catch (\Exception $e) {
            Yii::endProfile($token, __METHOD__);
            throw $e;
        }
    }

    /**
     * Removing the currently active HttpBinding connection instance.
     * It does nothing if the connection is already closed.
     */
    public function close()
    {
        if ($this->httpBinding !== null) {
            Yii::trace('Removing HttpBinding connection instance: ' . $this->uri, __METHOD__);
            $this->httpBinding = null;
        }
    }
    
    /**
     * Creates the HttpBinding connection instance.
     * This method is called by [[open]] to establish a DB connection.
     * The default implementation will create a HttpBinding instance.
     * You may override this method if the default HttpBinding needs to be adapted for certain DBMS.
     * @return HttpBinding the pdo instance
     */
    public function createHttpBindingInstance()
    {
        $fromUri = BindingParameters::create($this->uri);
        
        return new HttpBinding(BindingParameters::create([
            'host' => $fromUri->getHost(),
            'port' => $fromUri->getPort(),
            'database' => $fromUri->getDatabase(),
            'username' => $this->username,
            'password' => $this->password
        ]));
    }
    
    /**
     * Creates a command for execution.
     * @param string $sql the SQL statement to be executed
     * @param array $params the parameters to be bound to the SQL statement
     * @return Command the HttpBinding command
     */
    public function createCommand($sql = null, $params = [])
    {
        $command = new Command([
            'db' => $this,
            'sql' => $sql,
        ]);

        return $command->bindValues($params);
    }
}