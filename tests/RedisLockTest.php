<?php

use LaFourchette\Lock\RedisLock;
use LaFourchette\Lock\LockInterface;

/**
 * @author danielb
 */
class RedisLockTest extends \PHPUnit_Framework_TestCase
{
    protected function getRedisConnectionMock()
    {
        return $this->getMockBuilder('Redis')
            ->setMethods(array('get', 'set', 'delete', 'connect'))
            ->getMock();
    }
    
    protected function getRedisLockMock($redisMock)
    {
        $redisLockMock = $this->getMockBuilder('LaFourchette\Lock\RedisLock')
            ->setConstructorArgs(array(array('host'=>'127.0.0.1'), 'lock_key_test'))
            ->setMethods(array('initializeConnection', 'getConnection', 'getNewInstanceOfRedis'))
            ->getMock();
        
        $redisLockMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnCallback(
                function () use ($redisMock) {
                    return $redisMock;
                }
            ));
            
        return $redisLockMock;
    }

    public function testGetterOnConstruct()
    {
        //Exception
        $connectionInfos = array('host'=>'127.0.0.1');
        try {
            $redisLock = new RedisLock($connectionInfos, '');
        } catch (LogicException $e) {
            $this->assertInstanceOf('LogicException', $e);
        }

        //Setter
        $redisLockMock = $this->getRedisLockMock($this->getRedisConnectionMock());
        
        $this->assertEquals($redisLockMock->getLockKey(), 'lock_key_test');
        $this->assertEquals($redisLockMock->getConnectionInfos(), $connectionInfos);
        $this->assertInstanceOf('Redis', $redisLockMock->getConnection());
    }
    
    public function testSetLockKey()
    {
        $redisLockMock = $this->getRedisLockMock($this->getRedisConnectionMock());
        
        $redisLockMock->setLockKey('key1');
        $this->assertEquals($redisLockMock->getLockKey(), 'key1');
    }
    
    public function testAcquire()
    {
        // Content Lock Exist
        $redisConnection = $this->getRedisConnectionMock();
        $redisConnection->expects($this->any())
            ->method('get')
            ->will($this->returnValue('value1'));
        
        $redisLockMock = $this->getRedisLockMock($redisConnection);
        $redisLockMock->setLockKey('key1');
        $this->assertFalse($redisLockMock->acquire());
        
        // Set Key Value with success
        $redisConnection = $this->getRedisConnectionMock();
        $redisConnection->expects($this->any())
            ->method('set')
            ->will($this->returnValue(true));
        
        $redisLockMock = $this->getRedisLockMock($redisConnection);
        $redisLockMock->setLockKey('key1');
        $this->assertTrue($redisLockMock->acquire());
        
        // Set Key Value with failure
        $redisLockMock = $this->getRedisLockMock($this->getRedisConnectionMock());
        $redisLockMock->setLockKey('key1');
        $this->assertFalse($redisLockMock->acquire());
    }        
    
    public function testCheck()
    {
        // Get lock not exist
        $redisConnection = $this->getRedisConnectionMock();
        $redisConnection->expects($this->any())
            ->method('get')
            ->will($this->returnValue(false));
        
        $redisLockMock = $this->getRedisLockMock($redisConnection);
        $redisLockMock->setLockKey('value1');
        $this->assertEquals($redisLockMock->check(), LockInterface::CHECK_RETURN_NOLOCK);
        
        // Get lock exist
        $redisConnection = $this->getRedisConnectionMock();
        $redisConnection->expects($this->any())
            ->method('get')
            ->will($this->returnValue('value1'));
        
        $redisLockMock = $this->getRedisLockMock($redisConnection);
        $redisLockMock->setLockKey('value1');
        $this->assertEquals($redisLockMock->check(), 'value1');
    }
    
    public function testRelease()
    {
        // Delete lock
        $redisLockMock = $this->getRedisLockMock($this->getRedisConnectionMock());
        $redisLockMock->setLockKey('value1');
        
        $this->assertEquals($redisLockMock->release(), LockInterface::CHECK_RETURN_DEADLOCK);
    }
    
    public function testGetInfo()
    {
        $redisLockMock = $this->getRedisLockMock($this->getRedisConnectionMock());
        
        $this->assertEquals($redisLockMock->getInfo(), array(
            'lock_key' => 'lock_key_test',
            'connection_infos' => array(
                'host' => '127.0.0.1',
            ),
        ));
    }
}
