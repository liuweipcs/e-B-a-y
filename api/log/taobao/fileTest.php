<?php
    include "TopSdk.php";
    date_default_timezone_set('Asia/Shanghai'); 

    $c = new TopClient;
    $c->appkey = '23431729';
    $c->secretKey = 'f69976df508b3558144f36b0e8968afa';
    // $req = new TradeVoucherUploadRequest;
    // $req->setFileName("example");
    // $req->setFileData("@/Users/xt/Downloads/1.jpg");
    // $req->setSellerNick("奥利奥官方旗舰店");
    // $req->setBuyerNick("101NufynDYcbjf2cFQDd62j8M/mjtyz6RoxQ2OL1c0e/Bc=");
    // var_dump($c->execute($req));



	$req = new TradeGetRequest;
	$req->setFields("tid,type,status,payment,orders,seller_nick");
	$req->setTid(2154788803108301);
	$sessionKey = '6102706e91f59a15cd4b2962732d5dd22f7254e7660a8ea2873520183';
	$resp = $c->execute($req, $sessionKey);
	
	/*
	$req = new LogisticsTraceSearchRequest;
	$req->setTid("2050898279668301");
	$req->setSellerNick("秋子");
	//$req->setIsSplit("1");
	//$req->setSubTid("1,2,3");
	$resp = $c->execute($req);*/
	
    print_r($resp);
?>