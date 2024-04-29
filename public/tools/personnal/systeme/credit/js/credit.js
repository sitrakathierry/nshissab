$(document).ready(function(){
  $("#crd_paiement_date").datepicker()
  $("#agd_acp_date").datepicker()
  var facture_editor = new LineEditor(".facture_editor") ;
  
  var instance = new Loading(files.loading)
  var appBase = new AppBase() ;

  $("#formPaiementCredit").submit(function(){
    var crd_paiement_montant = parseFloat($("#crd_paiement_montant").val())
    var credit_total_restant = parseFloat($("#credit_total_restant").val())
    if(crd_paiement_montant > credit_total_restant)
    {
      $.alert({
        title: "Message",
        content: "Le montant entré ne doit pas dépasser le montant restant",
        type:"red"
      }) ;
 
      return false ;
    }
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
                          var dataCheck = [] ;
                          $(".btn_check_credit").each(function(){
                            if($(this).hasClass("btn-success"))
                              dataCheck.push($(this).data("value")) ;
                          }) ;
                          var realinstance = instance.loading()
                          $.ajax({
                              url: routes.credit_data_check_save,
                              type:'post',
                              cache: false,
                              data:{dataCheck:dataCheck},
                              dataType: 'json',
                              success: function(json){
                                realinstance.close()
                                var idFinance = self.data("value") ;
                                var url = routes.credit_echeance_imprimer + '/' + idFinance + '/' + idModeleEntete + '/' + idModeleBas;
                                window.open(url, '_blank');
                              },
                              error: function(resp){
                                  realinstance.close()
                                  $.alert(JSON.stringify(resp)) ;
                              }
                          }) ;
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

  $(document).on('click',".agd_btn_prints_ech",function(){
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
                content:response+`
                <div class="w-100 text-left">
                    <label for="nom" class="mt-2 font-weight-bold">Description échéance</label>
                    <textarea name="echeance_descri" id="echeance_descri" class="w-100 px-2" cols="10" rows="4" placeholder="Description . . ."></textarea>
                </div>
                `,
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
                            var idEcheance = self.data("value") ;
                            $.ajax({
                              url: routes.credit_echeance_update_description,
                              type:'post',
                              cache: false,
                              data: {
                                echeance_descri: $("#echeance_descri").val(),
                                id_echeance: self.data("value")
                              },
                              dataType: 'json',
                              success: function(response){
                                var url = routes.credit_echeance_unitaire_imprimer + '/' + idEcheance + '/' + idModeleEntete + '/' + idModeleBas;
                                window.open(url, '_blank');
                              },
                              error: function(resp){
                                $.alert(JSON.stringify(resp)) ;
                              }
                            })
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

  $(".btn_lock_activity").click(function(){
    var self = $(this)
    var id_user_admin = $(this).data("iduser") ;
    var user_admin = $(this).data("nameuser") ;
    $.confirm({
        title: "Authentification",
        content:`
        <div class="w-100 text-left">
            <label for="user_admin" class="font-weight-bold">Administrateur</label>
            <input type="text" readonly name="user_admin" id="user_admin" class="form-control font-weight-bold" value="`+user_admin+`">
            <input type="hidden" name="id_user_admin" id="id_user_admin" value="`+id_user_admin+`">

            <label for="pass_admin" class="mt-2 font-weight-bold">Mot de passe Administrateur</label>
            <input type="password" name="pass_admin" id="pass_admin" class="form-control" placeholder=". . .">
            <div class="w-100 d-flex mt-2 flex-row align-items-center">
                <input type="checkbox" class="form-input-check" id="loginTogglePass">
                <span class="ml-2" id="labelToggle">Afficher le mot de passe</span>
            </div>
        </div>
        `,
        type:"dark",
        theme:"modern",
        buttons:{
            btn1:{
                text: 'Annuler',
                action: function(){}
            },
            btn2:{
                text: 'Valider',
                btnClass: 'btn-red',
                keys: ['enter'],
                action: function(){
                    var realinstance = instance.loading()
                    $.ajax({
                        url: routes.credit_activity_authentification,
                        type:'post',
                        cache: false,
                        data:{
                          id_user_admin:$("#id_user_admin").val(),
                          pass_admin:$("#pass_admin").val()
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

  $(document).on("click","#loginTogglePass",function(){
    if($(this).is(':checked'))
    {
        $("#pass_admin").attr("type","text")
        $("#labelToggle").text("Masquer le mot de passe")
    }
    else
    {
        $("#pass_admin").attr("type","password")
        $("#labelToggle").text("Afficher le mot de passe")
    }
  })

  $(".btn_edit_credit").click(function(){
    var self = $(this) ;
    var parent = $(this).closest("tr") ;
    var crd_val_description = parent.find(".crd_val_description").text() ;
    var crd_val_date = parent.find(".crd_val_date").text() ;
    var crd_val_montant = parent.find(".crd_val_montant").text() ;
    $.confirm({
        title: "Modification",
        content:`
        <div class="w-100 text-left">
            <label for="crd_mod_date" class="mt-2 font-weight-bold">Date</label>
            <input type="text" name="crd_mod_date" id="crd_mod_date" class="form-control" value="`+crd_val_date+`" placeholder=". . .">
            
            <label for="crd_mod_description" class="mt-2 font-weight-bold">Description</label>
            <textarea name="crd_mod_description" oninput="this.value = this.value.toUpperCase();" id="crd_mod_description" cols="30" rows="3" class="w-100 px-2" placeholder=". . ." >`+crd_val_description+`</textarea>

            <label for="crd_mod_montant" class="mt-2 font-weight-bold">Montant</label>
            <input type="number" step="any" name="crd_mod_montant" id="crd_mod_montant" class="form-control" value="`+crd_val_montant+`" placeholder=". . .">
        </div>
        <script>
          $("#crd_mod_date").datepicker() ;
        </script>
        `,
        type:"orange",
        theme:"modern",
        buttons:{
            btn1:{
                text: 'Annuler',
                action: function(){}
            },
            btn2:{
                text: 'Valider',
                btnClass: 'btn-orange',
                keys: ['enter'],
                action: function(){
                    var realinstance = instance.loading()
                    $.ajax({
                        url: routes.credit_element_update,
                        type:'post',
                        cache: false,
                        data:{
                          crd_mod_description:$("#crd_mod_description").val(),
                          crd_mod_date:$("#crd_mod_date").val(),
                          crd_mod_montant:$("#crd_mod_montant").val(),
                          idCreditDetail:self.data("value")
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

  $(".btn_delete_credit").click(function(){
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
                        url: routes.credit_element_detail_delete,
                        type:'post',
                        cache: false,
                        data:{
                          idCrdDetail:self.data("value")
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

  $(".btn_check_credit").click(function(){
    if($(this).hasClass("btn-outline-success"))
    {
      $(this).removeClass("btn-outline-success") ;
      $(this).addClass("btn-success") ;
      $(this).html('<span class="font-weight-bold">OK</span>') ;
    }
    else
    {
      $(this).removeClass("btn-success") ;
      $(this).addClass("btn-outline-success") ;
      $(this).html('<i class="fa fa-check"></i>') ;
    }
  }) ;

  function getSearch()
  {
    var realinstance = instance.loading()
    $.ajax({
        url: routes.credit_element_update,
        type:'post',
        cache: false,
        data: {},
        dataType: 'html',
        processData: false,
        contentType: false,
        success: function(response){
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

  var elemSearchSuivi = [
    {
        name: "idClient",
        action:"change",
        selector : "#fact_search_client"
    },
    {
        name: "idEntrepot", 
        action:"change",
        selector : "#fact_search_entrepot"
    },
    {
      name: "idFinance",
      action:"change",
      selector : "#fact_search_credit"
    },
    {
      name: "currentDate",
      action: "change",
      selector: "#date_actuel",
    },
    {
      name: "dateSuivi",
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
  ]

  $("#credit_search_date_suivi").change(function(){
    var option = $(this).find("option:selected") ;
    var critere = option.data("critere") ;
    if(critere == "")
    {
        $(".elem_date").html("")
        if(option.text() == "TOUS")
        {
          searchSuiviCredit()
        }
        else
        {
            var currentDate = new Date();
            var day = currentDate.getDate();
            var month = currentDate.getMonth() + 1; // Les mois sont indexés à partir de zéro, donc nous ajoutons 1
            var year = currentDate.getFullYear();
            if (month < 10) {
                month = '0' + month;
              }
            var formattedDate = day + '/' + month + '/' + year;

            $(".elem_date").html(`
                <input type="hidden" id="date_actuel" name="date_actuel" value="`+formattedDate+`">
            `)
            $("#date_actuel").change();
        }
        return false;
    }

    if(critere.length == 2)
    {
        $(".elem_date").html(appBase.getItemsDate(critere))
    }
    else
    {
        var index = critere.split(",")
        var elements = ''

        index.forEach(elem => {
            elements += appBase.getItemsDate(elem)
        })

        $(".elem_date").html(elements)
        
    }
    searchSuiviCredit()
  })

  function searchSuiviCredit()
    {
        var instance = new Loading(files.search) ;
        $(".contentSuiviCredit").html(instance.search(9)) ;
        var formData = new FormData() ;
        for (let j = 0; j < elemSearchSuivi.length; j++) {
            const search = elemSearchSuivi[j];
            formData.append(search.name,$(search.selector).val());
        }

        $.ajax({
            url: routes.credit_search_suivi_items , 
            type: 'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(response){
                $(".contentSuiviCredit").html(response) ;
            }
        })
    }

    elemSearchSuivi.forEach(elem => {
        $(document).on(elem.action,elem.selector,function(){
            searchSuiviCredit()
        })
    })

})