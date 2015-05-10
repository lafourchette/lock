<?php

namespace LaFourchette\Lock;

/**
 * Implements locking with redis.
 *
 * @author danielb
 */
class RedisLock implements LockInterface
{
    /**
     *
     * @var \Redis connection 
     */
    protected $conn = null;
    
    /**
     *
     * @var array Informations of connection
     */
    protected $connection_infos = null;
    
    /**
     *
     * @var string Key of the lock
     */
    protected $lock_key = null;
    
    /**
     * 
     * @param array $connInfos Information of connection
     * @param string $lockKey Key of the lock
     * @throws \LogicException
     */
    public function __construct(array $connInfos, $lockKey)
    {
        if (empty($lockKey)) {
            throw new \LogicException('$lockKey can\'t be empty');
        }
        
        $this->initializeConnection($connInfos);
        
        $this->setLockKey($lockKey);
        $this->setConnectionInfos($connInfos);
    }
    
    /**
     * 
     * @param array $connInfos Informations of connection
     */
    protected function initializeConnection(array $connInfos)
    {
        $this->conn = $this->getNewInstanceOfRedis();
        $return = $this->getConnection()->connect($connInfos['host'], $connInfos['port'], $connInfos['timeout']);
        
        if (!$return) {
            throw new Exception('Error of connection Redis');
        }
    }
    
    /**
     * 
     * @return \Redis
     */
    protected function getNewInstanceOfRedis()
    {
        return new \Redis();
    }

    /**
     * 
     * @return \Redis Connection of Redis
     */
    public function getConnection()
    {
        return $this->conn;
    }
    
    /**
     * 
     * @return string Key of the lock
     */
    public function getLockKey()
    {
        return $this->lock_key;
    }
    
    /**
     * 
     * @param string $lock Key of the lock
     */
    public function setLockKey($lockKey)
    {
        $this->lock_key = $lockKey;
    }
    
    /**
     * 
     * @return array Informations of connection
     */
    public function getConnectionInfos()
    {
        return $this->connection_infos;
    }
    
    /**
     * 
     * @param array $connectionInfos Informations of connection
     */
    public function setConnectionInfos(array $connectionInfos)
    {
        $this->connection_infos = $connectionInfos;
    }
    
    /**
     * 
     * @param mixed $metadata Value of the lock key
     * @return boolean
     */
    public function acquire($metadata = null)
    {
        if ($this->check() !== LockInterface::CHECK_RETURN_NOLOCK && $this->check() !== LockInterface::CHECK_RETURN_DEADLOCK) {
            
            return false;
        }
        
        return (TRUE === $this->getConnection()->set($this->getLockKey(), $metadata) ? true : false);
    }
    
    /**
     * 
     * @return int|string Failed check or value of the key returned
     */
    public function check()
    {
        $contentLock = $this->getConnection()->get($this->getLockKey());
        if (FALSE == $contentLock) {
            
            return LockInterface::CHECK_RETURN_NOLOCK;
        } 
        
        return $contentLock;
    }
    
    /**
     * 
     * @return int Success of dead lock
     */
    public function release()
    {
        $this->getConnection()->delete($this->getLockKey());
        
        return LockInterface::CHECK_RETURN_DEADLOCK;
    }
    
    /**
     * 
     * @return array Informations about redis lock
     */
    public function getInfo()
    {
        $info = array('lock_key' => $this->getLockKey());
        $info = array_merge($info, array('connection_infos' => $this->getConnectionInfos()));
        
        return $info;
    }
}