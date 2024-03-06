$(document).ready(function(){

    $("#cmp_operation_type").change(function(){
        var instance = new Loading(files.loading) ;
        if($("#cmp_operation_type").val() == "")
          return false ;
        var reference = $(this).find("option:selected").data("reference")
        var dataElement = {
          "CHQ":{
            numero:"N° Chèque",
            editeurDepot:"Nom du Chèquier",
            editeurRetrait:"Libellé du chèque",
          },
          "VRT":{
            numero:"N° Virement",
            editeurDepot:"Virement émit par",
            editeurRetrait:"Libellé du virement",
          },
          "CBR":{
            numero:"Reference Carte",
            editeurDepot:"Editeur de la Carte",
            editeurRetrait:"Libellé de la carte",
          },
          "MOB":{
            numero:"Reference de Transfert",
            editeurDepot:"Editeur de Transfert",
            editeurRetrait:"Libellé du Transfert",
          },
        }
  
        if(reference == "CSH")
        {
          $(".caption_mode_numero").parent().hide()
          $(".caption_mode_editeur").parent().hide()
        }
        else
        {
          $(".caption_mode_numero").parent().show()
          $(".caption_mode_editeur").parent().show()

          $(".caption_mode_numero").text(dataElement[reference].numero)
          var categorieOperation = $("#cmp_operation_categorie").find("option:selected").data("reference")
          if(categorieOperation == "DEP")
            $(".caption_mode_editeur").text(dataElement[reference].editeurDepot)
          else
            $(".caption_mode_editeur").text(dataElement[reference].editeurRetrait)
        }
      })

      $("#cmp_operation_categorie").change(function(){
        if($("#cmp_operation_type").val() != "" && !$("#cmp_operation_type").is("input"))
          $("#cmp_operation_type").change()
      })

      $(document).on('click',".btn_update_mvt",function(){
        // compta_banque_operation_update
        var self = $(this)
        $.confirm({
            title: "Suppression",
            content:"Êtes-vous sûre ?",
            type:"red",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Non',
                    action: function(){}
                },
                btn2:{
                    text: 'Oui',
                    btnClass: 'btn-red',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.compta_banque_operation_update,
                            type:'post',
                            cache: false,
                            data:{idOpt:self.data("value")},
                            dataType: 'json',
                            success: function(json){
                                realinstance.close()
                                $.alert({
                                    title: 'Message',
                                    content: json.message,
                                    type: json.type,
                                    buttons: {
                                        OK: function(){
                                            if(json.type == "green")
                                            {
                                                location.reload()
                                            }
                                        }
                                    }
                                });
                            },
                            error: function(resp){
                                realinstance.close()
                                $.alert(JSON.stringify(resp)) ;
                            }
                        })
                    }
                }
            }
        })
        return false ;
    }) ;

    $(".chosen_select").trigger("chosen:updated") ;
})