jQuery(document).ready(function($) 
{
  // Set Timeout
  var t = setTimeout('fade_message()', 5000);

  $('#reindex-all').click(function() {

  		var note = 'This could take awhile...';

		//$.noty.growls.push('noty_layout_inline');
		$('#reindex-notifications').noty({
			animateOpen: {opacity: 'show'},
			animateClose: {opacity: 'hide'},
			layout: 'inline',
			theme: 'noty_theme_twitter',
			type: 'information',
			text: note, 
			buttons: [
			    {type: 'btn btn-primary', text: 'OK', click: function($noty) {
			    	$noty.close(); 
			    	$('#reindex-notifications').noty({force: true, theme: 'noty_theme_twitter', animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'inline', closable: false, text: '<img src="/wp-content/plugins/reindexer/assets/images/loader.gif" /> &nbsp; Running Reindexer..', type: 'success', 'timeout': false});
			    	reindex_blogs();
			    } 
			    }
			    ,
			    {type: 'btn btn-danger', text: 'Cancel', click: function($noty) {
			    	$noty.close(); 
			    	$('#reindex-notifications').noty({force: true, theme: 'noty_theme_twitter', animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'inline', text: 'Ok, maybe another time', type: 'error', 'timeout': 2000});
			    } 

			    }
		    ],
		  closable: false,
		  timeout: false,
		});

		

		return false;
		
	});

function reindex_blogs(){
	
	var blogs;

	$('#reindex-log').fadeIn('fast');

	$.ajax({
		type: "post",
		async: false,
		url: "/wp-admin/admin-ajax.php",
		dataType: "json",
		data: {
			action: 'init_reindex',
			_ajax_nonce: $('#_wpnonce').val()
		},success: function(data){
			blogs = data;
		}
	});
	
	$("#form-actions input:button").attr('disabled', true);

	$.each(blogs, function(index, blog){
	
		$.ajax({
			type: "post",
			url: "/wp-admin/admin-ajax.php",
			dataType: "json",
			data: {
				action: 'reindex_blog',
				_ajax_nonce: $('#_wpnonce').val(),
				blog_id: blog.blog_id
			},
			beforeSend: function() {
				$('#reindex-status span').html('Indexing ' + blog.blog_name + " - " + blog.post_count + " x posts");
				$('#reindex-status').fadeIn('fast');
			},
			success: function(data){
				var icon;
				if(data.status == 'success'){
					icon = '<img src="/wp-content/plugins/reindexer/assets/images/tick-icon.gif" />';
				}else{
					icon = '<img src="/wp-content/plugins/reindexer/assets/images/cross-icon.gif" />';
				}
				var entry = '<li><span class="icon">' + icon + '</span> <span class="summary">' + blog.blog_name + ' ( ' + blog.post_count + ' x posts indexed )</span></li>';
				$('#reindex-log ul').append(entry);

				ajaxFinished = true;
			}
		});
		
	});

	$("#form-actions input:button").removeAttr('disabled');
	$("#reindex-form input:checkbox").removeAttr('disabled');
}

    
});



// Fade Out Message
function fade_message() {
  jQuery('.fade').fadeOut(500);
  clearTimeout(t);
}