$(document).ready(function(){
    $("#loginTogglePass").click(function(){
        if($(this).is(':checked'))
        {
            $("#password").attr("type","text")
            $("#labelToggle").text("Masquer le mot de passe")
        }
        else
        {
            $("#password").attr("type","password")
            $("#labelToggle").text("Afficher le mot de passe")
        }
    })

})