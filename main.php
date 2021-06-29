
<?php

require ('parser.php'); // cfg parser
require ('paymentsapi.php'); // price parser

$m_pParser = new BCParser("product_cfg.cfg");
$m_pPayments = new PaymentsAPI();

foreach($m_pParser->products as $key => $val){ 
    if($val->free == 1){
        unset($m_pParser->products[$key]);
        array_push($m_pParser->products, $val);
}
}
foreach($m_pParser->products as $key => $val){
    if($val->status == 0){
        unset($m_pParser->products[$key]);
        array_push($m_pParser->products, $val);
}
}

foreach( $m_pParser->products as $product )
	{
		$productnew = "";
 		$productclasses = "";
		if ($product->status == 0) {
		$productclasses .= ' opacity-0h5';
		}
		if ($product->free == 1) {
		$productclasses .= '';		
		}
		if ($product->newproduct == 1) {
		$productnew = '<h5 class="m-0 p-0" style="z-index:1;"><span class="badge position-absolute bg-primary text-white">NEW</span></h5>';
		}
        $productid = substr($product->image, 7, -4);
    echo '
    <div class="col-md-4">
      <div class="card mb-4 shadow-sm'.$productclasses.'">
	  '.$productnew.'
	  <a class="text-center" target="_blank" href="'.$product->link.'"><img class="bd-placeholder-img card-img-top" src="/products/'.$productid.'.jpg">
	  <h5 class="text-center text-white display-5 p-2">'.$product->name.'</h5></div></a>
        <div class="card-body">
          ';
		  
          echo '
          <div class="d-flex justify-content-between align-items-center">';
              
			if ($product->status == 1 && $product->free == 0) {
			foreach( $product->payments as $pay )
			{
			if (isset($pay->link) == false) {
				echo '<button class="btn btn-block btn-secondary" disabled>'.$resourcelang->loadString("m_unavailable").'</button>';
			}	
			if ($m_pPayments->GetPriceFromLink( $pay->link ) == "Unavailable") {
				$buytext = $resourcelang->loadString("m_unavailable");
			}
			else {
			$ip_address = $_SERVER['REMOTE_ADDR'];
			$rubcountry = ["RU", "UA", "BY", "KZ", "AM", "AZ", "GE", "MD", "TJ", "TM", "UZ"];
			$ip_country = geoip_country_code_by_name ($ip_address);
			if (in_array($ip_country, $rubcountry)) {
			$paylink = round($m_pPayments->GetPriceFromLink( $pay->link )['price'], 0, PHP_ROUND_HALF_UP).' <i class="fas fa-ruble-sign"></i>';
			}
			else
			{
			$paylink = '<i class="fas fa-dollar-sign"></i>'.round($m_pPayments->GetPriceFromLink( $pay->link )['amount'], 0, PHP_ROUND_HALF_UP);
			}
			$buytext = $pay->time.' '.$resourcelang->loadString("m_month_for").' '.$paylink;

			echo '<a href="'.$pay->link.'" class="btn btn-block btn-primary">'.$buytext.'</a>';
			}
			}/* end pay foreach */
			
			}
			else {
				if ($product->status == 0) { // if frozen
					$epochfrzdate = strtotime($product->freezedate);
					
					echo '<button class="btn btn-block btn-secondary" disabled>'.$resourcelang->loadString("m_frozen").' '.date('d.m.y ', substr($epochfrzdate, 0, 10)).$resourcelang->loadString('m_frozenat').date(' H:i:s', substr($epochfrzdate, 0, 10)).'</button>';
				}
				if ($product->free == 1) { // if free
			echo '<a href="'.$resourcelang->loadString("m_freelink_en").'" class="btn btn-block btn-success">'.$resourcelang->loadString("m_free_download").'</a>';
			}
			}
         	echo '
              </div>
            </div>
          </div>
        </div>';

}
?>
