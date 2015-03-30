/*
	devs[i]	.g1
			.name
			.state
			.tz
			.lt  (UTC 或 ‘N’)
*/

var devs = new Array();

function change_view() {
	var target = $('#devices1');
	
	if ( target.is(":hidden") ) {
		view = 'list';
		$('#devices2').hide();
		target.show();
	}
	else {
		view = 'icon';
		target.hide();
		if ( $('#devices2').length<=0 ) {
			$('body').append('<ul id="devices2"></ul>');
			var devs_ul = $('#devices2');
			
			devs_ul.show();	
			
			$.each( devs, function(i,v) {
				add_dev_icon( devs_ul, i );
			} );	
		}
		$('#devices2').show();
	}
	refresh();
}

// devs_table - 添加设备 tr 元素的父对象，jquery对象
// dev_i - 待添加设备在 devs 数组中的索引
function add_dev_tr( devs_table, dev_i ) {
	
	$.base64.utf8encode = true;
		   
	var tr = $('<tr></tr>');
	tr.attr( 'id', devs[dev_i].g1 );
	
	var td_name = $('<td class="nickname"></td>');
	td_name.html('<a target="_blank" href="one_dev.html?g1='+$.base64.btoa(devs[dev_i].g1)+'">'+devs[dev_i].name+'</a>');
	tr.append( td_name );
	
	var td_g1 = $('<td align="center">'+devs[dev_i].g1+'</td>');
	tr.append( td_g1 );
	
	var td_state = $('<td align="center"></td>');
	td_state.html('<span class="green">'+devs[dev_i].state+'</span>');
	tr.append( td_state );
	if ( devs[dev_i].lt=='N' )
		var td_time = $('<td align="center">no time data</td>');
	else
		var td_time = $('<td align="center">'+formatDate(devs[dev_i].lt+devs[dev_i].tz*3600)+'</td>');
	
	tr.append( td_time );

	var td_detail = $('<td align="center"><a class="color" target="_blank" href="one_dev.html?g1='+$.base64.btoa(devs[dev_i].g1)+'">详情</a></td>');

	tr.append( td_detail );
	
	devs_table.append( tr );
}

function add_dev_icon( devs_ul, dev_i ) {
	
	var li = $('<li></li>');
	var div = $('<div class="device"></div>');
	div.attr( 'id', devs[dev_i].g1 );
	li.append( div );
	
	var p1 = $('<p class="logo">DEVICE.LOGO</p>');
	var p2 = $('<p class="name"><a target="_blank" href="one_dev.html?g1='+$.base64.btoa(devs[dev_i].g1)+'">'+devs[dev_i].name+'</a></p>');
	div.append( p1 );
	div.append( p2 );
		
	devs_ul.append( li );
}

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

function apply_dev() {
	$.post( 'my-php/devs_view/apply_dev.php', function( data ) {	
		if( data=='' | data=='NO' ) {
			$('#alarm_ul').children('li').text('申请新设备失败，超出最大设备拥有数量！');
			$('#alarm_ul').show(0).delay(3000).hide(0);
			return;
		}
		devs.push( {'g1':data,'name':'未命名','state':'未激活','tz':8,'lt':'N'} );
		if( view=='list' ) {
			var devs_table = $('#devices1').children('table.devs_table').children('tbody');
			add_dev_tr( devs_table, devs.length-1 );
		}
		else {
			var devs_ul = $('#devices2');
			add_dev_icon( devs_ul, devs.length-1 );
		}
	} ).fail( function() {
		$('#alarm_ul').children('li').text('此用户拥有设备数量已达到最大值！');
		$('#alarm_ul').show(0).delay(3000).hide(0);
	} );
}

function refresh() {
	if( view=='list' ) {
		var devs_table = $('#devices1').children('table.devs_table').children('tbody');
		var trs = devs_table.children('tr');
	}
	else {
		var devs_table = $('#devices2');
		var trs = devs_table.find( 'div.device' );
	}
	
	$.each( devs, function(index,value) {
		var sig = -1;
		$.each( trs, function(i,v) {
			var ele = $(v);
			var g1 = ele.attr('id');

			if( value.g1==g1 ) {
				sig = 1;
				return false;
			}	
		} );
			
		if( sig<0 ) {
			if( view=='list' )
				add_dev_tr( devs_table, index );
			else
				add_dev_icon( devs_table, index );
		}
	} );
	
}