<?php
require_once('../cgi-bin/config.php');
require_once('../cgi-bin/theme_functions.php');

if (!function_exists('print_form')):
function print_form ( $name,  $arr, $action=null, $values=null ) {
	//start print_form function
	if ($values==null) {
		if (!isset($_SESSION['lastpost'])) {
			$values=$_POST;
		} else $values = Fn()->sessions->last_post();
	}
?><div id="<?=$name?>">

<form action="<?=($action==null) ? '/' : $action?>" method="POST" enctype="multipart/form-data">
<input type="hidden" name="continue" value="<?=the_url()?>">
<input type="hidden" name="time" value="<?=time()?>">
<?php

foreach ($arr as $name => $data):
	$id = isset($data['id']) ? $data['id'] : $name;
	$name = isset($data['name']) ? $data['name'] : $name;
	if ( $data['type'] == "submit" ): ?>
	<div class="form-row submit">
		<input class="rectangle-button green" type="submit" value="<?=isset($data['value']) ? $data['value'] : 'Submit'?>">
	</div>

	<?php elseif ( $data['type'] == "html" && !isset( $data['label']) ): echo isset($data['value']) ? $data['value'] : ""; elseif ( $data['type'] == "hidden" ):?>
	<input type="hidden" name="<?=isset($data['name'])? $data['name'] : 'do'?>" value="<?=isset($data['value']) ? $data['value'] : ''?>"><?php else: $edit = isset($data['edit']) ? $data['edit'] : false; ?>

	<div class="form-row input<?=isset($data['type']) ? " ".$data['type'] : ""?> <?=preg_replace("#[^_a-zA-Z0-9-]#", "", $name)?> <?=($edit) ? "editable" : "noneditable"?>">

		<label<?=isset($data['id']) ? ' for="'.$data['id'].'"' : ''?>>
			<strong><?php if (is_array($error) && $error['field'] == $name): echo $error['msg']; else: echo isset($data['label']) ? $data['label'] : 'Field'; endif; ?></strong>
		</label>
		<?php if ($edit): ?>
        <div class="alternate-data">
        <?php
		
		if (isset($data['alt']) && $data['alt'] != "")
			$alt = $data['alt'];
		elseif (isset($data['value']) and $data['alt'] != "")
			$alt = $data['value'];
		elseif (isset($values[$name]) and $data['alt'] != "")
			$alt = $values[$name];
		else $alt='Edit';
		?>
        	<a href="javascript: void(0);" class="edit" data-edits="<?=$id?>"><?=$alt?></a>
        </div>
        <?php endif; ?>
		<div class="input-div">

		<?php if (isset($data['type']) and $data['type'] != 'text') {
			
			switch ($data['type']) {
				
				case 'textarea': ?>
					<textarea id="<?=$id?>" name="<?=$name?>" class="ferns-hover"><?=isset($values[$name]) ? $values[$name] : ''?></textarea>
				<?php
					break;
				case 'select': ?>
					<select id="<?=$id?>" name="<?=$name?>">
					<?php if (!isset($data['options'])) $data['options'] = array();
					
					foreach ( $data['options'] as $selName => $selData ): ?>
						<option value="<?=$selName?>"<?=(isset($values[$name]) && $values[$name]==$selName) ? ' selected="selected"' : ''?>>
						<?=isset($selData) ? $selData : $selName?>
                        </option>
				<?php endforeach; ?>
					</select>
				<?php
					break;
				case 'html':
				case 'htm':
				?><div class="form-html"><?=$data['value']?></div><?php
					break;
				default:
				?><input id="<?=$id?>" class="ferns-hover" autocomplete="off" type="<?=isset($data['type']) ? $data['type'] : 'text'?>" value="<?=(isset($values[$name]))?$values[$name]:""?>" name="<?=$name?>"<?=isset($data['spellcheck']) ?' spellcheck="'.$data['spellcheck'].'"' : ''?>><?php											
				break;
			}

		} else { $t = isset($data['value']) ? $data['value'] : false;  if ($t === false) $t = (isset($values[$name])) ? $values[$name] : ""; ?>

		<input id="<?=$id?>" class="ferns-hover" type="text" value="<?=$t?>" name="<?=$name?>"<?=isset($data['spellcheck']) ?' spellcheck="'.$data['spellcheck'].'"' : ''?>>

		<?php } ?>

		</div>

	</div>
	<?php endif; endforeach; ?>

</form>
</div><!--#<?=$name?>-->
<?php
} //end print_form
endif;

