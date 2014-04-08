<div class="content-wrapper" style="padding-top: 30px;"><?
use_connection('patsys');
//check if there is an ID to use
	
	//make sure the itemid exists 
	
	if (!isset($_GET['id'])) {
		?>ID isn't set!<?php	
	} else {
	
	//now check if the menu item exists
	
	$id = $_GET['id'];
	Fn()->load_extension('fquery');
	
	$img = fQuery( 'patsys_uploads', "id,src,active[x=1],item[x=?],time:order('desc')", $id );
	if ($img->count>0) {
		$IMGS = '<ul class="qq-fake">';
		$img->each(function($data) use (&$IMGS) {
			$IMGS .= '<li class="qq-upload-success" data-id="'.$data->id.'"><span class="qq-upload-file"><a class="ferns" style="text-decoration: none;" href="/files/'.basename($data->src).'?w=200">'.basename($data->src).'</a> <a href="javascript: void(0);" class="delete">[x]</a></span></li>';
		});
		$IMGS .= '</ul>';
	} else $IMGS = '';
	
	$f = fQuery('items', 'id[x=?],active[x=1],*', e($id));
	
	if ($f->count > 0):
	
		$html = '<div id="fupload-div"></div><span id="fstatus-text"></span><div class="files"></div>';
		$t = $f->this();
		$catname = lookup('patsys_item_taxonomy',  $t->categories, '[displayname]', 'id');
		
		$ItemData = json_decode($t->data);
		
		echo new WebForm('ItemEdit',
			array(
			'Name' => array('type' => 'text', 'label' => "Item Name", 'value' => $t->name),
			'Cat' => array('type' => 'text', 'label' => "Category", 'edit' => true, 'alt' => $catname, 'value' => $t->categories),
			'ItemPrice' => array('type' => 'text', 'edit' => ($t->price<0) ? true : false, 'alt' => 'User Entered',  'label' => "Price", 'value' => $t->price),
			'Qt' => array('type' => 'text', 'label' => "Stock", 'value' => $t->owned),
			'do' => array('type' => 'hidden', 'value' => 'edit_item'),
			'site' => array('type' => 'hidden', 'value' => 'patsys'),
			"Desc" => array('type' => 'textarea', 'label' => "Description", 'value' => $t->description),
			'ItemID' => array('type' => "hidden", 'value' => $id),
			'Tx' => array('type' => 'checkbox', 'label' => "Taxable", 'value' => 1, 'checked' => $t->taxable == 1),
			'Shp' => array('type' => 'text', 'label' => "Shipping Multiplier", 'value' => $t->shipping_coefficient),
			'Wt' => array('type' => 'text', 'label' => "Weight", 'value' => $t->weight),
			'Len'=> array('type' => 'text', 'label' => "Length", 'value' => (isset($ItemData->length) and $ItemData->length > 0) ? $ItemData->length : 0),
			'Wid'=> array('type' => 'text', 'label' => "Width", 'value' => (isset($ItemData->width) and $ItemData->width > 0) ? $ItemData->width : 0),
			'Height'=> array('type' => 'text', 'label' => "Height", 'value' => (isset($ItemData->height) and $ItemData->height > 0) ? $ItemData->height : 0),
			'Attch' => array(
			  'type' => 'html',
			  'value' => $IMGS.$html,
			  'label' => "Attachments"
			),
			array('type' => "submit", 'value' => "Update Item")
			), '/do-new.php'
			
		);
		
	else:
	
		?>That item does not exist.<?php
	
	endif;
	
	}
	
