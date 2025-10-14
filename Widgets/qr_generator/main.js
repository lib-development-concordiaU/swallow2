function generateQR(){
    const urlstr = $("#url").val();
    $.get( "generate.php?url="+urlstr, function(data) {
        $("#imageContainer").html("<img src='"+data+"' >");
        textField = window.parent.document.getElementById(textfield_id);
        textField.value =  data;
      });
}

const urlParams = new URLSearchParams(window.location.search);
var textfield_id = urlParams.get('id');

const imagePath = window.parent.document.getElementById(textfield_id);
if(imagePath.value != ''){
    $("#imageContainer").html("<img src='"+imagePath.value+"' >");
}