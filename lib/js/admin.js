//google.load('jqueryui','1.8.16');

jQuery(function($){
	$('#layout li img').click(function(){
		var value = $(this).attr('id');
		$('#layout li img').removeClass('selected');
		$(this).addClass('selected');
		$('#layout + input').val(value);
	});

	$('.datepicker').each( function(){
		$(this).datepicker({ dateFormat: 'mm/dd/yy', changeYear: true, yearRange: '1900:2100', changeMonth: true, altFormat: 'yy-mm-dd', altField: $(this).next() });
	});
	
	$( '#addservice' ).click( function(e){
		$('.service-table:visible + .service-table:hidden').slideDown('fast', function(){
			var vistables = $('.service-table:visible').length;
			if( vistables > 1 ){
				$('#remove').show('#remove');
				$('#remove').addClass('border-left');
			} 
			if( vistables == 5 ){
				$('#addservice').hide();
				$('#remove').removeClass('border-left');
			}
		});
		e.preventDefault();
	});
	$( '#removeservice' ).click( function(e){
		$('.service-table:visible:last input').val('');
		$('.service-table:visible:last').slideUp('fast', function(){
			var vistables = $('.service-table:visible').length;
			if( vistables < 2 ){
				$('#remove').hide('#remove');
			}
			if( vistables < 5 ){
				$('#addservice').show();
				$('#remove').addClass('border-left');
			}	
		});
		e.preventDefault();
	});
	
	$( '#addsurvivor' ).click( function(e){
		$('.survivor-table:visible + .survivor-table:hidden').slideDown('fast', function(){
			var vistables = $('.survivor-table:visible').length;
			if( vistables > 1 ){
				$('#removesurvivorspan').show('#removesurvivorspan');
				$('#removesurvivorspan').addClass('border-left');
			} 
			if( vistables == 20 ){
				$('#addsurvivor').hide();
				$('#removesurvivorspan').removeClass('border-left');
			}
		});
		e.preventDefault();
	});	
	$( '#removesurvivor' ).click( function(e){
		$('.survivor-table:visible:last input').val('');
		$('.survivor-table:visible:last').slideUp('fast', function(){
			var vistables = $('.survivor-table:visible').length;
			if( vistables < 2 ){
				$('#removesurvivorspan').hide('#removesurvivorspan');
			}
			if( vistables < 20 ){
				$('#addsurvivor').show();
				$('#removesurvivorspan').addClass('border-left');
			}	
		});
		e.preventDefault();
	});
	
	$( '#addpreceedor' ).click( function(e){
		$('.preceedor-table:visible + .preceedor-table:hidden').slideDown('fast', function(){
			var vistables = $('.preceedor-table:visible').length;
			if( vistables > 1 ){
				$('#removepreceedorspan').show('#removepreceedorspan');
				$('#removepreceedorspan').addClass('border-left');
			} 
			if( vistables == 20 ){
				$('#addpreceedor').hide();
				$('#removepreceedorspan').removeClass('border-left');
			}
		});
		e.preventDefault();
	});	
	$( '#removepreceedor' ).click( function(e){
		$('.preceedor-table:visible:last input').val('');
		$('.preceedor-table:visible:last').slideUp('fast', function(){
			var vistables = $('.preceedor-table:visible').length;
			if( vistables < 2 ){
				$('#removepreceedorspan').hide('#removepreceedorspan');
			}
			if( vistables < 20 ){
				$('#addpreceedor').show();
				$('#removepreceedorspan').addClass('border-left');
			}	
		});
		e.preventDefault();
	});			
});
