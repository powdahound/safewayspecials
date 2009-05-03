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
    <li>Go to <a href="http://safeway.com" target="_new">Safeway.com</a> and use the "Weekly Specials" link in the upper right to find the store you want.</li>
    <li>On the results page, click the "View Specials" button for the store you want.</li>
    <li>You should now see thumbnails of the print ads. Find the "Text Only Version" link below the thumbnail and get the ID number from the end of it (e.g. http://safeway.inserts2online.com/main_508.jsp?drpStoreID=<b>1108</b>)</li>
    <li>Put this ID in the form below and hit submit. (Make sure you enter it correctly &mdash; there is no validation.)</li>
  </ol>
   
  <h2>Get feed</h2>
  <form method="post" action="index.php">
    <label for "storeId">Store id:</label> <input type="text" name="storeId" size="5" id="storeId">
    <input type="submit" name="submit" value="Get feed">
  </form>
    
  <?php if (isset($_POST['storeId'])): ?>
  <?php $feedUrl = 'http://www.powdahound.com/safeway-specials/specials.php?storeId='.$_POST['storeId']; ?>
    
  <p>URL: <a href="<?php echo $feedUrl ?>"><?php echo $feedUrl ?></a></p>
  <p><a href="http://fusion.google.com/add?feedurl=<?php echo urlencode($feedUrl) ?>"><img src="http://buttons.googlesyndication.com/fusion/add.gif"></a></p>
  <p><a href="http://www.bloglines.com/sub/<?php echo urlencode($feedUrl) ?>">
<img src="http://static.bloglines.com/images/lang/default/sub_modern2.gif" border="0" alt="Subscribe with Bloglines" />
</a></p>
  <?php endif; ?>

  <h2>Questions?</h2>
  <p>Just <a href="http://www.powdahound.com/blog/about/">contact me</a> or <a href="http://github.com/powdahound/safewayspecials/">check out the source</a>.</p>
  
  </body>
</html>
