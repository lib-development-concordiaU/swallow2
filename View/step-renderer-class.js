function saveClass(selectid){
    const itemid = $('#itemid').val();
    const classid = $('#'+selectid).val();
    const urlstring = "Controller/item-save-step-class.php?itemid="+itemid+"&classid="+classid;

    console.log(urlstring);
    $.ajax({
      // The URL for the request
      url: urlstring,
      type: "GET", 

      success: function( data ) { 
        var response = jQuery.parseJSON(data);
        alert('Changes have been saved'); 
        $("#stepContainer").load("View/step-renderer-class.php?itemid="+response.itemid);

      },
      
      // Code to run if the request fails; the raw request and
      // status codes are passed to the function
      error: function( xhr, status, errorThrown ) {
        alert( "Sorry, there was a problem!" );
      }
      
    }); //$.ajax({
  }