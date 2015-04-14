<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
<title>Tweet-Your-Beat</title>
<link rel="stylesheet" href="lib/css/my_style.css" type="text/css" />
<link rel="stylesheet" href="lib/css/ui-lightness/jquery-ui-1.9.2.custom.min.css" type="text/css" />
<link rel="stylesheet" href="lib/css/cycle2.css" type="text/css" />
<script type="text/javascript" src="lib/js/jquery-1.8.3.js"></script>
<script type="text/javascript" src="lib/js/jquery-ui-1.9.2.custom.min.js"></script>

<script type="text/javascript" src="lib/jquery.cycle.all.js"></script>
<script type="text/javascript" src="lib/jquery.cycle2.js"></script>

<script type="text/javascript">
jQuery(document).ready(function() {
    
    jQuery('#searchUser').click(function(){
        jQuery.ajax({
            url         : 'search_request.php',
            data        : jQuery('#userForm').serialize(),
            dataType    : 'html',
            success     : function(data) {
                jQuery('#slideTweets').append(data);
                jQuery('#slideTweets').cycle({
                    fx: 'fade'
                });
            }
            
        });
    });
    
    jQuery('#downloadCsv').click(function(){
        jQuery('#downloadButtons').slideToggle('slow');
    });
    
});
</script>
<style type="text/css">
    #downloadButtons {
        background-color: #666;
        color:#FFF;
        width: 200px;
    }
    ul li {
        list-style: none;
    }
    ul li a {
        text-decoration: none;
        color : #FFF;
    }
</style>
</head>
<body>
    <?php
    require_once 'connection.php';
    $user = array();
    if ((isset($_REQUEST['login']) && $_REQUEST['login'] == 'true') || !isset($_SESSION['oauth_token'])) {
            $reply = $cb->oauth_requestToken(array(
                'oauth_callback' => 'http://localhost/tweetyourbeat'
            ));
            // store the token
            $cb->setToken($reply->oauth_token, $reply->oauth_token_secret);
            $_SESSION['oauth_token'] = $reply->oauth_token;
            $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;
            $_SESSION['oauth_verify'] = true;

            // redirect to auth website
            $auth_url = $cb->oauth_authorize();
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
        // assign access token on each page load
        $cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
        $user   = $cb->account_verifyCredentials();
        // send to same URL, without oauth GET parameters
        header('Location: ' . basename(__FILE__));
        die();
    }
    
//     assign access token on each page load
    $cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
//    $cb->setToken('138427770-bpZu34fezdXtijbHY7SHVtBMQJJOF0NuD43GoGUN','B2vDWA3FVuQi6OIRVTTX9G0DID8aBuJmnGbstqSCVZ0zM');
    //$user   = $cb->account_verifyCredentials();
    //get users latest tweets
    $myTweets = (array) $cb->statuses_homeTimeline();
    
    $i = 0;
    //$myTweets = array();
    ?>
    <div class=center>
    <span class="prev"><a href=#><< Prev</a></span>
    <span class="next" style="margin-left:20px"><a href=#>Next >></a></span>
    </div>
    <div id="slideTweets" class="cycle-slideshow" 
        data-cycle-fx=scrollHorz
        data-cycle-swipe=true
        data-cycle-swipe-fx=scrollHorz
        data-cycle-timeout=2000
        data-cycle-prev=".prev"
        data-cycle-next=".next"
        data-cycle-pager="#custom-pager"
        data-cycle-pager-template="<strong><a href=#> {{slideNum}} </a></strong>"
        data-cycle-slides="> div">
        
        <!-- empty element for pager links -->
        <!--<span class="cycle-pager"></span>-->
        <?php
        if(!empty($myTweets)) {
            foreach($myTweets as $tweet) {
                if(is_object($tweet) && $i < 10) {
                    echo '<div>'.$tweet->text.'</div>';
                }
                $i++;
            }
        }
        ?>
        <!-- empty element for pager links -->
        <span id="custom-pager" class="center"></span>
    </div>
    
    <?php
    $myFollowers = array();
    $followers = (array) $cb->followers_list();
    $i = 0;
    if(isset($followers['users']) && !empty($followers['users'])) {
        foreach($followers['users'] as $follower) {
            if(is_object($follower)) {
                $myFollowers[$i]['id']              = $follower->id;
                $myFollowers[$i]['name']            = $follower->name;
                $myFollowers[$i]['screen_name']     = $follower->screen_name;
                $myFollowers[$i]['description']     = $follower->description;
                $myFollowers[$i]['display_picture'] = $follower->profile_image_url;
            }
            $i++;
        }
    }
    $followersJson = json_encode($myFollowers);
    ?>
    <br /><br /><br />
    <div id="myFollowers">
        <?php
        $i = 0;
        foreach($myFollowers as $follower) {
            if($i < 10) {
                echo '<div>'.$follower['name'].'</div>';
            }
            $i++;
        }
        ?>
    </div>
    <div id="searchFollower">
        <form id="userForm">
        <input type="text" name="followerName" id="followerName" />
        <input type="button" id="searchUser" value="Search" />
        </form>
    </div>
    <div id="downloadButton">
        <input type="button" value="Download" id="downloadCsv" />
        <div id="downloadButtons" style="display: none">
        <ul>
            <li><a href="get_tweets.php?type=csv" >Download CSV</a></li>
            <li><a href="get_tweets.php?type=xls" >Download XLS</a></li>
            <li><a href="get_tweets_xml.php" >Download XML</a></li>
            <li><a href="get_tweets_json.php" >Download JSON</a></li>
            <li><a href="get_tweets_pdf.php" >Download PDF</a></li>
        </ul>
        </div>
    </div>

</body>
</html>