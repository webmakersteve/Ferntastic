<div class="deleteitems"><?
use_connection('patsys');
//check if there is an ID to use
	
	$str = isset($_GET['string']) ? $_GET['string'] : false;
	$str = preg_replace("#[;]+$#", "", $str);
	$arr = split(";", $str);
	
	Fn()->load_extension('fquery');
	$num = (isset($_GET['show']) && ( (int) $_GET['show'] > 20)) ? $_GET['show'] : 20;
	$curr = isset($_GET['start']) ? $_GET['start'] : 0;
	
	$f = fQuery('items', '*,id[x&=?],active[x=1]', $arr);
	
	if ($f->count < 1) {
		
	} else {
	
		$html = '<ul class="items-list">';
		$f->each(function($data) use (&$html) {
			
			$html .= '<li>'.$data->name.'<input name="items[]" type="hidden" value="'.$data->id.'"</li>';
			
		});
		$html .= '</ul>';
		
		$html_padding = '<div style="padding-top: 14px;"></div>';
		$form_array = array( 
			array('type' => 'html', 'value' => $html_padding),
			array(
				"type" => "html",
				"value" => $html,
				"label" => "Items	"
				),
				
			array(
				"type" => "html",
				"value" => "<div style=\"padding: 15px 10px;\">This can only be undone by an administrator. Please be sure you wish to do this before proceeding.</div>"
				),
				
			'do' => array('type' => 'hidden', 'value' => "delete_items"),
			
			array(
				'type' => 'submit',
				'value' => 'Delete'
				)
						
		);
	
		echo new WebForm('ItemDelete', $form_array, '/do-new.php');
	
	}
	
?></div>