/*
	dev		.g1
			.name			用户自定义名称
			.model			设备型号名称
			.state
			.tz
			.maker		(设备生产厂商，未实现)
			.lo				经度 （以后实时更新）
			.la				纬度 （以后实时更新）
			.data[]			Object 数组
			
	data[j] .d_id			参数id
			.lt				UTC时间，最后更新参数
			.plot			flot handler/ or UI handle
			.d_name			参数名称
			.ss				ss==0 累积数据; ss==1 不累积数据; ss==2 图片显示; ss==3 视频显示 ss==4 文本数据;
			.unit			单位
			.new_v			最新数据值
			.new_t			更新时间
			.real_h			图像实际 height
			.real_w			图像实际 width
			.update_fun		此参数的更新函数
			
	op[j] .id
		  .name
		  .remark
		  .p_num
		  .P[0]....
	
	P[j] .id
		 .name
		 .remark
		 .unit
*/

var dev = new Object();
/*
dev.name = '我的设备1';
dev.model = 'swaytech-1';
dev.g1 = '00001';
dev.tz = 8;
dev.lt = 0;
dev.maker = 'swaytech';
dev.lo = '104.06<sup>。</sup>';
dev.la = '30.67<sup>。</sup>';
*/

dev.data = new Array();
/*
var mid = new Object();
mid.name = '污水'
dev.data[0] = mid;
dev.data[1] = mid;
dev.data[2] = mid;
dev.data[3] = mid;
dev.data[4] = mid;
dev.data[5] = mid;
dev.data[6] = mid;
dev.data[7] = mid;
dev.data[8] = mid;
dev.data[9] = mid;
dev.data[10] = mid;
dev.data[11] = mid;
dev.data[12] = mid;
dev.data[13] = mid;
dev.data[14] = mid;
dev.data[15] = mid;
dev.data[16] = mid;
dev.data[17] = mid;
dev.data[18] = mid;
dev.data[19] = mid;
dev.data[20] = mid;
dev.data[21] = mid;
dev.data[22] = mid;
dev.data[23] = mid;
dev.data[24] = mid;
dev.data[25] = mid;
*/
var op = new Array();
/*
op[0] = new Object();
op[0].id = 1;
op[0].name = 'w1';
op[0].p_num = 2;
op[0].P = new Array();
op[0].P[0] = { id:23, name:'高度', unit:'m', remark:'控制设备高度信息' };
op[0].P[1] = { id:24, name:'方位', unit:'m', remark:'控制设备方位信息' };

op[1] = new Object();
op[1].id = 2;
op[1].name = 'w2';
op[1].p_num = 0;
*/
// devs_table - 添加设备 tr 元素的父对象，jquery对象
// dev_i - 待添加设备在 devs 数组中的索引
function add_dev_info() {
	var main = $('#dev_info');
	main.width( $('body').width()-40 );
	
	var li = $('<li class="dev_info_li"></li>');
	var table = $("<table><caption class='title'>设备信息</caption><tbody></tbody></table>");
	li.append( table );
	
	var tbody = table.children('tbody');						
	tbody.append( $('<tr><th width="40%">设备名称：</th><th id="dev_info_model" width="60%">'+dev.model+'</th></tr>') );
	tbody.append( $('<tr><th width="40%">昵称：</th><th id="dev_info_name" width="60%">'+dev.name+'</th></tr>') );
	tbody.append( $('<tr><th width="40%">设备guid号：</th><th id="dev_info_g1" width="60%">'+dev.g1+'</th></tr>') );
	tbody.append( $('<tr><th width="40%">所在地经纬度：</th><th id="dev_info_lo_la" width="60%">'+dev.lo+"&nbsp;"+dev.la+'</th></tr>') );
	if (dev.tz>0)
		tbody.append( $('<tr><th width="40%">所在地时区：</th><th id="dev_info_tz" width="60%">东'+dev.tz+'区</th></tr>') );
	else
		tbody.append( $('<tr><th width="40%">所在地时区：</th><th id="dev_info_tz" width="60%">西'+Math.abs(dev.tz)+'区</th></tr>') );
	
	tbody.append( $('<tr><th width="40%">生产厂家：</th><th id="dev_info_company" width="60%">'+dev.maker+'</th></tr>') );

	main.append( li );
	
	if( dev.update_fun==='' )
		dev.update_fun = function() {
			$('#dev_info_name').html( dev.name );
			$('#dev_info_model').html( dev.model );
			$('#dev_info_g1').html( dev.g1 );
			$('#dev_info_lo_la').html( dev.lo+"&nbsp;"+dev.la );
			if (dev.tz>0)
				$('#dev_info_tz').html( '东'+dev.tz+'区' );
			else
				$('#dev_info_tz').html( '西'+Math.abs(dev.tz)+'区' );
			
			$('#dev_info_company').html( dev.maker );
		};
}

