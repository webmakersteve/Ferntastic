<div class="content-wrapper" style="padding-top: 30px;"><?
use_connection('patsys');
//check if there is an ID to use
	
	$html = '<div id="fupload-div"></div><span id="fstatus-text"></span>';
	echo new WebForm('ItemEntry',
		array(
		'Name' => array('type' => 'text', 'label' => "Item Name"),
		'Cat' => array('type' => 'text', 'label' => "Category", 'edit' => false),
		'ItemPrice' => array('type' => 'text', 'label' => "Price"),
		'Qt' => array('type' => 'text', 'label' => "Stock"),
		'do' => array('type' => 'hidden', 'value' => 'add_item'),
		"Desc" => array('type' => 'textarea', 'label' => "Description"),
		'Tx' => array('type' => 'checkbox', 'label' => "Taxable", 'value' => 1),
		'Shp' => array('type' => 'text', 'label' => "Shipping Multiplier", 'value' => 100),
		'Wt' => array('type' => 'text', 'label' => "Weight"),
		'Len'=> array('type' => 'text', 'label' => "Length"),
		'Wid'=> array('type' => 'text', 'label' => "Width"),
		'Height'=> array('type' => 'text', 'label' => "Height"),
		'Attch' => array(
		  'type' => 'html',
		  'value' => $html,
		  'label' => "Attachments"
		),
		array('type' => "submit", 'value' => "Add Item")
		), 
		'/do-new.php'
	);
	
?></div>
<script type="text/javascript" src="/script/fupload.js"></script>
<script type="text/javascript">
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
            $('.input-div', container).append(description+'<input type="hidden" name="'+inputDiv+'" id="'+inputDiv+'" value="'+client+'">');

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

addAutocompleteClientsList("ItemEntry-Cat");
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
