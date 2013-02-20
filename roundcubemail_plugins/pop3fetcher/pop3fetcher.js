if (window.rcmail){
	rcmail.addEventListener('init', function (event){
		rcmail.addEventListener('plugin.edit_do_ok', pop3fetcher_edit_do_ok);
		rcmail.addEventListener('plugin.add_do_ok', pop3fetcher_add_do_ok);
		rcmail.addEventListener('plugin.delete_do_ok', pop3fetcher_delete_do_ok);
		rcmail.addEventListener('plugin.edit_do_error_connecting', pop3fetcher_edit_do_error_connecting);
		rcmail.addEventListener('plugin.add_do_error_connecting', pop3fetcher_add_do_error_connecting);
	});
}

function pop3fetcher_edit_do(){
	var params = {  '_edit_do': '1',
					'_pop3fetcher_id': $("#pop3fetcher_id").val(),
					'_pop3fetcher_email': $("#pop3fetcher_email").val(),
					'_pop3fetcher_username': $("#pop3fetcher_username").val(),
					'_pop3fetcher_password': $("#pop3fetcher_password").val(),
					'_pop3fetcher_serveraddress': $("#pop3fetcher_serveraddress").val(),
					'_pop3fetcher_serverport': $("#pop3fetcher_serverport").val(),
					'_pop3fetcher_ssl': $("#pop3fetcher_ssl").val(),
					'_pop3fetcher_leaveacopy': $("#pop3fetcher_leaveacopy").is(":checked")
	};
	rcmail.http_post('plugin.pop3fetcher', params);
}

function pop3fetcher_edit_do_ok(){
	window.location='?_task=settings&_action=edit-prefs&_section=pop3fetcher&_framed=1';
}

function pop3fetcher_edit_do_error_connecting(){
	alert(rcmail.gettext('pop3fetcher.account_unableconnect')+" "+$("#pop3fetcher_serveraddress").val()+":"+$("#pop3fetcher_serverport").val());
}

function pop3fetcher_add_do(){
	var params = {  '_add_do': '1',
					'_pop3fetcher_id': $("#pop3fetcher_id").val(),
					'_pop3fetcher_email': $("#pop3fetcher_email").val(),
					'_pop3fetcher_username': $("#pop3fetcher_username").val(),
					'_pop3fetcher_password': $("#pop3fetcher_password").val(),
					'_pop3fetcher_serveraddress': $("#pop3fetcher_serveraddress").val(),
					'_pop3fetcher_serverport': $("#pop3fetcher_serverport").val(),
					'_pop3fetcher_ssl': $("#pop3fetcher_ssl").val(),
					'_pop3fetcher_leaveacopy': $("#pop3fetcher_leaveacopy").is(":checked")
	};
	rcmail.http_post('plugin.pop3fetcher', params);
}

function pop3fetcher_add_do_ok(){
	window.location='?_task=settings&_action=edit-prefs&_section=pop3fetcher&_framed=1';
}

function pop3fetcher_add_do_error_connecting(){
	alert(rcmail.gettext('pop3fetcher.account_unableconnect')+" "+$("#pop3fetcher_serveraddress").val()+":"+$("#pop3fetcher_serverport").val());
}

function pop3fetcher_delete_do(element, id){
	$(element).parents("tr").addClass("to_be_removed");
	var params = {  '_delete_do': '1',
					'_pop3fetcher_id': id
	};
	rcmail.http_post('plugin.pop3fetcher', params);
}

function pop3fetcher_delete_do_ok(){
	$(".to_be_removed").remove();
}
