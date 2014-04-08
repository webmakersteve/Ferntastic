<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width">
<title>Login | 91ferns</title>
<link href="http://fonts.googleapis.com/css?family=Roboto:regular,medium,thin,italic,mediumitalic"
    rel="stylesheet" title="roboto">
<link rel="shortcut icon" href="http://secure.91ferns.com/favicon.ico">
<style type="text/css">
body {
	font-family: 'Roboto', Open Sans, helvetica, arial, sans-serif;	
	-webkit-font-smoothing: antialiased;
	color: #555;
}
h1 {
	text-align: center;
	-webkit-font-smoothing: antialiased;
	color: #555;
	font-size: 42px;
	font-weight: 300;
	margin-top: 0;
	margin-bottom: 20px;
}

button {cursor: pointer;}
h2 {
	text-align: center; 
	font-size: 18px;
	font-weight: 400;
	margin-bottom: 20px;
}
input[type="checkbox"] {
	-webkit-appearance: none;
	display: inline-block;
	width: 13px;
	height: 13px;
	margin: 0;
	cursor: pointer;
	vertical-align: bottom;
	background: #fff;
	border: 1px solid #c6c6c6;
	-moz-border-radius: 1px;
	-webkit-border-radius: 1px;
	border-radius: 1px;
	-moz-box-sizing: border-box;
	-webkit-box-sizing: border-box;
	box-sizing: border-box;
	position: relative;
}

.card {
	background-color: #f7f7f7;
	padding: 20px 25px 30px;
	margin: 0 auto 25px;
	width: 70%;
	-moz-border-radius: 2px;
	-webkit-border-radius: 2px;
	border-radius: 2px;
	-moz-box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
	-webkit-box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
	box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
	max-width: 304px;
}

.header {
	width: 200px;
	margin: 0 auto;
	margin-top: 35px;
	margin-bottom: 30px;
}
body {padding: 0; margin: 0;}
.header img {width: 100%;}

.card .form-group *:hover,
.card .form-group *:focus {
	outline: none;	
}

.card .form-group .form-control {
	width: 100%;
	display: block;
	margin-bottom: 10px;
	z-index: 1;
	position: relative;
	-moz-box-sizing: border-box;
	-webkit-box-sizing: border-box;
	box-sizing: border-box;	
	height: 44px;
	font-size: 16px;
	padding: 0 8px;
	margin: 0;
	background: #fff;
	-webkit-border-radius: 1px;
	border-radius: 1px;
	color: #404040;
	border: 1px solid #b9b9b9;
}
.card .form-group .form-control:hover {
	border: 1px solid #b9b9b9;
	border-top: 1px solid #a0a0a0;
	-moz-box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
	-webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
	box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
}

.card .form-group {padding: 5px 0;}
.btn:hover {
	opacity: 1;
}
.btn {
	opacity: 0.9;
	padding: 10px 16px;
	line-height: 1.33;
	background-color: #056838;
	text-align: center;
	white-space: nowrap;
	vertical-align: middle;
	display: inline-block;
	color: white;
	font-weight: normal;
	font-size: 13px;
	text-shadow: none;
	border: none;
	text-transform: uppercase;
	-webkit-transition: all 200ms ease-in;
	-o-transition: all 200ms ease-in;
	-moz-transition: all 200ms ease-in;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	-webkit-box-shadow: inset 0 -4px 0 rgba(0,0,0,0.15);
	-moz-box-shadow: inset 0 -4px 0 rgba(0,0,0,0.15);
	box-shadow: inset 0 -4px 0 rgba(0,0,0,0.15);	
}
input[type=checkbox]:checked::after {
	content: url(http://ssl.gstatic.com/ui/v1/menu/checkmark.png);
	display: block;
	position: absolute;
	top: -6px;
	left: -5px;
}

.cb {cursor: pointer;}

.create-account {
	text-align: center; font-size: 14px; padding-bottom: 50px;	
}

.avatar {
	text-align: center;
	padding: 18px 0 18px;	
}

.avatar img {
	-webkit-border-radius: 50%;
	border-radius: 50%;	
	border: 1px solid #e5e5e5;
	max-width: 70px;
	width: 70px;
}
.swatch-darkgreen {
	color: #056838;
}

a, a:link, a:visited {
	text-decoration: none;color: #428bca;	
}
a:hover,a:focus,a:active {text-decoration: underline;}

@media (max-width: 768px) {
	h1 {font-size: 30px; margin-bottom: 8px;}
	.header {
		margin-top: 10px; margin-bottom: 10px;	
	}
	h2 { margin-bottom: 10px;}
}
</style>

</head>

<body>

<div class="header">
	<img alt="Logo" src="http://secure.91ferns.com/img/91ferns.png">
</div>

<div class="title">
	<h1>Your <span class="swatch-darkgreen">91ferns</span> Account</h1>
    <h2>Login to continue to 91ferns.com</h2>
</div>

<div class="card">
	
    <div class="avatar">
    	<img src="https://pbs.twimg.com/profile_images/2312289015/txths4si4qre3cu62etr_normal.png" alt="91ferns Logo">
    </div>
    <?php if (isset($error)): ?>
    <div class="form-error"><?=$error?></div>
    <?php endif; ?>
    <form method="post" action="<?=$_SERVER['REQUEST_URI']?>">
    
    	<div class="form-group">
        	<input type="text" class="form-control" name="Email" placeholder="Email">
        </div>
        
    	<div class="form-group">
        	<input type="password" class="form-control" name="Password" placeholder="Password">
        </div>
        
        <div class="form-group">
        	<button type="submit" class="btn btn-default" style="width:100%;">Sign In</button>
        </div>
        
        <div class="form-group">
        	<label class="cb"><input type="checkbox" name="Remember" value="1"> <span style="font-size: 13px;">Stay signed in</span></label>
        </div>
    	<input type="hidden" name="uid" value="<?=$_GET['uid'] ? $_GET['uid'] : 0?>">
        <input type="hidden" name="callback" value="<?=$_GET['callback'] ? $_GET['callback'] : "/"?>">
    </form>
    
</div>

<div class="create-account">
	<a href="/signup.html">Create an account</a>
</div>

<!--div class="ad">
	<p>One account for all 91ferns sites.</p>
</div-->

</body>
</html>