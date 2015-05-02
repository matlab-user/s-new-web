 
 function data_info_get_and_add_views_update() {
	
	$.post( 'my-php/data_info_get.php',{'g1':dev.g1}, function( data ) {
		
		var sig = data_info_xml_parser( data );
		if (sig==true) {
			
			var data_num = dev.data.length;
			
			if( data_num<=14 ) {
				// 如果参数多于7个，则分2个更新栏显示最新数据
				var d_index = add_data_info( 0 );
				if ( d_index>0 )
					add_data_info( d_index );
			}
			else
				add_data_info_in_zone( );
		
			// 添加各参数视图
			$.each( dev.data, function(i,v) {
				dev.data[i].lt = 0;
				switch( v.ss ) {
					case 0:
						if( v.unit=='file/image' )
							add_image_view( i );
						else
							add_flot_view( i );
						break;
						
					case 1:
						if( v.unit=='file/image' )
							add_image_view( i );
						else {
							dev.data[i].update_fun = function() {
								var index = get_index( this.d_id );
								$('#'+index+'_data_info_v').html( this.new_v+' '+this.unit );
								$('#'+index+'_data_info_t').text( formatDate( this.new_t+dev.tz*3600) );
								dev.data[index].lt = this.new_t;
							};	
						}						
						break;

					default:
						break;
				}				
			} );
		}
		
		get_data_and_update_ui();
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
		dev.data[i].unit = v.children('u').html();
	} );

	return true;
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