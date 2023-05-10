$(document).ready(function(){
    $("#formClient").submit(function(event){
        event.preventDefault()
        console.log($("#clt_lien_telephone").val())
    })
    
})