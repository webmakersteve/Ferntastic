<?php

/**
 * class GUIElement is the main class for all GUI elements built through the framework.
 * 
 *
 */
namespace MVC\Helpers\GUI;

class Element extends Helper {
	
	protected $html = '';
	
	public function __toString() {
		return $this->make();	
	}
	
	protected function make() {
		return $this->html;	
	}
	
}

/**
 * class GUIElement is the main class for all GUI elements built through the framework.
 * 
 *
 */         
class ListView extends GUIElement {
	
	protected $ListViewName,$columns;
	static $names = array();
	static $num = 0;
	
	public function __construct( $ListViewName=null, $columns=3 ) {
		if ($ListViewName==null) $ListViewName="ListView".++self::$num;
		$ListViewName = trim($ListViewName);
		if (in_array($ListViewName, self::$names)) {
			$ListViewName=$ListViewName.++self::$num;
		}
		$this->columns = intval($columns);
		self::$names[]=$ListViewName;
	}
	
	private $selectable;
	
	public function isSelectable() {
		$this->selectable = true;	
	}
	
	protected $header,$footer;
	
	public function header( $str ) {
		$this->header=$str;	
	}
	
	public function footer( $str ) {
		$this->footer=$str;	
	}
	
	protected $rows;
	
	public function addRow(/* $id, ..mixed */ ) {
		
		//First Argument is the row ID
		$args = func_get_args();
		
		$rowID = $args[0];
		
		if ($rowID == null) $rowID = 0;
		unset($args[0]);
		
		//example:
		//$x->addRow(1, 'Park Place', 'Gasd asd a ', 'ad ad asd ');
		
		$i = 0;
		
		if (count($args) > $this->columns) {
			$cols = $this->columns;
			$args = array_filter( $args, function($v) use (&$i,$cols) {
				$i++;
				if ($i>$cols) return; else return true;
			});
		
		} elseif (count($args) < $this->columns) {
			
			//there are too few arguments
			for ($i=count($args);$i<$this->columns;$i++) {
				$args[$i]='';	
			}
			
		}
		
		$x = &$this->rows[$rowID];
		
		foreach ($args as $val) {
			$x[] = $val;
		}
		
	}
	
	protected $colClasses = array();
	public function addColClass($num, $class) {
		$num--;
		$this->colClasses[$num][]=$class;	
	}
	
	protected function make() {
		
		ob_start();
		
		?>
        <div class="ListView <?php if ($this->selectable) echo 'selectable'; else echo 'no-selectable'; ?>">
        	<?php if (strlen($this->header) > 0): ?>
        	<div class="ListView-header">
            	<?=$this->header?>
            </div>
            <?php endif; ?>
        <?php if (count($this->rows)>0): foreach ($this->rows as $rowKey => $rowData): ?>
            <a class="item-row" data-id="<?=$rowKey?>">
				<?php if ($this->selectable): ?>
                <div class="item-check">
                    <input type="checkbox" class="item-checkbox">
                </div>    
                <?php endif; ?>
                
                <div class="item-row-nocheck">
                    <div class="item-info-wrapper cols-<?=$this->columns?>">
                    <?php foreach ($rowData as $num=>$string): ?>
                        <div class="ListView-Column<?php
                        	if (isset($this->colClasses[$num])) {
								$t = $this->colClasses[$num];
								echo " ";
								if (count($t) > 1) echo implode(" ", $t);
								else echo $t[0];
							}
						?>">
                            <?=$string?>
                        </div>
                    <?php endforeach; ?>
                    </div> <!-- .item-info-wrapper-->
                </div> <!-- .item-row-nocheck -->
            
            </a>
        <?php endforeach; else: ?>
        	<?php $this->defaultRow(); ?>
        <?php endif; ?>
        	<?php if (strlen($this->footer) > 0): ?>
        	<div class="ListView-footer">
        		<?=$this->footer?>
        	</div> <!-- .ListView-footer-->
            <?php endif; ?>
        </div> <!-- .ListView -->
        <?php
		
		$contents = ob_get_clean();
		$this->html=$contents;
		return $contents;
		
	}
	
	function defaultRow() {
		?>
        <div class="item-row nohighlight" id="no-items">

              <div class="item-check">										

              </div>

              <div class="item-row-nocheck">

                  <div class="item-info-wrapper">
                      There are no items to list. Add some.
                  </div>

              </div>

        </div>
        <?php
	}
		
}

/**
 * class GUIElement is the main class for all GUI elements built through the framework.
 * 
 *
 */

class WebForm extends GUIElement {

	protected $formID, $data, $action, $values;
	protected $cached = '';
	protected $useCache = false;
	
	function __construct($formID, $data=null, $action=null, $preLoadedValues=null) {
	
		if ($preLoadedValues==null) {
			if (!isset($_SESSION['lastpost'])) {
				$preLoadedValues=$_POST;
			} else $preLoadedValues = Fn()->sessions->last_post();
		}
		
		$this->values = $preLoadedValues;
		$this->action = ($action==null) ? '/do.php' : $action;
		$this->data = ($data==null and is_array($data)) ? array() : (array) $data;
		$this->formID = $formID;
	}
	
	protected $enctype = null;
	
	function setEnctype( $value ) {
		$this->useCache = false;
		if ($values == 'files') {
			$this->enctype = "multipart/form-data";
		} else {
			$this->enctype = $value;	
		}
	}
	
