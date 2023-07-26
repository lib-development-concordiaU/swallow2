function toggleInstance(ul_id){
    $child_ul = $("#"+ul_id+"").children('ul');
    
    if($child_ul.hasClass('class-tree-hidden')){
        $child_ul.removeClass('class-tree-hidden');
        $("#"+ul_id+"-expand-icon").removeClass('glyphicon-expand');
        $("#"+ul_id+"-expand-icon").addClass('glyphicon-collapse-down');
        sessionStorage.expanded = sessionStorage.expanded + ","+ul_id;
    }else{
        $child_ul.addClass('class-tree-hidden');
        $("#"+ul_id+"-expand-icon").addClass('glyphicon-expand');
        $("#"+ul_id+"-expand-icon").removeClass('glyphicon-collapse-down');
        const expanded =  sessionStorage.expanded.split(",");
        var result = "";

        expanded.forEach(element => {
            if(element !== ul_id && element !== "" ) {
                result = result + "," + element;
                
            }
        });

        sessionStorage.expanded = result;
    }

}

function showInstance(ul_id){
    $child_ul = $("#"+ul_id+"").children('ul');
    
    if($child_ul.hasClass('class-tree-hidden')){
        $child_ul.removeClass('class-tree-hidden');
        $("#"+ul_id+"-expand-icon").removeClass('glyphicon-expand');
        $("#"+ul_id+"-expand-icon").addClass('glyphicon-collapse-down');
    }
}

function init(){

    if(sessionStorage.expanded == undefined){
        sessionStorage.expanded = "";
    }else{
        const expanded =  sessionStorage.expanded.split(",");
        expanded.forEach(element => {
            if(element !== ""){
                showInstance(element);
            }
        });
    }
}

function createInstance(definition,parentid){
    console.log(definition);
    $.ajax({
        // The URL for the request
        
        url: "Controller/class-create.php?definition="+encodeURI(definition)+"&parentid="+parentid,
        type: "GET", 
        
        success: function( data ) {
           // $("#main").load("View/collection-edit.php?collectionid="+data)
           console.log(data);
            $("#main").load("View/classes.php");
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


function editInstance(id){
    $("#right-pane").load("View/class-edit.php?classid="+id)	
}

function deleteInstance(id){
    if(confirm(' Are you sure you want to delete this class? \n All records associated with this class will be deleted forever. \n This action cannot be undone')){
        $.ajax({
            // The URL for the request
            url: "Controller/class-delete.php?id="+id,
            type: "GET", 
            
            success: function( data ) {
                console.log(data);
                $("#main").load("View/classes.php")	
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


function duplicateInstance(id){
    if(confirm('Are you sure you want to duplicate this class ? \nThis will create a duplicate of all items in the original class and associate them with the new one. \nThis may take a while depending on the size of the collection.')){
        $.ajax({
            // The URL for the request
            url: "Controller/class-duplicate.php?id="+id,
            type: "GET", 
            
            success: function( data ) {
                console.log(data);
                $("#main").load("View/classes.php")	
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
