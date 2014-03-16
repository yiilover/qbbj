<?php
/**
 * Created by JetBrains PhpStorm.
 * User: black
 * Date: 14-3-16
 * Time: ����4:36
 * To change this template use File | Settings | File Templates.
 */
$UPLOAD_CLASS_ERROR = array( 1 => '�������ϴ��ø�ʽ�ļ�',
    2 => 'Ŀ¼����д',
    3 => '�ļ��Ѵ���',
    4 => '��֪������',
    5 => '�ļ�̫��'
);

/**
 * Purpose
 * �ļ��ϴ�
 * Example
 *
$fileArr['file'] = $file;
$fileArr['name'] = $file_name;
$fileArr['size'] = $file_size;
$fileArr['type'] = $file_type;
// �������ϴ����ļ�����
$filetypes = array('gif','jpg','jpge','png');
// �ļ��ϴ�Ŀ¼
$savepath = "/usr/htdocs/upload/";
// û��������� 0 ������
$maxsize = 0;
// ���� 0 ������  1 ����
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

    /** ������ */
    var $savename;
    /** ����·�� */
    var $savepath;
    /** �ļ���ʽ�޶� */
    var $fileformat = array();
    /** ����ģʽ */
    var $overwrite = 0;
    /** �ļ�����ֽ� */
    var $maxsize = 0;
    /** �ļ���չ�� */
    var $ext;
    /** ������� */
    var $errno;

    /**
     * ���캯��
     * @param $fileArr �ļ���Ϣ���� 'file' ��ʱ�ļ�����·�����ļ���
    'name' �ϴ��ļ���
    'size' �ϴ��ļ���С
    'type' �ϴ��ļ�����
     * @param savename �ļ�������
     * @param savepath �ļ�����·��
     * @param fileformat �ļ���ʽ��������
     * @param overwriet �Ƿ񸲸� 1 ������ 0 ��ֹ����
     * @param maxsize �ļ����ߴ�
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

    /** �ϴ�  */
    function run()
    {
        /** ����ļ���ʽ */
        if (!$this->validate_format())
        {
            $this->errno = 1;
            return false;
        }
        /** ���Ŀ¼�Ƿ��д */
        if(!@is_writable($this->savepath))
        {
            $this->errno = 2;
            return false;
        }
        /** ����������ǣ�����ļ��Ƿ��Ѿ����� */
        if($this->overwrite == 0 && @file_exists($this->savepath.$this->savename))
        {
            $this->errno = 3;
            return false;
        }
        /** ����д�С���ƣ�����ļ��Ƿ񳬹����� */
        if ($this->maxsize != 0 )
        {
            if ($this->file_size > $this->maxsize)
            {
                $this->errno = 5;
                return false;
            }
        }
        /** �ļ��ϴ� */
        if(!@copy($this->file, $this->savepath.$this->savename))
        {
            $this->errno = 4;
            return false;
        }
        /** ɾ����ʱ�ļ� */
        $this->destory();
        return true;
    }

    /**
     * �ļ���ʽ���
     * @access protect
     */
    function validate_format()
    {

        if (!is_array($this->fileformat))  // û�и�ʽ����
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
     * ��ȡ�ļ���չ��
     * access public
     */
    function get_ext()
    {
        $ext = explode(".", $this->file_name);
        $ext = $ext[count($ext) - 1];
        $this->ext = $ext;
    }
    /**
     * �����ϴ��ļ�������ֽ�����
     * @param $maxsize �ļ���С(bytes) 0:��ʾ������
     * @access public
     */
    function set_maxsize($maxsize)
    {
        $this->maxsize = $maxsize;
    }

    /**
     * ���ø���ģʽ
     * @param ����ģʽ 1:������ 0:��ֹ����
     * @access public
     */
    function set_overwrite($overwrite)
    {
        $this->overwrite = $overwrite;
    }

    /**
     * ���������ϴ����ļ���ʽ
     * @param $fileformat �����ϴ����ļ���չ������
     * @access public
     */
    function set_fileformat($fileformat)
    {
        $this->fileformat = $fileformat;
    }

    /**
     * ���ñ���·��
     * @param $savepath �ļ�����·������ "/" ��β
     * @access public
     */
    function set_savepath($savepath)
    {
        $this->savepath = $savepath;
    }
    /**
     * �����ļ�������
     * @savename �����������Ϊ�գ���ϵͳ�Զ�����һ��������ļ���
     * @access public
     */
    function set_savename($savename)
    {
        if ($savename == '')  // ���δ�����ļ�����������һ������ļ���
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
     * ɾ���ļ�
     * @param $file ��Ҫɾ�����ļ���
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
     * ɾ����ʱ�ļ�
     * @access proctect
     */
    function destory()
    {
        $this->del($this->file);
    }

    /**
     * �õ�������Ϣ
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