?></div>
<script type="text/javascript" src="/script/fupload.js"></script>
<script type="text/javascript">
function setupFileup(statusText, div) {
	
	ul = $('.qq-upload-list', div);
	var status=$('#'+statusText); 
	new qq.FileUploader({
		element: $('#'+div)[0],
		debug: true,
		action: '/ajax/file_upload.php',
		allowedExtensions: ['jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'pdf', 'txt', 'rtf', 'html', 'xml', 'tiff'],
		//Name of the file input box  
		name: 'uploadfile',  
		onSubmit: function(file, ext){  

		},
		params: {ajax : true},
		onComplete: function(id, file, responseJSON){  
			//On completion clear the status 
			$('progress', $('li:last-child', '.qq-upload-list')).remove();
			status.html('');

			//Add uploaded file to list  
			if(responseJSON.success == true){

				newfile = responseJSON.filename;
				$('#'+div).parentsUntil('form').parent().append('<input type="hidden" id="hiddenfile'+id+'" class="hid_files" name="uploads[]" value="'+newfile+'">');
				if (newfile.toLowerCase().indexOf("jpg") > 0 || newfile.toLowerCase().indexOf("jpeg") > 0) $('li:last-child', '.qq-upload-list').append('<br><img src="'+newfile.substr(newfile.indexOf('/files'))+'?w=250" alt="">');
				
			} else{

			}

		},
		onProgress: function(id, fileName, loaded, total) {

			if ($('progress', $('li:last-child', '.qq-upload-list')).length < 1) {
				$('li:last-child', '.qq-upload-list').append('<progress id="progress" max="100" value="1"></progress>');
			}

			$('.qq-upload-size', $('li:last-child', '.qq-upload-list')).hide();
			$('.qq-upload-spinner', $('li:last-child', '.qq-upload-list')).hide();
			$('.qq-upload-cancel', $('li:last-child', '.qq-upload-list')).hide();

			loaded = parseInt(loaded);
			total = parseInt(total);
			currPercent = loaded/total;

			progress_bar = $('#progress', this.element);
			progress_bar.val(currPercent*100);

		},

		onCancel: function(id, fileName) {
			
		},

	});  

}
setupFileup('fstatus-text', 'fupload-div');

function openAltData(jQx) {

    jQx.parents('.editable').children('.input-div').show();
    jQx.parent().hide();

}

//Make AJAX delete item call
$(function() {
	$('.delete').click(function() {
		li = $(this).parents('.qq-upload-success');
		fileId = $(this).parents('.qq-upload-success').attr('data-id');
		//now we need to make a call to delete it
		$.post('/do-new.php', {do:'delete_file' , fileId: fileId, ajax: true}, function(data) {
			if (data.status=="ok") {
				li.fadeOut(300, null, function() {
					li.remove();
				});
			} else {
				alert('Couldn\'t delete that item.');	
			}
		}, 'json');
	});

	$.each($('a.edit'), function() {
    
		$(this).click(function() {
			openAltData($(this));
			x = $(this).parentsUntil('.form-row').parent();
			$('input', x).val('');
			addAutocompleteClientsList($('input', x).attr('id'));
		});
		
	});

});

function addAutocompleteClientsList(inputDiv) {

    $( "#"+inputDiv ).autocomplete({

        source: function( request, response) {
			
            $.ajax({
                url: '/ajax/load_categories.php',
                type: "GET",
                dataType: 'json',
                data: {query:$('#'+inputDiv).val()},
                success: function( data ) {

                    if (data.status == "ok") {
						
                        response( $.map( data.response, function( item ) {

                            desc = item.description;
                            val = item.value;
                            
                            return {
                                description: desc,
                                value: val,
                            }

                        }));													

                    } else {

                        //response is not ok

                    }

                }

            });

        },
        minLength: 1,
        select: function( event, ui ) {
            //other ajax function
            client = ui.item.value
            description = ui.item.description;
            container = $('#'+inputDiv).parentsUntil('.form-row').parent();
            container.css('height', container.height()+"px").css('vertical-align', 'middle');
            
            $('.input-div', container).css('padding-top', '5px');
            $('#'+inputDiv).remove();
            $('.input-div', container).append(description+'<input type="hidden" name="Cat" id="'+inputDiv+'" value="'+client+'">');

            return false;

        },
        appendTo: $('#'+inputDiv).parentsUntil('.Cat').parent()
    }).data( "autocomplete" )._renderItem = function( ul, item ) {

        if (item.value==-1) {											
            return $( "<li></li>" )
                .append( '<a href="javascript: void(0);">Add <strong>'+$('#'+inputDiv).val()+'</strong> to <strong>'+inputDiv.toLowerCase()+'s</strong></a>' )
                .appendTo( ul );
        } else {
            return $( "<li></li>" )
                .data( "item.autocomplete", item )
                .append( '<a href="javascript: void(0);" data-id="'+item.value+'">' + item.description + "</a>" )
                .appendTo( ul );
        }

    }

}



</script>
<style type="text/css">
/* FUPLOAD */
.qq-uploader { position:relative; width: 100%;}
.qq-upload-button {
    font-size:1em;font-weight:400;
	height:27px;line-height:27px;
	margin-right:16px;min-width:58px;
	outline:0;padding:5px 10px;
	text-align:center;
	background: none repeat 0 0 #35845F;
	color: white;
	text-decoration: none;
	border: 1px solid #48b482;
	transition: all .1s linear;
	-moz-transition: all .1s linear;
	-webkit-transition: all .1s linear;
	-o-transition: all .1s linear;
	display: inline;
}

.qq-upload-button:hover, .qq-upload-button-hover, .qq-upload-button-focus {

	transition: all .1s linear;
	-moz-transition: all .1s linear;
	-webkit-transition: all .1s linear;
	-o-transition: all .1s linear;
	color: white;
	text-decoration: none;
	background-color: #44a879}
.qq-upload-spinner {display: none;}
.qq-upload-size {}

.qq-upload-drop-area {
    position:absolute; top:0; left:0; width:100%; height:100%; min-height: 70px; z-index:2;
    background:#FF9797; text-align:center; 
}

.qq-upload-drop-area span {
    display:block; position:absolute; top: 50%; width:100%; margin-top:-8px; font-size:16px;
}

.qq-upload-drop-area-active {background:#FF7171;}
.qq-upload-list, .qq-fake {margin:15px 16px; padding:0; list-style: square;}
.qq-upload-list li, .qq-fake li { margin:0; padding:0; line-height:15px; font-size:12px;}
.qq-upload-file, .qq-upload-spinner, .qq-upload-size, .qq-upload-cancel, .qq-upload-failed-text {
    margin-right: 7px;
}

.qq-upload-cancel {display: none;}
.qq-upload-file {}
.qq-upload-spinner {display:inline-block; background: url("//socollege.me/img/ajax-loader.gif"); width:15px; height:15px; vertical-align:text-bottom;}

.qq-upload-size,.qq-upload-cancel {font-size:11px;}
* .qq-upload-size {display: none;}
.qq-upload-fail {color: #999;}
.qq-upload-fail .qq-upload-size {display: none;}
.qq-upload-failed-text {display: none;}
.qq-upload-fail .qq-upload-failed-text {display:inline;}

/* END FUPLOAD*/
</style>
