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
    
    $(".btn_edit_contrat").click(function(){
      var self = $(this)
      $.confirm({
          title: "Confirmation",
          content:"Vous êtes sûre ?",
          type:"orange",
          theme:"modern",
          buttons:{
              btn1:{
                  text: 'Non',
                  action: function(){}
              },
              btn2:{
                  text: 'Oui',
                  btnClass: 'btn-orange',
                  keys: ['enter', 'shift'],
                  action: function(){
                      var realinstance = instance.loading()
                      var dataArray = {
                          prest_ctr_id:$("#prest_ctr_id").val(),
                          prest_ctr_bail_type_location:$("#prest_ctr_bail_type_location").val(),
                          prest_ctr_pourcentage:$("#prest_ctr_pourcentage").val(),
                          prest_ctr_renouvellement:$("#prest_ctr_renouvellement").val(),
                          prest_ctr_caution:$("#prest_ctr_caution").val(),
                          prest_ctr_changement:$("#prest_ctr_changement").val()
                      }
                      $.ajax({
                          url: routes.prest_location_edit_contrat_valid,
                          type:'post',
                          cache: false,
                          data:dataArray,
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
                                            location.assign(routes.prest_location_contrat_liste)
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
    })

    $(".btn_delete_contrat").click(function(){
      var self = $(this)
      $.confirm({
          title: "Confirmation",
          content:"Vous êtes sûre ?",
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
                      var dataArray = {
                          id:self.attr("value"),
                      }
                      $.ajax({
                          url: routes.prest_location_contrat_delete,
                          type:'post',
                          cache: false,
                          data:dataArray,
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
                                            location.assign(routes.prest_location_contrat_liste)
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
    })

    $(".btn_annule_contrat").click(function(){
      var self = $(this)
      $.confirm({
          title: "Confirmation",
          content:"Vous êtes sûre ?",
          type:"dark",
          theme:"modern",
          buttons:{
              btn1:{
                  text: 'Non',
                  action: function(){}
              },
              btn2:{
                  text: 'Oui',
                  btnClass: 'btn-dark',
                  keys: ['enter', 'shift'],
                  action: function(){
                      var realinstance = instance.loading()
                      var dataArray = {
                          id:self.attr("value"),
                      }
                      $.ajax({
                          url: routes.prest_location_contrat_annule,
                          type:'post',
                          cache: false,
                          data:dataArray,
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
                                            location.assign(routes.prest_location_contrat_liste)
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
    })
})