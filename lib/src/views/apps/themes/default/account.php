<?php
require('../cgi-bin/config.php');

if (!is_logged_in()) {header("Location: /?continue=".urlencode($_SERVER['REQUEST_URI']));exit;}

Fn()->load_extension( 'sites' );

if (!Fn()->this_site->is_admin() and !isset($_COOKIE['redirected'])) {
	
	setcookie("redirected", "true", 0);
	$str ="/apps/dtmanager";
	
	if ($redir = Fn()->this_site->settings->redirect) {
		$str=$redir;	
		
	header(sprintf("Location: %s", $str));
	exit;
	
	}
} else {
	
}

require '../cgi-bin/theme_functions.php';

/* Account Panel specifics go here */
add_menu_item('javascript:void(0);', "Manage Sites", 'id="manage-sites"');
add_menu_item('javascript:void(0);', "Billing", 'id="billing-history"');
add_menu_item('javascript:void(0);', "Add Widgets", 'id="add-widgets"');
if (true or Fn()->this_site->is_admin()) add_action( 'in_head', function() {
?>
<style type="text/css">

div.progress {padding: 5px; width: 100%; height: 30px; border: 1px solid #39b54a; background-color: #d8f3dc;}
div.progress .p-wrapper {height:30px;width: auto;}
div.progress .p-wrapper .p-value {background-color: #056839;color: white;height:30px;
	transition: all .5s linear;
	-moz-transition: all .5s linear;
	-webkit-transition: all .5s linear;
	-o-transition: all .5s linear;
}
div.progress .p-wrapper .p-text {font-size: 17px;float: left; padding-left: 30px;padding-top:8px;font-weight: bold;color: #666;color:white;z-index: 10;}

code {font-size: 1.1em;}
.php-loops {color: orange;}
.php-special-words {color: green;}
.php-functions {color: darkblue;}
.php-string {color: magenta;}
.php-tags,.php-numbers {color: red;}
.php-var {color: lightblue;}

div.widget {
	border: 1px solid #E5E5E5;
	background-color: white;
}

div.widget .title {padding: 8px 20px 8px 14px; font-size: 1.4em; color: black; background-color: #F5F5F5; border-bottom: 1px solid #E5E5E5;}
div.widget .w-content {padding: 10px 15px;}

div.widget .w-content {font-size: 1.1em; line-height: 1.3em;} 
div.widget .w-content p {padding-bottom: 9px;}

.w-container {position: relative;}

div.w-col {
	float: left;
	width: 33%;
	height: 100%;
	display: block;
}

.w-col .w-col-wrapper {
	padding: 0px 10px; height: 100%;
}

</style>
<?php
});
if ( false and Fn()->account->id==1) add_action( 'content', function() {

$file = EXTPATH."/changelog.txt";
$file=file($file);
?>
<div class="widget-panel">
	<div class="widget" style="width:100%;">
        <h2><?=$file[0]?></h2>
        <div class="cl-content" style="padding-bottom: 10px;"><?php $in_list = false; ?>
        <?php
//        unset($file[0]);
		$file = array_values(array_filter($file, function($v) {$v = trim($v); return (empty($v) or $v == "") ? false : true;}));
		$file[] = "<br>";
		foreach($file as $l => $line): ?>
            <?php if (strlen(trim($line)) > 0): ?>
            	<?php
                //we need to get what purpose this fills. First let's trim it
				$line=trim($line); //now that it's trimmed, if it starts with a - we know it is an unorded list element
				if (preg_match("#^[-]#", $line)) {
					$line = preg_replace("#^[-] *#", "", $line);
					if ($in_list) {
						?><li><?=$line?></li><?php
					} else {
						echo '<ul>';?><li><?=$line?></li><?php $in_list=true;
					}
				} elseif (trim($line)=="\n") {
					echo '<br>';
				} else {
					//if it isn't starting a list we need to check if the list was recent
					if ($in_list) {
						$in_list=false; echo '</ul>';
					}
					?><div><?=$line;?></div>
					<?php if (preg_match("#^[-]#", $file[$l+1])) {
						echo '<ul>';
						$in_list=true;
					}
					
				}
			else: ?><?php endif; ?>
        <?php endforeach; ?>
        </div>
    </div>
    <div class="widget">
    <?php 
	function format_code( $str ) {
		
		$special_words = array('echo', 'return', 'die', 'exit', 'array', 'true', 'false');
		$loops = array( 'if', 'while', 'foreach', 'for', 'else', 'elseif', 'do' );
		
		$str = explode( "\n", $str );
		
		$inVariable = $inFunction = $inSpecial = $inQuotes = $inPHPTag = $inHTMLTag = false;
		$return = "";
		
		$currWord = "";
		$currQuote="";
		
		
		foreach ($str as $no=>$line) {
			
			$chars = str_split($line."\n");
			foreach ($chars as $char):
			
				if ( ($char == " " || $char=="\n" || $char=="\t" || $char=="(" || $char == ")" or empty($char) or $char==".") and !$inQuotes ) {
					//we are at the end of the word
					if ($inVar) {$return.="</span>";$inVar=false;}
					
					if (preg_match("#(([&]lt;[?](php)?)|([?][&][g]t;))#i", trim($currWord))) {
						$return=substr($return, 0, strpos($return, $currWord)-1).'<span class="php-tags">'.$currWord.'</span>';
					} elseif (in_array($currWord, $special_words) and !$inQuotes) { //if it is a special word
						$return=substr($return, 0, strpos($return, $currWord)-1).'<span class="php-special-words">'.$currWord.'</span>';
					} elseif (in_array($currWord, $loops) and !$inQuotes) { //if it is a loop
						$return=substr($return, 0, strpos($return, $currWord)-1).'<span class="php-loops">'.$currWord.'</span>';
					} elseif (!$inQuotes and !in_array($currWord, array(" ", "\n", "=", ".", "$", "%")) and !empty($currWord) ) { //if it is either a constant of a function
						
						if ($char== "(") { //starting a function
							$return=substr($return, 0, strpos($return, $currWord)-1).'<span class="php-functions">'.$currWord.'</span>';	
						} else {
							//$return=substr($return, 0, strpos($return, $currWord)-1).'<span class="php-const">'.$currWord.'</span>';
						}
					} elseif (preg_match("#[0-9]+#", $currWord)) {
						$return=substr($return, 0, strpos($return, $currWord)-1).'<span class="php-numbers">'.$currWord.'</span>';
					}
					$return.=$char;	 //just add the char to the return vals
					$currWord="";
				} else {
					if (preg_match("#['\"]#", $char)) { //if it is a quote
						if ($inQuotes and $currQuote==$char and substr($return, strlen($return)-1)!="\\") { //if it is in quotes and it is ending quotes
							$return .= $char."</span>";
							$inQuotes=false;
							$currQuote="";
						} else { //if it is a starting quote
							$return .= '<span class="php-string">'.$char;
							$currQuote=$char;
							$inQuotes=true;
						}
						
					} elseif($char=="$") {
						$return .= '<span class="php-var">$';
						$inVar=true;
					} else {
						if ($inQuotes) $return .= $char;	
						else if ($inVar) $return.=$char;
						else $return.=$char;
					} 
					$currWord.=$char;
					
				}
			endforeach;
			
		}
		
		
		return "<code>".nl2br($return)."</code>";
	
	}

	if (isset($_GET['debug']) and Fn()->account->id==1) {
		$errorlog = file(EXTPATH."/logs/errorlog.log");
		foreach ($errorlog as $line => $value) {
			
			$x = split("\t",$value);
			
			$x = array_values(array_filter( $x, function($var) {
				
				if (empty($var)) return false; else return true;
				
			}));
			list($class, $typeAndFile, $data, $time) = $x;
			$data = isset(json_decode($data)->data) ? (array) json_decode($data)->data : array();;
			$time = strtotime($time);
			
			?>
			<div class="error-logged" style="padding-bottom: 3px;">
				<div class="title"><strong><?=$class?></strong> (<?php $temp = split(" ",$typeAndFile); echo $temp[0]; ?>) on <?=date("F jS", $time)?> at <?=date("G:i",$time);?></div>
				<?php if (count($data) > 0): ?><div class="data"><ul>
				<?php foreach( $data as $k=>$v ): ?>
				<li><?=$k?> => <?=$v?></li>
				<?php endforeach; ?></ul>
				</div><?php endif; ?>
			</div>
			<?php
		}
	}
	
	
$ua = $_SERVER['HTTP_USER_AGENT'];
echo $ua;
$ua = "Mozilla/5.0 (Linux; Android 4.1.1; Nexus 7 Build/JRO03D) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.166 Safari/535.19"; //chrome syntax
if (preg_match("#Firefox\/(\d[.]?)+#", $ua)) { 
	
	preg_match("#(.+)\(([^;]+)[;][^)]+\) *([^ ]+) *([^ ]+)#i",$ua, $m);
	list(,$compat, $OS, $parser, $browser)=$m;

} elseif (preg_match("#Chrome[/]#", $ua)) {
	//
	preg_match("#(.+)\(r#i", $ua, $m);
	list()=$m;
	
}

//printf("Compat: %s, OS: %s, parser: %s, browser: %s<br>", $compat, $OS, $parser, $browser);
?>

</div>
<?php	
}); //end add action

if (Fn()->this_site->is_admin()) add_action( 'content', function() {
	$site=Fn()->site(1);
	if (!$site->settings->database or !$site->settings->storage_table or !$site->settings->total_storage) return;
	
	?>
    <div class="w-container">
    	<div class="w-col">
        	<div class="w-col-wrapper">
            	
                <div class="widget">
                	<?php if (Fn()->account->id==1): ?>
                    <div class="p-site-select" style="float: right; padding-top: 4px; display:block;">
                    <form action="/account.php" method="get">
                    	<select id="p-site-select" name="site-select">
                        	<?php $x = Fn()->site(1); ?><option value="<?=$x->id?>"><?=$x->name?></option>
                            <?php $x = Fn()->site(2); ?><option value="<?=$x->id?>"><?=$x->name?></option>
                            <?php $x = Fn()->site(4); ?><option value="<?=$x->id?>"><?=$x->name?></option>
                        </select>
					</form>
                    </div>
                    <?php endif; ?>
                	<div class="title">Data Usage</div>
                    <div class="w-content">
						<?php
                        add_connection( $reference, $site->settings->database);
                        use_connection($reference);
                        
						query('SELECT table_schema "DB Name", sum( data_length + index_length ) / 1024 / 1024 "DB Size in MB" 
FROM information_schema.TABLES GROUP BY table_schema ;');
						$size = 0;
						if (num_rows() > 0):
							while ($row = assoc()) {
								if ($row['DB Name']==$site->settings->database) {
									$size = $row['DB Size in MB'];
									break;	
								}
							}
						endif;
						
                        define('BPMB', 1048576);
						
                        $f = Fn()->fQuery( $site->settings->storage_table );
                        $total = $size*BPMB;
                        $f->query('*')->each(function($d) use (&$total) {
                            $src = $d->row('src');
                            if (file_exists($src)) {
                                $size = filesize($src);
                                $total=$total+$size;
                            }
                        });
                        
                        $allowance = $site->settings->total_storage; //.1 * (1.1 * pow(10, 9));
						if ($allowance < 1) $allowance = 1;
                        $textpercent = $percent = ($total/$allowance)*100;
						if ($percent > 100) $percent = 100;
                        override_default_connection();
                        ?>
                        <p>Below is your data usage of 91ferns Servers. The data allowance of your current plan is <?=sprintf("<strong>%d</strong>", $allowance/BPMB)?> Megabytes (MB). You are currently using <?=sprintf("<strong>%.2f</strong>", $total/BPMB); ?> Megabytes.</p>
                        <?php if ($percent>40): ?><p>To help lower your data usage, you can utilize the <span class="ferns">91ferns</span> Cleanup Utility. This will help remove unused files and entries to lower your data usage.</p><?php endif; ?>
                        <div class="progress" style="margin-left: -7px;">
                            <div class="p-wrapper">
                                <div class="p-text" style="color: white;"><?=sprintf("%.2f", $textpercent)?>% Full</div>
                                <div class="p-value" id="data-percent" style="width: <?=sprintf("%.2f", $percent)?>%">&nbsp;</div>
                            </div>
                        </div>
                        <script type="text/javascript">
							document.getElementById('data-percent').style.width="0%";
							
							$(function() {
								
								document.getElementById('data-percent').style.width=<?=sprintf("%d", $percent)?>+"%";
							/*var t = window.setInterval(function() {
								percent = <?=sprintf("%.2f", $percent)?>;
								elem = document.getElementById('data-percent');
								width = parseInt(elem.style.width);
								
								if (width>=percent) {
									window.clearInterval(t);
									return;	
								}
								
								newWidth=width+1;
								if (newWidth>percent) newWidth=percent;
								elem.style.width=newWidth+"%";
								
								$('.p-text').html(newWidth+"% Full");
								
								
								
							}, 100);*/
							
							function resizeProgress() {
								return;
								w = window.innerWidth;
								ww = w-310;
								
								if (ww<=640) {
									ww=640;
								}
								
								widgetWidth = Math.floor( (ww/3) - 50 );
								$('.progress').css('width', widgetWidth-20+"px");
								$('.p-wrapper').css('width', widgetWidth-30+"px");
								
							}
							
							$(window).resize(function(e) {
								resizeProgress();
							});
							
							resizeProgress();
							
							});
						</script>
                    
                    </div>
                </div>
                
            </div>
        </div>
        <div class="w-col">
        	<div class="w-col-wrapper">
            	
                <div class="widget">
                    <div class="p-site-select" style="float: right; padding-top: 4px; display:none;">
                    <form action="/account.php" method="get">
                    	<select id="p-site-select" name="site-select">
                        	<?php $x = Fn()->site(1); ?><option value="<?=$x->id?>"><?=$x->name?></option>
                            <?php $x = Fn()->site(2); ?><option value="<?=$x->id?>"><?=$x->name?></option>
                            <?php $x = Fn()->site(4); ?><option value="<?=$x->id?>"><?=$x->name?></option>
                        </select>
					</form>
                    </div>
                	<div class="title">Quick Links</div>
                    <div class="w-content">
                    <?php $links = array(
										array("Label" => "Socrates Society Site Login",
											  "href" => "http://socratessociety.com/wp-login.php"),
										array("Label" => "Patsy's Wordpress Login",
											  "href" => "http://wp.patsys.testing.91ferns.com/wp-login.php")
										); ?>
                    <?php if (count($links) > 0): ?>
                    	<ul>
                        <?php foreach( $links as $data ): $data = (object) $data; ?>
                        	<li><a href="<?=$data->href?>"><?=$data->Label?></a></li>
                        <?php endforeach; ?>
                        </ul>
                    <?php else: ?>There are no quick Links<?php endif; ?>
                    </div>
				</div>
			</div>
        </div>
        <div class="w-col"> </div>
        <div style="clear:both;">&nbsp;</div>
  	</div>
    <?php
});



/* End account panel specifics */

require '../cgi-bin/thematic.php';

?>