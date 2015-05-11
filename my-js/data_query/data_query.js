var flot_color = new Array();
flot_color[0] = '#0011FF';
flot_color[1] = '#FF0000';
flot_color[2] = '#00AA00';
flot_color[3] = '#00AAFF';
flot_color[4] = '#FFDD00';
flot_color[5] = '#00DDFF';
flot_color[6] = '#FF33FF';
flot_color[7] = '#EE4400';
flot_color[8] = '#9933FF';
flot_color[9] = '#551166';
flot_color[10] = '#FF0055';
flot_color[11] = '#000000';
flot_color[12] = '#001133';
flot_color[13] = '#003311';
	
var dev = new Array();
var cur_dev_index = -1;			// 记录当前选中的设备在 dev 中的索引值
var flot_data = new Array();
var plot = '';
var tz = 8;
	
function data_query_init() {
	
	$('#tooltip').hide();
	

	
	var plot_div = $('#plot_div');
	plot_div.height( plot_div.width()*0.26);
	
	// 当前电站本地时间，秒为单位
	var d = new Date();
	var st = d.getTime()/1000 + tz*3600;	
	d.setTime( st*1000 );
	var now_UTC_day = d.getUTCDate();
	var now_UTC_month = d.getUTCMonth();
	var now_UTC_year = d.getUTCFullYear();
	
	var options = {
		series: { 
			shadowSize: 0,
			lines: {
				show: true,
				lineWidth: 2
			}
		}, 
		yaxes: [ 
			{	show: true,
				tickDecimals: 1
			 }, 
			{	show: true, 
				position: "right", 
				tickDecimals: 1
			} 
		],
		xaxes: [ 
			{ 	show: true,
				tickLength: 0,
				mode: "time",
				timeformat: "%m/%d %H:%M",
				minTickSize: [90, 'minute'],
				min: Date.UTC(now_UTC_year,now_UTC_month,now_UTC_day,0,0),
				max: Date.UTC(now_UTC_year,now_UTC_month,now_UTC_day,23,59)
			} 
		],
		grid: { 
			borderWidth: {
				top: 0,
				right: 2,
				bottom: 2,
				left: 2
			},
			backgroundColor: "rgb(255,255,255)" ,
			hoverable: true,
			clickable: true
		}
	};
	
	plot = $.plot( plot_div, [], options );
	
	plot_div.bind( "plothover", function (event, pos, item) {

		if (item) {
			var x = item.datapoint[0],
				y = item.datapoint[1].toFixed(2);
			
			var d = new Date( x );   
			var month = d.getUTCMonth() + 1;
			var day = d.getUTCDate();
			var hour = d.getUTCHours();     
			var minute = d.getUTCMinutes(); 
			var date_str = month+'-'+day+' '+hour+':'+minute+':'+d.getUTCSeconds();
			
			$("#tooltip").html( 'x= ' + date_str + " y= " + y)
				.css({top: item.pageY+5, left: item.pageX+5})
				.fadeIn(200);
		} else
			$("#tooltip").hide();
	});
	
	var d = new Date();
	var now_day = d.getDate();
	var now_month = d.getMonth()+1;
	var now_year = d.getFullYear();
	
	$('#t1_input').attr('value', now_year+'-'+now_month+'-'+(now_day-1) );
	$('#t2_input').attr('value', now_year+'-'+now_month+'-'+now_day );
	
	var dev_sel = $('#dev_sel');
	var did_sel = $('#did_sel');
		
	$.post( 'my-php/data_query/get_devs.php',function( data ) {
		
		if( devs_xml_parser(data) ) {
			cur_dev_index = 0;
			$.each( dev, function(i,value) {
				dev_sel.append( $("<option value='"+value.g1+"'>"+value.name+"</option>") );		
			} );

			$.post( 'my-php/data_query/get_ps.php', {'g1':dev_sel.val()}, function( data ) {
				if( ps_xml_parser(data) ) {
					$.each( dev[cur_dev_index].ps, function(i,value) {
						did_sel.append( $("<option value='"+value.d_id+"'>"+value.name+"</option>") );		
					} );		
				}
			} );
		}	
	} );

	dev_sel.change( function() {
		
		var dev_id = $(this).val();
		cur_dev_index = get_index( dev_id );
		
		var did_sel = $('#did_sel');
		did_sel.find('option').remove();
		
		if( !dev[cur_dev_index].ps ) {
			$.post( 'my-php/data_query/get_ps.php', {'g1':dev_sel.val()}, function( data ) {
				if( ps_xml_parser(data) ) {
					$.each( dev[cur_dev_index].ps, function(i,value) {
						did_sel.append( $("<option value='"+value.d_id+"'>"+value.name+"</option>") );		
					} );		
				}
			} );
		}
		else {
			$.each( dev[cur_dev_index].ps, function(i,value) {
				did_sel.append( $("<option value='"+value.d_id+"'>"+value.name+"</option>") );		
			} );		
		}
		
	} );
	
	did_sel.change( function() {
	} );

	$('#show').click( function() {
		
		var t1_text = $('#t1_input').val();
		var t2_text = $('#t2_input').val();
		
		t1_text = pro_time_str( t1_text );
		t2_text = pro_time_str( t2_text );	
		
		var dev_id = dev[cur_dev_index].g1;
		var did_sel = $('#did_sel');
		var d_id = did_sel.val();
		var d_name = did_sel.find('option:selected').text();
		
		var d = new Date( t1_text );
		var t1 = d.getTime()/1000;
		
		var d = new Date( t2_text );
		var t2 = d.getTime()/1000+24*3600;
		
		var if_add = $('#box')[0].checked;
		var c = flot_color[Math.ceil(Math.random()*30)%14];
		
		var table_list = $('#line_list tbody');
		
		$.post( 'my-php/data_query/data_query.php', {'t0':t1,'t1':t2,'g1':dev_id,'d_id':d_id}, function(data) {

			data_xml_parser( data ); 
			var s_index = -1;			//  记录数据在 plot.getData() 返回结构中的索引号
			
			if( flot_data.length>0 ) {
				
				if( if_add==true ) {	// 追加数据
					var dataset = plot.getData();
					if( dataset.length<=0 ) {
						dataset = new Array();
						dataset[0] = new Object();
						dataset[0].data = flot_data;
						dataset[0].color = c;
						dataset[0].label = d_name;
						s_index = 0;
					}
					else {
						var mid = new Object();
						mid.data = flot_data;
						mid.color = c;
						mid.label = d_name;
						dataset.push( mid );
						s_index = dataset.length-1;
					}
				}
				else {
					dataset = new Array();
					dataset[0] = new Object();
					dataset[0].data = flot_data;
					dataset[0].color = c;	
					dataset[0].label = d_name;	
					s_index = 0;
					
					table_list.find('tr').remove();
				}
				
				flot_data = [];
				
				options.xaxes[0].min = null;
				options.xaxes[0].max = null;
				delete( plot );
				plot = $.plot( '#plot_div', dataset, options );
				
				var tr = $('<tr></tr>');
				table_list.append( tr );
				tr.append( $('<td>'+dev[cur_dev_index].name+'</td>') );
				tr.append( $('<td>'+d_name+'</td>') );
				tr.append( $('<td>'+t1_text+' 0:0:0 - '+t2_text+' 23:59:59 </td>') );
				tr.append( $('<td><div style="width:30px;height:30px;margin:10px;background:'+c+'"></div></td>') );
				var button = $("<button class='button_1' type='button' style='border:1px solid black' >删除</button>");
				button.attr( 's_index', s_index );
				tr.append( $("<td></td>").append(button) );
				
				button.click( function() {
					var s_index = $(this).attr('s_index');
					var dataset = plot.getData();
					dataset[s_index] = [];
					delete( plot );
					plot = $.plot( '#plot_div', dataset, options );
					$(this).parent().parent().remove();	
				} );
				
			}
		} );
		
	} );
}

