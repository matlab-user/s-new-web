
function add_state_view( d_i ) {
	
	var target = dev.data[d_i];
	var main = $('#flot_zone');

	var div_id = d_i+'_state_holder';		// 需要修改
	
	var li = $('<li class="module"><p class="title">'+target.name+'<span type="time"></span></p></li>');
	target.plot = li;
	main.append( li );
	li.find('span[type="time"]').html( '2014.12.10 22:44:10' );
	li.append('<div class="flot_holder"><table class="state_show_table" id="'+div_id+'"><tbody></tbody></table></div>');

	parse_state_remark( d_i );

	var tbody = $('#'+div_id ).children('tbody');	
	for( $i=0; $i<8; $i++ )
		tbody.append( $('<tr><td type="'+$i+'_n"></td><td type="'+$i+'_v"></td><td type="'+($i+8)+'_n"></td><td type="'+($i+8)+'_v"></td></tr>') );
	
	for( var x in target.json ) {
		var t = target.json[x];
		var td_n = tbody.find( 'td[type='+x+'_n]' );
		var td_v = tbody.find( 'td[type='+x+'_v]' );

		td_n.attr('id', t.bit+'_bit' );
		td_v.attr('id', t.bit+'_bit_v' );
		td_n.html( target.json[x][t.bit] );
	}
		
	target.update_fun = state_update_fun;
}

// dev.data[d_i].json - 对象数组
// 每个 json 对象中有：mask 域；bit 域，数值，位序号；
function parse_state_remark( d_i ) {
	
	var unit = dev.data[d_i].unit;
	var remark = dev.data[d_i].remark;

	if( unit!='state' || remark.length<=0 )
		return false;
	
	dev.data[d_i].json = JSON.parse( remark );
	
	// 计算 mask
	for ( var x in dev.data[d_i].json ) {
		
		var t = dev.data[d_i].json[x];
		
		for( var y in t ) {
			switch( y ) {
				case 'v':
				case 'mask':
					break;
				default:
					var num = Math.abs( parseInt(y) );
					if( num<=15  ) {
						t.mask = 1 << num;
						t.bit = num;
					}
					break;
			}
		}
	}
	
}

function state_update_fun() {
	var li = this.plot;
	
	var t = new Date();
	t.setTime( (this.new_t[0]+dev.tz*3600)*1000 );
	var t_str = t.getUTCFullYear()+'.'+(t.getUTCMonth()+1)+'.'+t.getUTCDate()+" "+t.getUTCHours()+':'+t.getUTCMinutes()+':'+t.getSeconds();
	li.find('span[type="time"]').html( t_str );
	
	this.lt = this.new_t[0];
	
	for( var x in this.json ) {
		var num = this.json[x].bit;
		var mask = this.json[x].mask; 
		var res = (this.new_v & mask) >> num;
		
		var td_id = '#' + num + '_bit_v';
		
		$( td_id ).html( this.json[x].v[res] );
	}
	
	// 信息栏更新
	var d_i = get_index( this.d_id );
	$('#'+d_i+'_data_info_v').html( this.new_v[0]+' '+this.unit );
	$('#'+d_i+'_data_info_t').text( formatDate( this.new_t[0]+dev.tz*3600) );
}

