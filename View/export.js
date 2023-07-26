
function query(page){
    var query = $("#metadaquery").val();
    var institution = $("#f_institution").val();
    var cataloguer = $("#f_cataloguer").val();
    var collection = $("#f_collection").val();
    var schema = $("#f_schema").val();

    $("#main").load("View/export.php?metadataquery="+btoa(unescape(encodeURIComponent(query)))+"&institution="+encodeURI(institution)+"&cataloguer="+cataloguer+"&collection="+collection)+"&schema="+schema+"&page="+page;
}


function filterClass(select_id,page){
    const class_id = $("#"+select_id).val();
    $("#selectedClass").val(class_id);
    filter(page);
}

function filter(page){
    const cataloguer = $("#f_cataloguer").val();
    const orderby =  $("#f_orderby").val();
    const query = $("#metadaquery").val();
    const schema = $("#f_schema").val();
    const selectedClass = $("#selectedClass").val();

    $("#main").load("View/export.php?cataloguer="+cataloguer+"&selectedClass="+selectedClass+"&metadataquery="+btoa(unescape(encodeURIComponent(query)))+"&page="+page+"&orderby="+orderby+"&schema="+schema);
}


function exportdataset(){
    var format = $("#export_format").val();
    var institution = $("#f_institution").val();
    var cataloguer = $("#f_cataloguer").val();
    var selectedClass = $("#selectedClass").val(); 
    var schema = $("#f_schema").val();
    var query = $("#metadaquery").val();
    if (query != ''){
        query = btoa(query);
    }

    switch(format){
        case '1':
            url = "Controller/export.php?institution="+encodeURI(institution)+"&cataloguer="+cataloguer+"&class="+selectedClass+"&schema="+schema+"&query="+query+"&format="+format;
            break;
        case '2':
            url = "Controller/export-triplets.php?institution="+encodeURI(institution)+"&cataloguer="+cataloguer+"&class="+selectedClass+"&schema="+schema+"&query="+query+"&format="+format;
            break;
    }
    

    window.open(url);
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
