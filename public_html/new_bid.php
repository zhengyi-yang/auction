<?php
  session_start();


  require_once("../resources/email.php");
  require_once("../resources/dbconnection.php");
  require_once("../resources/config.php");

  if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
  }

  $query = "select * from rating where rated_by='".$_SESSION['user_id']."' and is_pending='1'";
  $result = mysqli_query($connection, $query);
  if ($rating = mysqli_fetch_array($result)) {
    header("Location: rating.php?id=".$rating['rating_id']);
  }

  if ($_SESSION['user_type'] == "seller") {
    header("Location: main.php");
  }

  if (!isset($_GET['auction']) || $_GET['auction'] == '') {
    header("Location: search.php");
  }

  function validate_price($price) {
    $pattern = '/^[1-9][0-9]*\.[0-9]{2}$/';
    return preg_match($pattern, $price);
  }

  $query = "select * from auction where auction_id='".$_GET['auction']."'";
  $result = mysqli_query($connection, $query);
  $auction = mysqli_fetch_array($result);

  $current_price = $auction['current_price'];

  $query = "select * from user where user_id='".$auction['seller_id']."'";
  $result = mysqli_query($connection, $query);
  $seller = mysqli_fetch_array($result);

  $query = "select * from user where user_id='".$_SESSION['user_id']."'";
  $result = mysqli_query($connection, $query);
  $bidder = mysqli_fetch_array($result);

  $query = "select b.price, u.user_id, u.name, u.email_address
            from bid as b
            left join user as u
            on b.bidder_id=u.user_id
            where b.auction_id=".$auction['auction_id']."
            order by b.price desc
            limit 1";
  $result = mysqli_query($connection, $query);
  $highest_bid = mysqli_fetch_array($result);

  $query = "select * from item where item_id='".$auction['item_id']."'";
  $result = mysqli_query($connection, $query);
  $item = mysqli_fetch_array($result);

  $message = '';

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['bid-price'])) {
      $message .= 'You need to set bid price.\n';
    } elseif (!validate_price($_POST['bid-price'])) {
      $message .= 'Enter bid price in valid format. (e.g. 50.00)';
    } elseif ($_POST['bid-price'] <= $current_price) {
      $message .= 'You need to bid more than the current price.';
    } elseif ($_POST['bid-price'] > 99999999.99) {
      $message .= 'You cannot bid more than \u00A399999999.99';
    } else {
      $bid_price = mysqli_real_escape_string($connection, $_POST['bid-price']);
    }
    if ($message != '') {
      echo "<script type='text/javascript'>alert('$message');</script>";
    } else {
      mysqli_query($connection, "insert into bid(bidder_id, auction_id, price, created_at) values('".$_SESSION['user_id']."','".$auction['auction_id']."','".$bid_price."',NULL)");
      mysqli_query($connection, "update auction set current_price=".$bid_price." where auction_id=".$auction['auction_id']."");
      $sender = new email_sender();
      $sender->send($highest_bid['email_address'],
                    'You Are Outbid!!',
                    'Your bid on <a href="http://ec2-52-58-25-40.eu-central-1.compute.amazonaws.com/auction.php?auction='.$auction['auction_id'].'">'.$item['name'].'</a> (&#163; '.$highest_bid['price'].') has been outbid!<br>
                    <a href="http://ec2-52-58-25-40.eu-central-1.compute.amazonaws.com/user.php?user='.$bidder['user_id'].'">'.$bidder['name'].'</a> placed a bid of &#163; '.$bid_price.'.<br>
                    Don\'t let it get away!<br>
                    Go to the website and bid again!');
      $sender->send($seller['email_address'],
                    'You Auction Got a New Bid!!',
                    'Your auction (<a href="http://ec2-52-58-25-40.eu-central-1.compute.amazonaws.com/auction.php?auction='.$auction['auction_id'].'">'.$item['name'].'</a>) got a new bid.<br>
                    <a href="http://ec2-52-58-25-40.eu-central-1.compute.amazonaws.com/user.php?user='.$bidder['user_id'].'">'.$bidder['name'].'</a> placed a bid of '.'&#163; '.$bid_price.'.<br>
                    This auction has got '.$auction['view_count'].' view(s) so far.');
      echo "<script type='text/javascript'>alert('Your bid placed successfully.');</script>";
    }
  }

  $date = time();
  $date = date('Y-m-d H:i:s', $date);

  require_once(TEMPLATES_PATH . '/top_bar.php');
?>
<h1>
  Place a Bid
</h1>
<div class="panel panel-default" id="auction-detail">
  <div class="panel-heading">
    <h3 class="panel-title">Auction Detail</h3>
  </div>
  <div class="panel-body">
    <span class="auction-info">Seller: <?php echo "<a href='user.php?user=".$seller['user_id']."'>".$seller['name']."</a>";?></span>
    <span class="auction-info">Item: <?php echo "<a href='auction.php?auction=".$auction['auction_id']."'>".$item['name']."</a>";?></span>
    <span class="auction-info">Description: <?php echo $item['description'];?></span>
    <span class="auction-info">End Date: <?php echo $auction['end_date']; if ($date >= $auction['end_date']) {echo ' (Already Ended)';}?></span>
    <span class="auction-info">Start Price: &#163; <?php echo $auction['start_price']?></span>
    <span class="auction-info">Current Price: &#163; <?php echo $current_price;?></span>
  </div>
</div>
<div class="center-block col-xs-6" id="bid-form">
  <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]).'?auction='.$auction['auction_id'];?>" class="clearfix" id="add-bid">
    <?php
      if ($date >= $auction['end_date']) {
        echo "<span>You cannot bid on this auction as it has already ended..</span>";
      } else {
        echo "<span>You need to bid more than the current price.</span>";
      }
    ?>
    <div class="input_group">
    <input name="bid-price" type="text" class="form-control" placeholder="How much do you want to bid? (e.g. 50.00)" <?php if ($date >= $auction['end_date']) {echo 'disabled';}?>>
    </div>
    <input name="action" type="hidden" value="new-bid">
    <input type="submit" value="submit" name="submit" class="btn btn-default pull-right" <?php if ($date >= $auction['end_date']) {echo 'disabled';}?>>
  </form> 
</div>

<?php
  require_once(TEMPLATES_PATH . '/bottom_bar.php');
?>
