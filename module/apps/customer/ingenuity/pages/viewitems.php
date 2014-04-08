<div class="content-wrapper"><?php

use_connection('patsys');

$tbl = new ListView(null, 3);

$tbl->isSelectable();
$tbl->addColClass(1, 'places');
$tbl->addColClass(2, 'hey');

$num = (isset($_GET['show']) && ( (int) $_GET['show'] > 20)) ? $_GET['show'] : 20;
$curr = isset($_GET['start']) ? $_GET['start'] : 0;
$f = fQuery('items', '*,active[x=1]:limit(30)');
if ($f->count>0) $f->each(function($d) use (&$tbl) {
	$data = unserialize($d->data);
	$tbl->addRow($d->id, $d->name, substr($d->description, 0, 50), $d->owned." in stock");
}, function($data) use ($tbl,$num,$curr) {
	$show = $num;
	$qstr = isset($_GET['query']) ? "&amp;query=".$_GET['query'] : ""; ob_start(); ?>
Showing <?php if ($curr+1==$data->count): ?> 1 result <?php else: ?>results <?=$curr+1?>-<?=$curr+$data->count?><?php endif;if ($data->count < $data->total_count):?> of <?=$data->total_count?><?php if (isset($_GET['start']) && $curr > 0) { $val = $_GET['start']-$show; $val = $val<0 ? 0 : $val;?> | <a href="?start=<?=$val.$qstr?>&amp;show=<?=$show?>">Previous</a><?php } endif; ?><?php if ($data->total_count > $data->count+$curr+1):?> | <a href="?start=<?=($curr+$show).$qstr?>&amp;show=<?=$show?>">Next</a><?php endif;
	
	$contents = ob_get_clean();
	$tbl->footer($contents);
}); else $tbl->footer('This is the footer');

echo $tbl;
?>
<script type="text/javascript">
    $.each($('.item-row').not('.nohighlight'), function() {
    
        $(this).dblclick(function() {
            window.location.href="/apps/patsys/items/edit?id="+$(this).attr('data-id');
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
		
        url = window.location.pathname+"/"+action+"?string="+getstring;
        window.location=url;
        
    }
    $('#actions-delete a').click(function() {
        if ($(this).hasClass('active')) openChecked("delete");
    }).attr('href', 'javascript:void(0);');
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
</script></div><?php

use_connection('91f');