//NOW THE FUN STUFF

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
header('Cache-Control: no-cache, must-revalidate');
?>

<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<link rel="shortcut icon" href="/favicon.ico">

<link rel="stylesheet" type="text/css" href="/style/static.css">
<link rel="stylesheet" type="text/css" href="/style/buttons.css">
<link rel="stylesheet" type="text/css" href="/style/nolog.css">
<link rel="stylesheet" type="text/css" href="/style/contact.css">
<title>Register | Service by 91ferns</title>
<style type="text/css">

.rectangle-button {}

.page-title h1 {font-size: 3.65em;font-weight:400; padding-bottom: .62em;}
.under-page-title p,.step2 p, .step3 p {font-size: 1.25em; line-height: 1.54em; width: 520px;padding-bottom: 1.85em;}

#step1 {padding-bottom: 17px;}

.step2,.step3,#success {display:none;}

</style>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript">
$(function() {
	
	$('li', '.shown-on-click').click(function(e) {
		e.stopPropagation();
		context = $(this).parentsUntil('.fake-select').parent();
		contextID = context.attr('id');
		jqCID="#"+contextID;
		
		data = $(this).attr('data-value');
		text = $(this).html();
		
		$('.text', jqCID).html(text);
		$('.shown-on-click', jqCID).css('display', 'none');
		
		//do something with data
		runStep2(data);
		
	});
	
	$('.fake-select').click(function(e) {
		e.stopPropagation();
		$('.shown-on-click', this).show();
	});
	
	$(document).click(function() {
		$('.shown-on-click', '.fake-select').hide();
	});
	
		//Ajax Check calls
	$('#lookup-invoice form').attr('action','javascript:void(0);').submit(function() {
		$.getJSON('/do-unsecure.php', {InvoiceID: document.getElementById('InvoiceID').value,ajax:true,do:'acc_cust'}, function(response) {
			if (response.status=="ok") {
				$('.step2').hide();
				$('.fullname').html(response.response.name);
				$('#step3-cust form').append('<input type="hidden" name="email" value="'+response.response.email+'">');
				$('#step3-cust form').append('<input type="hidden" id="customer_id" name="customer_id" value="'+response.response.id+'">');
				$('#step3-cust').fadeIn(300);
				
				$('.fake-select').click(function(e) {e.stopPropagation();return false;});
				$('#step3-cust form').attr('action', 'javascript:void(0);').submit(function() {
					//first check if passwords are the same
					password = $('input[name="Password"]', "#step3-cust").val();
					confPassword = $('input[name="Conf"]', '#step3-cust').val();
					
					if (password.length<7) {
						//password is too short
						alert('Password is too shrt'); 
					} else if (password!=confPassword) {
						//passwords dont match	
						alert('password dont match');
					} else {
						data = {
							InvoiceID: document.getElementById('InvoiceID').value,
							ajax:true,
							do:'complete_cust',
							customer_id: $('#customer_id').val(),
							password: $('#step3-cust form input[name="Password"]').val()
							};
						$.post('/do-unsecure.php',
							data,
							function(data) {
								if (data.status=="ok") {
									$('.step3').hide();
									$('#success').html(data.response).show();
								} else {
									alert(data.response);	
								}
							}, 'json');
					
					}
				});
			} else {
				//didn't work
				alert(response.response);
			} //end callback
		}); //end getJSON
	}); //end lookup invoice form
	
	$('#register-user form').attr('action', 'javascript:void(0);').submit(function() {
		x = $(this);
		data = {do:'add_acc',
				Name:$('input[name="Name"]', x).val(),
				Passwd:$('input[name="Passwd"]', x).val(),
				ConfEmail:$('input[name="ConfEmail"]', x).val(),
				Email:$('input[name="Email"]', x).val(),
				ajax:true
				 };
		$.post('/do-unsecure.php', data, function(res) {
			
			if (res.status!="ok") {
				alert(res.response);
			} else {
				$('#success').html(res.response).show();
				$('.step2').hide();
			}
			
		}, 'json'); //end $.post
	}); //end onsubmit of register-user form
	
	$('#lookup-friend form').attr('action', 'javascript:void(0);').submit(function() {
		ctxt = $(this); //this form
		
		data = {do:'acc_friend',
				ajax: true,
				'SpecialCode': $('input[name="SpecialCode"]', ctxt).val()
				};
		
		$.getJSON('/do-unsecure.php', data, function(res) {
			
			if (res.status!="ok") {
				alert(res.response);	
			} else {
				//it worked. Extract the data and input it as needed
				FirstName = res.response.FirstName;
				LastName = res.response.LastName;
				Email = res.response.Email;
				
				if (FirstName == null || FirstName.toLowerCase() == "none") {
					//no first name	
				}
				
				if (LastName == null || LastName.toLowerCase() == "none") {
					//no Last name	
				}
				
				Name = FirstName+" "+LastName;
				ctxt = $('#step3-friend form');
				
				$('.fullname', ctxt).html(Name);
				
				if (Email == null || Email.toLowerCase() == "none") {
					//no email address	
				} else {
					p = $('div.Email .input-div', ctxt);
					p.prepend('<span style="font-size: 1.1em;">'+Email+'</span>');
					
					$('input[name="Email"]', ctxt).remove();
					$('div.ConfEmail', ctxt).remove();
					$('<input>').attr('type', 'hidden').attr('name', 'Email').attr('value', Email).appendTo(p);
					//$('input[name="ConfEmail"]', ctxt).remove();
				}
				
				//now show the new form
				(function() {
					$('.step2').hide();
					$('.step3').hide();
					$('#step3-friend').fadeIn(300);
				})();
				
				ctxt.attr('action', 'javascript: void(0);').submit(function() {
					data = {};
					$.post('/do-unsecure.php', data, function(res) {
						
						
						
					}, 'json');
				}); //end form submission script
				
			}
			
				
		});
		
	});
	
});

