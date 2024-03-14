$(document).ready(function(){
    var cheque_editor = new LineEditor("#cheque_editor") ;
    var instance = new Loading(files.loading)

    $("#chk_date_cheque").datepicker()
    $("#chk_date_declaration").datepicker()
    cheque_editor.setEditorText($("#cheque_editor").text())

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

    $(document).on('click',".chk_btn_valider", function(){
      var self = $(this);
      $.confirm({
        title: "Confirmation",
        content: "Etes-vous sûre ?",
        type: "blue",
        theme: "material",
        buttons: {
          btn1: {
            text: "Annuler",
            action: function () {},
          },
          btn3:{
            text: 'Rejeter',
            btnClass: 'btn-red',
            keys: ['escape'],
            action: function(){
              var realinstance = instance.loading();
              $.ajax({
                url: routes.compta_cheque_validation,
                type: "post",
                cache: false,
                data: {
                      id:self.data("value"),
                      reference:"REJET"
                  },
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
            }
          },
          btn2: {
            text: "Valider",
            btnClass: "btn-blue",
            keys: ["enter", "shift"],
            action: function () {
              // Enregistrement sur opération de compte 
              var realinstance = instance.loading()
              $.ajax({
                  url: routes.compta_banque_compte_bancaire_get,
                  type:'post',
                  cache: false,
                  data:{id:self.data("banque")},
                  dataType: 'html',
                  success: function(response){
                      realinstance.close()
                      $.confirm({
                        title: "Enregistrement",
                        content:`
                        <div class="w-100">
                            <label for="cmp_operation_compte" class="font-weight-bold">Choisir le Compte Bancaire</label>
                            <select name="cmp_operation_compte" class="custom-select chosen_select custom-select-sm" id="cmp_operation_compte">
                                `+response+`
                            </select>
                        </div>
                        `,
                        type:"blue",
                        theme:"material",
                        buttons:{
                            btn1:{
                                text: 'Annuler',
                                action: function(){}
                            },
                            btn2:{
                                text: 'Valider',
                                btnClass: 'btn-blue',
                                keys: ['enter'],
                                action: function(){
                                  var realinstance = instance.loading();
                                  $.ajax({
                                    url: routes.compta_cheque_validation,
                                    type: "post",
                                    cache: false,
                                    data: {
                                          id:self.data("value"),
                                          reference:"VALIDE",
                                          compte:$("#cmp_operation_compte").val(),
                                      },
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
                                }
                            }
                        }
                    })
                    return false ;
                  },
                  error: function(resp){
                      realinstance.close()
                      $.alert(JSON.stringify(resp)) ;
                  }
              })
              // fin
            },
          },
        },
      });
      return false;
    });

    $("#search_date").datepicker()
    $("#search_date_debut").datepicker()
    $("#search_date_fin").datepicker()
    

    var elemSearch = [
      {
        name:"idType",
        action:"change",
        selector:"#search_type_cheque",
      },
      {
          name: "currentDate",
          action:"change",
          selector : "#search_current_date"
      },
      {
          name: "dateDeclaration",
          action:"change",
          selector : "#search_date"
      },
      {
          name: "dateDebut",
          action:"change",
          selector : "#search_date_debut"
      },
      {
          name: "dateFin",
          action:"change",
          selector : "#search_date_fin"
      },
      {
          name: "annee",
          action:"keyup",
          selector : ".search_annee"
      },
      {
          name: "annee",
          action:"change",
          selector : ".search_annee"
      },
      {
          name: "mois",
          action:"change",
          selector : "#search_mois"
      }
  ] 

  function searchDepense()
  {
      var instance = new Loading(files.search) ;
      $(".elemCheque").html(instance.search(9)) ;
      var formData = new FormData() ;
      for (let j = 0; j < elemSearch.length; j++) {
          const search = elemSearch[j];
          formData.append(search.name,$(search.selector).val());
      }
      formData.append("affichage",$("#search_cheque").val())
      $.ajax({
          url: routes.compta_cheque_search ,
          type: 'post',
          cache: false,
          data:formData,
          dataType: 'html',
          processData: false, // important pour éviter la transformation automatique des données en chaîne
          contentType: false, // important pour envoyer des données binaires (comme les fichiers)
          success: function(response){
              $(".elemCheque").empty().html(response) ;
          }
      })
  }

  elemSearch.forEach(elem => {
      $(document).on(elem.action,elem.selector,function(){
          searchDepense()
      })
  })

  $("#search_cheque").change(function(){

      $("#caption_search_date").hide()
      $("#caption_search_date_debut").hide()
      $("#caption_search_date_fin").hide()
      $("#caption_search_mois").hide()
      $("#caption_search_annee").hide()

      if($(this).val() == "JOUR")
      {
          var currentDate = new Date();
          var day = currentDate.getDate();
          var month = currentDate.getMonth() + 1; // Les mois sont indexés à partir de zéro, donc nous ajoutons 1
          var year = currentDate.getFullYear();
          if (month < 10) {
              month = '0' + month;
              }
          var formattedDate = day + '/' + month + '/' + year;

          $("#search_current_date").val(formattedDate)

          searchDepense()
      }
      else if($(this).val() == "SPEC")
      {
          $("#caption_search_date").show()
      }
      else if($(this).val() == "LIMIT")
      {
          $("#caption_search_date_debut").show()
          $("#caption_search_date_fin").show()
      }
      else if($(this).val() == "MOIS")
      {
          var currentDate = new Date();
          var month = currentDate.getMonth() + 1; 
          $("#search_mois").val(month)

          $("#caption_search_annee").show()
          $("#caption_search_mois").show()

          $(".chosen_select").trigger("chosen:updated")
      }
      
      searchDepense()

      var currentDate = new Date();
      var year = currentDate.getFullYear();
      var month = currentDate.getMonth() + 1; 

      $("#search_date").val("")
      $("#search_date_debut").val("")
      $("#search_date_fin").val("")
      $("#search_mois").val(month)
      $(".search_annee").val(year)

      $(".chosen_select").trigger("chosen:updated")
  })
})