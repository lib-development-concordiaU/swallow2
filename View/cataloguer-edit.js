$(document).ready(function(){
	
	$("#applicationForm").submit(function(e){
	
		$.ajax({
			// The URL for the request
			url: "Controller/cataloguer-save.php",
			type: "POST", 
			// The data to send (will be converted to a query string)
			data: new FormData( this ),
			processData: false,
      		contentType: false,
			 
			// Code to run if the request succeeds;
			// the response is passed to the function
			success: function( data ) {
				alert('Changes have been saved');
				console.log(data);
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

function cancel(){
    $("#main").load("View/cataloguers.php");
}

function changeClass(selectedSchema,select_id,cataloguerid){
    const class_id = $("#"+select_id).val();
	
    $('#permissionClasses').load('View/cataloguer-edit-permissions.php?selectedSchema='+selectedSchema+'&selectedClass='+class_id+'&cataloguerid='+cataloguerid);
}

function addClassPermission(select_id,in_cataloguer_id){
	const in_class_id = $("#"+select_id).val();
	$.get( "Controller/permission-add.php", {class_id:in_class_id ,cataloguer_id:in_cataloguer_id})
		.done( function( data ) {
			$('#currentPermissions').load('./View/cataloguer-edit-current-permissions.php?cataloguerid='+in_cataloguer_id);
	  });
}

function deletePermission(in_class_id,in_cataloguer_id){
	$.get( "Controller/permission-delete.php", {class_id:in_class_id ,cataloguer_id:in_cataloguer_id})
	.done(function(data){
		$('#currentPermissions').load('./View/cataloguer-edit-current-permissions.php?cataloguerid='+in_cataloguer_id);

	});
}

function changePermissionSchema(in_cataloguerid){
	var selectedSchema = $("#permission_schema_selector").val();
	$('#permissionClasses').empty();
	$('#permissionClasses').load('./View/cataloguer-edit-permissions.php?selectedSchema='+selectedSchema+"&cataloguerid="+in_cataloguerid );
}