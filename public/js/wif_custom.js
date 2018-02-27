/**
 * Validate an IP is valid or not?
 *
 * @ip: ip address or ip range
 * @type: 'opt_ip' for ip 'or opt_ip_range' for ip range
 */
function ip_validation(ip,type){
	if(type=="opt_ip")
		var ipformat = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;  
	else if(type=="opt_ip_range")
		var ipformat = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\/(3[0-2]|2[0-9]|1[0-9]|[0-9])$/;  
	 if(ip.match(ipformat))  
	 {  
		return true;  
	 }  
	 else{  
		return false;
	 }  
}

/**
 * Validate an E-mail is valid or not?
 *
 * @email: E-mail is to be controlled
 *
 */
function validateEmail(email) {
    var regex = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
    return regex.test(email);
}

/**
 * Converts the file size to human-readable format
 *
 * @bytes: file size in bytes
 * @si: true if 1 kB = 1000 B
 *      false if 1 kB = 1024 B
 *
 */
function humanFileSize(bytes, si) {
    var thresh = si ? 1000 : 1024;
    
	if(Math.abs(bytes) < thresh) {
        return bytes + ' B';
    }
	
    var units = si
        ? ['kB','MB','GB','TB','PB','EB','ZB','YB']
        : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
    var u = -1;
    
	do {
        bytes /= thresh;
        ++u;
    } while(Math.abs(bytes) >= thresh && u < units.length - 1);
    
	return bytes.toFixed(2)+' '+units[u];
}

// Equivalent of PHP’s number_format
function number_format(number, decimals, dec_point, thousands_sep) {
  number = (number + '')
    .replace(/[^0-9+\-Ee.]/g, '');
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function(n, prec) {
      var k = Math.pow(10, prec);
      return '' + (Math.round(n * k) / k)
        .toFixed(prec);
    };
  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
    .split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '')
    .length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1)
      .join('0');
  }
  return s.join(dec);
}

// Keep selected tab on page refresh
function rememberTabSelection(tabPaneSelector, useHash) {
	var key = 'selectedTabFor' + tabPaneSelector;
	if(get(key)) 
		$(tabPaneSelector).find('a[href="' + get(key) + '"]').tab('show');

	$(tabPaneSelector).on("click", 'a[data-toggle]', function(event) {
		set(key, this.getAttribute('href'));
	}); 

	function get(key) {
		return useHash ? location.hash: localStorage.getItem(key);
	}

	function set(key, value){
		if(useHash)
			location.hash = value;
		else
			localStorage.setItem(key, value);
	}
}

// Sweet Alert Customized Functions
function alertBox(title, message, type){
	swal({   
		title: title,   
		text: message,
		type: type,
		html: true
	});
}

function confirmBox(title, message, type, the_function, closeOnConfirm){
	if (typeof(closeOnConfirm) === 'undefined') closeOnConfirm = false;
	swall_obj ={
		title: title,   
		text: message,   
		type: type,
		html: true,
		showCancelButton: true,   
		confirmButtonColor: "#DD6B55",   
		confirmButtonText: "OK",
		cancelButtonText: "Vazgeç", 
		closeOnConfirm: closeOnConfirm 
	};

	swal(swall_obj, the_function);
}

function jquery_escape(the_str){
	return the_str.replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, "\\$&");
}

function getCookie( name ) {
	var parts = document.cookie.split(name + "=");
	if (parts.length == 2) return parts.pop().split(";").shift();
}

function expireCookie( cName ) {
document.cookie =
  encodeURIComponent( cName ) +
  "=deleted; expires=" +
  new Date( 0 ).toUTCString();
}

/*
 * Display the error message on the relevant input field
 *
 * It is displayed for 5 seconds and then automatically closed.
 */
function pop_up_source_error(element, message,time=true){

	$("#"+element).hide();
	$("#"+element).html(message);
	$("#"+element).fadeIn(200,function(){

		if(time == true)
			$(this).fadeOut(5000);
	});
}

