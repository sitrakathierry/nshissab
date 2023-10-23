$(document).ready(function(){
  $("#crd_paiement_date").datepicker()
  $("#agd_acp_date").datepicker()
  var facture_editor = new LineEditor(".facture_editor") ;
  
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

  $(".crd_btn_annule_acompte").click(function () {
    var self = $(this);
    $.confirm({
      title: "Confirmation",
      content: "Vous êtes sûre ?",
      type: "dark",
      theme: "modern",
      buttons: {
        btn1: {
          text: "Non",
          action: function () {},
        },
        btn2: {
          text: "Oui",
          btnClass: "btn-dark",
          keys: ["enter", "shift"],
          action: function () {
            var realinstance = instance.loading();
            var formData = new FormData();
            formData.append('id', self.attr("value"));
            $.ajax({
              url: routes.crd_acompte_annule,
              type: "post",
              cache: false,
              data: formData,
              dataType: "json",
              processData: false,
              contentType: false,
              success: function (json) {
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
  })

  // TOUT SUR LA RECHERCHE DES ELEMENTS
  var appBase = new AppBase();
  var elemSearch = [
    {
      name: "refPaiement",
      action: "change",
      selector: "#refPaiement",
    },
    {
      name: "statut",
      action: "change",
      selector: "#crd_statut",
    },
    {
      name: "idC",
      action: "change",
      selector: "#fact_search_client",
    },
    {
      name: "currentDate",
      action: "change",
      selector: "#date_actuel",
    },
    {
      name: "dateFacture",
      action: "change",
      selector: "#date_specifique",
    },
    {
      name: "dateDebut",
      action: "change",
      selector: "#date_fourchette_debut",
    },
    {
      name: "dateFin",
      action: "change",
      selector: "#date_fourchette_fin",
    },
    {
      name: "annee",
      action: "keyup",
      selector: "#date_annee",
    },
    {
      name: "mois",
      action: "change",
      selector: "#date_mois",
    },
  ];

  $("#fact_search_date").change(function () {
    var option = $(this).find("option:selected");
    var critere = option.data("critere");
    if (critere == "") {
      $(".elem_date").html("");
      if (option.text() == "TOUS") {
        searchFinance();
      } else {
        var currentDate = new Date();
        var day = currentDate.getDate();
        var month = currentDate.getMonth() + 1; // Les mois sont indexés à partir de zéro, donc nous ajoutons 1
        var year = currentDate.getFullYear();
        if (month < 10) {
          month = "0" + month;
        }
        var formattedDate = day + "/" + month + "/" + year;

        $(".elem_date").html(
          `
                    <input type="hidden" id="date_actuel" name="date_actuel" value="` +
            formattedDate +
            `">
                `
        );
        $("#date_actuel").change();
      }
      return false;
    }

    if (critere.length == 2) {
      $(".elem_date").html(appBase.getItemsDate(critere));
    } else {
      var index = critere.split(",");
      var elements = "";

      index.forEach((elem) => {
        elements += appBase.getItemsDate(elem);
      });

      $(".elem_date").html(elements);
    }
    searchFinance();
  });

  function searchFinance() {
    var instance = new Loading(files.search);
    $(".elem_finance").html(instance.search(11));
    var formData = new FormData();
    for (let j = 0; j < elemSearch.length; j++) {
      const search = elemSearch[j];
      formData.append(search.name, $(search.selector).val());
    }

    $.ajax({
      url: routes.crd_acompte_credit_search_items,
      type: "post",
      cache: false,
      data: formData,
      dataType: "html",
      processData: false, // important pour éviter la transformation automatique des données en chaîne
      contentType: false, // important pour envoyer des données binaires (comme les fichiers)
      success: function (response) {
        $(".elem_finance").html(response);
      },
    });
  }

  elemSearch.forEach((elem) => {
    $(document).on(elem.action, elem.selector, function () {
      searchFinance();
    });
  });

  $(".crd_btn_statut").click(function () {
    var btnClass = $(this).data("class");
    var target = $(this).data("target");
    var currentbtnClass = "btn-outline-" + btnClass.split("-")[1];
    var inputValue = $(this).attr("value");
    var self = $(this);

    $(target).val(inputValue);

    $(this).addClass(btnClass);
    $(this).removeClass(currentbtnClass);

    $(".crd_btn_statut").each(function () {
      if (!self.is($(this))) {
        $(this).addClass(currentbtnClass);
        $(this).removeClass(btnClass);
      }
    });

    $(target).change();
  });

  $(".agd_btn_valid_check").click(function(){
    var self = $(this)
    $.confirm({
        title: "Confirmation",
        content:"Vous êtes sûre ?",
        type:"blue",
        theme:"modern",
        buttons:{
            btn1:{
                text: 'Non',
                action: function(){}
            },
            btn2:{
                text: 'Oui',
                btnClass: 'btn-blue',
                keys: ['enter', 'shift'],
                action: function(){
                    var realinstance = instance.loading()
                    $.ajax({
                        url: routes.agd_echeance_check,
                        type:'post',
                        cache: false,
                        data:{id:self.attr('value')},
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
  })

  $("#formAgdAcompte").submit(function(){
    var self = $(this)
    $.confirm({
        title: "Confirmation",
        content:"Vous êtes sûre ?",
        type:"blue",
        theme:"modern",
        buttons:{
            btn1:{
                text: 'Non',
                action: function(){}
            },
            btn2:{
                text: 'Oui',
                btnClass: 'btn-blue',
                keys: ['enter', 'shift'],
                action: function(){
                    var data = self.serialize();
                    var realinstance = instance.loading()
                    $.ajax({
                        url: routes.agd_acompte_agenda_save,
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
    return false;
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
                            var url = routes.credit_echeance_imprimer + '/' + idFinance + '/' + idModeleEntete + '/' + idModeleBas;
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

  $(document).on("click",".agd_btn_delete_ech",function(){
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
                        url: routes.agenda_delete_echeance,
                        type:'post',
                        cache: false,
                        data:{idEcheance:self.data("value")},
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
})