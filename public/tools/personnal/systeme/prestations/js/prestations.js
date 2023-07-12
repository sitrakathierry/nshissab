$(document).ready(function(){
    var prest_prestation_editor = new LineEditor(".prest_prestation_editor") ;
    var paiement_editor = new LineEditor("#paiement_editor") ;
    var instance = new Loading(files.loading) 
    prest_prestation_editor.setEditorText($(".prest_prestation_editor").text())
    
    $("#formService").submit(function(){
      var self = $(this);
      $(".prest_prestation_editor").val(prest_prestation_editor.getEditorText('.prest_prestation_editor'))
      $.confirm({
        title: "Confirmation",
        content: "Etes-vous sûre de vouloir enregistrer ?",
        type: "blue",
        theme: "modern",
        buttons: {
          btn1: {
            text: "Non",
            action: function () {
              $("#srv_nom").val("")
              prest_prestation_editor.setEditorText("")
            },
          },
          btn2: {
            text: "Oui",
            btnClass: "btn-blue",
            keys: ["enter", "shift"],
            action: function () {
              var data = self.serialize();
              var realinstance = instance.loading();
              $.ajax({
                url: routes.prest_service_save,
                type: "post",
                data: data,
                dataType: "json",
                success: function (json) {
                  realinstance.close() ;
                  $.alert({
                    title: "Message",
                    content: json.message,
                    type: json.type,
                    buttons: {
                      OK: function () {
                        if (json.type == "green") {
                          var elements = data.split("&");
                          elements.forEach((elem) => {
                            $("#" + elem.split("=")[0]).val("");
                          });
                          prest_prestation_editor.setEditorText("")
                          location.reload();
                        }
                      },
                    },
                  });
                },
                error: function (resp) {
                  realinstance.close();
                  $.alert(JSON.stringify(resp));
                },
              });
            },
          },
        },
      });
        return false;
    });

    $("#srv_tarif_format").change(function(){
      var selectedOption = $(this).find("option:selected") ;
      var reference = selectedOption.data("reference")
      if(reference == "DRE")
      {
        var realinstance = instance.loading()
        $.ajax({
            url: routes.prest_service_duree_get,
            type:'post',
            cache: false,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
              realinstance.close()
              $(response).insertAfter(".caption_format") ; 
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
      }
      else
      {
        $(".caption_duree").remove()
      }
      
    })

    $("#formServPrix").submit(function(){
      var self = $(this);
      $.confirm({
        title: "Confirmation",
        content: "Etes-vous sûre ?",
        type: "blue",
        theme: "modern",
        buttons: {
          btn1: {
            text: "Non",
            action: function () {},
          },
          btn2: {
            text: "Oui",
            btnClass: "btn-blue",
            keys: ["enter", "shift"],
            action: function () {
              var data = self.serialize();
              var realinstance = instance.loading();
              $.ajax({
                url: routes.prest_service_prix_save,
                type: "post",
                data: data,
                dataType: "json",
                success: function (json) {
                  realinstance.close();
                  $.alert({
                    title: "Message",
                    content: json.message,
                    type: json.type,
                    buttons: {
                      OK: function () {
                        if (json.type == "green") {
                          var elements = data.split("&");
                          elements.forEach((elem) => {
                            $("#" + elem.split("=")[0]).val("");
                          });
                          location.reload();
                        }
                      },
                    },
                  });
                },
                error: function (resp) {
                  realinstance.close();
                  $.alert(JSON.stringify(resp));
                },
              });
            },
          },
        },
      });
      return false;
    });
    
})