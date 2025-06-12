function renderStep(name,type,itemid,menu_element){
    $("[id^=mnu_li_]").removeClass('active');    
    $("#"+menu_element).addClass('active');

    $("#stepContainer").load("View/step-renderer.php?name="+name+"&type="+type+"&itemid="+itemid); 
}

function renderClassification(itemid){
    $("[id^=mnu_li_]").removeClass('active');    
    $("#mnu_li_classification").addClass('active');

    $("#stepContainer").load("View/step-renderer-class.php?itemid="+itemid); 
}


function toggleLock(itemId){
    $.ajax({
        // The URL for the request
        url: "Controller/item-lock.php?itemid="+itemId,
        type: "GET", 
         
        // Code to run if the request succeeds;
        // the response is passed to the function
        success: function( data ) {
            $("#main").load("View/deposit-item.php?itemid="+data)	
        },
         
        // Code to run if the request fails; the raw request and
        // status codes are passed to the function
        error: function( xhr, status, errorThrown ) {
            alert( "Sorry, there was a problem!" );
        },
            
         
    }); //$.ajax({

}

function toggleExport(itemId){
    $.ajax({
        // The URL for the request
        url: "Controller/item-hide-export.php?itemid="+itemId,
        type: "GET", 
         
        // Code to run if the request succeeds;
        // the response is passed to the function
        success: function( data ) {
            $("#main").load("View/deposit-item.php?itemid="+data)	
        },
         
        // Code to run if the request fails; the raw request and
        // status codes are passed to the function
        error: function( xhr, status, errorThrown ) {
            alert( "Sorry, there was a problem!" );
        },
            
         
    }); //$.ajax({

}
