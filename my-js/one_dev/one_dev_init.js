/*
	dev		.g1
			.name
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
			.new_v			最新数据值 or image src
			.new_t			更新时间
			.update_fun		此参数的更新函数
*/

var dev = new Object();

dev.data = new Array(12);
$.each( dev.data, function(i,v) {
	dev.data[i] = new Object();
	dev.data[i].new_v = new Array();
	dev.data[i].new_t = new Array();
} );

// devs_table - 添加设备 tr 元素的父对象，jquery对象
// dev_i - 待添加设备在 devs 数组中的索引
function add_dev_info() {
	var main = $('#dev_info');
	
	var li = $('<li class="dev_info_li"></li>');
	var table = $("<table><caption class='title'>设备信息</caption><tbody></tbody></table>");
	li.append( table );
	
	var tbody = table.children('tbody');						
	tbody.append( $('<tr><th width="40%">设备名称：</th><th width="60%">sway dev</th></tr>') );
	tbody.append( $('<tr><th width="40%">别名：</th><th width="60%">××××××××</th></tr>') );
	tbody.append( $('<tr><th width="40%">设备guid号：</th><th width="60%">××××××××</th></tr>') );
	tbody.append( $('<tr><th width="40%">所在地经纬度：</th><th width="60%">×××××  ××××××</th></tr>') );
	tbody.append( $('<tr><th width="40%">所在地时区：</th><th width="60%">东八区</th></tr>') );
	tbody.append( $('<tr><th width="40%">生产厂家：</th><th width="60%">××××××××</th></tr>') );

	main.append( li );
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
		var tr = $('<tr id="'+i+'_lastest'+'"></tr>');			//  后续时需要修改此代码
		table.append( tr );	
		var ths = $('<th width="30%">参数1</th><th width="20%">124</th><th width="50%">2014-12-19 12:45:20</th>');
		tr.append( ths );	
	} );
	main.append( li );
	
	return d_index;
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

function xml_parser( responseTxt ) {
	
	var dev_i = -1;			// dev 索引
			
	var xml = $(responseTxt);
	if ( xml.length<=0 )
		return false;
	
	var ds = xml.find('dev');
	$.each( ds, function( i, v ) {			// 遍历 xml
		
		var g1 = $(this).children('g1').text();
		dev_i = -1;
		$.each( devs, function( i, v ) {
			if( this.g1==g1 ) {
				dev_i = i;
				return false;
			}
		} );

		if( dev_i<0 ) {
			dev_i = devs.length;
			devs[dev_i] = new Object();
			devs[dev_i].g1 = g1;
		}
		
		devs[dev_i].name = $(this).children('n').text();
		devs[dev_i].tz = parseInt( $(this).children('tz').text() );
		devs[dev_i].state = $(this).children('s').text();
		
		var lt = $(this).children('lt').text();
		if ( lt=='N' )
			devs[dev_i].lt = lt;
		else
			devs[dev_i].lt = parseInt( lt );
	
	} );
}
*/