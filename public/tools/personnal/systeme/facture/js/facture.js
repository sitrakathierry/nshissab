$(document).ready(function(){  
    var facture_editor = new LineEditor(".facture_editor") ;
    var instance = new Loading(files.loading)
    $("#cmd_date").datepicker()

    var appBase = new AppBase() ;
    $("#fact_date").datepicker() ;
    $("#agd_ech_date").datepicker() ;
    $("#agd_acp_date").datepicker() ;
    facture_editor.setEditorText($(".facture_editor").val())
    $("#formFacture").submit(function(event){
        event.preventDefault()
        var self = $(this)
        $(".facture_editor").val(facture_editor.getEditorText('.facture_editor'))
        $.confirm({
            title: "Confirmation",
            content:"Etes-vous sûre ?",
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
                        url: routes.fact_save_activites,
                        type:"post",
                        data:data,
                        dataType:"json",
                        success : function(json){
                            realinstance.close()
                            $.alert({
                                title: 'Message',
                                content: json.message,
                                type: json.type,
                                buttons: {
                                    OK: function(){
                                        if(json.type == "green")
                                        {
                                            $(".chosen_select").val("")
                                            $("#fact_remise_prod_general").val("")
                                            $("#fact_prod_tva").val("")
                                            $(".chosen_select").trigger("chosen:updated");
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
    
    $(".fact_btn_miseajour").click(function(){
        $("#formModifFacture").submit() ;
    })

    $("#formModifFacture").submit(function(){
        var self = $(this)
        $(".facture_editor").val(facture_editor.getEditorText('.facture_editor'))
        $.confirm({
            title: "Confirmation",
            content:"Etes-vous sûre ?",
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
                        url: routes.fact_rajoute_element_activites,
                        type:"post",
                        data:data,
                        dataType:"json",
                        success : function(json){
                            realinstance.close()
                            $.alert({
                                title: 'Message',
                                content: json.message,
                                type: json.type,
                                buttons: {
                                    OK: function(){
                                        if(json.type == "green")
                                        {
                                            $(".chosen_select").val("")
                                            $(".chosen_select").trigger("chosen:updated");
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
    
    $(document).on("click",".fact_dtls_btn_suppr",function(){
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
                            url: routes.fact_activites_supprime,
                            type:'post',
                            cache: false,
                            data:{idFacture:self.data("value")},
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

    $("#fact_client").change(function(){
            var selectedText = $(this).find("option:selected").text();
            $(".fact_table_client").text(selectedText)
    })

    $(".fact_btn_type").click(function(){
        var btnClass = $(this).data("class")
        var target = $(this).data("target")
        var currentbtnClass = "btn-outline-"+btnClass.split("-")[1]

        var inputValue = $(this).attr("value")
        var self = $(this)
        var btnText = $(this).data("text")
        $(target).val(inputValue) ;

        var paiementArray = ["ES","VR","CH","CB","MN","CR","AC"]

        var modePaiement = $(this).data('mode').split(",")
        
        paiementArray.forEach(elem => {
            $(".fact_btn_paiement").filter("." + elem).prop("disabled", true);
            $(".fact_btn_paiement").filter("." + elem).addClass("btn-outline-info") ; 
            $(".fact_btn_paiement").filter("." + elem).removeClass("btn-info");
        })
        $("#fact_paiement").val("")
        $(".fact_table_paiement").text("")
        modePaiement.forEach(elem => {
            $(".fact_btn_paiement" + "." + elem).removeAttr("disabled");
        })

        var reference = $(this).data("reference")

        if(reference != "DF")
        {
            $("#fact_libelle").hide();
            $(".fact_libelle_caption").text("")

            $("#fact_num").hide();
            $(".fact_num_caption").text("")
        }

        $(this).addClass(btnClass)
        $(this).removeClass(currentbtnClass)
        $(".fact_table_type").text(btnText)
        $(".fact_btn_type").each(function(){
            if (!self.is($(this))) {
                $(this).addClass(currentbtnClass) ; 
                $(this).removeClass(btnClass);
            }
        })

        $(".agd_acompte").hide()

        var modeleFacture = $(".fact_btn_modele.btn-warning").data("indice")

        if(reference == "DF" && modeleFacture == "PROD")
        {
            var realinstance = instance.loading()
            $.ajax({
                url: routes.fact_get_ticket_de_caisse,
                type:'post',
                cache: false,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    $("#contentTicketDeCaisse").html(response)
                },
                error: function(resp){
                    realinstance.close()
                    $.alert(JSON.stringify(resp)) ;
                }
            })
        }
        else
        {
            $("#contentTicketDeCaisse").empty()
        }
    })

    $(document).on('change',"#fact_ticket_caisse",function(){
        var self = $(this)
        var data = new FormData() ;
        data.append("idCs",self.val())
        var realinstance = instance.loading()
        $.ajax({
            url: routes.fact_ticket_caisse_display,
            type:'post',
            cache: false,
            data: data,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(resp){
                realinstance.close()
                $("#detailFacture").html(resp) ;
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    $(".fact_btn_paiement").click(function(){
        var btnClass = $(this).data("class")
        var target = $(this).data("target")
        var currentbtnClass = "btn-outline-"+btnClass.split("-")[1]
        var inputValue = $(this).attr("value")
        var btnText = $(this).data("text")
        var self = $(this)
        var numCaption = $(this).data('numcaption')
        var libelleCaption = $(this).data('libelle')
        var reference = $(this).data('reference')

        $(target).val(inputValue) ;

        $(".fact_table_paiement").text(btnText)

        if(reference != "ES")
        {
            $(".fact_libelle_caption").text(libelleCaption)
            $("#fact_libelle").parent().show()
            $(".fact_num_caption").text(numCaption)
            $("#fact_num").parent().show();

            if(reference == "AC" || reference == "CR")
            {
                $(".fact_num_caption").text("")
                $("#fact_num").parent().hide()
                $(".teleportMontant").html($("#fact_libelle").parent().html())
                $(".contentMontant").empty()
            }
            else
            {
                if($(".teleportMontant").html() != "")
                {
                    $(".contentMontant").html($(".teleportMontant").html())
                    $(".teleportMontant").empty()
                }
            }

        }
        else
        {
            $(".fact_libelle_caption").text("")
            $("#fact_libelle").parent().hide()
            $(".fact_num_caption").text("")
            $("#fact_num").parent().hide()
        }   

        // AGENDA
        if ($(this).hasClass('CR'))
        {
            $("#agd_echance").show()
        }
        else
        {
            $("#agd_echance").hide()
        }

        // Agenda acompte
        if ($(this).hasClass('AC')) {
            $(".agd_acompte").show()
        }
        else
        {
            $(".agd_acompte").hide()
        }

        // if($(this).hasClass('AC') || $(this).hasClass('CR'))
        // {
        //     var contentMontant = $(".contentMontant").html()
        //     if(contentMontant != "")
        //     {
        //         $(".teleportMontant").html(contentMontant)
        //         $(".contentMontant").empty()
        //     }
        // }else{
        //     var teleportMontant = $(".teleportMontant").html()
        //     if(teleportMontant != "")
        //     {
        //         $(".contentMontant").html(teleportMontant)
        //         $(".teleportMontant").empty()
        //     }
        // }

        $(this).addClass(btnClass)
        $(this).removeClass(currentbtnClass)
        $(".fact_btn_paiement").each(function(){
            if (!self.is($(this))) {
                $(this).addClass(currentbtnClass) ; 
                $(this).removeClass(btnClass);
            }
        })
    })

    function displayTemplateFacture(factRoute = [], indice)
    {
        var realinstance = instance.loading()
        $.ajax({
            url: factRoute[indice],
            type:'post',
            cache: false,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                realinstance.close()
                if(indice == "PLOC")
                {
                    $("#factureStandard").empty().append(response)
                }
                else
                {
                    $("#detailFacture").empty().html(response)
                    sessionStorage.setItem('optionP',$(".fact_mod_prod_designation").html()) ;
                }
                
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    }

    $(document).on("click",".btn_btp_forfait",function(){
        if($(this).hasClass("btn-outline-info"))
        {
            $(this).removeClass("btn-outline-info")
            $(this).addClass("btn-info")
            $(this).html('<span class="font-weight-bold" ><i class="fa fa-check" ></i>&nbsp;FORFAIT</span>')
            $("#fact_is_forfait").val("OUI")
            $("#fact_btp_qte").hide()
            $("#fact_btp_qte").val("1")
            $("#fact_btp_label_qte").attr("type","text") 
            $(".content_fact_prix").html('<input type="number" name="fact_btp_prix" id="fact_btp_prix" class="form-control" placeholder=". . .">') ;
        }
        else
        {
            $(this).removeClass("btn-info")
            $(this).addClass("btn-outline-info")
            $(this).html('<span class="font-weight-bold" ><i class="fa fa-cube" ></i>&nbsp;FORFAIT</span>')
            $("#fact_is_forfait").val("NON")
            $("#fact_btp_qte").show()
            $("#fact_btp_qte").val("")
            $("#fact_btp_label_qte").attr("type","hidden") 
            $(".content_fact_prix").html(`
                <select class="custom-select custom-select-sm chosen_select" name="fact_btp_prix" id="fact_btp_prix" >
                    <option value=""></option> 
                </select>
                `) ;
            $("#fact_btp_designation").change() ;
        }
    })

    $(".fact_btn_modele").click(function(){
        var btnClass = $(this).data("class")
        var target = $(this).data("target")
        var currentbtnClass = "btn-outline-"+btnClass.split("-")[1]
        var inputValue = $(this).attr("value")
        var self = $(this)
        var indice = $(this).data("indice") ;
        var factRoute = {
            "PROD" : routes.ftr_creation_produit, 
            "PBAT" : routes.ftr_creation_prest_batiment, 
            "PSTD" : routes.ftr_creation_prest_service,
            "PLOC" : routes.fact_creation_prest_location,
        } ;

        $(target).val(inputValue) ;

        $(this).addClass(btnClass)
        $(this).removeClass(currentbtnClass)

        $(".fact_btn_modele").each(function(){
            if (!self.is($(this))) {
                $(this).addClass(currentbtnClass) ; 
                $(this).removeClass(btnClass);
            }
        })
        
        var data = new FormData() ;
        data.append('id',inputValue)

        var realinstance = instance.loading()
        $.ajax({
            url: routes.ftr_modele_get,
            type:'post',
            cache: false,
            data: data,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                realinstance.close()
                if(response == "")
                {
                    displayTemplateFacture(factRoute,indice) ;
                    return false ;
                } 
                $("#detailFacture").empty()
                $.confirm({
                    title: (self.text()).toUpperCase(),
                    content:`
                        <label for="fact_src_opt_modele" class="font-weight-bold">Choisissez une option</label>
                        <select name="fact_src_opt_modele" class="custom-select custom-select-sm" id="fact_src_opt_modele">
                            <option value="" reference="" >-</option>
                            `+response+`
                        </select>
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
                            keys: ['enter', 'shift'],
                            action: function(){
                                var optModele = $("#fact_src_opt_modele").find("option:selected")
                                $(target).val(optModele.attr("value")) ;
                                displayTemplateFacture(factRoute,optModele.attr("reference")) ;
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
    })

    function getContentClient(type)
    {
      $("#contentClient").empty().html(instance.otherSearch())
      var formData = new FormData();
      formData.append("type",type)
      $.ajax({
          url: routes.ftr_client_information_get,
          type:'post',
          cache: false, 
          data:formData,
          dataType: 'html',
          processData: false,
          contentType: false,
          success: function(response){
              $("#contentClient").empty().html(response)
          },
          error: function(resp){
            $("#contentClient").empty().html(resp)
              $.alert(JSON.stringify(resp)) ;
          }
      })
    }

    $(document).on("click",".fact_existing_client",function(){
      getContentClient("EXISTING")
    })

    $(document).on("click",".fact_new_client",function(){
      getContentClient("NEW")
    })

    function ameliorerQualiteImageBase64(base64, callback) {
        var img = new Image();
        img.src = base64;
        img.onload = function() {
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            canvas.width = img.width;
            canvas.height = img.height
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            callback(canvas.toDataURL('image/jpeg'),1);
        };
    }

    $(document).on('click',".btn_imprimer_facture",function(){
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
                                var realinstance = instance.loading()
                                $.ajax({
                                    url: routes.fact_element_description_update,
                                    type:'post',
                                    cache: false,
                                    data:{
                                        idFacture:self.data("value"),
                                        facture_editor:facture_editor.getEditorText(),
                                        cmd_lieu:$("#cmd_lieu").val(),
                                        cmd_date:$("#cmd_date").val()
                                    },
                                    dataType: 'json',
                                    success: function(response){
                                        realinstance.close()
                                        var idFacture = self.data("value") ;
                                        var url = routes.fact_facture_detail_imprimer + '/' + idFacture + '/' + idModeleEntete + '/' + idModeleBas;
                                        window.open(url, '_blank');
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
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
        return false ;
    })

    $(".fact_btn_facture_supprime").click(function(){
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
                            url: routes.stock_delete_facture_activity,
                            type:'post', 
                            cache: false,
                            data:{idFacture:self.data("value")},
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
                                                location.href = routes.ftr_consultation
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

    $(".fact_btn_basculer_definitif").click(function(){
        // console.log(routes.ftr_details_activite)
        var self = $(this)
        $.confirm({
            title: "Confirmation",
            content:"Êtes-vous sûre ?",
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
                            url: routes.fact_type_paiement_get,
                            type:'post',
                            cache: false,
                            dataType: 'html',
                            processData: false,
                            contentType: false,
                            success: function(response){
                                realinstance.close()
                                $.confirm({
                                    title: "Spécifier Type Paiement",
                                    content:response,
                                    type:"orange",
                                    theme:"modern",
                                    buttons:{
                                        btn1:{
                                            text: 'Annuler',
                                            action: function(){
                                                location.reload()
                                            }
                                        },
                                        btn2:{
                                            text: 'Valider',
                                            btnClass: 'btn-orange',
                                            keys: ['enter', 'shift'],
                                            action: function(){
                                                var realinstance = instance.loading()
                                                $.ajax({
                                                    url: routes.fact_basculer_vers_definitive,
                                                    type:'post',
                                                    cache: false,
                                                    data:{
                                                        idFacture:self.data("value"),
                                                        fact_type_paiement:$("#fact_type_paiement").val()
                                                    },
                                                    dataType: 'json',
                                                    success: function(json){
                                                        realinstance.close()
                                                        $.confirm({
                                                            title: 'Message',
                                                            content: json.message,
                                                            type: json.type,
                                                            buttons: {
                                                                OK: function(){
                                                                    if(json.type == "green")
                                                                    {
                                                                        location.reload() 
                                                                    }
                                                                },
                                                                btn2:{
                                                                    text: 'Consulter',
                                                                    btnClass: 'btn-green',
                                                                    keys: ['enter', 'shift'],
                                                                    action: function(){
                                                                        if(json.type == "green")
                                                                        {
                                                                            var url = routes.ftr_details_activite + '/' + json.idNewFacture;
                                                                            window.open(url, '_blank');
                                                                            location.reload()
                                                                        } 
                                                                        else
                                                                        {
                                                                            $.alert({
                                                                                title: 'Message',
                                                                                content: "Veuller corriger l'erreur",
                                                                                type: "orange",
                                                                            });
                                                                        }
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
    

    // DEBUT CALCUL AVOIR FACTURE

    $(document).on("change","#fact_client",function(){
        // console.log($(".fact_btn_type.btn-primary").data("reference")) ;
        if($(".fact_btn_type.btn-primary").data("reference") != "DF")
            return false ;
        var self = $(this)
        var realinstance = instance.loading()
        var formData = new FormData() ;
        formData.append("idClient",self.val()) ;
        $.ajax({
          url: routes.facture_form_avoir_get,
          type:"post",
          data:formData,
          dataType:"html",
          processData:false,
          contentType:false,
          success : function(response){
              realinstance.close()
              $(".content_avoir").html(response) ;
          },
          error: function(resp){
              realinstance.close()
              $.alert(JSON.stringify(resp)) ;
          }
        })
      })
  
      $(document).on("click",".btn_delete_avoir",function(){
        $(".content_avoir").html(`
          <div style="width: 220px ;">
          <button type="button" class="btn btn-primary btn-sm btn_redisplay_avoir py-2 text-uppercase btn-block"><i class="fa fa-plus"></i>&nbsp;Ajouter Avoir client</button>
          <label for="" class="font-weight-bold">&nbsp;</label>
          </div>
        `)
      })
  
      $(document).on("click",".btn_redisplay_avoir",function(){
        if($("#fact_client").val() == "")
        {
          $.alert({
            title:"Message",
            content:"Veuillez seléctionner un client",
            type:'orange'
          }) ;
  
          return false ;
        }
  
        $("#fact_client").change() ;
      })
  
      $(document).on("click",".fact_auto_bouton",function(){
        var totalTtc = parseFloat($(".fact_enr_total_general").val()) ;
        var totalAvoir = parseFloat($("#fact_total_avoir").val()) ;
        var totalUtilisee = 0 ;
        var totalRestant = 0 ;
  
        if(totalTtc > totalAvoir)
        {
          totalUtilisee = totalAvoir ;
          totalRestant = totalTtc - totalAvoir ;
        }
        else
        {
          totalUtilisee = totalTtc ;
          totalRestant = 0 ;
        }
  
        $("#fact_avoir_use").val(totalUtilisee)
        $("#avoir_total_restant").val(totalRestant)
      })
  
      $(document).on("keyup","#fact_avoir_use",function(){
        var totalTtc = parseFloat($(".fact_enr_total_general").val())
        var totalAvoir = parseFloat($("#fact_total_avoir").val())
        var totalUtilisee = parseFloat($(this).val()) ;
        var totalRestant = 0 ;
  
        if($(this).val() == "")
        {
          $("#avoir_total_restant").val(totalTtc)
          return false ;
        }
  
        if(totalUtilisee > totalAvoir)
        {
          $.alert({
            title:"Message",
            content:"Le montant est trop grand. Veuillez entrer une valeur inférieue à "+totalAvoir,
            type:"red"
          }) ;
  
          $("#fact_avoir_use").val("") ;
          $("#avoir_total_restant").val(totalTtc) ;
  
          return false ;
  
        }else if(totalUtilisee <= 0 )
        {
          $.alert({
            title:"Message",
            content:"Montant négatif. Veuillez entrer une valeur supérieur à 0",
            type:"red"
          }) ;
  
          $("#fact_avoir_use").val("") ;
          $("#avoir_total_restant").val(totalTtc) ;
  
          return false ;
        }
  
        if(totalTtc <= 0 )
        {
          $.alert({
            title:"Message",
            content:"Veuillez ajouter des éléments",
            type:"orange"
          }) ;
  
          $("#fact_avoir_use").val(0) ;
          $("#avoir_total_restant").val(0) ;
  
          return false ;
        }
  
        if(totalTtc > totalUtilisee)
        {
          totalRestant = totalTtc - totalUtilisee ;
        }
        else
        {
          $.alert({
            title:"Message",
            content:"L'avoir utilisé ne doit pas dépasser le montant TTC",
            type:"blue"
          }) ;
  
          totalUtilisee = totalTtc ;
          totalRestant = 0 ;
          $("#fact_avoir_use").val(totalUtilisee)
        }
  
        $("#avoir_total_restant").val(totalRestant)
      })

    // FIN CALCUL AVOIR FACTURE
})