 
 function data_info_get_and_add_views() {
	
	$.post( 'my-php/data_info_get.php',{'g1':dev.g1}, function( data ) {
		
		var sig = data_info_xml_parser( data );
		if (sig==true) {
			// 如果参数多于7个，则分2个更新栏显示最新数据
			var d_index = add_data_info( 0 );
			if ( d_index>0 )
				add_data_info( d_index );
			
			// 添加各参数视图
			$.each( dev.data, function(i,v) {
				switch( v.ss ) {
					case 0:
						add_flot_view( i );
						break;
					case 2:
						add_image_view( i );
						break;
					default:
						break;
				}				
			} );
		}
		
	} );
	
 }
 
function data_info_xml_parser( responseTxt ) {
			
	var xml = $(responseTxt);
	if ( xml.length<=0 )
		return false;
	
	var ds = xml.find('d');
	$.each( ds, function(i,value) {
		var v = $(value);
		dev.data[i] = new Object();
		dev.data[i].d_id = parseInt( v.attr('id') );
		dev.data[i].name = v.children('name').text();
		dev.data[i].ss = parseInt( v.children('ss').text() );
		dev.data[i].unit = v.children('u').text();
	} );

	return true;
}
 