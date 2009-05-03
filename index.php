<html>
  <head>
    <title>Safeway Weekly Specials RSS</title>
    <style>
      body, p {
        font-family: verdana, sans-serif;
        font-size: 12px;
      }
      img {
        border: 0;
      }
    </style>
  </head>
  <body>
  
  <h1>Safeway Weekly Specials RSS</h2>
  <h2>How to use</h2>
  <ol>
    <li>Go to <a href="http://safeway.com" target="_new">Safeway.com</a> and use the "View Weekly Specials" search in the lower left to find the store you want</li>
    <li>On the results page, select the "Weekly Specials" link for the store you want.</li>
    <li>You should now be seeing a list of weekly specials. Look at the URL and remember the ID at the very end. <i>(ex: http://shop.safeway.com/superstore/sixframeset.asp?mainurl=http://safeway1.inserts2online.com/storeReview.jsp?drpStoreID=<b>1108</b>)</i>
    <li>Put this ID in the form below and hit submit. (Make sure you enter it correctly &mdash; there is no validation.)</li>
  </ol>
   
  <h2>Enter store id</h2>
  <form method="post" action="index.php">
    <label for "storeId">Store id:</label> <input type="text" name="storeId" size="5" id="storeId">
    <input type="submit" name="Get feed" value="Get feed">
  </form>
    
  <?
    if (@$_POST['storeId']):
      $feedUrl = 'http://www.powdahound.com/safeway-specials/specials.php?storeId='.$_POST['storeId'];
  ?>
    
  <h2>Your store's feed</h2>
  <p>URL: <a href="<?= $feedUrl ?>"><?= $feedUrl ?></a></p>
  <p><a href="http://fusion.google.com/add?feedurl=<?= urlencode($feedUrl) ?>"><img src="http://buttons.googlesyndication.com/fusion/add.gif"></a></p>
  <p><a href="http://www.bloglines.com/sub/<?= urlencode($feedUrl) ?>">
<img src="http://static.bloglines.com/images/lang/default/sub_modern2.gif" border="0" alt="Subscribe with Bloglines" />
</a></p>
  <? endif; ?>

  <h3>If you have questions... <a href="http://www.powdahound.com/blog/about/">contact me</a>!</h3>
  
  </body>
</html>