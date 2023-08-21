$(document).ready(function(){
    var instance = new Loading(files.loading)
    var depense_editor = new LineEditor("#depense_editor") ;
    
    $("#cmp_operation_date").datepicker()

    // $("#cmp_banque_nom").val("")
    $("#formBanque").submit(function(){
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
                  url: routes.compta_banque_etablissement_save,
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
                            $("#cmp_banque_nom").val("")
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

    $("#formCompte").submit(function(){
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
                  url: routes.compta_banque_compte_bancaire_save,
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
                            $("#cmp_compte_banque").val("")
                            $("#cmp_compte_numero").val("")
                            $("#cmp_compte_solde").val("")
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

    $("#formOperation").submit(function(){
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
                  url: routes.compta_banque_operation_save,
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
                            var elements = data.split("&");
                            elements.forEach((elem) => {
                              $("#" + elem.split("=")[0]).val("");
                            });
                            $(".chosen_select").trigger("chosen:updated"); 
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

    $("#cmp_operation_banque").change(function(){
        var self = $(this)
        var realinstance = instance.loading()
        var data = new FormData() ;
        data.append("id",self.val())
        $.ajax({
            url: routes.compta_banque_compte_bancaire_get,
            type:'post',
            cache: false,
            data:data,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                realinstance.close()
                $("#cmp_operation_compte").html(response)
                $("#cmp_operation_compte").trigger("chosen:updated")
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    $("#cmp_operation_compte").change(function(){
        var self = $(this)
        var realinstance = instance.loading()
        $.ajax({
            url: routes.compta_banque_compte_solde_get,
            type:'post',
            cache: false,
            data:{id:self.val()},
            dataType: 'json',
            success: function(response){
                realinstance.close()
                $("#cmp_operation_solde").val(response.solde)
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    for (let i = 1; i <= 4 ; i++) {
      $("#ttpPaiement_"+i).easyTooltip({
        content: '<span class="text-white">'+$("#ttpPaiement_"+i).data("content")+'</span>',
        defaultRadius: "3px",
        tooltipZindex: 1000,
        tooltipPadding: "10px 15px",
        tooltipBgColor: "rgba(0,0,0,0.85)",
      })
    }


    // $(".toolTipPaiement").mouseover(function(){
    //   var self = $(this)
    //   $(this).easyTooltip({
    //     content: '<div class="text-white text-center">'+self.data("libelle")+'<br>'+self.data("percent")+'</div>',
    //     defaultRadius: "3px",
    //     tooltipZindex: 1000,
    //     tooltipPadding: "10px 15px",
    //     tooltipBgColor: "rgba(0,0,0,0.85)",
    //   })
    // })

})