	function add( $data ) {
		if (is_array($data)) {
			$this->useCache = false;
			$this->data = $this->data + $data;	
		} else return;
	}
	
	protected $method = "POST";
	
	function setMethod($str) {
		
		$acc = array('GET', 'POST', 'HEAD');
		$str = strtoupper($str);
		
		if (in_array($str, $acc)) {
			$this->useCache = false;
			$this->method = $str;
		} else return;
		
	}
	
	protected function make() {
		
		if ($this->useCache) {
			return $this->cached;	
		}
		
		$arr = $this->data;
		if (count($arr) < 1) return;
		
		$formID = $this->formID;
		$action = $this->action;
		$values = $this->values;
		//start print_form function

		ob_start();
		?>
        <div id="<?=$formID?>-wrapper">
        
            <form action="<?=$this->action?>" id="<?=$formID?>" method="<?=$this->method;?>" <?php if ($this->enctype!=null): ?>enctype="<?=$this->enctype;?>"<?php endif; ?>>
            <input type="hidden" name="continue" value="<?=function_exists('the_url') ? the_url() : ''?>">
            <input type="hidden" name="time" value="<?=time()?>">
            <?php
            
            foreach ($arr as $name => $data):
                $id = isset($data['id']) ? $data['id'] : $formID."-".$name;
                $name = isset($data['name']) ? $data['name'] : $name;
                if ( $data['type'] == "submit" ): ?>
                <div class="form-row submit">
                    <input class="rectangle-button green" type="submit" value="<?=isset($data['value']) ? $data['value'] : 'Submit'?>">
                </div>
            
                <?php elseif ( $data['type'] == "html" && !isset( $data['label']) ): echo isset($data['value']) ? $data['value'] : ""; elseif ( $data['type'] == "hidden" ):?>
                <input type="hidden" name="<?=!empty($name) ? $name : 'do'?>" value="<?=isset($data['value']) ? $data['value'] : ''?>"><?php else: $edit = isset($data['edit']) ? $data['edit'] : false; ?>
            
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
                            
                            case 'textarea':
							$t = isset($data['value']) ? $data['value'] : false;  if ($t === false) $t = (isset($values[$name])) ? $values[$name] : "";
							?>
                                <textarea id="<?=$id?>" name="<?=$name?>" class="ferns-hover"><?=$t?></textarea>
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
								
							case 'checkbox':
								?>
                                <div style="text-align: left;">
                                <input <?php if ($data['checked'] or (isset($values[$name]) and ($data['value'] == $values[$name])) ): ?>checked="checked"<?php endif; ?> style="text-align:left; width: auto;" type="checkbox" value="<?=$data['value']?>" name="<?=$name?>">
								</div>
								<?php
								break;
								
                            default:
                            ?><input id="<?=$id?>" class="ferns-hover" autocomplete="off" type="<?=isset($data['type']) ? $data['type'] : 'text'?>" value="<?=(isset($values[$name]))?$values[$name]:""?>" autocomplete="off" name="<?=$name?>"<?=isset($data['spellcheck']) ?' spellcheck="'.$data['spellcheck'].'"' : ''?>><?php											
                            break;
                        }
            
                    } else { $t = isset($data['value']) ? $data['value'] : false;  if ($t === false) $t = (isset($values[$name])) ? $values[$name] : ""; ?>
            
                    <input id="<?=$id?>" autocomplete="off" class="ferns-hover" type="text" value="<?=$t?>" name="<?=$name?>"<?=isset($data['spellcheck']) ?' spellcheck="'.$data['spellcheck'].'"' : ''?>>
            
                    <?php } ?>
            
                    </div>
            
                </div>
                <?php endif; endforeach; ?>
            
            </form>
        </div> <!-- end autoform -->
		<?php
		$content = ob_get_clean();
		$this->cached = $content;
		$this->useCache = true;
		$this->html = $content;
		return $content;
	}
		
}

class SearchForm extends GUIElement {
	
	private $fields = array();
	private $types = array('text', 'checkbox', 'select', 'radio');
	
	function addFilter( $label, $type, $values=null ) {
		
		if (!in_array( $type, $this->types)) return;
		switch ($type) {
			default: return;	
		}
		$this->fields[$label];
		
	}
	
	protected $name, $action;
	
	function __construct( $name='query', $action=null ) {
		if ($action==null) $action = $_SERVER['REQUEST_URI'];
		
		$this->name=$name;
		$this->action=$action;
		ob_start();
		?>
        <div class="search-form-wrapper">
		 <form action="<?=$action?>" method="GET">
			<input type="text" placeholder="Search" class="search-field" name="<?=$name?>" value="<?=isset($_GET[$name]) ? $_GET[$name] : ""?>">
			<input type="submit" value="Go" class="rectangle-button green">
		</form>
        </div>
		<?php
		$this->html = ob_get_clean();	
	}
	
	
}

class ButtonMenu extends GUIElement {
	
	protected $elements = array();
	
	public function addOption( $id, $label ) {
		$this->elements[$id]=$label;
	}
	
	protected $label;
	
	function __construct( $label ) {
		$this->label=$label;
	}
		
	protected function make() {
		ob_start(); ?>
        <div class="ButtonMenu"><?=$this->label?>
            <ul class="buttons-list">
                <?php foreach ($this->elements as $id => $label): ?>
                    <li class="subitem inactive" id="<?=$id?>"><?=$label?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php return ob_get_clean(); 	
	}
}
