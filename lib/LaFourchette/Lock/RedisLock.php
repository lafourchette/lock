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
     * @var \Redis connexion 
     */
    protected $conn = null;
    
    /**
     *
     * @var string Key of the lock
     */
    protected $lock_key = null;
    
    public function __construct(\Redis $conn)
    {
        $this->conn = $conn;
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
     * @param mixed $metadata Value of the lock key
     * @return boolean
     * @throws \LogicException
     */
    public function acquire($metadata = null)
    {
        if ($this->check() !== LockInterface::CHECK_RETURN_NOLOCK && $this->check() !== LockInterface::CHECK_RETURN_DEADLOCK) {
            
            return false;
        }
        
        $lockKey = $this->getLockKey();
        if (empty($lockKey)) {
            throw new \LogicException('$lockKey attribute must be set before calling this function');
        }
        
        return (TRUE === $this->conn->set($lockKey, $metadata) ? true : false);
    }
    
    /**
     * 
     * @return int|string Failed check or value of the key returned
     * @throws \LogicException
     */
    public function check()
    {
        $lockKey = $this->getLockKey();
        
        if (empty($lockKey)) {
            throw new \LogicException('$lockKey attribute must be set before calling this function');
        }
        
        $contentLock = $this->conn->get($lockKey);
        if (FALSE == $contentLock) {
            
            return LockInterface::CHECK_RETURN_NOLOCK;
        } 
        
        return $contentLock;
    }
    
    /**
     * 
     * @throws \LogicException
     */
    public function release()
    {
        $lockKey = $this->getLockKey();
        
        if (empty($lockKey)) {
            throw new \LogicException('$lockKey attribute must be set before calling this function');
        }
        
        $this->conn->delete($lockKey);
        
        return LockInterface::CHECK_RETURN_DEADLOCK;
    }
    
    /**
     * 
     * @return array Information about redis lock
     */
    public function getInfo()
    {
        return array('lock_key' => $this->getLockKey());
    }
}