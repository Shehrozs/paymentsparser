<?php

function get_ssl_web_page( $url )
{
	$urlmd5 = "paymentcache/cached_".md5( $url ).".txt";
	
	if( file_exists( $urlmd5 ) )
	{
		return file_get_contents( $urlmd5 );
	}
	
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    curl_close($ch);
	
	file_put_contents( $urlmd5, $result );
	
    return $result;
}

function get_web_page( $url )
{
	$urlmd5 = "paymentcache/cached_".md5( $url ).".txt";
	
	if( file_exists( $urlmd5 ) )
	{
		$return = array( );
		$return['errno'] = 0;
		$return['http_code']  = 200;
		$return['content'] = file_get_contents( $urlmd5 );;
		return $return;
	}
	
    $user_agent='Mozilla/5.0 (Windows NT 6.0; rv:8.0) Gecko/20100101 Firefox/8.0';

    $options = array(

        CURLOPT_CUSTOMREQUEST  =>"GET",        //set request type post or get
        CURLOPT_POST           =>false,        //set to GET
        CURLOPT_USERAGENT      => $user_agent, //set user agent
        CURLOPT_COOKIEFILE     =>"cookie.txt", //set cookie file
        CURLOPT_COOKIEJAR      =>"cookie.txt", //set cookie jar
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
	file_put_contents( $urlmd5, $header[ 'content' ] );
    return $header;
}


class PaymentsAPI
{
	public function __construct( )
	{
		
	}
	
	public function GetPriceFromLinkOplata( $link )
	{
		/*
		<div class="goods_price" id="goods_price">
				300.00<span style=font-size:14px>&nbsp;RUR</span>

		*/
		$parselink = parse_url($link);
		parse_str($parselink['query'], $query);
		$newlink = "https://www.oplata.info/asp2/price_options.asp?p=".$query['id_d']."&n=0&c=WMR";
		$result = get_ssl_web_page( $newlink );
		$status = "";
		
		/*if( $result[ 'errno' ] != 0 )
			$status = "CONNECTION ERROR::COULDNT CONNECT TO Oplata.info->".$result[ 'errno' ]."!";
	
		if( $result[ 'http_code' ] != 200 )
			$status = "CONNECTION ERROR::COULDNT DO HTTP TO Oplata.info->".$result[ 'http_code' ]."!";*/
		//$russian_rouble = " RUB";
		$resultjson = json_decode($result,true);
		
		//error_log($result[ 'content' ], 0);
		
		//$content = ceil($resultjson["price"]/10)*10;
		$content = $resultjson["price"];
		return $content;		
	}
	
	public function GetPriceFromLinkPayPro( $link )
	{
		*/
		$result = get_ssl_web_page( $link );

		$content = trim( $result );
		$content = strstr( $content, '<span class="billing-currency"><span class="price-amount">' );
		$content = substr( $content, strlen( '<span class="billing-currency"><span class="price-amount">') );
		
		$content_currency = $content; //copy
		$content_currency = strstr( $content_currency, '<span class="price-currency">' );
		$content_currency = substr( $content_currency, strlen( '<span class="price-currency">') );
		
		$ptr = strstr( $content_currency, '</span></span>' );
		$ptr_len = strlen( $ptr );
		$con_len = strlen( $content_currency );
		$cut = $con_len - $ptr_len;
		$currency = strip_tags( substr( $content_currency, 0, $cut ) );
		
		$ptr = strstr( $content, '</span><span class="price-currency">' );
		$ptr_len = strlen( $ptr );
		$con_len = strlen( $content );
		
		$cut = $con_len - $ptr_len;
		$content = substr( round($content/63), 0, $cut );
		$price = strip_tags( $content );
	
		return $price;
	}
	public function GetPriceFromLink( $link )
	{
		if( strstr( $link, 'payproglobal.com' ) )
			return $this->GetPriceFromLinkPayPro( $link );
		else if( strstr( $link, 'oplata.info' ) )
			return $this->GetPriceFromLinkOplata( $link );
		return 'Unavailable yet :/';
	}
};

?>