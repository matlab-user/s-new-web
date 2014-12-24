/*
	dev		.g1
			.name			用户自定义名称
			.model			设备型号名称
			.state
			.tz
			.lt  			(UTC 或 ‘N’)
			.company		(设备生产厂商，未实现)
			.lo				经度 （以后实时更新）
			.la				纬度 （以后实时更新）
			.data[]			Object 数组
			.update_fun		设备UI更新函数
			
	data[j] .d_id			参数id
			.plot			flot handler/ or UI handle
			.d_name			参数名称
			.ss				ss==0 累积数据; ss==1 不累积数据; ss==2 图片显示; ss==3 视频显示 ss==4 文本数据;
			.unit			单位
			.new_v			最新数据值
			.new_t			更新时间
			.real_h			图像实际 height
			.real_w			图像实际 width
			.update_fun		此参数的更新函数
*/

var dev = new Object();
dev.name = '我的设备1';
dev.model = 'swaytech-1';
dev.g1 = '00001';
dev.tz = 8;
dev.lt = 0;
dev.company = 'swaytech';
dev.lo = '104.06<sup>。</sup>';
dev.la = '30.67<sup>。</sup>';
dev.update_fun = '';
dev.data = new Array();

// devs_table - 添加设备 tr 元素的父对象，jquery对象
// dev_i - 待添加设备在 devs 数组中的索引
function add_dev_info() {
	var main = $('#dev_info');
	
	var li = $('<li class="dev_info_li"></li>');
	var table = $("<table><caption class='title'>设备信息</caption><tbody></tbody></table>");
	li.append( table );
	
	var tbody = table.children('tbody');						
	tbody.append( $('<tr><th width="40%">设备名称：</th><th id="dev_info_model" width="60%">'+dev.model+'</th></tr>') );
	tbody.append( $('<tr><th width="40%">别名：</th><th id="dev_info_name" width="60%">'+dev.name+'</th></tr>') );
	tbody.append( $('<tr><th width="40%">设备guid号：</th><th id="dev_info_g1" width="60%">'+dev.g1+'</th></tr>') );
	tbody.append( $('<tr><th width="40%">所在地经纬度：</th><th id="dev_info_lo_la" width="60%">'+dev.lo+"&nbsp;"+dev.la+'</th></tr>') );
	if (dev.tz>0)
		tbody.append( $('<tr><th width="40%">所在地时区：</th><th id="dev_info_tz" width="60%">东'+dev.tz+'区</th></tr>') );
	else
		tbody.append( $('<tr><th width="40%">所在地时区：</th><th id="dev_info_tz" width="60%">西'+Math.abs(dev.tz)+'区</th></tr>') );
	
	tbody.append( $('<tr><th width="40%">生产厂家：</th><th id="dev_info_company" width="60%">'+dev.company+'</th></tr>') );

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
			
			$('#dev_info_company').html( dev.company );
		};
}

// d_i_s - 从第几个索引参数开始显示
// 返回第一个未显示参数的索引， 全部显示完后，返回 -1
function add_data_info( d_i_s ) {
	var main = $('#dev_info');
	
	var li = $('<li class="data_info_li"></li>');
	if ( d_i_s!=0 )
		li.css( {'width':'28%','margin-left':'20px'} );
	
	var table = $("<table><caption class='title'>数据最新更新</caption><tbody></tbody></table>");
	li.append( table );
	table = table.children('tbody');
	
	var d_index = -1;
	$.each( dev.data, function(i,v) {
		if (i<d_i_s)
			return true;
		
		if ( i>=(7+d_i_s) ) {
			li.css( {'width':'28%','margin-left':'20px'} );
			d_index = i;
			return false;
		}	
		var tr = $('<tr></tr>');			//  后续时需要修改此代码
		table.append( tr );	
		var ths = $('<th width="30%">'+dev.data[i].name+'</th><th id="'+i+'_data_info_v" width="20%">no data</th><th id="'+i+'_data_info_t" width="50%">2014-12-19 12:45:20</th>');
		tr.append( ths );	
	} );
	main.append( li );
	
	return d_index;
}

function info_xml_parser( responseTxt ) {
			
	var xml = $(responseTxt);
	if ( xml.length<=0 )
		return false;
	
	dev.name = xml.find('name').text();
	dev.model = xml.find('model').text();
	dev.company = xml.find('company').text();
	dev.tz = parseInt( xml.find('tz').text() );
	dev.lo = xml.find('longitude').text()+'<sup>。</sup>';
	dev.la = xml.find('latitude').text()+'<sup>。</sup>';
	
	return true;
}

function data_xml_parser( responseTxt ) {
			
	var xml = $(responseTxt);
	if ( xml.length<=0 )
		return false;
	
	var ds = xml.find('d');
	$.each( ds, function(i,value) {
		var v = $(value);
		var d_id = parseInt( v.attr('id') );
	
		var index = get_index( d_id );
		if( index<0 )
			return true;
		
		var b_t = v.children('base_t').text();
		if( b_t==='' )
				b_t = 0;
		else
			b_t = parseFloat( b_t );

		var vs = v.children('v');
		var v = new Array(), t = new Array();
		
		$.each( vs, function(i,value) {
			var tv = $(value);
			v[i] = parseFloat( tv.text() );
			t[i] = b_t + parseFloat( tv.attr('t') );
		} );
		
		switch( dev.data[index].ss ) {
			case 0:
				if( v.length>0 ) {
					dev.data[index].new_v = v;
					dev.data[index].new_t = t;
					had_new_index.push( index );
				}
				break;
				
			default:
				if( v.length>0 ) {
					dev.data[index].new_v = v[0];
					dev.data[index].new_t = t[0];
					had_new_index.push( index );
				}
				break;
		}
		
	} );
	
	return true;
}

function get_data() {

	var temp_t = new Date().getTime();
	
	$.post( 'my-php/dev_data_get.php', {'tz':dev.tz,'g1':dev.g1,'lt':dev.lt}, function( data ) {
		data_xml_parser( data );
		console.log(dev.lt);
		update_ui();
		dev.lt = Math.floor( temp_t );
		setTimeout( "get_data();",5000 );
	} );
	
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

//
function update_ui() {
	$.each( had_new_index, function(i,v) {
		
		
	} );
	
	had_new_index = [];
}
/*
// UTC - 单位为: 秒
function formatDate( UTC ) {
	var d = new Date( UTC*1000 );
	var year = d.getUTCFullYear();     
	var month = d.getUTCMonth() + 1;     
	var date = d.getUTCDate();     
	var hour = d.getUTCHours();     
	var minute = d.getUTCMinutes();     
	var second = d.getUTCSeconds();     
	return   year+"-"+month+"-"+date+"   "+hour+":"+minute+":"+second;     
}
*/