function download_file(url, data, method){
    //url and data options required
    if( url && data ){
        //data can be string of parameters or array/object
        data = typeof data == 'string' ? data : jQuery.param(data);
        //split params into form inputs
        var inputs = '';
        jQuery.each(data.split('&'), function(){
            var pair = this.split('=');
            inputs+='<input type="hidden" name="'+ pair[0] +'" value="'+ pair[1] +'" />';
        });
        //send request
        jQuery('<form action="'+ url +'" method="'+ (method||'post') +'">'+inputs+'</form>')
        .appendTo('body').submit().remove();
    }
}

function custom_toastr(message, type="success"){
	setTimeout(function(){
		toastr.options = {
			"closeButton": true,
			"debug": false,
			"newestOnTop": false,
			"progressBar": true,
			"positionClass": "toast-top-right",
			"preventDuplicates": false,
			"onclick": null,
			"showDuration": "3000",
			"hideDuration": "2000",
			"timeOut": "5000",
			"extendedTimeOut": "1000",
			"showEasing": "swing",
			"hideEasing": "linear",
			"showMethod": "fadeIn",
			"hideMethod": "fadeOut"
		};
		toastr[type](message);
	},1300);
}

function get_event_logs(post_obj, error_str){

	result_obj = false;
	post_obj = JSON.stringify(post_obj);

	$.ajax({
		method:"POST",
		url:"/event_logs/get_events",
		async:false,
		data:"data="+post_obj,
		success:function(return_text){
			if($.trim(return_text) != "ERROR"){
				result_obj = JSON.parse(return_text);
			}
			else{
				alertBox("Oops...",error_str,"error");
			}
		}
	});

	return result_obj;
}

