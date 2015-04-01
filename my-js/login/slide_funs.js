/*
----------- slide structure ---------------

	<div class='slide_top_level'>
	
		<div id='show_1' class='slide_show'></div>
		<div id='show_2' class='slide_show'></div>
		<div id='show_3' class='slide_show'></div>
		<div id='show_4' class='slide_show'></div>
		
		<div class='slide'>
			<ul class='points_line'>
				<li class='point'><a>1</a></li>
				<li class='point'><a>2</a></li>
				<li class='point'><a>3</a></li>
				<li class='point current'><a>4</a></li>
			</ul>
		</div>
	</div>
	
	.slide_show need user-define style
*/

//------------ user custom ------------------------------------------------------
var b_image = "url('image/login/banner.jpg')";						// div.slide background-image
var slide_top_width = '';				// div.slide_top_level's width
var slide_top_height = '';				// div.slide_top_level's height

var slide_interval = 12;				// change inteval, seconds
//-------------------------------------------------------------------------------
			
function slide_init() {
	$('.slide').css('top',$(".slide_top_level").offset().top+'px');
	$('.slide_top_level').css('background',b_image);
	
	$('.points_line').children('li').click( function(e) {
		$('.slide_show').hide();
		var index;
		var obj = e.target;
		var j_obj = $(obj);
		switch(obj.tagName) {
			case 'A':
				$('.points_line').find('li').removeClass('current');
				$(obj).parent('li').addClass('current');
				index = j_obj.text();
				break;
			case 'LI':
				$('.points_line').find('li').removeClass('current');
				j_obj.addClass('current');
				var j_a = j_obj.children('a');
				index = j_a.text();
				break;
			default:
				break;
		}
		$('#show_'+index).fadeIn( 900 );
	} );
	
	var cur_li = $('.points_line .current');

	$('#show_'+cur_li.children('a').text()).fadeIn();
	
	var main = $('.slide_top_level');
	if( slide_top_height.length>0 )
		main.css('height',slide_top_height);
	
	$('.slide').css('height',main.height()+'px');

	setInterval( 'change();',slide_interval*1000 );	
}

function change() {
	var cur_li = $('.points_line .current');
	var next_li = cur_li.next('li');
	if( next_li.length<=0 )
		next_li = $('.points_line li:first');
	
	var index = next_li.children('a').text();
	$('.slide_show').fadeOut( 900 );
	$('#show_'+index).fadeIn( 900 );
	
	$('.points_line li').removeClass('current');
	next_li.addClass('current');
}