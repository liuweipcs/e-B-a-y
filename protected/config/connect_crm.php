<?php
/**
 * @Author: anchen
 * @Date:   2017-09-27 19:51:30
 * @Last Modified by:   anchen
 * @Last Modified time: 2017-09-28 10:29:19
 */
/* Connect to an ODBC database using driver invocation */
// $dsn  =  'mysql:dbname=ueb_crm;host=192.168.3.201' ;
// $user  =  'root' ;
// $password  =  '49BA59ABBE56E057' ;
// try {
//      $connect_crm_Ob  = new  PDO ( $dsn ,  $user ,  $password );
// } catch ( PDOException $e ) {
//     echo  'Connection failed: '  .  $e -> getMessage ();
// }
class connect_crm
{
    function __construct()
    {
        $conn = mysql_connect('192.168.3.201','root','49BA59ABBE56E057');
        mysql_select_db("ueb_crm");
    }

    //获取买家差评记录
    public function getCommentData($buyer_name='',$platform_code='')
    {
        $sql = " select * from test where id=1";
        $query = mysql_query($sql);
        $temp_array = array();
        while($temp_array = mysql_fetch_array($query)){
            $result[] = $temp_array;
        }

        return $result;
    }

    //获取买家纠纷记录
    public function getDisputeData($buyer_name='',$platform_code='')
    {
        switch ( $platform_code )
        {
            case 'EB':
                $sql = " select * from ueb_ebay_cancellations where buyer='$buyer_name'";
                break;
            case 'ALI':
                $sql = " select * from ueb_ebay_cancellations where buyer='$buyer_name'";
                break;
            default:
                return '没有可用平台';
                break;
        }

        $query = mysql_query($sql);
        $temp_array = array();
        while($temp_array = mysql_fetch_array($query)){
            $result[] = $temp_array;
        }

        return $result;
    }


    //获取买家退款记录
    public function getReimburseData($buyer_name='',$platform_code='')
    {
        $sql = " select * from test where id=1";
        $query = mysql_query($sql);
        $temp_array = array();
        while($temp_array = mysql_fetch_array($query)){
            $result[] = $temp_array;
        }

        return $result;
    }


    //获取买家黑名单记录
    public function getBlacklistData($id='',$data='')
    {
        $sql = " select * from test where id=1";
        $query = mysql_query($sql);
        $temp_array = array();
        while($temp_array = mysql_fetch_array($query)){
            $result[] = $temp_array;
        }

        return $result;
    }


    //获取买家补发货记录
    public function getRecordData($id='',$data='')
    {
        $sql = " select * from test where id=1";
        $query = mysql_query($sql);
        $temp_array = array();
        while($temp_array = mysql_fetch_array($query)){
            $result[] = $temp_array;
        }

        return $result;
    }



}










