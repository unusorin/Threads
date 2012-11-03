<?php
/**
 * Memory Block
 * @author Badea Sorin
 * @since 16.10.2012
 */
class MemoryBlock
{
    /**
     * memory block id
     * @var int
     */
    private $BlockId = null;
    /**
     * memory block size
     * @var int
     */
    private $BlockSize = 100;

    /**
     * memory block resource
     * @var null
     */
    private $BlockResource = null;

    /**
     * memory block data
     * @var string
     */
    private $BlockData = null;

    /**
     * new memory block flag
     * @var bool
     */
    private $BlockNew = false;

    /**
     * memory block
     * @param int $BlockId block id
     * @param bool $New new memory block flag
     */
    public function __construct($BlockId,$New=false)
    {

        $this->BlockId = $BlockId;
        $this->BlockNew=$New;

        if(!$New){
            $this->Create();
            $this->BlockData=$this->Read();
        }
    }

    /**
     * memory block factory
     * @param int $BlockId memory block id
     * @return MemoryBlock
     */
    public static function Get($BlockId){
        $Block = new MemoryBlock($BlockId,false);
        if($Block->Valid()){
            return $Block;
        }else{
            return new MemoryBlock($BlockId,true);
        }
    }

    /**
     * class destructor
     */
    public function __destruct()
    {
        $this->Write();
        $this->Close();
    }

    /**
     * toString magic method
     * @return string
     */
    public function __toString(){
        return $this->Data()."";
    }

    /**
     * check if current memory block was created
     * @return bool
     */
    public function Valid()
    {
        if (!$this->BlockResource) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * get memory block size
     * @return int
     */
    private function Size()
    {
        return @shmop_size($this->BlockResource);
    }

    /**
     * get/set memory block data
     * @param null|string $Data
     * @return string
     */
    public function Data($Data = null)
    {
        if ($Data == null) {
            return $this->BlockData;
        } else {
            $this->BlockData = $Data;
            $this->BlockSize = intval(strlen($this->BlockData));
            return $Data;
        }
    }

    /**
     * Delete current memory block
     */
    public function Delete()
    {
        @shmop_delete($this->BlockResource);
    }

    /**
     * create memory block
     */
    private function Create()
    {
        if($this->BlockNew){
            $this->BlockResource = @shmop_open($this->BlockId, "c", 0644, $this->BlockSize);
        }else{
            $this->BlockResource = @shmop_open($this->BlockId, "w", 0, 0);
        }
    }

    /**
     * write data in memory block
     * @return bool
     */
    private function Write()
    {
        if($this->BlockNew){
            $this->Create();
        }
        $BytesWritten = @shmop_write($this->BlockResource, $this->BlockData, 0);

        if ($BytesWritten != strlen($this->BlockData)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * read memory block data
     * @return string
     */
    private function Read()
    {
        return @shmop_read($this->BlockResource, 0, $this->Size());
    }

    /**
     * close memory block resource
     */
    private function Close()
    {
        @shmop_close($this->BlockResource);
    }
}

