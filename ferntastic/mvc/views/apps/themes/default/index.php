<?php

if (isset($_GET['unsave'])) {
	
	setcookie( '91fus', null, -300 );
	header( "Location: /" );
	exit;
		
}

if (isset($_GET['site'])) {
	if ($_GET['site'] == "dt") {
	
		setcookie('currsite',  base64_encode(md5('dennistaylor')), 0, '/', '', 1); 
		$GLOBALS['currsite'] = base64_encode(md5('dennistaylor'));
		
	} else {
		
		setcookie('currsite', '', time()-3500, '/', '', 1);
		$remove = true;
		
	}
}

if (isset($_COOKIE['currsite']) && !isset($remove)) {
	
	$GLOBALS['currsite'] = $_COOKIE['currsite'];

}

define('HTTP', 'http://testing.91ferns.com/');
$error = false;

if (is_logged_in()) {
	header("Location: /account.php");
	exit;
}

if (isset($_POST['op']) and $_POST['op'] == "login"):
	
	$error = &$_SESSION['formerror'];
	$error = false;
	if (!isset($_POST['Email']) or !filter_var($_POST['Email'], FILTER_VALIDATE_EMAIL)) $error = array('msg' => "<span class=\"formerror\">Please enter a valid email</span>", "field" => "email");
	elseif (!isset($_POST['Password']) or strlen($_POST['Password']) < 4) $error = array('msg' => "<span class=\"formerror\">Please enter in your password</span>", "field" => "password");
	else {
		
		//structure should be [domain].[extension], but [extension] can have
		$l = login(array("Email" => $_POST['Email'], "Passwd" => $_POST['Password'], "Remember" => isset($_POST['Remember']) ? $_POST['Remember'] : false), &$error, &$data);
		
		if ($l) {
			$domain = $data['domain'];
			//Domain has to be correct or it wouldn't log them in. Use that
			Fn()->load_extension('fquery');
			if ($domain == "") $domain = "91ferns.com";
			$site = Fn()->fQuery( 'sites', 'domain[x=?],idstring:limit(1)', $domain );
			
			if ($site->count>0) {
				$t = $site->this();
				setcookie('currsite', base64_encode(md5($t->idstring)), 0, '/', '', 1);
				setcookie('currsite2', md5($t->idstring), 0);
				setcookie('currsitetext', $t->idstring, 0);
			} else {
				setcookie('currsite', '', time()-20000);	
			}
			header("Location: ".urldecode($_POST['continue']));
			exit;
		} else {
			
			//login error
			$error = $error;
		}
		
	}

endif; //end login action

?><!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<link rel="shortcut icon" href="/favicon.ico">

<link rel="stylesheet" type="text/css" href="/style/static.css">
<link rel="stylesheet" type="text/css" href="/style/buttons.css">
<link rel="stylesheet" type="text/css" href="/style/nolog.css">
<title>Login | Service by 91ferns</title>
<style type="text/css">

.rectangle-button {}
#full-wrapper {}
#full-wrapper .head {}
#full-wrapper .head h1 {font-size: 3.65em;font-weight:400; padding-bottom: .62em;}
#full-wrapper .head h2 {display:none;}
#full-wrapper p {font-size: 1.25em; line-height: 1.54em; width: 520px;padding-bottom: 1.85em;}

#full-wrapper .login-aside-new {}
#full-wrapper .login-aside-new h2 {font-weight: 500; font-size: 2.3em; padding-bottom: .6em;}

#full-wrapper .login-aside-new .input-div, #full-wrapper .login-aside-new .form-row {padding-bottom: 1.3em;}
#full-wrapper .login-aside-new .input-div label, #full-wrapper .login-aside-new .input-div input {
	display:block;clear:both;
}