function createJsTree(
	data,
	distributor_id,
	div,
	lang,
	contextmenu=true,
	checkbox=false,
	multiple=false,
	selected_id=false,
	three_state=true,
	checked_ids = false
){
    window.is_auto_move = false;

    return $('#'+div).jstree({
		'core' : {
			'check_callback' : contextmenu,
			'themes' : {
				'variant' : 'large',
				'responsive': false
			},
			'animation': 500,
			'strings' : {
				'New node' : lang.new_node
			},
			'multiple' : multiple, // if checkbox enabled, multiple selection can be enable|disabled
			'data' : JSON.parse(data),
			error : function(err) {
				if(err.id === 'unique_01') {
					alertBox('', lang.same_name_warning, 'warning');
				}
			}
		},
		'types' : {
			'default' : {
				'icon': false
			}
		},
		'checkbox' : {
			'keep_selected_style' : false,
			'three_state': three_state,
            'tie_selection':three_state
		},
		'contextmenu':{
			'items': function(node) {
				if( node.id.indexOf("client") >= 0 ){
					return false;
				}

				if (node.parents.length < 2) { // if the selected node is master node, than return only create option
					return {
						'Create': {
							'icon': 'fa fa-plus-square-o',
							'separator_before': false,
							'separator_after': false,
							'label': lang.add,
							'action': function (data) {
								parent_id = $('#div_schema').jstree('get_selected');
								$.ajax({
									method:'POST',
									url:'/organization_schema/add_node',
									data:'parent_id='+parent_id+'&distributor_id='+distributor_id,
									success:function(return_text){
										if( $.isNumeric(return_text) && Math.floor(return_text) == return_text && Math.abs(return_text) == return_text ){
											// CREATE client side too
											var ref = $.jstree.reference(data.reference);
											sel = ref.get_selected();
											if(!sel.length) {
												return false;
											}
											sel = sel[0];
											sel = ref.create_node(sel, {"id":return_text, "parent":parent_id});
											if(sel) {
												ref.edit(sel);
											}
										}
										else{
											alertBox('', lang.unexpected_error , 'error');
										}
									}
								});

							}
						}
					};
				}

				return {
					'Create': {
						'icon': 'fa fa-plus-square-o',
						'separator_before': false,
						'separator_after': false,
						'label': lang.add,
						'action': function (data) {
							parent_id = $('#div_schema').jstree('get_selected');

							$.ajax({
								method:'POST',
								url:'/organization_schema/add_node',
								data:'parent_id='+parent_id+'&distributor_id='+distributor_id,
								success:function(return_text){
									if( $.isNumeric(return_text) && Math.floor(return_text) == return_text && Math.abs(return_text) == return_text ){
										// CREATE client side too
										var ref = $.jstree.reference(data.reference);
										sel = ref.get_selected();
										if(!sel.length) {
											return false;
										}
										sel = sel[0];
										sel = ref.create_node(sel, {'id':return_text, 'parent':parent_id});
										if(sel) {
											ref.edit(sel);
										}
									}
									else{
										alertBox('', lang.unexpected_error , 'error');
									}
								}
							});
						}
					},
					'Rename': {
						'icon': 'fa fa-edit',
							'separator_before': false,
							'separator_after': false,
							'label': lang.rename,
							'action': function (data) {
								var inst = $.jstree.reference(data.reference);
								obj = inst.get_node(data.reference);
								inst.edit(obj);
							}
					},
					'Remove': {
						'icon': 'fa fa-trash-o',
						'separator_before': true,
						'separator_after': false,
						'label': lang.delete,
						'action': function (data) {
							node_id = $('#div_schema').jstree('get_selected');

                            var node = $("#"+div).jstree().get_node(node_id);
                            var children = node.children;

                            if( children != "" ){
                                return alertBox('', lang.not_deletable_warning, 'warning');
                            }

							confirmBox(
								'',
								lang.delete_node_warning,
								'warning',
								function(){
									$.ajax({
										method:'POST',
										url:'/organization_schema/delete_node',
										data:'id='+node_id,
                                        async: false,
										success:function(return_text){
											if(return_text == 'SUCCESS'){
												// DELETE client side too
												var ref = $.jstree.reference(data.reference),
													sel = ref.get_selected();
												if(!sel.length) {
													return false;
												}
												ref.delete_node(sel);
											}
											else if( return_text.indexOf('ERROR') >= 0 ){
                                                return alertBox('', lang.unexpected_error , 'error');
                                            }
											else{
                                                return alertBox('', return_text , 'warning');
											}
										}
									});
                                },
								false
							);
						}
					}
				};
			}
		},
        'sort': function (a, b) {
			if(a.indexOf("client") >= 0 && b.indexOf("client") >= 0){
                return this.get_text(a).toLowerCase() > this.get_text(b).toLowerCase() ? 1 : -1;
			}
			else if(a.indexOf("client") >= 0){
                return 1;
			}
            else if(b.indexOf("client") >= 0){
                return -1;
            }
			else{
                return this.get_text(a).toLowerCase() > this.get_text(b).toLowerCase() ? 1 : -1;
			}
        },
		'plugins' : [
			'themes',
			(contextmenu) ? 'contextmenu' : '',
            (checkbox) ? 'checkbox' : '',
			'sort', // Automatically sorts all siblings in the tree
			'unique', // Enforces that no nodes with the same name can coexist as siblings
			'types',
            (contextmenu) ? 'dnd' : '',
		]
	})
    .on('select_node.jstree', function (e, data) {
        $("#"+div).next().find(".form-control").val("");

        if(data.node.id.indexOf("client")>=0){
			$("#"+div).next().find(".form-control").attr("disabled","disabled");
            $("#"+div).next().find("button").attr("disabled","disabled");
			return;
		}
		else{
            $("#"+div).next().find(".form-control").removeAttr("disabled");
            $("#"+div).next().find("button").removeAttr("disabled");
		}

		if( contextmenu == true ){
            load_node_detail(data.node.id, distributor_id);
		}

		if(contextmenu==false && checkbox==false){
			if (data.node.children.length > 0) {
                $('#'+div).jstree(true).deselect_node(data.node);
                $('#'+div).jstree(true).toggle_node(data.node);
            }
		}
    })
    .on('rename_node.jstree', function (e, data) {
        // This event runs after the rename operation is complete.
        var parent = data.node.parent;
        var node_id = data.node.id;
        var node_name = data.node.text;

        $.ajax({
            method:'POST',
            url:'/organization_schema/add_node',
            data:'node_id='+node_id+"&text="+node_name,
            success:function(return_text){
                if( return_text != 'SUCCESS' ){
                    alertBox('', lang.unexpected_error , 'error');
                }
            }
        });
    }).on('delete_node.jstree', function (e, data) {
        // This event runs after the delete operation is complete.
        alertBox('', '[' +data.node.text + '] ' + lang.node_deleted, 'success');
    }).on('create_node.jstree', function (e, data) {
		// This event runs after the create operation is complete.
	}).on('ready.jstree',function (e,data){
		if( contextmenu ){
            load_node_detail(0, distributor_id);
		}

		if(selected_id != false){
            $("#"+div).jstree("select_node", selected_id);
		}

		if( checked_ids !== false ){
            $('#'+div).jstree(true).deselect_all();

            $.each(checked_ids, function( index, value ) {
                $('#'+div).jstree(true).check_node(value);
            });
		}

		if(checkbox == true){
            $("#"+div).jstree("close_all");

            var $tree = $(this);
			$($tree.jstree().get_json($tree, {
				flat: true
			}))
			.each(function(index, value) {
				var node = $("#"+div).jstree().get_node(this.id);

				if(node.parent == "#"){
                    $("#"+div).jstree("open_node",this.id);
                    return false;
				}
			});
        }
	}).on('move_node.jstree',function(e,data){
		var parentNode = $('#'+div).jstree(true).get_node("[id='"+data.parent+"']");

        if(data.node.id.indexOf("client")>=0 && parentNode.children[0].indexOf("client")<0 && is_auto_move==false){
			is_auto_move = true;
			$("#"+div).jstree("move_node", data.node, data.old_parent);
            return;
		}

		if(data.parent.indexOf("client")>=0 || data.parent=="#"){
            is_auto_move = true;
            $("#"+div).jstree("move_node", data.node, data.old_parent);
		}
		else{
			if(is_auto_move == true){
				is_auto_move = false;
				return;
			}

			node_id = data.node.id;
			new_parent_id = data.parent;
            old_parent_id = data.old_parent;

            if(new_parent_id == old_parent_id)
            	return;

			$.ajax({
				method:'POST',
				url:'/organization_schema/move_node',
				data:'node_id='+node_id+'&new_parent_id='+new_parent_id+'&old_parent_id='+old_parent_id,
				success:function(return_text){
					if( return_text != "SUCCESS" ){

						is_auto_move = true;
                        $("#"+div).jstree("move_node", data.node, data.old_parent);
                        alertBox("",lang.unexpected_error,"error");
					}
				}
			});
		}
	}).on('check_node.jstree',function(e,data){
		if(data.node.id.indexOf('client')>=0){
            $('#'+div).jstree(true).uncheck_node(data.node.id);
            return false;
		}

		the_level = data.node.parents.length;
		checked_elements_1 = $("#"+div).jstree('get_checked');
		to_be_unchecked_elements = [];

		for(i=0; i<checked_elements_1.length;i++){
			the_node = $('#'+div).jstree(true).get_node(checked_elements_1[i]);

            if(the_level != the_node.parents.length){
            	to_be_unchecked_elements.push(the_node.id);
			}
		}

		$.each(to_be_unchecked_elements, function( index, value ) {
			$('#'+div).jstree(true).uncheck_node(value);
		});
	});
}