// d_i_s - 从第几个索引参数开始显示
// 返回第一个未显示参数的索引， 全部显示完后，返回 -1
function add_data_info( d_i_s ) {
	var main = $('#dev_info');
	
	var li = $('<li class="data_info_li"></li>');
	if ( d_i_s!=0 )
		li.css( {'width':'31%','margin-left':'20px'} );
	
	var table = $("<table><caption class='title'>数据最新更新</caption><tbody></tbody></table>");
	li.append( table );
	table = table.children('tbody');
	
	var d_index = -1;
	$.each( dev.data, function(i,v) {
		if (i<d_i_s)
			return true;
		
		if ( i>=(7+d_i_s) ) {
			li.css( {'width':'31%','margin-left':'20px'} );
			d_index = i;
			return false;
		}	
		var tr = $('<tr></tr>');			//  后续时需要修改此代码
		table.append( tr );	
		var ths = $('<th width="34%">'+dev.data[i].name+'</th><th id="'+i+'_data_info_v" width="28%">no data</th><th id="'+i+'_data_info_t" width="38%">2014-12-19 12:45:20</th>');
		tr.append( ths );		
	} );
	main.append( li );
	
	return d_index;
}

// 当设备参数大于 14 个时，建立专门的 data_info zone 显示数据最新信息
function add_data_info_in_zone() {
	
	var p_num = dev.data.length;
	var f_num = Math.ceil( p_num/2 ) - 1 ;
	
	var main = $('#dev_info');
	main.css( 'margin-bottom', 0 );
	var zone = $( "<ul class='modules' id='data_info_zone' style='padding:20px;background:#a0a0a0'></ul>" );
	zone.width( $('body').width()-40 );
	main.after( zone );

	var li_width = ( zone.width()-20 ) / 2;
	var li_height = 24 * (f_num+2) + f_num*4;
	
	var li_1 = $('<li class="data_info_li"></li>');
	li_1.css( {'width':li_width+'px','margin':'0 10px 0 0', 'height':li_height+'px'} );
	var table_1 = $("<table><caption class='title'>数据最新更新</caption><tbody></tbody></table>");
	li_1.append( table_1 );
	table_1 = table_1.children('tbody');
	zone.append( li_1 );
	
	var li_2 = $('<li class="data_info_li"></li>');
	li_2.css( {'width':li_width+'px','margin':'0 0 0 10px', 'height':li_height+'px'} );
	var table_2 = $("<table style='margin:10px 0'><tbody></tbody></table>");
	li_2.append( table_2 );
	table_2 = table_2.children('tbody');
	zone.append( li_2 );
	
	$.each( dev.data, function(i,v) {
		var tr = $('<tr></tr>');			//  后续时需要修改此代码
		if( i<f_num )
			table_1.append( tr );	
		else
			table_2.append( tr );
		
		var ths = $('<th width="35%">'+dev.data[i].name+'</th><th id="'+i+'_data_info_v" width="25%">no data</th><th id="'+i+'_data_info_t" width="40%">2014-12-19 12:45:20</th>');
		tr.append( ths );	
	} );
}

function info_xml_parser( responseTxt ) {
			
	var xml = $(responseTxt);
	if ( xml.length<=0 )
		return false;
	
	dev.name = xml.find('name').text();
	dev.model = xml.find('model').text();
	dev.maker = xml.find('maker').text();
	dev.tz = parseInt( xml.find('tz').text() );
	dev.lo = xml.find('longitude').text()+'<sup>。</sup>';
	dev.la = xml.find('latitude').text()+'<sup>。</sup>';
	
	return true;
}

// 返回更新参数在 dev.data 中的 index
function data_xml_parser( responseTxt ) {
			
	var xml = $(responseTxt);
	if ( xml.length<=0 )
		return -1;

	var ds = xml.find('d');
	if( ds.length<=0 )
		return -1;

	var res= -1;
	
	$.each( ds, function(i,value) {
		var v = $(value);
		var d_id = parseInt( v.attr('id') );
	
		var index = get_index( d_id );
		if( index<0 )
			return -1;
		
		var b_t = v.children('base_t').text();
		if( b_t==='' )
				b_t = 0;
		else
			b_t = parseFloat( b_t );

		var vs = v.children('v');
		var v = new Array(), t = new Array();
		
		$.each( vs, function(i,value) {
			var tv = $(value);
			var u = dev.data[index].unit;
			if( u.indexOf('file')>=0 | u=='bin' | u=='utf8' )
				v[i] = tv.text();
			else
				v[i] = parseFloat( tv.text() );
		
			t[i] = b_t + parseFloat( tv.attr('t') );
		} );
		
		switch( dev.data[index].ss ) {
			case 0:
				if( v.length>0 ) {
					dev.data[index].new_v = v;
					dev.data[index].new_t = t;
					res = index;
				}
				break;
				
			default:
				if( v.length>0 ) {
					dev.data[index].new_v = v[0];
					dev.data[index].new_t = t[0];
					res = index;
				}
				break;
		}
		
	} );
	
	return res;
}

