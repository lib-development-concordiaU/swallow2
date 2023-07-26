$(document).ready(function(){
	
	$("#applicationForm").submit(function(e){
	
		$.ajax({
			// The URL for the request
			url: "Controller/class-save.php",
			type: "POST", 
			// The data to send (will be converted to a query string)
			data: new FormData( this ),
			processData: false,
      		contentType: false,
			 
			// Code to run if the request succeeds;
			// the response is passed to the function
			success: function( data ) {
				alert('Changes have been saved');
                //console.log(data);
                
				$("#main").load("View/classes.php?classid="+data);
			},
			 
			// Code to run if the request fails; the raw request and
			// status codes are passed to the function
			error: function( xhr, status, errorThrown ) {
            	alert( "Sorry, there was a problem!" );
			},
				
			complete: function( xhr, status){
				
			}
			 
		}); //$.ajax({
		
		e.preventDefault();
    })
});