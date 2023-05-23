$(document).ready(function(){
    var sav_annule_editor = new LineEditor(".sav_annule_editor") ;
    var instance = new Loading(files.loading) ;
    
    $("#sav_facture").change(function(){
        var self = $(this)
        var data = new FormData() ;
        data.append("idF",self.val())
        var realinstance = instance.loading()
        $.ajax({
            url: routes.sav_facture_display,
            type:'post',
            cache: false,
            data: data,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(resp){
                realinstance.close()
                $(".elem_sav_facture").html(resp) ;
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })
})