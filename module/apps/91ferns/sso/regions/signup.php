<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width">
<title>Sign Up | 91ferns</title>
<link href="http://fonts.googleapis.com/css?family=Roboto:regular,medium,thin,italic,mediumitalic"
    rel="stylesheet" title="roboto">
<link rel="shortcut icon" href="http://secure.91ferns.com/favicon.ico">
<style type="text/css">
body {
	font-family: 'Roboto', Open Sans, helvetica, arial, sans-serif;	
	-webkit-font-smoothing: antialiased;
	color: #555;
}
a, a:link, a:visited {
	text-decoration: none;color: #428bca;	
}
a:hover,a:focus,a:active {text-decoration: underline;}
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
	width: 304px;
	-moz-border-radius: 2px;
	-webkit-border-radius: 2px;
	border-radius: 2px;
	-moz-box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
	-webkit-box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
	box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);	
}

.header {
	width: 200px;
	margin: 0 auto;
	margin-top: 35px;
	margin-bottom: 10px;
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

.swatch-darkgreen {
	color: #056838;
}
.form-group {margin-bottom: 6px;}
.form-group label span {display: block; font-weight: bold; font-size: 13px; margin-bottom: 3px;}
.form-group.double .form-control {
	width: 49%;
}
.form-group.double .form-control:nth-child(2) {float: right;}
.form-group .form-control {width: 100%;}

.form-group .cb span {
	display: inline; font-weight: 100;
}

.pseudo-select {
	-webkit-border-radius: 2px;
	-moz-border-radius: 2px;
	border-radius: 2px;
	background-color: #f5f5f5;
	background-image: -webkit-gradient(linear,left top,left bottom,from(#f5f5f5),to(#f1f1f1));
	background-image: -webkit-linear-gradient(top,#f5f5f5,#f1f1f1);
	background-image: -moz-linear-gradient(top,#f5f5f5,#f1f1f1);
	background-image: -ms-linear-gradient(top,#f5f5f5,#f1f1f1);
	background-image: -o-linear-gradient(top,#f5f5f5,#f1f1f1);
	background-image: linear-gradient(top,#f5f5f5,#f1f1f1);
	border: 1px solid #dcdcdc;
	color: #444;
	font-size: 11px;
	font-weight: bold;
	line-height: 27px;
	list-style: none;
	margin: 0 2px;
	min-width: 46px;
	outline: none;
	padding: 0 18px 0 6px;
	text-decoration: none;
	vertical-align: middle;	
	font-size: 13px;
	cursor: pointer;
}
body {padding-bottom: 40px;}
</style>
<style type="text/css">
@media (max-width: 768px) {
	h1 {font-size: 22px;}
	
	.card {
		background-color: transparent;
		padding: 20px 25px 30px;
		margin: 0 auto 25px;
		width: 304px;
		-moz-border-radius: 0;
		-webkit-border-radius: 0;
		border-radius: 0;
		-moz-box-shadow: none;
		-webkit-box-shadow: none;
		box-shadow: none;	
	}
	body {
		background-color: #f7f7f7;	
	}

}

</style>
</head>

<body class="signup">

<div class="header">
	<img alt="Logo" src="http://secure.91ferns.com/img/91ferns.png">
</div>

<div class="title">
	<h1>Create Your <span class="swatch-darkgreen">91ferns</span> Account</h1>
</div>

<div class="card">
    
    <form action="">
    
    	<div class="form-group double">
        	<label><span>Name</span>
	        	<input type="text" class="form-control" name="LName" placeholder="Last" tabindex="2">
    	        <input type="text" class="form-control" name="FName" placeholder="First" tabindex="1">
            </label>
        </div>
    
    	<div class="form-group">
        	<label><span>Email</span>
        	<input type="text" class="form-control" name="User" tabindex="3"></label>
        </div>
        
    	<div class="form-group">
        	<label><span>Password</span>
        	<input type="password" class="form-control" name="Passwd" tabindex="4"></label>
        </div>
        
        <div class="form-group">
        	<label><span>Confirm password</span>
        	<input type="password" class="form-control" name="CPasswd" tabindex="5"></label>
        </div>
        
        <div class="form-group">
        	<label><span>Referral Code</span>
            <input type="text" class="form-control" name="Ref" placeholder="(optional)" tabindex="6">
        </div>
        
        <div class="form-group">
        	<label><span>Mobile Phone</span>
            <input type="text" class="form-control" name="Phone" tabindex="7">
        </div>
        
        <hr style="border: none; border-top: 1px solid #e5e5e5;">
        
        <div class="form-group">
        	<label class="cb"><input tabindex="8" type="checkbox" name="Remember" value="1"> <span style="font-size: 13px;">I agree to the terms of service and privacy policy.</span></label>
        </div>
        
        <div class="form-group">
        	<button tabindex="9" type="submit" class="btn btn-default" style="width:100%;">Create Account</button>
        </div>
    
    </form>
    
</div>

<!--div class="ad">
	<p>One account for all 91ferns sites.</p>
</div-->

</body>
</html>