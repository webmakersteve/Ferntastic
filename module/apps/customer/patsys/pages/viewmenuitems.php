<div class="viewshipment"><?
use_connection('patsys');
//check if there is an ID to use
	
	Fn()->load_extension('fquery');
	$num = (isset($_GET['show']) && ( (int) $_GET['show'] > 20)) ? $_GET['show'] : 20;
	$curr = isset($_GET['start']) ? $_GET['start'] : 0;
	
	$f = fQuery('patsys_menu_items', '*,category:order(\'ASC\'),active[x=1]:limit('.$curr.','.$num.')');
	if ($f->count<1) {
		//no shipment by that ID
		?>There are currently no menu items.<?php
	} else {
		
		$tbl = new ListView(null, 3);

		$tbl->isSelectable();
		$tbl->addColClass(1, 'places');
		$tbl->addColClass(2, 'hey');
		$tbl->addColClass(3, 'category');
		
		$cats = array();
		
		$f->each(function($d) use (&$tbl,&$cats) {
			
			if (strlen($d->description) < 1) $desc = "N/A";
			else $desc = substr(stripslashes($d->description), 0, 30)."...";
			
			//get the category ready
			$cat = $d->category;
			//check if we have this one in the array
			if (isset($cats[$cat])) $category = $cats[$cat];
			else {
				//we need to get it and set it 
				$catname = lookup('patsys_menu_categories', $cat, "[displayname]", 'id');
				$cats[$cat]=$catname;
				$category=$catname;
			}
			
			$tbl->addRow($d->id, stripslashes(substr($d->name,0,30)), $desc, $category);
			
		});
		$data=$f;
		$show = (isset($_GET['show']) && ( (int) $_GET['show'] > 20)) ? $_GET['show'] : 20;
		$curr = isset($_GET['start']) ? $_GET['start'] : 0;
		ob_start();
		?>Showing results <?=$curr+1?>-<?=$curr+$data->count?>
		<?php if ($data->count < $data->total_count):?> of <?=$data->total_count?><?php if (isset($_GET['start']) && $curr > 0) { $val = $_GET['start']-$show; $val = $val<0 ? 0 : $val;?> | <a href="?start=<?=$val.$qstr?>&amp;show=<?=$show?>">Previous</a><?php } endif; ?>
		<?php if ($data->total_count > $data->count+$curr+1):?> | <a href="?start=<?=($curr+$show).$qstr?>&amp;show=<?=$show?>">Next</a><?php endif;
		$x = ob_get_clean();
		$tbl->footer($x);
		
		echo $tbl;
		
	}
?></div>
<script type="text/javascript">
    $.each($('.item-row').not('.nohighlight'), function() {
    
        $(this).dblclick(function() {
            window.location.href="/apps/patsys/menu/edit?id="+$(this).attr('data-id');
        });
    
    });
    
    function openChecked(action) {
        if (action == null) action="transfer";
        i = 0;
        ids = new Array();
        getstring = "";
        
        $.each($('.item-row.checked'), function() {
            
            ids[i] = $(this).attr('data-id');
            getstring+=ids[i]+";";
            i++;
            
        });
		
		console.log(getstring);
		
        url = window.location.pathname+"/"+action+"?string="+getstring;
        window.location=url;
        
    }
    $('#actions-transfer').click(function() {
        console.log('clicked');
        if ($(this).hasClass('active')) openChecked("transfer");
    });
    $('#actions-delete').click(function() {
        if ($(this).hasClass('active')) openChecked("delete");
    });
	function checkActivity() {
		if ($('.item-checkbox:checked').length < 1) $('.valid-when-checked').removeClass('active');
		else $('.valid-when-checked').addClass('active');
	}
	$('.item-checkbox').click(function() {
		
		if ($(this).is(":checked")) {
			$(this).parentsUntil("item-row").parent().addClass('checked');
		} else {
			$(this).parentsUntil("item-row").parent().removeClass('checked');	
		}
		
		//get count
		checkActivity();
		
	});
	$('.item-row').click(function(e) {
		
		if (e.ctrlKey) {
			if ($('.item-checkbox', this).is(':checked')) {
				$(this).removeClass('checked');
				$('.item-checkbox', this).removeAttr('checked');
			} else {
				$('.item-checkbox', this).attr('checked', 'checked');
				$(this).addClass('checked');
			}
		}
		
		checkActivity();
		
	});
</script>