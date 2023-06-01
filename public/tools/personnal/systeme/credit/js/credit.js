$(document).ready(function(){
    $("#crd_paiement_date").datepicker()
    var instance = new Loading(files.loading)
    
    $("#formPaiementCredit").submit(function(){
      var self = $(this);
      $.confirm({
        title: "Confirmation",
        content: "Etes-vous sûre de vouloir enregistrer ?",
        type: "blue",
        theme: "modern",
        buttons: {
          btn1: {
            text: "Non",
            action: function () {
                $("#crd_paiement_date").val("")
                $("#crd_paiement_montant").val("")
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
                url: routes.crd_paiement_credit_save,
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