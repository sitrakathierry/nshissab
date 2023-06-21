$(document).ready(function(){
    var prest_prestation_editor = new LineEditor(".prest_prestation_editor") ;
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

    var duree = $("#srv_tarif_duree").html() ;
    $("#srv_tarif_format").change(function(){
      var selectedOption = $(this).find("option:selected") ;
      var reference = selectedOption.data("reference")
      if(reference == "DRE")
      {
        var element = `
          <div class="col-md-3 caption_duree">
            <label for="srv_tarif_duree" class="font-weight-bold text-uppercase">Durée</label>
            <select name="srv_tarif_duree" class="custom-select chosen_select custom-select-sm" id="srv_tarif_duree">
                `+duree+`
            </select>
          </div>
          <script>
            $(".chosen_select").chosen({
                no_results_text: "Aucun resultat trouvé : "
            });
          </script>
        `
        $(element).insertAfter(".caption_format") ; 
      }
      else
      {
        $(".caption_duree").remove()
      }
      
    })

})