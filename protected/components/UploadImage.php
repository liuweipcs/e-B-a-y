<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/12 0012
 * Time: 下午 5:31
 */
class UploadImage
{
    protected $file;
    public static $errorCodeMessage = [1=>'上传文件大小超过服务器允许上传的最大值。',2=>'上传文件的大小超过表单中 MAX_FILE_SIZE 指定的值。',3=>'文件只有部分被上传。',4=>'没有文件被上传。',6=>'找不到临时文件夹。',7=>'文件写入失败。',8=>'文件上传扩展没有打开。'];

    public $maxSize = 1048576;//单位bytes 1048576bytes = 1M，允许的最大大小。
    protected $size;    //文件大小

    public $allowExtensions = ['gif','jpg','png','jpeg','tif','bmp']; //允许的文件后缀。
    protected $extension; //文件后缀名

    public $allowMine = ['image/gif','image/jpeg','image/png','image/tif','image/bmp']; //允许的MINE
    protected $mine;

    public $savePath; //文件路径 可以定义要保存到那个文件夹
    public $saveName;   //保存文件名 可以定义新保存的文件名称(不含后缀)
    protected $filePath; //保存的文件路径

    protected $fileName; //原始文件名称（不含后缀）
    protected $fileAllName; //原始文件名称（含后缀）


    protected $errors;

    public function __construct($file = null)
    {
        if(isset($file))
            $this->setFile($file);
    }

    public function setFile($file)
    {
        $this->errors = null;
        if(is_string($file))
            $file = $_FILES[$file];
        if(is_array($file))
            $this->file = $file;
        else
            $this->errors = '上传文件信息错误！';
        if(!isset($this->errors))
            $this->validate();
    }

    public function run()
    {
        if(isset($this->errors))
            return false;
        else
        {
            if(!isset($this->savePath))
                $this->savePath = 'upload/image/ebay/'.date('Y/m/d');
            if(!is_dir($this->savePath))
                mkdir($this->savePath,0760,true);
            if(isset($this->saveName))
                $name = $this->saveName.'.'.$this->extension;
            else
                $name = uniqid().'.'.$this->extension;
            $this->filePath = $this->savePath.'/'.$name;
            if(move_uploaded_file($this->file['tmp_name'],$this->filePath))
                return true;
            else
            {
                $this->errors = '文件移动失败';
                return false;
            }
        }
    }

    public function getFilePath()
    {
        if(isset($this->errors))
            return null;
        else
            return $this->filePath;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function getFileAllName()
    {
        return $this->fileAllName;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function delete()
    {
        unlink($this->filePath);
    }

    protected function ini()
    {
        if(empty($this->file))
        {
            $this->errors = '未上传文件。';
        }
        else
        {
            if($this->file['error'] !== 0)
            {
                $this->errors = $this->errorCodeMessage[$this->file['error']] ? $this->errorCodeMessage[$this->file['error']] : '文件上传不成功。';
            }
        }
        if(isset($this->errors))
            return false;
        $this->size = $this->file['size'];
        $this->mine = $this->file['type'];
        $pos = strrpos($this->file['name'],'.');
        if($pos === false)
        {
            $this->errors = '图片后缀错误。';
            return false;
        }
        else
            $posAfter = $pos + 1;
        $this->extension = strtolower(substr($this->file['name'],$posAfter));
        $this->fileAllName = $this->file['name'];
        $this->fileName = substr($this->fileAllName,0,$pos);
        return true;
    }

    protected function validate()
    {
        if($this->ini())
        {
            if($this->size > $this->maxSize)
            {
                $this->errors = '文件不能超过'.($this->maxSize/1024).'KB。';
                return false;
            }
            if(!in_array($this->extension,$this->allowExtensions))
            {
                $this->errors = '只能上传'.implode('|',$this->allowExtensions).'格式图片。';
                return false;
            }
            if(!in_array($this->mine,$this->allowMine))
            {
                $this->errors = '文件内容类型错误。';
                return false;
            }
        }
    }
}