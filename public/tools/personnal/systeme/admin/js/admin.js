$(document).ready(function(){
    $("#formDataImport").submit(function(){
        var self = $(this)
        // $.confirm({
        //     title: "Importation",
        //     content:"Êtes-vous sûre ?",
        //     type:"blue",
        //     theme:"modern",
        //     buttons:{
        //         btn1:{
        //             text: 'Non',
        //             action: function(){}
        //         },
        //         btn2:{
        //             text: 'Oui',
        //             btnClass: 'btn-blue',
        //             keys: ['enter', 'shift'],
        //             action: function(){
        //                 var realinstance = instance.loading()
        //                 $.ajax({
        //                     url: routes.stock_delete_fournisseur,
        //                     type:'post',
        //                     cache: false,
        //                     data:{:self.data("value")},
        //                     dataType: 'json',
        //                     success: function(json){
        //                         realinstance.close()
        //                         $.alert({
        //                             title: 'Message',
        //                             content: json.message,
        //                             type: json.type,
        //                             buttons: {
        //                                 OK: function(){
        //                                     if(json.type == "green")
        //                                     {
        //                                         location.reload()
        //                                     }
        //                                 }
        //                             }
        //                         });
        //                     },
        //                     error: function(resp){
        //                         realinstance.close()
        //                         $.alert(JSON.stringify(resp)) ;
        //                     }
        //                 })
        //             }
        //         }
        //     }
        // })
        return false ;
    }) ;

    $(".btn_import_data").click(function(){
        $("#file_to_import").click() ;
    }) ;

    $("#file_to_import").change(function(e){
        var file = e.target.files[0];
        if(file){
          var reader = new FileReader();
          reader.onload = function(e){
            var base64Data = e.target.result.split(',')[1];
            // console.log('Base64 Data:', base64Data);
            // Envoi de base64Data au serveur PHP via AJAX
            var formData = new FormData() ;
            formData.append("base64Data",base64Data) ;
            $.ajax({
                url: routes.admin_import_data_display,
                type: 'POST',
                data: formData,
                dataType:'html',
                processData: false,
                contentType: false,
                success: function(response) {
                    $(".contentData").html(response) ;
                    $(".btn_import_data").parent().html(`
                    <button type="button" class="btn btn_import_reset px-4 btn-sm ml-3 btn-dark"><i class="fa fa-times"></i>&nbsp;Annuler</button>
                    <button type="submit" class="btn px-4 btn-sm ml-3 btn-perso-one"><i class="fa fa-save"></i>&nbsp;Enregistrer</button>
                    `)
                },
                error: function(resp){
                    $.alert(JSON.stringify(resp)) ;
                }
            });
          };
          reader.readAsDataURL(file) ; 
        }
    }) ;

    $(document).on("click",".btn_import_reset",function(){
        location.reload() ;
    })

})