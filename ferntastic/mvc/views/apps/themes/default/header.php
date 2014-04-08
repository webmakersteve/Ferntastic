<?php if (!defined('ABSPATH')) {header("Location: /");exit;} ?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="msapplication-navbutton-color" content="#35845F" />
<meta name="msapplication-window" content="width=1024;height=768" />
<link rel="shortcut icon" href="/img/favicon32.ico">

<link rel="stylesheet" type="text/css" href="/style/static.css">
<link rel="stylesheet" type="text/css" href="/style/buttons.css">
<link href="/style/account.css" rel="stylesheet" type="text/css">
<link href="/style/autocomplete.css" rel="stylesheet" type="text/css">
<title><?=title()?> | 91ferns Web Development</title>

<style type="text/css">
#app-container .app-menu li.app-of-site {padding : 6px 11px 6px 15px;}
#app-container .app-menu li.app-of-site:hover,
#app-container .app-menu li.app-of-site:focus,
#app-container .app-menu li.app-of-site:active {padding: 5px 10px 5px 14px;}

#app-container .app-menu li.site {font-weight: bold;}

#app-container .app-menu li.site:hover,
#app-container .app-menu li.site:focus,
#app-container .app-menu li.site:active {border: 0px;background: none;padding : 6px 11px 6px 7px;}

div.footer {background-color:white;}
.modal {display:none;position:fixed;width:100%;height:100%;left:0;top:0;bottom:0;right:0;margin:0;padding:0;}

</style>

<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
<script type="text/javascript" src="/script/default.js"></script>
<script type="text/javascript" src="/script/pageloader.js"></script>

<!-- SITE SPECIFIC -->
<? head(); ?>
<!-- END SITE SPECIFIC -->
</head>
<body>
<?php if (!is_logged_in()): ?>
<div id="header-button">
	<a href="https://secure.91ferns.com/register" class="rectangle-button green"><?=Fn()->resources->strings->new_acc_button?></a>
</div>
<?php else: ?>
<div id="logged-in-side">
	<?php
		$id = Fn()->account()->id;
		Fn()->load_extension('fquery');
		$fq = fQuery('accounts', 'id[x=?],*', $id);
		$row = $fq->this();
		
		$name = sprintf("%s %s", $row->row('firstname'), $row->row('lastname') );
		$email = $row->row('username');
		
		$domain = $row->row('domain');
		$managed = !strstr($domain, "91ferns.com"); //it isn't managed if 91ferns is in the name
		
	?>
    <div id="logged-in-header">Welcome, <a href="javascript: void(0);" id="acc-link"><?=$name?></a></div>
    
	<div id="logged-in-content" style="display: none;">
        
		<div class="content">
        	
            <div class="left" style="">
            	
            </div>
            
            <div class="right">
            	<strong><?=$name?></strong><br>
            	<span class="ferns"><?=$email?></span><br>
                <?=$domain?><br>
                <a href="/apps/settings">Manage Account</a>
            </div>
            
        </div>
        
       	<div class="footer">
    	
    	    <p><a href="/account.php?logout=true" class="rectangle-button green">Logout</a></p>
        
	    </div>
        
    </div>

</div>
<?php endif; ?>
<div id="header-logo">
   <a href="https://secure.91ferns.com"><img alt="Logo" src="<?php
    	
		if (if_is_site()) {
			$sitestr = base64_decode(get_site()); //still md5'd
			if ($x = lookup('sites', $sitestr, '[logolink]', 'md5(idstring)')) {
				echo str_replace("http://", "https://", $x);
				
				$path = "/nfs/c10/h02/mnt/145083/domains/secure.91ferns.com/html/img/".basename($x);
				if ( file_exists( $path ) and !is_dir( $path ) ) {
					echo $src;
					list( $width, $height ) = getimagesize( $path );
					$width = sprintf("%d",(55/$height)*$width);
					echo '" width="'.round($width).'" height="55';
					
				} else echo '';
				
			} else {
				
				$src = Fn()->resources->strings->ferns_logo; //default logo
				if ($src == "") $src="/img/91ferns.png";
				$path = "/nfs/c10/h02/mnt/145083/domains/secure.91ferns.com/html/img/".basename($src);
				if ( file_exists( $path ) and !is_dir( $path ) ) {
					echo $src;
					list( $width, $height ) = getimagesize( $path );
					$width = sprintf("%d",(55/$height)*$width);
					echo '" width="'.round($width).'" height="55';
					
				} else echo '';
				
			}
			
			
		} else {
			
			$src = Fn()->resources->strings->ferns_logo; //default logo
			if ($src == "") $src="/img/91ferns.png";
			$path = "/nfs/c10/h02/mnt/145083/domains/secure.91ferns.com/html/img/".basename($src);
			if ( file_exists( $path ) and !is_dir( $path ) ) {
				echo $src;
				list( $width, $height ) = getimagesize( $path );
				$width = (55/$height)*$width;
				echo '" width="'.round($width).'" height="55';
				
			} else echo '';
		}
	
	?>"></a>
</div>