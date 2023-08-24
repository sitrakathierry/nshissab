$(document).ready(function(){
    var cheque_editor = new LineEditor("#cheque_editor") ;
    var instance = new Loading(files.loading)

    $("#chk_date_cheque").datepicker()
    $("#chk_date_declaration").datepicker()
    cheque_editor.setEditorText($("#cheque_editor").val())

    $("#formCheque").submit(function(){
        var self = $(this);
        $("#cheque_editor").val(cheque_editor.getEditorText('#cheque_editor'))
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
                  url: routes.compta_cheque_save,
                  type: "post",
                  cache: false,
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
                            $("#chk_nom_chequier").val("")
                            $("#chk_banque").val("")
                            $("#chk_type").val("")
                            $("#chk_numCheque").val("")
                            $("#chk_montant").val("")
                            $("#cheque_editor").val("")
                            $("#chk_date_cheque").val("")
                            
                            $(".chosen_select").trigger("chosen:updated")
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