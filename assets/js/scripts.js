jQuery(document).ready(function($) 
{
  // Set Timeout
  var t = setTimeout('fade_message()', 5000);

  $('#reindex-all').click(function() {

  		$.noty.closeAll();
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
			    	$('#reindex-notifications').noty({force: true, theme: 'noty_theme_twitter', animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'inline', text: '<img src="/wp-content/plugins/reindexer/assets/images/loader.gif" /> &nbsp; Running Reindexer..', type: 'success', 'timeout': false, closable: false});
			    	reindex_blogs();
			    } 
			    }
			    ,
			    {type: 'btn btn-danger', text: 'Cancel', click: function($noty) {
			    	$noty.close(); 
			    	$('#reindex-notifications').noty({force: true, theme: 'noty_theme_twitter', animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'inline', text: 'Ok, maybe another time', type: 'error', 'timeout': 2000, closable: false});
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
	$("#form-actions input:button").attr('disabled', true);
	$(".tablenav-pages").hide();

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

	var q = new $.AsyncQueue();

	$.each(blogs, function(index, blog){
		q.add(function(q){
			index_blog(q, blog);
		});
	});
	
	q.onComplete(function(){
		$.noty.closeAll();
		$('#reindex-notifications').noty({force: true, theme: 'noty_theme_twitter', animateOpen: {opacity: 'show'}, animateClose: {opacity: 'hide'}, layout: 'inline', closable: false, text: 'Reindex Complete', type: 'alert', 'timeout': false});

		$('#reindex-log').append('<div>COMPLETED</div>');

		$("#form-actions input:button").removeAttr('disabled');
		$("#reindex-form input:checkbox").removeAttr('disabled');
		$(".tablenav-pages").show();
	});
	q.run();

}



function index_blog(q, blog){

	q.pause();

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
			$('#reindex-status span').html('Indexing ' + blog.blog_name + " - " + number_format(blog.post_count) + " x posts");
			$('#reindex-status').fadeIn('fast');
		},
		success: function(data){

			console.log(data);

			var icon;
			if(data.status == 'success'){
				icon = '<img src="/wp-content/plugins/reindexer/assets/images/tick-icon.gif" />';
				msg = blog.blog_name + ' ( ' + number_format(blog.post_count) + ' x posts indexed )';

				chkbox = $('#reindex-form table tbody tr th input[value=' + blog.blog_id + ']');

				if(chkbox){
					row = chkbox.parent().parent();
					row.find('.last_indexed').html(data.timestamp);
					row.find('.indexed_items').html(number_format(blog.post_count));
				}
			}else{
				icon = '<img src="/wp-content/plugins/reindexer/assets/images/cross-icon.gif" />';
				msg = blog.blog_name + ' - does not exist';
			}
			var entry = '<li><span class="icon">' + icon + '</span> <span class="summary">' + msg + '</span></li>';
			$('#reindex-log ul').append(entry);

			q.run();
		}
	});

}

    
});



// Fade Out Message
function fade_message() {
  jQuery('.fade').fadeOut(500);
  clearTimeout(t);
}

function number_format (number, decimals, dec_point, thousands_sep) {
 
    number = (number + '').replace(/[^0-9+-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/B(?=(?:d{3})+(?!d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}