function get_data_and_update_ui() {
	
	var loop = dev.data.length;
	
	$.each( dev.data, function(i,v) { 
		//console.log( i+'---'+dev.data[i].lt );
		$.post( 'my-php/dev_data_get.php', {'tz':dev.tz,'g1':dev.g1,'lt':dev.data[i].lt,'d_id':dev.data[i].d_id}, function( data ) {
			var index = data_xml_parser( data );
			if( index>=0 )
				dev.data[index].update_fun();
		} );	
	} );
	
	setTimeout( "get_data_and_update_ui();",5000 );	
}

// 根据 参数id 值d_id， 在dev.data 中找到其 index
// 没有占到，返回 -1
function get_index( d_id ) {
	var index = -1;
	$.each( dev.data, function(i,v) {
		if( v.d_id==d_id ) {
			index = i;
			return false;
		}
	} );
	return index;
}

function change_tab( tab_mode ) {
	switch( tab_mode ) {
		case 'main':
			tab_main_show();
			return true;
		case 'set':
			tab_set_show();
			return true;
		case 'control':
			tab_control_show();
			return true;
		case 'record':
			tab_record_show();
			return true;
		default:
			return false;
	}
}

function tab_main_show() {
	$('#tab_main').css( 'display','block');
	$('#tab_set').css( 'display','None');
	$('#tab_control').css( 'display','None');
	$('#tab_record').css( 'display','None');
	
	if( tab_init['main']==0 )
		tab_init['main'] = 1;
	else
		return;
	
	// add dev_info_li and data_info_li		
	add_dev_info();
	$('#dev_info').outerWidth( $('body').width() );
	
	data_info_get_and_add_views_update();
}

function tab_set_show() {
	$('#tab_set').css( 'display','block');
	$('#tab_control').css( 'display','None');
	$('#tab_record').css( 'display','None');
	$('#tab_main').css( 'display','None');
	
	if( tab_init['set']==0 ) {
		tab_init['set'] = 1;
		add_set_list();
	}
	else
		return;
}

function tab_control_show() {
	$('#tab_set').css( 'display','None');
	$('#tab_control').css( 'display','block');
	$('#tab_record').css( 'display','None');
	$('#tab_main').css( 'display','None');
	
	if( tab_init['control']==0 ) {
		tab_init['control'] = 1;
		
		if( op.length<=0 ) {
			$.post('my-php/dev_fun_get.php', {'g1':dev.g1}, function( data ) {
				parse_fun_xml( data );
				add_control_list( 'info_set' );
			} );
		}
	}
	else
		return;
	
}

function tab_record_show() {
	$('#tab_set').css( 'display','None');
	$('#tab_control').css( 'display','None');
	$('#tab_record').css( 'display','block');
	$('#tab_main').css( 'display','None');
	
	if( tab_init['record']==0 )
		tab_init['record'] = 1;
	else
		return;
}

function add_control_list( table_id ) {
	var table = $('#'+table_id);
	var tbody = table.next('tbody');
	
	if( typeof(op)==='undefined' )
		return;
	
	$.each( op, function(i,v) {

		var tr = $('<tr></tr>');
		tr.attr( 'op_id', v.id );
		var td_f = $('<td class="f"></td>');
		var td_s = $('<td class="s"></td>');
		var td_t = $('<td class="t"></td>');
		
		var bt = $("<input class='button' type='submit' name='submit'>");
		bt.attr( 'value',v.name );
		bt.attr( 'index', i );
		bt.attr( 'op_id', v.id );
		bt.click( function() {
			var mtr = $(this).parent().parent();
			var td_f = mtr.children('td.f');
			var ps_str = '';
	
			var inputs = td_f.children('input');
			if( inputs.length>0 ) {
				inputs.each( function(i,v) {
					ps_str += ',' + v.value;
				} );
			}
			
			var res_h = $(this).parent().next('.t');
			res_h.text('已发送');
			
			var cmd = '['+dev.g1+',('+mtr.attr('op_id')+ps_str+')]';
			// data 返回值大于0，为正常
			$.post( 'my-php/dev_ctrl.php', {'g1':dev.g1,'cmd':cmd}, function( data ) {
				switch( data ) {
					case'OK':
						res_h.text('成功');
						res_h.delay(3000).fadeIn(function(){$(this).text('');});
						break;
					default:
						res_h.text('失败');
						res_h.delay(3000).fadeIn(function(){$(this).text('');});
						break;
				}
			} ).fail( function() {
				res_h.text('失败');
				res_h.delay(3000).fadeIn(function(){$(this).text('');});
			} );

		} );
		
		td_s.append( bt );
		
		if( op[i].p_num>0 ) {
			$.each( op[i].P, function(i,v) {
				var input = $('<input type="text"/>');
				if( v.name=='' ) {
					input.val( '未知' );
					input.attr( 'n', '未知' );	
				}	
				else {
					input.val( v.name );
					input.attr( 'n', v.name );
				}
				input.focus( input_focus );
				input.blur( input_blur );
				td_f.append( input );
			} );
		}
		
		tr.append( td_f );
		tr.append( td_s );
		tr.append( td_t );
		table.append( tr );
	} );
}