function runStep2( data ) {
	
	switch (data) {
		
		case 'invoice':
			$('.step2').not('#step2-client').hide();
			$('#step2-client').fadeIn(300);
			break;
		case 'user':
			$('.step2').not('#step2-user').hide();
			$('#step2-user').fadeIn(300);
			break;
		case 'friend':	
			$('.step2').not('#step2-friend').hide();
			$('#step2-friend').fadeIn(300);
			break;
		default: return false; break;
		
	}
	
}

</script>

</head>

<body>

<noscript>
	<style type="text/css">.site-inner-wrapper, {display: none;}</style>
    <!-- NO JS -->
</noscript>

<div class="wrapper">

    <div class="site-inner-wrapper">
        <div class="header">
            
            <?php the_header();?>
            
        </div>
    
        <div class="section" id="body">
        
        	<div class="page-title"><h1><span class="ferns">91ferns</span> Account Registration</h1></div>
            
            <?php 
			if (isset($_GET['activate'])) {
				if (isset($_GET['key'])):
					//check if the key is there
					Fn()->load_extension('fquery');
					$f = fQuery('accounts', "active[x=0],uniquestr[x=?]:limit(1)", $_GET['key']);
					if ($f->count == 1) {
						$f->update(array('active' => 1));
				?>
            <div class="temp">
            	<?=R()->strings->account_activated?>
            </div>
            <?php
					} else {
			?>	
			<div class="temp">
            	<?=R()->strings->account_key_used?>
            </div>
            <?php		
					}
			endif; }?>
            
            <div id="step1">
            
            	<div class="under-page-title">
                    <p>We provide different ways to sign up for your 91ferns account. Please select which way you would like to use:</p>
                </div>
            	
                <div class="content">
                	<?php
					$temp_arr = array('invoice' => "I'm a client and have an invoice number.",
									  'friend' => "I have a referral code.",
									  'user' => "I'm a regular user and just want an account."
									  );
					?>
                    <div class="fake-select" id="fs1">
                            
							<div class="wrapper">
							
                            <div class="shown">
                                <div class="text"><?php if (isset($status)):?><?=(isset($_POST) and array_key_exists($_POST['reason'],$temp_arr)) ? $temp_arr[$_POST['reason']] : R()->strings->default_dropdown_register; else: echo R()->strings->default_dropdown_register; endif; ?></div>
                            </div>
                            
                            <ul id="formlike-reason" class="shown-on-click">
                            <?php
                            foreach ($temp_arr as $k=>$v) printf("<li data-value=\"%s\"><div class=\"sel-wrapper\">%s</div></li>", $k,$v);
                            ?>
                            </ul>
							
							</div> <!--.wrapper-->
                            
                        </div> <!-- .fake-select -->
                    
                </div> <!-- .content -->
                
            </div> <!-- #step1 -->
            
            <div id="step2-client" class="step2 client subsection">
            	
                <p>Please enter the number of your most recent invoice. Then, we'll look up your information and sign you up.</p>

				<?php $temp_arr = array();
				$temp_arr['InvoiceID'] = array('type' => "text", 'label' => "Invoice Number");
				$temp_arr['do'] = array('type' => "hidden", 'value' => "acc_cust");
				$temp_arr[] = array('type' => "submit", 'value' => "Look up");
				print_form("lookup-invoice", $temp_arr, "/do-unsecure.php");
				?>
            
            </div>
            
            <div id="step2-friend" class="step2 friend subsection">
            
            	<p>Please enter the special code we sent you in your email.</p>

				<?php $temp_arr = array();
				$temp_arr['SpecialCode'] = array('type' => "text", 'label' => "Referral Code");
				$temp_arr['do'] = array('type' => "hidden", 'value' => "acc_friend");
				$temp_arr[] = array('type' => "submit", 'value' => "Look up");
				print_form("lookup-friend", $temp_arr, "/register.php");
				?>
            
            </div>
            
            <div id="step2-user" class="step2 user subsection" style="">
            
            	<p>Please complete the following form to complete your registration. All information is required to make your <span class="ferns">91ferns</span> account.</p>
                <?php 
				
				$arr = array();
