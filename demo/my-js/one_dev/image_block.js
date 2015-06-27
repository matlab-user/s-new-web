// d_i - 待添加 flot UI的参数序号，dev.data 中的索引号
function add_image_view( d_i ) {
	
	var main = $('#new_img');
	main.append( $('<li id="'+d_i+'_img"><div class="img"><img/><p></p></div></li>') );
	var img = main.find('#'+d_i+'_img').find('img');
	var time = main.find('#'+d_i+'_img').find('p');
	
	img.attr( 'src',"images/default.jpg" );
	time.text( '2014.12.10 22:44:10' );
	
	dev.data[d_i].plot = main.find('#'+d_i+'_img');
	
	dev.data[d_i].real_h = 0;
	dev.data[d_i].real_w = 0;
	
	dev.data[d_i].update_fun = function() {

		var new_img = $( "<img />" );
		var main = this.plot.find('img');
		var new_src = '';
				
		var get_t = 0;
		if( $.type(this.new_v)=='array' ) {
			new_src = this.new_v[0];
			get_t = this.new_t[0];
		}
		else {
			new_src = this.new_v;
			get_t = this.new_t;
		}
		
		if( main.attr('src')==new_src )
			return;
		
		new_img.attr( 'src', new_src );
		this.lt = get_t;
		
		// 更新 data_info_li 上内容 
		var index = get_index( this.d_id );
		$('#'+index+'_data_info_v').html( 'image' );
		$('#'+index+'_data_info_t').text( formatDate( get_t+dev.tz*3600) );
		
		new_img.load( function() {
								
			dev.data[d_i].real_w = this.width;
			dev.data[d_i].real_h = this.height;
			
			main.animate( {opacity:0}, 800, function() {
				this.src = new_img.attr( 'src' );
				$(this).animate( {opacity:1},800, function() {
					new_img.remove();
					var t = new Date();
					t.setTime( (get_t+dev.tz*3600)*1000 );
					time.text( t.getUTCFullYear()+'.'+(t.getUTCMonth()+1)+'.'+t.getUTCDate()+" "+t.getUTCHours()+':'+t.getUTCMinutes()+':'+t.getSeconds() );
				} );	
			} );	
			
		});	
	};
	
	main.find('#'+d_i+'_img').click( function(e) {
		
		var div = $('#float_img_div');
		var d_i = parseInt( this.id );			// 参数的索引

		if( div.length<=0 ) {
			$('body').append( $("<div id='float_img_div' style='padding:0px;margin:0px;'><img style='width:100%;height:100%;'/></div>") );
			div = $('#float_img_div');
			div.css( {'position':'absolute','background':'rgba(255,255,255,0.8)','border-radius':'2px'} );
		}
		
		// use the image real size
		div.width( dev.data[d_i].real_w );
		div.height( dev.data[d_i].real_h );
		
		div.find('img').attr( 'src', dev.data[d_i].new_v );
		
		var pos_x = e.pageX + div.width(), 
			pos_y = e.pageY - div.height();
		
		if( pos_x>$('body').width() )
			pos_x = $('body').width() - div.width() - 10;
		else
			pos_x = e.pageX;
		
		if( pos_y<=0 )
			pos_y = 10;
				
		div.css( {'top':pos_y,'left':pos_x} );
		div.show(0);
		
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