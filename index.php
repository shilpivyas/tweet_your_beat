
<?php echo session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <?php require_once 'header.php'; ?>
    <script type="text/javascript">
    var followers;
    jQuery(document).ready(function(){
        jQuery('.details').hide();
        jQuery(document).delegate('.view_details','click',function(){
            jQuery(this).parent().find('.details').slideToggle('slow');
        });
    
        var slider = jQuery('#tweetSlider').bxSlider({
            mode: 'fade',
            auto: true,
            infiniteLoop: true,
            touchEnabled:true,
            pause: 5000,
            autoHover : true,                        
            speed: 500,
            adaptiveHeight: true
        });

        jQuery( "#searchFollower" ).autocomplete({
            source: followers,
            focus: function( event, ui ) {
              jQuery( this ).val( ui.item.label );
              return false;
            },
            select: function( event, ui ) {
                var selectedValue = ui.item.value;
                
                jQuery( "#follower_screen_name" ).val( selectedValue );
                jQuery( this ).val( ui.item.label );
                jQuery.ajax({
                    url : 'get_followers_tweet.php',
                    data : 'screen_name='+selectedValue,
                    dataType : 'html',
                    success : function(data) {
                        jQuery('#tweetSlider').append(data);
                        slider.reloadSlider();
                        var arr     = {
                            'id'            : ui.item.id,
                            'name'          : ui.item.label,
                            'url'           : ui.item.profile_img,
                            'description'   : ui.item.description
                        };
                        var html    = jQuery('.new_follower').html(); 
                        jQuery.each(arr,function(key,value){
                            html = html.replace('%'+key+'%',value);
                        });
                        jQuery('.follower_block').append(html);
                        
                    },
                    failure : function() {
                        alert('Could not fetch followers tweets');
                    }
                });

                return false;
            },
            open: function() {
            
            },
            close: function() {
                jQuery(this).val('');
            }
        });
    });
    </script>
</head>
<body>
    
<?php 
require_once 'connection.php';
if (isset($_REQUEST['login']) && $_REQUEST['login'] == 'true') {
    // get the request token
    $reply = $cb->oauth_requestToken(array(
        'oauth_callback' => 'http://localhost/tweetyourbeat/',
    ));

    // store the token
    $cb->setToken($reply->oauth_token, $reply->oauth_token_secret);
    $_SESSION['oauth_token'] = $reply->oauth_token;
    $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;
    $_SESSION['oauth_verify'] = true;

    // redirect to auth website
    $auth_url = $cb->oauth_authorize();
//    print_r($auth_url);
//    die();
    header('Location: ' . $auth_url);
    die();

} elseif (isset($_GET['oauth_verifier']) && isset($_SESSION['oauth_verify'])) {
    // verify the token
    $cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
    unset($_SESSION['oauth_verify']);

    // get the access token
    $reply = $cb->oauth_accessToken(array(
        'oauth_verifier' => $_GET['oauth_verifier']
    ));

    // store the token (which is different from the request token!)
    $_SESSION['oauth_token'] = $reply->oauth_token;
    $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;

    // send to same URL, without oauth GET parameters
    header('Location: ' . basename(__FILE__));
    die();
}

// assign access token on each page load
$cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

//fetch tweets of user
$allTweets  = (array) $cb->statuses_homeTimeline();

$myTweets   = array();
$i =0 ;
foreach($allTweets as $tweet) {
    if(is_object($tweet)) {
        $myTweets[$i]['tweet']      = $tweet->text;
        $myTweets[$i]['tweeted_on'] = $tweet->created_at;
        $myTweets[$i]['tweeted_by'] = $tweet->user->screen_name;
    }
    $i++;
}
if(count($myTweets) < 10) {
    $myTopTenTweets = array_slice($myTweets, 0);
} else {
    $myTopTenTweets = array_slice($myTweets, 0, 10);
}

$followers      = (array) $cb->followers_list();
$myFollowers    = array();
$i = 0;
if(isset($followers['users']) && !empty($followers['users'])) {
    foreach($followers['users'] as $follower) {
        if(is_object($follower)) {
            $myFollowers[$i]['id']          = $follower->id;
            $myFollowers[$i]['label']       = $follower->name;
            $myFollowers[$i]['value']       = $follower->screen_name;
            $myFollowers[$i]['description'] = $follower->description;
            $myFollowers[$i]['profile_img'] = $follower->profile_image_url;
        }
        $i++;
    }
}
if(count($myFollowers) < 10) {
    $myTopTenFollowers = array_slice($myFollowers, 0);
} else {
    $myTopTenFollowers = array_slice($myFollowers, 0, 10);
}

?>
<div>
    
    <a id="totop" href="#"><i class="fa fa-angle-up"></i></a>
    <div id="wrapper">
    <?php require_once 'top_header.php'; ?>
    <div id="page-wrapper">
        <?php require_once 'breadcrumb.php'; ?>
        <div class="page-content">
            <div id="tab-blog">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <div class="title pull-left">Site Stats</div>
                                <div class="downloadBtn">
                                    <button type="button" data-toggle="dropdown" class="btn btn-yellow dropdown-toggle pull-right">
                                        Download <i class="fa fa-download "></i>
                                    </button>
                                    <ul class="dropdown-menu pull-right">
                                        <li><a href="#">Download CSV</a></li>
                                        <li><a href="#">Download XLS</a></li>
                                        <li><a href="#">Download PDF</a></li>
                                        <li><a href="#">Download JSON</a></li>
                                        <li><a href="#">Download XML</a></li>
                                        <li class="divider"></li>
                                        <li><a href="#">Export to Google Spreadsheet</a></li>
                                    </ul>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div class="panel-body">
                                <div class="site-stats">
                                    <div id="site-stats-chart" style="width: 100%; height:300px">
                                        <div class="slide-navigation">
                                        </div>

                                        <div class="slideshow" id="tweetSlider">
                                            <?php
                                            foreach ($myTopTenTweets as $tweet) {
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
                                        </div>
                                    </div>
                                    <br class="mbl"/>
                                    <hr/>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="st-top-posts">Follower</div>
                                            <ul class="top-post-list list-unstyled">
                                                <?php
                                                if(isset($myTopTenFollowers) && !empty($myTopTenFollowers)) {
                                                    foreach($myTopTenFollowers as $follower) {
                                                        ?>
                                                        <li><a href="#"><?php echo $follower['label'] ?></a><span class="st-views"><?php echo '@'.$follower['value'] ?></span>
                                                        </li>
                                                        <?php
                                                    }
                                                } else {
                                                    ?>
                                                    <li>
                                                        <a href="#"><?php echo 'No Followers Found...';?></a>
                                                    </li>
                                                    <?php
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="st-top-searches">Search Follower</div>
                                            <div>
                                                <input type="text" id="searchFollower" class="form-control" />
                                                <input type="hidden" id="follower_screen_name" />
                                            </div>
                                            <div class="follower_block">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="page-footer">
            <div class="copyright"></div>
        </div>
    </div>
    </div>
</div>
</body>
<script type="text/javascript">
    followers = <?php echo json_encode($myFollowers); ?>;
</script>
</html>

<div class="new_follower hide">
    <div class="follower_details" follower-id="%id%">
        <div class="view_details">
            <span class="pull-left">%name%</span>
            <span class="pull-right remove_investor"><i class="fa fa-times-circle"></i></span>
            <div class="clearfix"></div>
        </div>
        <div class="details">
            <img class="pull-left profile_pic" src="%image%" height="60" width="60" />
            <div class="pull-left description">%description%</div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>