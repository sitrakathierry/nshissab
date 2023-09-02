$(document).ready(function(){

    $("#cmp_operation_type").change(function(){
        var reference = $(this).find("option:selected").data("reference")
  
        var dataElement = {
          "CHQ":{
            numero:"N° Chèque",
            editeur:"Nom du Chèquier",
          },
          "VRT":{
            numero:"N° Virement",
            editeur:"Virement émit par",
          },
          "CBR":{
            numero:"Reference Carte",
            editeur:"Editeur de la Carte",
          },
          "MOB":{
            numero:"Reference de Transfert",
            editeur:"Editeur de Transfert",
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
          $(".caption_mode_editeur").text(dataElement[reference].editeur)
        }
  
      })


})