.input-div label {font-size: 1.25em; padding-bottom: 6px;}
.input-div input {width: 440px; padding: 0.4em 1.0em; border: 1px solid #E5E5E5; outline: none; background-color: !important white; outline: none !important;}
.input-div input:hover {border-color: #999;}

.input-div input:focus,.input-div input:active, .input-div input:hover:focus, .input-div input:hover:active {
	box-shadow: 0 0 2px #39B54A;
	border-color: #39B54A;
}

.ferns-submit {font-size: 1.3em; padding-left: 40px; padding-right: 40px;}

.form-row a,.form-row a:link,.form-row a:visited {text-decoration:none;}
.form-row a:hover {text-decoration:underline;}

#form-error {
	font-weight:bold;
	padding-bottom:8px;
	font-size: 1.25em;
}

strong.formerror {color:black;}
input.formerror {border-color:red;color:black;}

</style>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript">

$(function() {
	$('.formerror').click(function() {
		$(this).parent('.input-div').children('.formerror').removeClass('formerror');
	}).focus(function() {
		$(this).parent('.input-div').children('.formerror').removeClass('formerror');
	});
	
});
</script>
</head>

<body>

<noscript>
	<style type="text/css">.site-inner-wrapper, {display: none;}</style>
    <!-- NO JS -->
</noscript>

<?php if (isset($_COOKIE['91fus'])) {
	
	$sql = "SELECT * FROM accounts WHERE SHA1(MD5(username)) = '%s' LIMIT 1";
	$sql = sprintf( $sql, $_COOKIE['91fus'] );
	
	query( $sql );
	if (num_rows() == 1) {
		
		$row = assoc();
		$username = $row['username'];	
		$domain = $row['domain'];
		$prelog = true;
		
	}
	
}?>


<div class="wrapper">

    <div class="site-inner-wrapper">
        <div class="header">
            
            <?php the_header();?>
            
        </div>
    
        <div id="body">
        
        	<div id="full-wrapper">
        
        	<div class="head">
                <h1><?=Fn()->resources->strings->login_title?></h1>
            </div>
                <p>
                <?=Fn()->resources->strings->login_intro?>
                </p>
        
                <div class="login-aside-new">
                <form action="/" method="post">
                    <h2><?=R()->strings->login_form_title?></h2>
                    <?php if (is_array($error) and isset($error['msg'])) echo '<div id="form-error">'.$error['msg'].'</div>'; ?>
                    <div class="input-div username">
                        <?php if ($prelog): ?>
                        <label><strong<?php if (is_array($error) and $error['field'] == "email") echo ' class="formerror"';?>><?=R()->strings->login_email_field?></strong></label>
                        <div class="alt-text"><?=$username?></div>
                        <input type="hidden" value="<?=$username?>" name="Email">
                        <?php else: ?>
                        <label for="Email">
                            <strong<?php if (is_array($error) and $error['field'] == "email") echo ' class="formerror"';?>><?=Fn()->resources->strings->login_email_field?></strong>
                        </label>
                        <input id="Email" autocomplete="off" spellcheck="false" type="email" value="<?=(isset($_POST['Email']))?$_POST['Email']:""?>" name="Email" spellcheck="false"<?php if (is_array($error) and $error['field'] == "email") echo ' class="formerror"';?>>
                        <?php endif; ?>
                    </div>
                    
                    <div class="input-div password">
                        <label for="Password">
                            <strong<?php if (is_array($error) and $error['field'] == "password") echo ' class="formerror"';?>><?=Fn()->resources->strings->login_password_field?></strong>
                        </label>
                        <input id="Password" type="password" value="" name="Password" spellcheck="false" autocomplete="false"<?php if (is_array($error) and $error['field'] == "password") echo ' class="formerror"';?>>
                    </div>
                    <input type="hidden" value="login" name="op">
                    <input type="hidden" name="continue" value="<?=isset($_GET['continue']) ? $_GET['continue'] : '/account.php'?>">
                    <div class="form-row">
    
                        <label class="checkbox-single" for="Remember">
                            <input id="Remember" name="Remember_me" type="checkbox" value="1">
                            <strong class="checkbox-label"><?=Fn()->resources->strings->login_remember_field?></strong>
                        </label>
                
                    </div>
                    
                    <div class="form-row">
                    	<input type="submit" value="Log in" class="ferns-submit rectangle-button green">
                    </div>
                    
                    <div class="form-row">
                        <a href="#"><?=Fn()->resources->strings->login_noremember_label?></a>
                    </div>
                    <?php if (isset($prelog) and $prelog): ?><div class="form-row"><a href="/?unsave"><?=Fn()->resources->strings->login_diffuser_label?></a></div><?php endif; ?>
                </form>
                </div> <!-- .aside -->
                
            </div> <!-- .article -->
        
        </div> <!-- .section -->
        
    </div>
    
    <div class="push"></div>

</div>

<footer class="footer">
    
    <? footer(); ?>
    
</footer>
    


</body>
</html>