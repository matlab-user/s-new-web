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
			  
var res = '<xml><dev><g1>00000000000000000000A0B0C0D0E0F0</g1><s>unknown</s><n>气象数据采集仪</n><tz>8</tz><lt>N</lt></dev><dev><g1>1982011602030410182910a1F2C3D02A</g1><s>unknown</s><n>空气参数测量仪</n><tz>8</tz><lt>1406637812</lt></dev><dev><g1>1982011602030410182910a1F2C3D08A</g1><s>need_data</s><n>图像采集仪</n><tz>8</tz><lt>1418785363</lt></dev><dev><g1>54455354444556535357415900000001</g1><s>unknown</s><n>农业环境测量仪</n><tz>8</tz><lt>1418908245</lt></dev><dev><g1>54455354444556535357415900000002</g1><s>unknown</s><n>农业环境测量仪</n><tz>8</tz><lt>1418908233</lt></dev><dev><g1>54455354444556535357415900000003</g1><s>unknown</s><n>农业环境测量仪</n><tz>8</tz><lt>N</lt></dev><dev><g1>A1B0C4D0E0FF</g1><s>unknown</s><n>环境数据采集仪</n><tz>8</tz><lt>N</lt></dev></xml>';

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