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

    for (let j = 1; j <= $(".elemDepense tr").length ; j++) {
      $("#ttpStatut_"+j).easyTooltip({
        content: '<div class="text-white text-center">'+$("#ttpStatut_"+j).data("content")+'</div>',
        defaultRadius: "3px",
        tooltipZindex: 1000,
        tooltipPadding: "10px 15px",
        tooltipBgColor: "rgba(0,0,0,0.85)",
      })
    }

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

    }

    $(".dep_details_ajouter").click(function(){
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
        var itemLigne = `
          <tr>
            <td>
            `+$("#dep_details_designation option:selected").text()+`
            <input type="hidden" name="dep_item_id_libelle[]" value="`+dep_details_designation+`">
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
        var realinstance = instance.loading()
        $.ajax({
            url: routes.compta_libelle_depense_save,
            type:'post',
            cache: false,
            data:{
              libelle:dep_details_designation
            },
            dataType: 'json',
            success: function(json){
                realinstance.close()
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
                $(".elemDetailsDepense").append(itemLigne) ;

                calculMontantDetails()
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
      }
    })

    $(document).on('click',".dep_supprime_ligne",function(){
      $(this).closest("tr").remove()
      calculMontantDetails()
    })

})