function add_set_list( ) {
	$('#dev_name_set').val( dev.name );
	$('#dev_tz_set').val( dev.tz );
	
	// 添加采集参数信息
	var tab_p = $('#frq_set').find('tbody');
	$.each( dev.data, function(index,val) {
		var tr = $( '<tr></tr>' );
		tab_p.append( tr );
		
		var td1 = $( '<td width="10%"></td>' );
		td1.html( "参数&nbsp;"+val.d_id );
		tr.append( td1 );
		
		var td2 = $( '<td width="10%"></td>' );
		td2.html( val.name );
		tr.append( td2 );
		
		var td3 = $( '<td width="20%"></td>' );
		if( val.ss==0 )
			td3.html( '保存历史数据' );
		
		else
			td3.html( '仅保存最新数据' );
		tr.append( td3 );
		
	} );
		
	var tab_a = $('#alarm_set').find('tbody');
	$.each( dev.data, function(index,val) {
		var u = val.unit;
		if( u=='bin' | u=='utf8' )
			return true;
		
		if( u.indexOf('file')>=0 )
			return true;
		
		var tr = $( '<tr></tr>' );
		tab_a.append( tr );
		
		var td1 = $( '<td width="20%"></td>' );
		td1.html( '参数'+val.d_id+"&nbsp;&nbsp;&nbsp;&nbsp;"+val.name );
		tr.append( td1 );
		
		var td2 = $( '<td width="80%"></td>' );
		tr.append( td2 );	
		var sel = $("<select class='alarm_rel'></select>");
		sel.append( $('<option value="1">></option>') );
		sel.append( $('<option value="2">>=</option>') );
		sel.append( $('<option value="3">==</option>') );
		sel.append( $('<option value="4"><=</option>') );
		sel.append( $('<option value="5"><</option>') );
		sel.append( $('<option value="6">!=</option>') );
		td2.append( sel );
		
		td2.append( $("<input type='text' style='padding:5px;margin-left:10px;width:90px'/>") );
		td2.append( $("<input type='checkbox' style='margin:0 0 0 40px'/>") );
		td2.append( "&nbsp;生效" );
	} );
}
//-----------------------------------------------------------------------------
function input_focus() {
	$(this).val('');
}

function input_blur() {
	var h = $(this);
	if( !(h.val()) )
		h.val( h.attr('n') );
}

function parse_fun_xml( xml ) {
	
	var xml_obj = $(xml);
	var ops = xml_obj.children('op');
	$.each( ops, function(i,v) {
		if(!v)
			return ture;
		
		var jv = $(v);
		var mid_op = new Object();
		mid_op.id = jv.attr('id');
		mid_op.name = jv.children('n').text();
		mid_op.remark = jv.children('rm').text();
		
		var ps = jv.children('p');
		mid_op.p_num = ps.length;
		
		$.each( ps, function(i,v) {
			if( !mid_op.P )
				mid_op.P = new Array();
			
			var midp = new Object();
			var jv2 = $(v);
			midp.id = jv2.attr( 'ind' ); 
			midp.name = jv2.children('pn').text(); 
			midp.remark = jv2.children('prm').text();
			midp.unit = jv2.children('pu').text();
			
			mid_op.P.push( midp );
		} );
		
		op.push( mid_op );
	} );
}

function gen_link() {
	$.post( './my-php/one_dev/gen_d_link.php', {'VT':$('#vt_input').val()}, function(data) {
		$('#link_output').val( data );	
	} );	
}

function refresh() {
	
	
}