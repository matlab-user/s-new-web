var dev = new Array();
var cur_dev_index = -1;			// 记录当前选中的设备在 dev 中的索引值

function data_query_init() {
	
	var tz = 8;
	
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
				timeformat: "%H:%M",
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
	
	var plot = $.plot( plot_div, [], options );
	
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
		var d_id = $('#did_sel').val();
		console.log(dev_id+'---'+d_id);
		
		var d = new Date( t1_text );
		var t1 = d.getTime()/1000;
		
		var d = new Date( t2_text );
		var t2 = d.getTime()/1000+24*3600;
		
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