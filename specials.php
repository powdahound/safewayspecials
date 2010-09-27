<?php
  define('COOKIE_FILE', "/tmp/safewayspecials_cookies.txt");
  header("Content-type: text/xml");

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
    curl_setopt($h, CURLOPT_COOKIEJAR, COOKIE_FILE);
    curl_setopt($h, CURLOPT_COOKIEFILE, COOKIE_FILE);

    if ($postData != null) {
      curl_setopt($h, CURLOPT_POSTFIELDS, http_build_query($postData));
      curl_setopt($h, CURLOPT_POST, 1);
    }

    $doc = curl_exec($h);

    return $doc;
  }

  function safeway_clearCookies() {
    $h = fopen(COOKIE_FILE, 'w');
    fwrite($h, '');
    fclose($h);
  }

  function safeway_getCategories($storeId) {
    $data = curl_hitUrl("http://safeway.inserts2online.com/main_508.jsp?drpStoreID=1108");

    preg_match_all('/itemResult_508\.jsp\?catSearch=(.+)">/', $data, $matches);

    foreach ($matches[1] as $match) {
      $cats[] = urldecode($match);
    }

    return $cats;
  }

  function safeway_getSpecials($storeId) {
    $specials = array();

    // hit this URL to gather necessary cookies
    curl_hitUrl("http://safeway.inserts2online.com/storeReview.jsp?drpStoreID=$storeId&showFlash=false");

    $categories = safeway_getCategories($storeId);

    foreach ($categories as $category) {
      $categorySpecials = (array)safeway_getSpecialsInCategory($category);
      foreach ($categorySpecials as $special) {
        $specials[] = $special;
      }
    }

    return $specials;
  }

  function safeway_getSpecialsInCategory($category) {
    $data = curl_hitUrl("http://safeway.inserts2online.com/itemResult_508.jsp?showAllItem=100&catSearch=".urlencode($category));

    // each row in table represends a single special
    preg_match_all('/<tr class="(?:odd|even)Color">(.+)<\/tr>/sU', $data, $matches);

    $specials = array();
    foreach ($matches[1] as $str) {
      $specialData = safeway_parseSpecial($str);

      if ($specialData) {
        $specialData['category'] = $category;
        $specials[] = $specialData;
      }
    }

    return $specials;
  }

  function safeway_parseSpecial($data) {
    $special = array();

    if (preg_match('/id="itemName\d+">(.+)<\//U', $data, $matches)) {
      $special['name'] = htmlspecialchars(trim($matches[1]));
    } else {
      return null;
    }

    if (preg_match('/width="\*">(.*)<\/td>/U', $data, $matches)) {
      $special['desc'] = trim($matches[1]);
    } else {
      return null;
    }

    if (preg_match('/id="itemPrice\d+".+>(.*)<\/td>/U', $data, $matches)) {
      $special['price'] = trim($matches[1]);
    } else {
      return null;
    }

    if (preg_match('/width="20%">(.*)<\/td>/U', $data, $matches)) {
      $special['savings'] = trim($matches[1]);
    } else {
      return null;
    }

    if (preg_match('/width="80px">(.*)<\/td>/U', $data, $matches)) {
      $special['date'] = trim($matches[1]);
    } else {
      return null;
    }

    return $special;
  }

  // get valid through date
  function safeway_getValidThroughDate($specials) {
    $date = $specials[0]['date'];
    list($start, $end) = explode('-', $date);
    return strtotime($end);
  }


  // --------------------------------------------------------------------------
  // RENDER PAGE
  // --------------------------------------------------------------------------

  safeway_clearCookies();

  $storeId = isset($_GET['storeId']) ? $_GET['storeId'] : 1108;

  $specials = safeway_getSpecials($storeId);
  $validThrough = safeway_getValidThroughDate($specials);
  $pubDate = date(date('U', $validThrough) - (7 * 24 * 3600));
  $validThroughDate = date('l F j, Y', $validThrough);
  $weekOfSpecials = date('l F j, Y', $pubDate);

  echo '<?xml version="1.0" encoding="UTF-8"?>';
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
    echo "  <item>\n";
    echo "    <title>".htmlentities($special["name"])."</title>\n";
    echo "    <description><![CDATA[\n";
    echo "    Description: ".$special["desc"]."<br />\n";
    echo "    Category: ".$special["category"]."<br />\n";
    echo "    Price: ".$special["price"]."<br />\n";
    echo "    Savings: ".$special["savings"]."<br />\n";
    echo "    Price valid: ".$special["date"]."<br />\n";
    echo "    ]]></description>\n";
    echo "  </item>\n";
  }
  ?>
</channel>
</rss>