//				$arr[] = array('type' => "html", 'value' => '<div class="info-wrapper"><h3>General Information</h3>');
				$arr["Name"] = array('type' => "Text", 'label' => "Name");
				$arr["Email"] = array('type' => "Text", 'label' => "Email");
				$arr["ConfEmail"] = array('type' => 'text', 'label' => "Confirm Email");
				//$arr[] = array('type'=>'html', 'value'=>"<br>");
				$arr['Passwd'] = array('type' => "password", 'label' => "Password");
				$arr['do'] = array('type' => "hidden", 'value' => "acc_acc");
				$arr[] = array('type'=>'html', 'value'=>"<br>");
//				$arr[] = array('type' => 'html', 'value' => '</div><!-- .info-wrapper -->');
				$arr[] = array('type' => "submit", 'value' => "Register");
				
				print_form('register-user', $arr, '/register.php'); ?>
            
            </div><!-- .subsection -->
            
            <div id="step3-cust" class="step3 customer subsection">
            	
                <p>Please fill in the rest of the form and we can complete your registration. Thanks for signing up!</p>
                <?php $arr = array();
					$arr[] = array('type' => 'html', 'label' => "Name", 'value' => '<span class="fullname" style="font-size:1.1em;"></span>');
					$arr['Password'] = array("type" => "password", 'label' => "Password");
					$arr['Conf'] = array('type' => 'password', 'label' => "Confirm");
					$arr['do'] = array('type' => "hidden", 'value' => "finish_cust");
					$arr[] = array('type' => "submit", 'value' => "Complete");
					
					print_form('finish-cust-reg', $arr, '/register.php');
				?>
                
                
            </div> <!-- .step3 #step3-cust -->
            
            <div id="step3-friend" class="step3 friend subsection">
            	
                <p>Please fill in the rest of the form and we can complete your registration. Thanks for signing up!</p>
                <?php $arr = array();
					$arr[] = array('type' => 'html', 'label' => "Name", 'value' => '<span class="fullname" style="font-size:1.1em;"></span>');
					$arr['Email'] = array('type' => 'text', 'label' => "Email");
					$arr['ConfEmail'] = array('type' => 'password', 'label' => "Confirm");
					$arr['Password'] = array("type" => "password", 'label' => "Password");
					
					$arr['do'] = array('type' => "hidden", 'value' => "finish_friend");
					$arr[] = array('type' => "submit", 'value' => "Complete");
					
					print_form('finish-cust-reg', $arr, '/register.php');
				?>
                
                
            </div> <!-- .step3 #step3-cust -->
        
        	<div id="success"></div>
        
        </div>
        
    </div>
    
    <div class="push"></div>

</div>

<div class="footer">
    
    <? footer(); ?>
    
</div>

</body>
</html>
