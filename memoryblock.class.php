<?php
/**
 * Memory Block
 *
 * @author Badea Sorin
 * @since  16.10.2012
 */
class MemoryBlock
{
    /**
     * memory block id
     *
     * @var int
     */
    private $blockId = NULL;
    /**
     * memory block size
     *
     * @var int
     */
    private $blockSize = 100;

    /**
     * memory block resource
     *
     * @var null
     */
    private $blockResource = NULL;

    /**
     * memory block data
     *
     * @var string
     */
    private $blockData = NULL;

    /**
     * new memory block flag
     *
     * @var bool
     */
    private $blockNew = FALSE;

    /**
     * memory block
     *
     * @param int  $blockId block id
     * @param bool $new     new memory block flag
     */
    public function __construct($blockId, $new = FALSE)
    {

        $this->blockId  = $blockId;
        $this->blockNew = $new;

        if (!$new) {
            $this->create();
            $this->blockData = $this->read();
        }
    }

    /**
     * memory block factory
     *
     * @param int $blockId memory block id
     *
     * @return MemoryBlock
     */
    public static function get($blockId)
    {
        $block = new MemoryBlock($blockId, FALSE);
        if ($block->valid()) {
            return $block;
        } else {
            return new MemoryBlock($blockId, TRUE);
        }
    }

    /**
     * class destructor
     */
    public function __destruct()
    {
        $this->write();
        $this->close();
    }

    /**
     * toString magic method
     *
     * @return string
     */
    public function __toString()
    {
        return $this->data() . "";
    }

    /**
     * check if current memory block was created
     *
     * @return bool
     */
    public function valid()
    {
        if (!$this->blockResource) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * get memory block size
     *
     * @return int
     */
    private function size()
    {
        return @shmop_size($this->blockResource);
    }

    /**
     * get/set memory block data
     *
     * @param null|string $data
     *
     * @return string
     */
    public function data($data = NULL)
    {
        if ($data == NULL) {
            return $this->blockData;
        } else {
            $this->blockData = $data;
            $this->blockSize = intval(strlen($this->blockData));
            return $data;
        }
    }

    /**
     * Delete current memory block
     */
    public function delete()
    {
        @shmop_delete($this->blockResource);
    }

    /**
     * create memory block
     */
    private function create()
    {
        if ($this->blockNew) {
            $this->blockResource = @shmop_open($this->blockId, "c", 0644, $this->blockSize);
        } else {
            $this->blockResource = @shmop_open($this->blockId, "w", 0, 0);
        }
    }

    /**
     * write data in memory block
     *
     * @return bool
     */
    private function write()
    {
        if ($this->blockNew) {
            $this->create();
        }
        $BytesWritten = @shmop_write($this->blockResource, $this->blockData, 0);

        if ($BytesWritten != strlen($this->blockData)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * read memory block data
     *
     * @return string
     */
    private function read()
    {
        return @shmop_read($this->blockResource, 0, $this->size());
    }

    /**
     * close memory block resource
     */
    private function close()
    {
        @shmop_close($this->blockResource);
    }
}

