<?php
  header("Content-type: text/xml");
  echo '<?xml version="1.0" encoding="UTF-8"?>';

  function curl_hitUrl($url, $postData = null) {
    $userAgent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";

    $h = curl_init();

    curl_setopt($h, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($h, CURLOPT_URL, $url);
    curl_setopt($h, CURLOPT_FAILONERROR, 1);
    curl_setopt($h, CURLOPT_FOLLOWLOCATION, 1);    // allow redirects
    curl_setopt($h, CURLOPT_RETURNTRANSFER,1);     // return into a variable
    curl_setopt($h, CURLOPT_TIMEOUT, 15);          // times out after 15s
    curl_setopt($h, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($h, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookies.txt');
    curl_setopt($h, CURLOPT_COOKIEFILE, dirname(__FILE__).'/cookies.txt');

    if ($postData != null) {
      curl_setopt($h, CURLOPT_POSTFIELDS, http_build_query($postData));
      curl_setopt($h, CURLOPT_POST, 1);
    }
     
    $doc = curl_exec($h);

    return $doc;
  }

  function safeway_clearCookies() {
    $h = fopen(dirname(__FILE__).'/cookies.txt', 'w');
    fwrite($h, '');
    fclose($h);
  }

  function safeway_locateStore($zip) {
    $postData = array();
    $postData['postalcode']  = $zip;
    $postData['brand']       = 'shop.safeway.com';
    $postData['mode']        = 'zip';
    $postData['miles']       = 7;
    $postData['limit']       = 9;
    $postData['LocateStore'] = 1;

    $results = curl_hitUrl("http://shop.safeway.com/corporate/storefinder/storeResults.asp", $postData);
    
    return $results;
  }
  
  function safeway_getSpecials($storeId) {
    $specials = array();
    
    // hit this URL to gather necessary cookies
    curl_hitUrl("http://safeway1.inserts2online.com/storeReview.jsp?drpStoreID=$storeId");
    
    // get the specials on each page and add them to the list
    for ($i = 0; $i < 8; $i++) {
      $pageSpecials = safeway_getSpecialsOnPage($storeId, $i);
      
      foreach ($pageSpecials as $special) {
        $specials[] = $special;
      }
    }
    
    return $specials;
  }

  function safeway_getSpecialsOnPage($storeId, $pageNum) {
    $specials = array();
    
    $data = curl_hitUrl("http://safeway1.inserts2online.com/pageLarge.jsp?pageNumber=$pageNum&drpStoreId=$storeId");
    
    preg_match_all('/<script>temp(.*)<\/script>/', $data, $matches);
    
    foreach ($matches[1] as $str) {
      $specialData = safeway_parseSpecial($str);
      
      if ($specialData) {
        $specials[] = $specialData;
      }
    }
    
    return $specials;
  }
  
  function safeway_parseSpecial($data) {
    $special = array();
    // <script>temp0 = escape("<table class=Main><tr><td valign=top width=65%><div class=Header1> Tropicana Pure Premium </div><div class=Description>   59 to 64-oz.  Chilled orange juice. Selected varieties.</div><div class=Price2> CLUB PRICE  SAVE up to $5.99 on 2 </div></td><td align=right valign=top><img src='SafewaySafeway07182007NorCal/items/small/01_25WIN29_N1-33.jpg'><div class=Price>BUY ONE, GET ONE FREE</div></td></tr></table>");</script>
    
    if (preg_match('/Header1>(.*)<\/div>/U', $data, $matches))
      $special['name'] = htmlspecialchars(trim($matches[1]));
    else return null;
    
    if (preg_match('/Description>(.*)<\/div>/U', $data, $matches))
      $special['desc'] = trim($matches[1]);
    else return null;

    if (preg_match('/Price>(.*)<\/div>/U', $data, $matches))
      $special['price'] = trim($matches[1]);
    else return null;
    
    if (preg_match('/Price2>(.*)<\/div>/U', $data, $matches))
      $special['price2'] = trim($matches[1]);
    else return null;

    if (preg_match('/img src=\'(.*)\'>/U', $data, $matches))
      $special['image'] = trim($matches[1]);
    else return null;
    
    return $special;
  }
  
  // get valid through date
  function safeway_getValidThroughDate($storeId) {
    $data = curl_hitUrl("http://safeway1.inserts2online.com/pageLarge.jsp?pageNumber=1&drpStoreId=$storeId");

    // <span style='width:100%' class='pricesgood'>Prices Valid Through&nbsp;07/24/2007</span>
    preg_match('/Prices Valid Through&nbsp;(\d*)\/(\d*)\/(\d*)<\/span>/', $data, $matches);
    
    return mktime(0, 0, 0, $matches[1], $matches[2], $matches[3]);
  }


  // --------------------------------------------------------------------------
  // RENDER PAGE
  // --------------------------------------------------------------------------

  safeway_clearCookies();

  //$stores = safeway_locateStore($_GET['zip']);
  
  $storeId = isset($_GET['storeId']) ? $_GET['storeId'] : 1108;

  $specials = safeway_getSpecials($storeId);
  $validThrough = safeway_getValidThroughDate($storeId);
  $pubDate = date(date('U', $validThrough) - (7 * 24 * 3600));
  $validThroughDate = date('l F j, Y', $validThrough);
  $weekOfSpecials = date('l F j, Y', $pubDate);

?>

<rss version="2.0">
<channel>
  <language>en</language>
  <title>Safeway Weekly Specials</title>
  <link>http://www.powdahound.com/safeway-specials/</link>
  <description>Safeway specials for the week starting on <?= $weekOfSpecials ?> (Store: <?= $storeId ?>)</description>
  <pubDate><?= date('D, d M Y G:i:s', $pubDate) ?> PST</pubDate>
  <lastBuildDate><?= date('D, d M Y G:i:s', $pubDate) ?> PST</lastBuildDate>
  <image>
    <url>http://www.powdahound.com/safeway-specials/safeway.gif</url>
    <title>Safeway</title>
    <link>http://www.powdahound.com/safeway-specials/</link>
  </image>
  <skipDays>
    <day>Wednesday</day>
    <day>Thursday</day>
    <day>Friday</day>
    <day>Saturday</day>
    <day>Sunday</day>
  </skipDays>
  
  <?
  foreach ($specials as $special) {
    echo '<item>';
    echo '<title>'.$special['name'].'</title>';
    
    echo '<description><![CDATA[';
    
    echo   $special['desc'].'<br />';
    echo   $special['price'].'<br />';
    echo   $special['price2'].'<br />';
    echo   '<img src="http://safeway1.inserts2online.com/'.$special['image'].'"><br />';
    echo   'Expires: '.$validThroughDate;
    
    echo ']]></description>';
    echo '</item>';
    
    echo "\n\n";
  }
  ?>
</channel>
</rss>