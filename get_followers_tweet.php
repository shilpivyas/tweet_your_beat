<?php

require_once 'connection.php';

session_start();
$cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
$tweets = $cb->statuses_userTimeline(array('screen_name' => $_REQUEST['screen_name']));

$myTweets   = array();
$i =0 ;
foreach($tweets as $tweet) {
    if(is_object($tweet) && $i < 10) {
        $myTweets[$i]['tweet']      = $tweet->text;
        $myTweets[$i]['tweeted_on'] = $tweet->created_at;
        $myTweets[$i]['tweeted_by'] = $tweet->user->screen_name;
    }
    $i++;
}

foreach ($myTweets as $tweet) {
?>
    <div>
        <blockquote>
            <p class="tweet"><?php echo $tweet['tweet'] ?></p>
            <footer>Tweeted By 
                <cite title="Source Title">
                    <?php echo $tweet['tweeted_by'] ?>
                </cite> 
                 on <?php echo $tweet['tweeted_on'] ?>
            </footer>
        </blockquote>
    </div>
<?php
}
?>
