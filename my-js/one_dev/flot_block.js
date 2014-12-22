
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

// d_i - 待添加 flot UI的参数序号，dev.data 中的索引号
function add_flot_view( d_i ) {
	
	var flot_holder = add_flot_holder( d_i );
	
	if( $('#tooltip').length<=0 ) {
		$('body').append( $('<div id="tooltip">cos(x) of 3.00 = -0.99</div>') );
		$('#tooltip').hide();
	}
	
	var sin = [];

	for (var i = 0; i < 14; i += 0.5) {
		sin.push([i, Math.sin(i)]);
	}
	
	var plot = $.plot( '#'+flot_holder, [ 
		{ data: sin, label: "sin(x)"} 
	], {
		series: {
			lines: {
				show: true,
				lineWidth: 2,
			}
		},
		yaxis: {
			tickDecimals: 1
		},
		xaxis: {
			tickLength: 0,
			mode: "time",
			timeformat: "%h:%M"
		},
		shadowSize: 0,
		colors: [ flot_color[Math.ceil(Math.random()*30)%14] ],
		grid: {
			borderWidth: {
				top: 0,
				right: 0,
				bottom: 2,
				left: 2
			},
			hoverable: true,
			clickable: true
		}
	});
	
	$('#'+flot_holder).bind("plothover", function (event, pos, item) {

		if (item) {
			var x = item.datapoint[0].toFixed(2),
				y = item.datapoint[1].toFixed(2);

			$("#tooltip").html(item.series.label + " of " + x + " = " + y)
				.css({top: item.pageY+5, left: item.pageX+5})
				.fadeIn(200);
		} else
			$("#tooltip").hide();

	});
	
	dev.data[d_i].plot = plot;
	//dev.data[d_i].new_t = [15,16];
	//dev.data[d_i].new_v = [1,1.5];
	// define flot uodate function
	dev.data[d_i].update_fun = function () {
		
		var plot = this.plot;	
		var dataset = plot.getData();
		var series = dataset[0];
		var i = 0, loop = this.new_v.length;
	
		if ( loop>0 ) {
			
			for (i=0; i<loop; i++)				
				series.data.push( [this.new_t[i],this.new_v[i]] );			
	
			var y = this.new_v.pop();
			var x = this.new_t.pop();

			this.new_t = [];
			this.new_v = [];
		
			this.new_t[0] = x;
			this.new_v[0] = y;
			
			series.label = 'x='+x.toFixed(2)+'; y='+y.toFixed(2);
			
			plot.setData( [series] );
			plot.setupGrid();
			plot.draw();
		}
	};
	
	
} 

// d_i - 待添加 flot UI的参数序号，dev.data 中的索引号
// 返回能够添加 flot 的 div id，无 
function add_flot_holder( d_i ) {
	
	var div_id = d_i+'_flot_holder';		// 需要修改
	var main = $('ul.modules');
	
	var li = $('<li class="module"><p class="title">某某一数据</p></li>');
	main.append( li );
	li.append('<div id="'+div_id+'" class="flot_holder"></div>');
	
	return div_id;
}