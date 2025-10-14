$.getJSON( "vocabulary.json", function( data ) {
    $("#category").autocomplete({
        source: data,
        minLength: 3,
    });
    
});

function addCategory(){
    var category = $('#category').val();
    if(category != ''){
        $.get( "process.php?category="+category, function(data) {
            textField = window.parent.document.getElementById(textfield_id);
            textField.value =  category;
          });
    }
}


const params = new URLSearchParams(window.location.search);
var textfield_id = params.get('id');