<?php
/**
 * Created by JetBrains PhpStorm.
 * User: black
 * Date: 14-3-16
 * Time: 下午4:36
 * To change this template use File | Settings | File Templates.
 */
$UPLOAD_CLASS_ERROR = array( 1 => '不允许上传该格式文件',
    2 => '目录不可写',
    3 => '文件已存在',
    4 => '不知名错误',
    5 => '文件太大'
);

/**
 * Purpose
 * 文件上传
 * Example
 *
$fileArr['file'] = $file;
$fileArr['name'] = $file_name;
$fileArr['size'] = $file_size;
$fileArr['type'] = $file_type;
// 所允许上传的文件类型
$filetypes = array('gif','jpg','jpge','png');
// 文件上传目录
$savepath = "/usr/htdocs/upload/";
// 没有最大限制 0 无限制
$maxsize = 0;
// 覆盖 0 不允许  1 允许
$overwrite = 0;
$upload = new upload($fileArr, $file_name, $savepath, $filetypes, $overwrite, $maxsize);
if (!$upload->run())
{
echo   $upload->errmsg();
}
 */
class upload
{
    var $file;
    var $file_name;
    var $file_size;
    var $file_type;

    /** 保存名 */
    var $savename;
    /** 保存路径 */
    var $savepath;
    /** 文件格式限定 */
    var $fileformat = array();
    /** 覆盖模式 */
    var $overwrite = 0;
    /** 文件最大字节 */
    var $maxsize = 0;
    /** 文件扩展名 */
    var $ext;
    /** 错误代号 */
    var $errno;

    /**
     * 构造函数
     * @param $fileArr 文件信息数组 'file' 临时文件所在路径及文件名
    'name' 上传文件名
    'size' 上传文件大小
    'type' 上传文件类型
     * @param savename 文件保存名
     * @param savepath 文件保存路径
     * @param fileformat 文件格式限制数组
     * @param overwriet 是否覆盖 1 允许覆盖 0 禁止覆盖
     * @param maxsize 文件最大尺寸
     */
    function upload($fileArr, $savename, $savepath, $fileformat, $overwrite = 0, $maxsize = 0) {
        $this->file = $fileArr['file'];
        $this->file_name = $fileArr['name'];
        $this->file_size = $fileArr['size'];
        $this->file_type = $fileArr['type'];

        $this->get_ext();
        $this->set_savepath($savepath);
        $this->set_fileformat($fileformat);
        $this->set_overwrite($overwrite);
        $this->set_savename($savename);
        $this->set_maxsize($maxsize);
    }

    /** 上传  */
    function run()
    {
        /** 检查文件格式 */
        if (!$this->validate_format())
        {
            $this->errno = 1;
            return false;
        }
        /** 检查目录是否可写 */
        if(!@is_writable($this->savepath))
        {
            $this->errno = 2;
            return false;
        }
        /** 如果不允许覆盖，检查文件是否已经存在 */
        if($this->overwrite == 0 && @file_exists($this->savepath.$this->savename))
        {
            $this->errno = 3;
            return false;
        }
        /** 如果有大小限制，检查文件是否超过限制 */
        if ($this->maxsize != 0 )
        {
            if ($this->file_size > $this->maxsize)
            {
                $this->errno = 5;
                return false;
            }
        }
        /** 文件上传 */
        if(!@copy($this->file, $this->savepath.$this->savename))
        {
            $this->errno = 4;
            return false;
        }
        /** 删除临时文件 */
        $this->destory();
        return true;
    }

    /**
     * 文件格式检查
     * @access protect
     */
    function validate_format()
    {

        if (!is_array($this->fileformat))  // 没有格式限制
            return true;
        $ext = strtolower($this->ext);
        reset($this->fileformat);
        while(list($var, $key) = each($this->fileformat))
        {
            if (strtolower($key) == $ext)
                return true;
        }
        reset($this->fileformat);
        return false;
    }

    /**
     * 获取文件扩展名
     * access public
     */
    function get_ext()
    {
        $ext = explode(".", $this->file_name);
        $ext = $ext[count($ext) - 1];
        $this->ext = $ext;
    }
    /**
     * 设置上传文件的最大字节限制
     * @param $maxsize 文件大小(bytes) 0:表示无限制
     * @access public
     */
    function set_maxsize($maxsize)
    {
        $this->maxsize = $maxsize;
    }

    /**
     * 设置覆盖模式
     * @param 覆盖模式 1:允许覆盖 0:禁止覆盖
     * @access public
     */
    function set_overwrite($overwrite)
    {
        $this->overwrite = $overwrite;
    }

    /**
     * 设置允许上传的文件格式
     * @param $fileformat 允许上传的文件扩展名数组
     * @access public
     */
    function set_fileformat($fileformat)
    {
        $this->fileformat = $fileformat;
    }

    /**
     * 设置保存路径
     * @param $savepath 文件保存路径：以 "/" 结尾
     * @access public
     */
    function set_savepath($savepath)
    {
        $this->savepath = $savepath;
    }
    /**
     * 设置文件保存名
     * @savename 保存名，如果为空，则系统自动生成一个随机的文件名
     * @access public
     */
    function set_savename($savename)
    {
        if ($savename == '')  // 如果未设置文件名，则生成一个随机文件名
        {
            srand ((double) microtime() * 1000000);
            $rnd = rand(100,999);
            $name = date('Ymdhis') + $rnd;
            $name = $name.".".$this->ext;
        } else {
            $name = $savename;
        }
        $this->savename = $name;
    }
    /**
     * 删除文件
     * @param $file 所要删除的文件名
     * @access public
     */
    function del($file)
    {
        if(!@unlink($file))
        {
            $this->errno = 3;
            return false;
        }
        return true;
    }
    /**
     * 删除临时文件
     * @access proctect
     */
    function destory()
    {
        $this->del($this->file);
    }

    /**
     * 得到错误信息
     * @access public
     * @return error msg string or false
     */
    function errmsg()
    {
        global $UPLOAD_CLASS_ERROR;

        if ($this->errno == 0)
            return false;
        else
            return $UPLOAD_CLASS_ERROR[$this->errno];
    }
}
?>