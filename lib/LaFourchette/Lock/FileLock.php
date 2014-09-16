<?php

namespace LaFourchette\Lock;

/**
 * Implements locking with file.
 *
 * The locking mechanism is weak, aka not concurrency proof.
 *
 * @author david
 */
class FileLock implements LockInterface
{
    /**
     * @var string Path to the locked file.
     */
    protected $path;

    /**
     * @var
     */
    protected $directory;

    /**
     * @var
     */
    protected $name;

    /**
     * @return string Path to lock file.
     */
    public function getPath()
    {
        return $this->getDirectory() . DIRECTORY_SEPARATOR . $this->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getInfo(){
        return array('path' => $this->getPath());
    }
    
    /**
     * @return string Name. Default as script-name.lock
     */
    public function getName()
    {
        if(! $this->name){
            $this->name = basename( __FILE__ ) . '.' . time() . '.lock';
        }
        return $this->name;
    }

    /**
     * @return string Directory. Default as system's temp dir.
     */
    public function getDirectory()
    {
        if(! $this->directory){
            $this->directory = sys_get_temp_dir();
        }
        return $this->directory;
    }

    /**
     * @param string $path Set directory. Fluent.
     * @throws \Exception
     */
    public function setDirectory($path)
    {
        if (! is_dir($path)) {
            if (false === @mkdir($path, 0755, true)) {
                throw new \RuntimeException(sprintf("Unable to create directory (%s)\n", $path));
            }
        } elseif (! is_writable($path)) {
            throw new \RuntimeException(sprintf("Unable to write in directory (%s)\n", $path));
        }
        $this->directory = realpath($path);
        return $this;
    }
    
    /**
     * @param string $path Set name. Fluent.
     * @throws \Exception
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function acquire($metadata = null) {
        if ($this->check() !== LockInterface::CHECK_RETURN_NOLOCK
                && $this->check() !== LockInterface::CHECK_RETURN_DEADLOCK) {
            return false;
        }

        return file_put_contents($this->getPath(), $metadata) !== false;
    }

    /**
     * {@inheritdoc}
     * @exception \LogicException
     */
    public function release() {
        $path = $this->getPath();
        if (empty($path)) {
            throw new \LogicException('$path attribute must be set before calling this function');
        }

        @unlink($path);
    }

    /**
     * {@inheritdoc}
     */
    public function check() {
        $path = $this->getPath();
        if (empty($path)) {
            throw new \LogicException(
                    '$path attribute must be set before calling this function');
        }

        if (!file_exists($path)) {
            return LockInterface::CHECK_RETURN_NOLOCK;
        }

        if (!is_readable($path)) {
            throw new \RuntimeException(
                    'Lock is present but not readable. Please check permissions.');
        }

        return file_get_contents($path);
    }
}
