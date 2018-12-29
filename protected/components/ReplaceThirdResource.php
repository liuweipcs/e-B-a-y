<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/24 0024
 * Time: 下午 5:54
 */
class ReplaceThirdResource
{
    public $subject; //要搜索替换的目标字符串或字符串数组
    protected $host;   //链接中出现根目录时，需要拼接的域名地址
    protected $relativeLink;   //链接中出现相对目录时，需要拼接的url地址
    const HTTPS_HOST = 'https://image-us.bigbuy.win';   //下载后访问的https域名
    public $errorLogPath = 'log/replaceThirdResource_error.log';  //错误日志
    public $level = 1;  //递归到第几层
    public $savePlace = 'Location';
    public $debug;
    protected $hasReplace;  //是否有替换，true有替换

    public function getHasReplace()
    {
        return $this->hasReplace;
    }

    public function setHost($url)
    {
        $url = trim($url);
        $url = trim($url,'/');
        $url = trim($url,'\\');
        $this->host = $url;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setRelativeLink($url)
    {
        $url = trim($url);
        $url = trim($url,'/');
        $url = trim($url,'\\');
        $this->relativeLink = $url;
    }

    public function getRelativeLink()
    {
        return $this->relativeLink;
    }

    //当subject时html时下载替换链接替换
    public function replace()
    {
        $this->replaceHrefLink();
        $this->replaceSrcLink();
        $this->replaceUrlLink();
        $this->replaceDelete();
        return $this->subject;
    }

    public function replaceDelete()
    {
        $this->subject = preg_replace('/<img[^>]+src=("|\')https:\/\/gate\.datacaciques\.com\/track\/img[^\\1]+\\1[^>]*>/Ui','',$this->subject);
        return $this->subject;
    }

    //当subject时url时下载替换链接替换
    public function replaceLink()
    {
        $subject = $this->subject;
        $this->subject = "src='{$subject}'";
        $replaced = $this->replace();
        $this->subject = $subject;
        return trim(substr($replaced,5),"'");
    }

    //替换<link rel="stylesheet" href="wcss.css" type="text/css" />形式的链接（css）
    public function replaceHrefLink()
    {
//        $this->subject = preg_replace_callback('/\\b(href=)(\'|")([^\\2]+)\\2/Ui', array($this, 'hrefLink'), $this->subject);
        $this->subject = preg_replace_callback('/(<\s*link[^>]+href=)("|\')([^\\2]+)(\\2[^>]*>)/Ui',array($this, 'hrefLink'),$this->subject);
        return $this->subject;
    }

    public function hrefLink($match)
    {
        $link = $match[3];
        if (strpos($link, '/') === 0)   //跟目录
        {
            if(strpos($link, '//') === 0)
            {
                $resultLink = $this->handleLink('https' . $link,true,'css');
            }
            else if(isset($this->host))
            {
                $resultLink = $this->handleLink($this->host . $link,true,'css');
            }
            else
            {
                $resultLink = $link;
            }

        }
        else if (strpos($link, 'http') === 0)  //全链接
        {
            $resultLink = $this->handleLink($link,true,'css');
        }
        else if (isset($this->relativeLink))                       //相对目录
        {
            $resultLink = $this->handleLink($this->relativeLink . '/' . $link,true,'css');
        } else {
            $resultLink = $link;
        }
        return $match[1] . $match[2] . $resultLink . $match[4];
    }

    /*public function hrefLink($match)
    {
        $link = $match[3];
        if (preg_match('/[.]css/i', $link, $linkMatch, PREG_OFFSET_CAPTURE))  //判断是不是css文件
        {
            if (strpos($link, '/') === 0 && isset($this->host))   //跟目录
            {
                $resultLink = $this->handleLink($this->host . $link);
            } else if (strpos($link, 'http') === 0)  //全链接
            {
                $link = substr($link, 0, $linkMatch[0][1] + 4);//去掉链接.css后的尾缀
                $resultLink = $this->handleLink($link);
            } else if (isset($this->relativeLink))                       //相对目录
            {
                $resultLink = $this->handleLink($this->relativeLink . '/' . $link);
            } else {
                $resultLink = $link;
            }
        } else {
            $resultLink = $link;
        }
        return $match[1] . $match[2] . $resultLink . $match[2];
    }*/

    //替换src='http://***'形式的链接(js和图片)
    public function replaceSrcLink()
    {
        $this->subject = preg_replace_callback('/\\b(src=)(\'|")([^\\2]+)\\2/Ui', array($this, 'srcLink'), $this->subject);
        return $this->subject;
    }
    public function srcLink($match)
    {
        $link = $match[3];
        if (preg_match('/[.](js|bmp|jpg|jpeg|png|tiff|gif|pcx|tga|exif|fpx|svg|psd|cdr|pcd|dxf|ufo|eps|ai|raw|WMF)/i', $link, $linkMatch, PREG_OFFSET_CAPTURE))  //判断是不是图片或js
        {
            if (strpos($link, '/') === 0 && isset($this->host))   //跟目录
            {
                $resultLink = $this->handleLink($this->host . $link,false);
            } else if (strpos($link, 'http') === 0)  //全链接
            {
                $link = substr($link, 0, $linkMatch[0][1] + strlen($linkMatch[0][0]));//去掉链接后的尾缀
                $resultLink = $this->handleLink($link,false);
            } else if (isset($this->relativeLink))                       //相对目录
            {
                $resultLink = $this->handleLink($this->relativeLink . '/' . $link,false);
            } else {
                $resultLink = $link;
            }
        } else {
            $resultLink = $link;
        }
        return $match[1] . $match[2] . $resultLink . $match[2];
    }

    //替换css中url('http://***')形式的链接(css和图片)
    public function replaceUrlLink()
    {
        $this->subject = preg_replace_callback('/\\b(url)\(([^\)]+)\)/Ui', array($this, 'urlLink'), $this->subject);
        return $this->subject;
    }
    public function urlLink($match)
    {
        $link = $match[2];
        if(preg_match('/^(\'|").+\1$/',$link,$quotMatch,PREG_OFFSET_CAPTURE))  //判断是否被引号括起来
        {
            $quot = $quotMatch[1][0];
            $link = trim($link,$quot);
        }
        if (preg_match('/[.](css|bmp|jpg|jpeg|png|tiff|gif|pcx|tga|exif|fpx|svg|psd|cdr|pcd|dxf|ufo|eps|ai|raw|WMF)/i', $link, $linkMatch, PREG_OFFSET_CAPTURE))  //判断是不是图片或css
        {
            if(strtolower($linkMatch[0][0]) == '.css')
            {
                $recursion = true;
            }
            else
            {
                $recursion = false;
            }
            if (strpos($link, '/') === 0 && isset($this->host))   //跟目录
            {
                $resultLink = $this->handleLink($this->host . $link,$recursion);
            } else if (strpos($link, 'http') === 0)  //全链接
            {
                $link = substr($link, 0, $linkMatch[0][1] + strlen($linkMatch[0][0]));//去掉链接图片后缀后的尾缀
                $resultLink = $this->handleLink($link,$recursion);
            } else if (isset($this->relativeLink))                       //相对目录
            {
                $resultLink = $this->handleLink($this->relativeLink . '/' . $link,$recursion);
            } else {
                $resultLink = $link;
            }
        } else {
            $resultLink = $link;
        }
        if(isset($quot))
        {
            $resultLink = $quot.$resultLink.$quot;
        }
        return $match[1] . '(' . $resultLink . ')';
    }

    protected function handleLink($link, $recursion = true,$extend = null)
    {
//        file_put_contents($this->errorLogPath, __CLASS__ . __METHOD__ . $this->level . '。Link:' . $link . PHP_EOL, FILE_APPEND);
        try{
            $linkInfo = parse_url($link);
//            $currentHostInfo = parse_url(Yii::app()->request->hostInfo);
            $currentHostInfo = parse_url('http://120.24.249.36');
            $httpsHostInfo = parse_url(self::HTTPS_HOST);
            switch ($linkInfo['host']) {
                case '47.88.35.136':  //美国图片服务器地址
                    $this->hasReplace = true;
                    if ($linkInfo['scheme'] == 'http')
                        return str_replace('http://47.88.35.136', self::HTTPS_HOST, $link);
                    else
                        return str_replace('https://47.88.35.136', self::HTTPS_HOST, $link);
                    break;
                case $currentHostInfo['host']:    //本地地址
                    $this->hasReplace = true;
                    if ($linkInfo['scheme'] == 'http')
                        return str_replace('http://' . $currentHostInfo['host'], self::HTTPS_HOST, $link);
                    else
                        return str_replace('https://' . $currentHostInfo['host'], self::HTTPS_HOST, $link);
                    break;
                case $httpsHostInfo['host']:        //美国服务器域名地址
                    if ($httpsHostInfo['scheme'] == 'http')
                    {
                        $this->hasReplace = true;
                        return str_replace('http://', 'https://', $link);
                    }
                    else
                        return $link;
                    break;
                default:     //第三方资源
                    $this->hasReplace = true;
//                    file_put_contents($this->errorLogPath,'第三方资源。Link:' . $link . PHP_EOL, FILE_APPEND);
                    $thirdResourceModel = UebModel::model('ThirdResource')->find('source_url=:source_url', array(':source_url' => $link));
//                    file_put_contents($this->errorLogPath,'查询是否有第三方资源：'.count($thirdResourceModel).'。Link:' . $link . PHP_EOL, FILE_APPEND);
                    if (empty($thirdResourceModel)) {
                        if($extend === null)
                            $extend = end(explode('.', $linkInfo['path']));
                        $content = self::getContentsMany($link);
                        if ($content['data'] !== false) {
                            $content['data'] = trim($content['data']);
                            if($content['data'] === '')
                            {
                                file_put_contents($this->errorLogPath, $link . '资源内容为空。第' . $this->level . '层。' . PHP_EOL, FILE_APPEND);
                                return $link;
                            }
                            else
                            {
                                if ($recursion) {
                                    $obj = new self();
                                    $obj->savePlace = $this->savePlace;
                                    $obj->subject = $content;
                                    $obj->setHost($linkInfo['scheme'] . '://' . $linkInfo['host']);
                                    $obj->setRelativeLink($obj->getHost() . dirname($linkInfo['path']));
                                    $obj->level = $this->level + 1;
                                    $content = $obj->replace();
                                    unset($obj);
                                }
//                            file_put_contents($this->errorLogPath,'第三方资源开始保存。Link:' . $link . PHP_EOL, FILE_APPEND);
                                switch ($this->savePlace)
                                {
                                    case 'Location':
                                        $destinationPath = $this->saveLinkLocation($extend,$content['data'],$link);
                                        break;
                                    case 'USA':
                                        $destinationPath = $this->saveLinkUSA($extend,$content['data'],$link);
                                        break;
                                }
                                if(!empty($destinationPath))
                                    return self::HTTPS_HOST . '/' . $destinationPath;
                                else
                                    return $link;
                            }
                        } else {
                            file_put_contents($this->errorLogPath, $link . '资源下载不成功。第' . $this->level . '层。' . PHP_EOL, FILE_APPEND);
                            return $link;
                        }
                    }
                    else
                        return self::HTTPS_HOST . '/' . $thirdResourceModel->destination_path;
            }
        }catch (Exception $e){
            file_put_contents($this->errorLogPath, __CLASS__ . '报错。第' . $this->level . '层。错误:'.$e->getMessage() . '。Link:' . $link . PHP_EOL, FILE_APPEND);
            return $link;
        }
    }

    protected function saveLinkLocation($extend,$content,$link)
    {
        $destinationPath = 'upload/third_resource/' . date('Y/m/d/H/i/');
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        $destination = $destinationPath . uniqid() . mt_rand(10000, 99999) . '.' . $extend;
        if (file_put_contents($destination, $content)) {
            $thirdResourceModel = new ThirdResource();
            $thirdResourceModel->source_url = $link;
            $thirdResourceModel->destination_path = $destination;
            $thirdResourceModel->create_time = date('Y-m-d H:i:s');
            $thirdResourceModel->save();
            return $thirdResourceModel->destination_path;
        } else {
            file_put_contents($this->errorLogPath, $link . '资源文件保存本地不成功。第' . $this->level . '层。' . PHP_EOL, FILE_APPEND);
        }
    }

    protected function saveLinkUSA($extend,$content,$link)
    {
//        file_put_contents($this->errorLogPath,'第三方资源推送美国。Link:' . $link . PHP_EOL, FILE_APPEND);
        $response = self::pushData(['file'=>$content,'suffix'=>$extend],'http://image-us.bigbuy.win/receiveFile.php');
        if(empty($response))
        {
            file_put_contents($this->errorLogPath, $link . '资源文件推送美国服务器不成功，推送response为空。第' . $this->level . '层。' . PHP_EOL, FILE_APPEND);
        }
        else
        {
//            file_put_contents($this->errorLogPath,'第三方资源推送美国有response。Link:' . $link . PHP_EOL, FILE_APPEND);
            $response = json_decode($response,true);
//            file_put_contents($this->errorLogPath,'第三方资源推送美国response ACK:'.$response['ack'].'。Link:' . $link . PHP_EOL, FILE_APPEND);
            if($response['ack'] == 'Success')
            {
//                file_put_contents($this->errorLogPath, $link . '美国链接'.$response['path'].'。第' . $this->level . '层。' . PHP_EOL, FILE_APPEND);
                $thirdResourceModel = new ThirdResource();
                $thirdResourceModel->source_url = $link;
                $thirdResourceModel->destination_path = $response['path'];
                $thirdResourceModel->create_time = date('Y-m-d H:i:s');
                $thirdResourceModel->save();
                return $thirdResourceModel->destination_path;
            }
            else
            {
                file_put_contents($this->errorLogPath, $link . '资源文件推送美国服务器不成功，ack:'.$response['ack'].'，error:'.$response['error'].'第' . $this->level . '层。' . PHP_EOL, FILE_APPEND);
            }
        }
    }

    public static function getContentsMany($url,$times = 5)
    {
        $content = self::getContents($url);
        if($content['data'] === false && $times > 1)
        {
            return self::getContentsMany($url,$times - 1);
        }
        else
        {
            return $content;
        }
    }

    public static function getContents($url)
    {
        $cn = curl_init();
        curl_setopt($cn,CURLOPT_URL,$url);
        curl_setopt($cn,CURLOPT_TIMEOUT,180);
        curl_setopt($cn,CURLOPT_CONNECTTIMEOUT,120);
        curl_setopt($cn,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($cn,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($cn,CURLOPT_RETURNTRANSFER,true);
        $response = curl_exec($cn);
        $info = curl_getinfo($cn);
        curl_close($cn);
        if($info['http_code'] == 302 && isset($info['redirect_url']))
        {
            return self::getContents($info['redirect_url']);
        }
        else
        {
            return ['data'=>$response,'content_type'=>$info['content_type']];
        }
    }
	
    public static function pushData($data,$url)
    {
        $cn = curl_init();
        curl_setopt($cn,CURLOPT_URL,$url);
        curl_setopt($cn,CURLOPT_POST,true);
        curl_setopt($cn,CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($cn,CURLOPT_POSTFIELDS,$data);
        curl_setopt($cn,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($cn,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($cn,CURLOPT_RETURNTRANSFER,true);
        $response = curl_exec($cn);
        curl_close($cn);
        return $response;
    }
	
    public static function replaceDesctionsGarbled($content)
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/protected/vendors/simpleHtmlDom.php';
        $html = new simple_html_dom();
        $html->load($content);
        $html->find('div[id=patemplate_store_category]',0)->parent->outertext ='';
        $newContent = $html->outertext;
        $html->clear();
        return $newContent;
//         $newContent = preg_replace_callback('/([\s\S]*?)(<div.*patemplate_store_category\">[\s\S]*?<\/div>)(\s*<div\s*class=\"patemplatebox\"\s*id=\"patemplate_left_cp\">[\s\S]*?)/Ui', function($data) {
//             return $data[1].$data[3];
//         }, $content);
//         return $newContent;
    }
}