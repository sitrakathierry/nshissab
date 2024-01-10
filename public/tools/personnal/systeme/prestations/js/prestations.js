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

    $(".fiche_caution_display").click(function(){
      
    })

    $(document).on('click',".pres_service_supprimer", function(){
      var self = $(this)
      $.confirm({
          title: "Suppression",
          content:"Êtes-vous sûre de vouloir supprimer ?",
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
                          url: routes.param_service_element_delete,
                          type:'post',
                          cache: false,
                          data:{idService:self.data("value")},
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
    })

    $(document).on("click",".param_tarif_supprimer",function(){
      var self = $(this)
      $.confirm({
          title: "Suppression",
          content:"Êtes-vous sûre de vouloir supprimer ?",
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
                          url: routes.param_service_tarif_delete,
                          type:'post',
                          cache: false,
                          data:{idTarif:self.data("value")},
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
    })

    $(document).on("click",".param_tarif_modifier",function(){
      var self = $(this)
      var tarif = self.closest("tr").find(".srv_libelle_Tarif").text()
      var prixTarif = self.closest("tr").find(".srv_prix_Tarif").text()
      $.confirm({
          title: "Modification",
          content:`
            <div class="text-left">
              <label for="" class="font-weight-bold text-uppercase">Tarif</label>
              <input type="text" name="nom_tarif" id="nom_tarif" class="form-control" value="`+tarif+`" readonly placeholder=". . .">

              <label for="srv_tarif_modif_prix" class="font-weight-bold mt-3 text-uppercase">Prix</label>
              <input type="number" step="any" name="srv_tarif_modif_prix" id="srv_tarif_modif_prix" class="form-control" value="`+prixTarif+`" placeholder=". . .">
            </div>
          `,
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
                      $.ajax({
                          url: routes.param_service_tarif_update,
                          type:'post',
                          cache: false,
                          data:{
                            idTarif:self.data("value"),
                            prixMTarif : $("#srv_tarif_modif_prix").val()
                          },
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
    })

    $(".srv_modif_service").click(function()
    {
      var self = $(this)
      $.confirm({
          title: "Modification",
          content:"Êtes-vous sûre ?",
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
                      $.ajax({
                        url: routes.params_service_element_update,
                        type:'post',
                        cache: false,
                        data:{
                          idService:self.data("value"),
                          srv_nom:$("#srv_nom").val(),
                          srv_description:prest_prestation_editor.getEditorText('.prest_prestation_editor'),
                        },
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

    var elemforSearch = [
      {
          name: "nom",
          action:"keyup",
          selector : "#prest_std_nom"
      },
      {
          name: "nom",
          action:"change",
          selector : "#prest_std_nom"
      }
  ]

  function searchService()
  {
      var instance = new Loading(files.search) ;
      $(".elem_service").html(instance.search(3)) ;
      var formData = new FormData() ;
      for (let j = 0; j < elemforSearch.length; j++) {
          const search = elemforSearch[j];
          formData.append(search.name,$(search.selector).val());
      }
      $.ajax({
          url: routes.prest_service_item_search ,
          type: 'post',
          cache: false,
          data:formData,
          dataType: 'html',
          processData: false, 
          contentType: false, 
          success: function(response){
              $(".elem_service").html(response) ;
          }
      })
  }

  elemforSearch.forEach(elem => {
      $(document).on(elem.action,elem.selector,function(){
          searchService()
      })
  })

  $(document).on('click',".btn_imprimer_echeance",function(){
    var self = $(this)
    var realinstance = instance.loading()
    $.ajax({
        url: routes.param_modele_pdf_get,
        type:"post",
        dataType:"html",
        processData:false,
        contentType:false,
        success : function(response){
            realinstance.close()
            $.confirm({
                title: "Impression Facture",
                content:response,
                type:"blue",
                theme:"modern",
                buttons:{
                    btn1:{
                        text: 'Annuler',
                        action: function(){}
                    },
                    btn2:{
                        text: 'Imprimer',
                        btnClass: 'btn-blue',
                        keys: ['enter', 'shift'],
                        action: function(){
                            var idModeleEntete = $("#modele_pdf_entete").val() ;
                            var idModeleBas = $("#modele_pdf_bas").val() ;
                            var idFinance = self.data("value") ;
                            var url = routes.prest_location_caution_imprimer + '/' + idFinance + '/' + idModeleEntete + '/' + idModeleBas;
                            window.open(url, '_blank');
                        }
                    }
                }
            })
        },
        error: function(resp){
            realinstance.close()
            $.alert(JSON.stringify(resp)) ;
        }
    })
    return false ;
  })

  $("#formLocataire").submit(function(){
    var self = $(this);
    $.confirm({
      title: "Modification",
      content: "Etes-vous sûre ?",
      type: "orange",
      theme: "modern",
      buttons: {
        btn1: {
          text: "Non",
          action: function () {
          }, 
        },
        btn2: {
          text: "Oui",
          btnClass: "btn-orange",
          keys: ["enter", "shift"],
          action: function () {
            var data = self.serialize();
            var realinstance = instance.loading();
            $.ajax({
              url: routes.prest_location_locataire_update,
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

  $(".lct_btn_modifier").click(function(){
    $("#lct_nom").removeAttr("readonly") ;
    $("#lct_tel").removeAttr("readonly") ;
    $("#lct_adresse").removeAttr("readonly") ;
    $("#lct_email").removeAttr("readonly") ;
    $(this).parent().html('<button type="submit" class="btn btn-purple btn-sm"><i class="fa fa-database"></i>&nbsp;Mettre à jour</button>')
    $.alert({
        title: 'Modification',
        content: 'Les champs sont maintenant active',
    })
  })
})