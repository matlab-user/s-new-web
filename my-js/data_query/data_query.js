var dev = new Array();


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
			
			$.each( dev, function(i,value) {
				dev_sel.append( $("<option value='"+value.g1+"'>"+value.name+"</option>") );		
			} );
			
			console.log( dev_sel.val() );
/*
			$.post( 'my-php/data_query/get_ps.php', {'g1':dev_sel.val()}, function( data ) {
				
				
			} );
*/
		}	
		
	} );

/*
	did_sel.append( $("<option value='0'>Text1</option>") );
	did_sel.append( $("<option value='1'>Text2</option>") );
*/

	dev_sel.change( function() {
		console.log('wwwwwwww');
		
	} );
	
	did_sel.change( function() {
		console.log('aaaaaaa');
		
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