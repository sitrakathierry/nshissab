$(document).ready(function(){
    var instance = new Loading(files.loading)
    var depense_editor = new LineEditor("#depense_editor") ;
    var appBase = new AppBase() ;

    $("#dep_date_mode").datepicker() ;
    $("#dep_date_declaration").datepicker() ;
    depense_editor.setEditorText($("#depense_editor").val())
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

    $(document).on("click",".btn_modif_compte",function(){
      var self = $(this)
      var formData = new FormData() ;
      var realinstance = instance.loading()
      formData.append('idCompte',self.data("value"))
      $.ajax({
          url: routes.compta_banque_compte_modif_get,
          type:'post',
          cache: false,
          data:formData,
          dataType: 'html',
          processData: false,
          contentType: false,
          success: function(response){
              realinstance.close()
              $("#contentCompte").html(response)
          },
          error: function(resp){
              realinstance.close()
              $.alert(JSON.stringify(resp)) ;
          }
      })
    })

    $(document).on("submit","#formUpdateCompte",function(){
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
                      var data = self.serialize() ;
                      $.ajax({
                          url: routes.compta_banque_compte_bancaire_update,
                          type:'post',
                          cache: false,
                          data:data,
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

    $(document).on("click",".btn_delete_compte",function(){
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
                          url: routes.compta_banque_compte_bancaire_delete,
                          type:'post',
                          cache: false,
                          data:{idCompte:self.data("value")},
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

    $("#formDepense").submit(function(){
      var self = $(this);
      $("#depense_editor").val(depense_editor.getEditorText('#depense_editor'))
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
                url: routes.compta_declaration_depense_save,
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
                          $("input,select").val("")
                          $(".chosen_select").trigger("chosen:updated") ;
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
    })

    function calculMontantDetails()
    {
      var totalGeneral = 0 ;

      $(".elemDetailsDepense tr").each(function(){
        var totalLigne = $(this).find("#dep_item_total").val()

        totalGeneral += parseFloat(totalLigne) ;
      })

      $(".totalDepGeneral").text(totalGeneral) ;

      $("#dep_details_designation").val("")
      $("#dep_details_quantite").val("")
      $("#dep_details_prix").val("")

      $(".chosen_select").trigger("chosen:updated")
    }

    $(document).on("click",".dep_details_ajouter",function(){
      var dep_details_designation = $("#dep_details_designation").val()
      var dep_details_quantite = $("#dep_details_quantite").val()
      var dep_details_prix = $("#dep_details_prix").val()

      var result = appBase.verificationElement([
          dep_details_designation,
          dep_details_quantite,
          dep_details_prix,
      ],[
          "Désignation",
          "Quantité",
          "Prix Unitaire",
      ]) ;

      if(!result["allow"])
      {
          $.alert({
              title: 'Message',
              content: result["message"],
              type: result["type"],
          });

          return result["allow"] ;
      }

      var totalLigne = parseFloat(dep_details_quantite) * parseFloat(dep_details_prix) ;

      if($("#add_new_designation").val() == "NON")
      {
        isAjoute = true ;
        $(".elemDetailsDepense tr").each(function(){
          var idLibelle = $(this).find("#dep_item_id_libelle").val()
          if(dep_details_designation == idLibelle)
          {
            isAjoute = false ;
            return ;
          }
        })

        if(!isAjoute)
        {
          $.alert({
            title: "Message",
            content: "Désolé, cette désignation existe déjà sur la liste",
            type: "orange",
            buttons: {
              OK: function(){
                $("#dep_details_designation").val("")
                $("#dep_details_quantite").val("")
                $("#dep_details_prix").val("")

                $(".chosen_select").trigger("chosen:updated")
              }
            }
          });
          
          return false ;
        }

        var itemLigne = `
          <tr>
            <td>
            `+$("#dep_details_designation option:selected").text()+`
            <input type="hidden" name="dep_item_id_libelle[]" id="dep_item_id_libelle" value="`+dep_details_designation+`">
            <input type="hidden" name="dep_item_designation[]" value="`+$("#dep_details_designation option:selected").text()+`">
            <input type="hidden" name="dep_item_quantite[]" id="dep_item_quantite" value="`+dep_details_quantite+`">
            <input type="hidden" name="dep_item_prix[]" id="dep_item_prix" value="`+dep_details_prix+`">
            <input type="hidden" id="dep_item_total" value="`+totalLigne+`">
            </td>
            <td>`+dep_details_quantite+`</td>
            <td>`+dep_details_prix+`</td>
            <td>`+totalLigne+`</td>
            <td class="text-center align-middle">
                <button class="btn btn-sm dep_supprime_ligne btn-outline-danger font-smaller"><i class="fa fa-times"></i></button>
            </td>
        </tr>
        `
        $(".elemDetailsDepense").append(itemLigne) ;
        calculMontantDetails()
      }
      else
      {
        var oldContentDetail = $(".elemDetailsDepense").html()
        $(".elemDetailsDepense").html(instance.search(5))
        $.ajax({
            url: routes.compta_libelle_depense_save,
            type:'post',
            cache: false,
            data:{
              libelle:dep_details_designation
            },
            dataType: 'json',
            success: function(json){
                var itemLigne = `
                  <tr>
                    <td>
                    `+dep_details_designation+`
                    <input type="hidden" name="dep_item_id_libelle[]" value="`+json.id+`">
                    <input type="hidden" name="dep_item_designation[]" value="`+dep_details_designation+`">
                    <input type="hidden" name="dep_item_quantite[]" id="dep_item_quantite" value="`+dep_details_quantite+`">
                    <input type="hidden" name="dep_item_prix[]" id="dep_item_prix" value="`+dep_details_prix+`">
                    <input type="hidden" id="dep_item_total" value="`+totalLigne+`">
                    </td>
                    <td>`+dep_details_quantite+`</td>
                    <td>`+dep_details_prix+`</td>
                    <td>`+totalLigne+`</td>
                    <td class="text-center align-middle">
                        <button class="btn btn-sm dep_supprime_ligne btn-outline-danger font-smaller"><i class="fa fa-times"></i></button>
                    </td>
                </tr>
                `
                $(".elemDetailsDepense").empty().html(oldContentDetail+itemLigne) ;

                calculMontantDetails()
            },
            error: function(resp){
                $.alert(JSON.stringify(resp)) ;
            }
        })
      }
    })

    $(document).on('click',".dep_supprime_ligne",function(){
      $(this).closest("tr").remove()
      calculMontantDetails()
    })

    function getContentService(type)
    {
      $("#contentService").empty().html(instance.otherSearch())
      var formData = new FormData();
      formData.append("type",type)
      $.ajax({
          url: routes.compta_dep_content_service_get,
          type:'post',
          cache: false,
          data:formData,
          dataType: 'html',
          processData: false,
          contentType: false,
          success: function(response){
              $("#contentService").empty().html(response)
          },
          error: function(resp){
            $("#contentService").empty().html(resp)
              $.alert(JSON.stringify(resp)) ;
          }
      })
    }

    $(document).on("click",".dep_existing_service",function(){
      getContentService("EXISTING")
    })

    $(document).on("click",".dep_new_service",function(){
      getContentService("NEW")
    })

    function getContentLibelle(type)
    {
      $("#contentLibelle").empty().html(instance.otherSearch())
      var formData = new FormData();
      formData.append("type",type)
      $.ajax({
          url: routes.compta_dep_content_libelle_get,
          type:'post',
          cache: false,
          data:formData,
          dataType: 'html',
          processData: false,
          contentType: false,
          success: function(response){
              $("#contentLibelle").empty().html(response)
          },
          error: function(resp){
              $.alert(JSON.stringify(resp)) ;
          }
      })
    }

    $(document).on("click",".dep_existing_libelle",function(){
      getContentLibelle("EXISTING")
    })

    $(document).on("click",".dep_new_libelle",function(){
      getContentLibelle("NEW")
    })

    $("#dep_mode_paiement").change(function(){
      var reference = $(this).find("option:selected").data("reference")

      var dataElement = {
        "CHK":{
          numero:"N° Chèque",
          editeur:"Nom du Chèquier",
          date:"Date Chèque",
        },
        "VRM":{
          numero:"N° Virement",
          editeur:"Virement émit par",
          date:"Date Virement",
        },
        "CBR":{
          numero:"Reference Carte",
          editeur:"Editeur de la Carte",
          date:"Date Paiement",
        },
        "MOB":{
          numero:"Reference de Transfert",
          editeur:"Editeur de Transfert",
          date:"Date Transfert",
        },
      }

      if(reference == "ESP")
      {
        $(".caption_mode_numero").parent().hide()
        $(".caption_mode_editeur").parent().hide()
        $(".caption_mode_date").parent().hide()
      }
      else
      {
        $(".caption_mode_numero").parent().show()
        $(".caption_mode_editeur").parent().show()
        $(".caption_mode_date").parent().show()

        $(".caption_mode_numero").text(dataElement[reference].numero)
        $(".caption_mode_editeur").text(dataElement[reference].editeur)
        $(".caption_mode_date").text(dataElement[reference].date)
      }
    })

    $(".btn_dep_modif").click(function(){
      var self = $(this) ;
      var realinstance = instance.loading()
      $.ajax({
          url: routes.compta_depense_template_get,
          type:'post',
          cache: false,
          dataType: 'html',
          processData: false,
          contentType: false,
          success: function(response){
              realinstance.close() ;
              var tabElemDep = [
                "#dep_nom_concerne",
                "#dep_element",
                "#dep_service",
                "#dep_motif",
                "#dep_mode_paiement",
                "#dep_montant",
                "#dep_numero_mode",
                "#dep_editeur_mode",
                "#dep_date_mode",
                "#dep_num_facture",
                "#dep_mois_facture",
                "#dep_annee_facture",
                "#dep_date_declaration",
              ] ;

              for (let i = 0; i < tabElemDep.length; i++) {
                const element = tabElemDep[i];
                $(element).removeAttr("readonly") ;
                $(element).removeClass("text-primary") ;
                $(element).addClass("text-success") ;
                self.parent().html(`
                <button type="submit" class="btn btn-sm btn_dep_maj font-weight-bold text-white ml-3 btn-warning px-3"><i class="fa fa-check"></i>&nbsp;Mettre à jour</button>
                <button type="button" class="btn btn-sm ml-3 btn-primary px-3"><i class="fa fa-print"></i>&nbsp;Imprimer</button>
                `) ;
              }

              $(".contentEditDep").html(response) ;
          },
          error: function(resp){
              realinstance.close()
              $.alert(JSON.stringify(resp)) ;
          }
      })
    })

    $("#formDepModif").submit(function(){
      var self = $(this);
      $("#depense_editor").val(depense_editor.getEditorText('#depense_editor'))
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
                url: routes.compta_declaration_depense_save,
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
                          // $("input,select").val("")
                          $(".chosen_select").trigger("chosen:updated") ;
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
    }) ;

})