// <li><div class="img"><img src="http://img.newtheme.cn/www/2014/09/tao2014-thumb.jpg"><time>2014.12.10 22:44:10</time></div></li>
// d_i - 待添加 flot UI的参数序号，dev.data 中的索引号
function add_image_view( d_i ) {
	
	var main = $('#new_img');
	main.append( $('<li id="'+d_i+'_img"><div class="img"><img/><time></time></div></li>') );
	var img = main.find('#'+d_i+'_img').find('img');
	var time = main.find('#'+d_i+'_img').find('time');
	
	img.attr( 'src',"http://img.newtheme.cn/www/2014/09/tao2014-thumb.jpg" );
	time.text( '2014.12.10 22:44:10' );
	
	dev.data[d_i].plot = main.find('#'+d_i+'_img');
	
	dev.data[d_i].update_fun = function() {
		
		//console.log(d_i);
		var new_img = $( "<img>" );
		var main = this.plot.find('img');
		var new_t = this.new_t;
		
		new_img.attr( 'src', this.new_v );
		//new_img.attr( 'src', "http://img.newtheme.cn/www/2014/09/tao2014-thumb.jpg" );
		
		new_img.load( function() {
			
			new_t.push( $(this).width() );
			new_t.push( $(this).height() );
			
			main.animate( {opacity:0}, 800, function() {
				this.src = "http://img.newtheme.cn/www/2014/09/tao2014-thumb.jpg";
				$(this).animate( {opacity:1},800 );	
			} );	
		});	
	};
	
	main.find('#'+d_i+'_img').click( function(e) {
		
		var div = $('#float_img_div');
		//var d_i = parseInt( this.id );			// 参数的索引

		if( div.length<=0 ) {
			$('body').append( $('<div id="float_img_div" style="padding:0px;margin:0px;"></div>') );
			div = $('#float_img_div');
			div.css( {'position':'absolute','background':'rgba(255,255,255,0.8)','border-radius':'2px'} );
		}
		
		// use the image real size
		div.width(360);
		div.height(240);
		
		var pos_x = e.pageX + div.width(), 
			pos_y = e.pageY - div.height();
		
		if( pos_x>$('body').width() )
			pos_x = $('body').width() - div.width() - 10;
		else
			pos_x = e.pageX;
		
		if( pos_y<=0 )
			pos_y = 10;
				
		div.css( {'top':pos_y,'left':pos_x} );
		div.show();
		
		//dev.data[d_i].update_fun();
		
		e.stopPropagation();
	} );
	
	$('body').click( function(e) {
		
		if( e.target.id=='float_img_div' )
			return false;
		
		var div = $('#float_img_div');
		if( div.length>0 ) {
			if( div.is(":visible") )
				div.hide();
		}	
	} );
	
}