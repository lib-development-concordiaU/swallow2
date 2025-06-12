function filterClass(select_id,page){
    const class_id = $("#"+select_id).val();
    $("#selectedClass").val(class_id);
    filter(page);
}


function createItem(){
    $.ajax({
        // The URL for the request
        url: "Controller/item-create.php",
        type: "GET", 
        
        success: function( data ) {
            console.log(data);
            $("#main").load("View/deposit-item.php?itemid="+data)
            //$("#main").load("View/items.php")	
        },
         
        // Code to run if the request fails; the raw request and
        // status codes are passed to the function
        error: function( xhr, status, errorThrown ) {
            console.log(errorThrown);
            alert( "Sorry, there was a problem!" );
        },
            
        complete: function( xhr, status){
            
        }
         
    }); //$.ajax({
}

function editItem(id){
    $("#main").load("View/deposit-item.php?itemid="+id)	
}

function filter(page){
    const cataloguer = $("#f_cataloguer").val();
    const orderby =  $("#f_orderby").val();
    const query = $("#metadaquery").val();
    const schema = $("#f_schema").val();
    const selectedClass = $("#selectedClass").val();

    $("#main").load("View/items.php?cataloguer="+cataloguer+"&selectedClass="+selectedClass+"&metadataquery="+btoa(unescape(encodeURIComponent(query)))+"&page="+page+"&orderby="+orderby+"&schema="+schema);
}

function query(page){

    const cataloguer = $("#f_cataloguer").val();
    const query = $("#metadaquery").val();
    const selectedClass = $("#selectedClass").val();
   // console.log(query);

     $("#main").load("View/items.php?cataloguer="+cataloguer+"&selectedClass="+selectedClass+"&metadataquery="+btoa(unescape(encodeURIComponent(query)))+"&page="+page);
}

function deletedataset(){

    const cataloguer = $("#f_cataloguer").val();
    const selectedClass = $("#selectedClass").val();
    const query = $("#metadaquery").val();
    const schema = $("#f_schema").val();

    if( confirm('All items that matching the search / filter criteria will be deleted. This operation cannot be undone and will result in dataloss.') ){
        $("#batchDeleteStatus").html("Processing Request: Deleting Items <img class='smallLoader' src='images/loading.gif' />");
        $.ajax({
            // The URL for the request
            url: "Controller/batch-delete.php?cataloguer="+cataloguer+"&selectedClass="+selectedClass+"&query="+btoa(query)+"&schema="+schema,
            type: "GET", 
            
            success: function( data ) {
                console.log(data);
                $("#main").load("View/items.php")	
            },
             
            // Code to run if the request fails; the raw request and
            // status codes are passed to the function
            error: function( xhr, status, errorThrown ) {
                console.log(errorThrown);
                $("#batchDeleteStatus").html(errorThrown);
                alert( "Sorry, there was a problem!" );
            },
                
            complete: function( xhr, status){
                $("#batchDeleteStatus").html("");
            }
             
        }); //$.ajax({
    }
    
}

function changeClass(){
    const cataloguer = $("#f_cataloguer").val();
    const selectedClass = $("#selectedClass").val();
    const query = $("#metadaquery").val();
    const schema = $("#f_schema").val();

    if( confirm('All items that matching the search / filter criteria will have their classes changed. This operation cannot be undone.') ){
        $("#batchChangeStatus").html("Processing Request: Updating Items <img class='smallLoader' src='images/loading.gif' />");
        $.ajax({
            // The URL for the request
            url: "Controller/batch-change-class.php?cataloguer="+cataloguer+"&selectedClass="+selectedClass+"&query="+btoa(query)+"&schema="+schema+"&newclass="+$("#batch_class").val(),
            type: "GET", 
            
            success: function( data ) {
                console.log(data);
                $("#main").load("View/items.php");
                $("#batchChangeStatus").html("Done");
            },
             
            // Code to run if the request fails; the raw request and
            // status codes are passed to the function
            error: function( xhr, status, errorThrown ) {
                console.log(errorThrown);
                $("#batchChangeStatus").html(errorThrown);
                alert( "Sorry, there was a problem!" );
            },
                
            complete: function( xhr, status){
                // $("#batchChangeStatus").html("");
            }
             
        }); //$.ajax({
    }
}

function preview(id){
    $.ajax({
        // The URL for the request
        url: "View/preview-record.php?id="+id,
        type: "GET", 
        
        success: function( data ) {
           $("#modal-main").empty();
           $("#modal-main").append(data);
        },
         
        // Code to run if the request fails; the raw request and
        // status codes are passed to the function
        error: function( xhr, status, errorThrown ) {
            console.log(errorThrown);
            alert( "Sorry, there was a problem!" );
        },
            
        complete: function( xhr, status){
            
        }
         
    }); //$.ajax({
}


function deleteItem(id){
    if(confirm(' Are you sure you want to delete this item ? \n This action cannot be undone and data will be lost')){
        $.ajax({
            // The URL for the request
            url: "Controller/item-delete.php?itemid="+id,
            type: "GET", 
            
            success: function( data ) {
                console.log(data);
                $("#main").load("View/items.php")	
            },
             
            // Code to run if the request fails; the raw request and
            // status codes are passed to the function
            error: function( xhr, status, errorThrown ) {
                console.log(errorThrown);
                alert( "Sorry, there was a problem!" );
            },
                
            complete: function( xhr, status){
                
            }
             
        }); //$.ajax({
    }
}


function duplicateItem(id){
    if(confirm(' Are you sure you want to duplicatethis item ? \n This action will create an exact duplicate if the record with a new unique identifier. \n It should appear right after the original')){
        $.ajax({
            // The URL for the request
            url: "Controller/item-duplicate.php?itemid="+id,
            type: "GET", 
            
            success: function( data ) {
                console.log(data);
                $("#main").load("View/items.php")	
            },
             
            // Code to run if the request fails; the raw request and
            // status codes are passed to the function
            error: function( xhr, status, errorThrown ) {
                console.log(errorThrown);
                alert( "Sorry, there was a problem!" );
            },
                
            complete: function( xhr, status){
                
            }
             
        }); //$.ajax({
    }
}