function devs_xml_parser( responseTxt ) {
			
	var xml = $(responseTxt);
	if ( xml.length<=0 )
		return false;
	
	var ds = xml.find('d');
	$.each( ds, function(i,value) {
		var v = $(value);
		dev[i] = new Object();
		dev[i].g1 = v.attr( 'i' );
		dev[i].name = v.html();
	} );

	return true;
}

function ps_xml_parser( responseTxt ) {
			
	var xml = $(responseTxt);
	if ( xml.length<=0 || cur_dev_index<0 )
		return false;
	
	var ds = xml.find('p');
	dev[cur_dev_index].ps = new Array();
	$.each( ds, function(i,value) {
		var v = $(value);
		dev[cur_dev_index].ps[i] = new Object();
		dev[cur_dev_index].ps[i].d_id = v.attr( 'i' );
		dev[cur_dev_index].ps[i].name = v.html();
	} );

	return true;
}

function get_index( value ) {
	var index = -1;
	$.each( dev, function(i,v) {
		if( v.g1===value ) {
			index = i;
			return false;
		}
	} );
	return index;
}

// 返回 YYYY-MM-DD 格式的字符串
function pro_time_str( t_str ) {
	var str_a = new Array();
	str_a = t_str.split('-');

	if( str_a.length<3 )
		return '';
		
	if( (str_a[1]*1)<10 && str_a[1].length<2 )
		str_a[1] = '0' + str_a[1]; 
	
	if( (str_a[2]*1)<10 && str_a[2].length<2 )
		str_a[2] = '0' + str_a[2]; 
	
	return str_a.join('-');
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
		
		var b_t = v.children('base_t').text();
		if( b_t==='' )
				b_t = 0;
		else
			b_t = b_t * 1;

		var vs = v.children('v');
		var v = 0, t = 0;
		
		$.each( vs, function(i,value) {
			var tv = $(value);
			v = tv.text()*1;
			t = b_t + tv.attr('t')*1;
			flot_data.push( [ t*1000+8*3600000, v ] );
		} );
		
	} );
	
	return res;
}