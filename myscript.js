jQuery( window ).load(function() {
   	jQuery('.main-menu-wrap .inner >.sub-menu').each(function(){
		var $_this = jQuery(this);
		$_flag=true;
		$_this.find('>li').each(function(){
			if(jQuery(this).attr('data-cols')!=1){ $_flag=false; }
		});
		if($_flag){
			$_this.masonry({
	          itemSelector: '.main-menu-wrap .inner >.sub-menu >li', 
	        });
		}
	});

});